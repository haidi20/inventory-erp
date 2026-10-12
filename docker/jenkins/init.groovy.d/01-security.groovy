import hudson.security.FullControlOnceLoggedInAuthorizationStrategy
import hudson.security.HudsonPrivateSecurityRealm
import jenkins.install.InstallState
import jenkins.model.Jenkins

def instance = Jenkins.get()
def adminUser = System.getenv('JENKINS_ADMIN_USER') ?: 'admin'
def adminPassword = System.getenv('JENKINS_ADMIN_PASSWORD') ?: 'qwerty123'

def realm = instance.getSecurityRealm()
if (!(realm instanceof HudsonPrivateSecurityRealm)) {
    realm = new HudsonPrivateSecurityRealm(false)
}

def adminExists = realm.allUsers.any { it.id == adminUser }
if (!adminExists) {
    realm.createAccount(adminUser, adminPassword)
}

instance.setSecurityRealm(realm)

def strategy = new FullControlOnceLoggedInAuthorizationStrategy()
strategy.setAllowAnonymousRead(false)
instance.setAuthorizationStrategy(strategy)
instance.setInstallState(InstallState.INITIAL_SETUP_COMPLETED)
instance.save()
