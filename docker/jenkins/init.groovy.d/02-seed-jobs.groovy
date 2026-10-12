import jenkins.model.Jenkins
import org.jenkinsci.plugins.workflow.cps.CpsFlowDefinition
import org.jenkinsci.plugins.workflow.job.WorkflowJob


def buildJobScript(String containerName, String branchName, String webhookToken) {
    return """
pipeline {
  agent any
  options { disableConcurrentBuilds(); timestamps() }
  triggers {
    GenericTrigger(
      genericVariables: [
        [key: 'ref', value: '\$.ref'],
        [key: 'after', value: '\$.after']
      ],
      causeString: 'Triggered by push: \$ref @ \$after',
      token: '${webhookToken}',
      printContributedVariables: true,
      printPostContent: false,
      regexpFilterText: '\$ref',
      regexpFilterExpression: '^refs/heads/${branchName}\$'
    )
  }
  stages {
    stage('Preflight') {
      steps {
        sh '''
          set -e
          docker inspect ${containerName} >/dev/null
          docker inspect --format='{{.State.Running}}' ${containerName} | grep -q true
        '''
      }
    }
    stage('Install Dependencies') {
      steps {
        sh 'docker exec -w /var/www/html ${containerName} sh -lc "composer install --no-interaction --prefer-dist"'
      }
    }
    stage('Run Tests') {
      steps {
        sh 'docker exec -w /var/www/html ${containerName} sh -lc "php artisan test"'
      }
    }
    stage('Reload App') {
      steps {
        sh 'docker restart ${containerName}'
      }
    }
  }
}
""".stripIndent()
}

def instance = Jenkins.get()
def workspaceRoot = System.getenv('JENKINS_WORKSPACE_ROOT') ?: '/workspace/klinik-starter'
def devBranch = System.getenv('JENKINS_DEV_BRANCH') ?: 'dev'
def prodBranch = System.getenv('JENKINS_PROD_BRANCH') ?: 'main'
def devToken = System.getenv('JENKINS_DEV_WEBHOOK_TOKEN') ?: 'app-dev-delivery'
def prodToken = System.getenv('JENKINS_PROD_WEBHOOK_TOKEN') ?: 'app-prod-delivery'

def legacyJobs = ['fe-dev-delivery', 'be-dev-delivery', 'fe-prod-delivery', 'be-prod-delivery']
legacyJobs.each { name ->
    def oldJob = instance.getItem(name)
    if (oldJob != null) {
        oldJob.delete()
    }
}

def jobConfigs = [
    [
        name       : 'app-dev-delivery',
        description: 'CI/CD aplikasi Laravel (dev). Trigger: push branch dev.',
        script     : buildJobScript('klinik_app_dev', devBranch, devToken),
        token      : devToken,
    ],
    [
        name       : 'app-prod-delivery',
        description: 'CI/CD aplikasi Laravel (prod). Trigger: push branch main.',
        script     : buildJobScript('klinik_app_prod', prodBranch, prodToken),
        token      : prodToken,
    ],
]

jobConfigs.each { jobConfig ->
    WorkflowJob job = instance.getItem(jobConfig.name)
    if (job == null) {
        job = instance.createProject(WorkflowJob, jobConfig.name)
    }

    job.setDescription(
        """${jobConfig.description}
Workspace root: ${workspaceRoot}
Webhook endpoint: /cicd/generic-webhook-trigger/invoke?token=${jobConfig.token}
""".stripIndent()
    )
    job.setDefinition(new CpsFlowDefinition(jobConfig.script, true))
    job.save()
}

instance.save()
