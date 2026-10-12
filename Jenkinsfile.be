pipeline {
    agent {
        docker {
            image 'golang:1.25-alpine'
        }
    }

    environment {
        PROJECT_REPO = '/repo/klinik-starter'
        DEPLOY_PATH = '/deploy'
    }

    stages {
        stage('Checkout') {
            steps {
                script {
                    sh "git clone -b haidi ${PROJECT_REPO} ."
                }
            }
        }

        stage('Vendor Dependencies') {
            steps {
                dir('be') {
                    sh 'go mod vendor'
                }
            }
        }

        stage('Test Gate') {
            steps {
                dir('be') {
                    sh '''
                        if [ -d "database/seeders/V1" ]; then
                          echo "Folder database/seeders/V1 tidak boleh ada. Gunakan lowercase v1."
                          exit 1
                        fi
                        if [ ! -d "database/seeders/v1" ]; then
                          echo "Folder database/seeders/v1 wajib ada."
                          exit 1
                        fi
                        go test ./test/config -run TestBackendTestFilesMustLiveUnderBeTest -count=1
                        go test ./test/config -run TestTestHookAPIMustNotBeUsedInProductionCode -count=1
                        go test ./... -count=1
                    '''
                }
            }
        }

        stage('Build Binary') {
            steps {
                dir('be') {
                    sh 'go build -o klinik_be .'
                }
            }
        }

        stage('Deploy') {
            agent { label 'built-in' }
            steps {
                script {
                    sh "cd ${DEPLOY_PATH} && docker compose up -d --build be_dev"
                }
            }
        }
    }

    post {
        always {
            cleanWs()
        }
    }
}
