FROM jenkins/jenkins:lts-jdk17

USER root

RUN apt-get update \
    && apt-get install -y --no-install-recommends curl git docker.io \
    && rm -rf /var/lib/apt/lists/*

RUN printf '%s\n' \
    workflow-aggregator \
    git \
    github \
    generic-webhook-trigger \
    configuration-as-code \
    docker-workflow \
    ws-cleanup \
    > /usr/share/jenkins/ref/plugins.txt \
    && jenkins-plugin-cli --plugin-file /usr/share/jenkins/ref/plugins.txt

COPY init.groovy.d/ /usr/share/jenkins/ref/init.groovy.d/

USER jenkins
