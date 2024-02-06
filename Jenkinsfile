void setBuildStatus(String message, String state, String repo ) {
  step([
      $class: "GitHubCommitStatusSetter",
      reposSource: [$class: "ManuallyEnteredRepositorySource", url: "https://github.com/$repo"],
      contextSource: [$class: "ManuallyEnteredCommitContextSource", context: "ci/jenkins/build-status"],
      errorHandlers: [[$class: "ChangingBuildStatusErrorHandler", result: "UNSTABLE"]],
      statusResultSource: [ $class: "ConditionalStatusResultSource", results: [[$class: "AnyBuildResult", message: message, state: state]] ]
  ]);
}

pipeline {

    agent {
        kubernetes {
            inheritFrom 'jenkins-agent'
            yamlFile 'KubernetesPod.yaml'
        }
    }

    environment {
        PROJECT_NAME = "moodle-plugin_template"
        REPO_NAME = "fioru-software/$PROJECT_NAME"
        GITHUB_API_URL = "https://api.github.com/repos/$REPO_NAME"
        GITHUB_TOKEN = credentials('jenkins-github-personal-access-token')
        GIT_COMMIT = sh(script: "git log -1 --format=%H", returnStdout:true).trim()
        GCLOUD_KEYFILE = credentials('jenkins-gcloud-keyfile')
        CONTAINER_NAME = 'moodle-plugin_template_web_1'
        MOODLE_ABSOLUTE_PATH = '/var/www/html'
        MOODLE_PLUGIN_RELATIVE_PATH = 'local/example'
    }

    stages {

        stage('Build') {

            steps {
                setBuildStatus("Pending", "PENDING", env.REPO_NAME)
                container('gcloud') {
                    script {
                        sh 'gcloud auth activate-service-account jenkins-agent@veri-cluster.iam.gserviceaccount.com  --key-file=${GCLOUD_KEYFILE}'
                        env.GCLOUD_TOKEN = sh(script: "gcloud auth print-access-token", returnStdout: true).trim()
                    }
                }
                container('git') {
                    script {
                        sh 'git clone https://github.com/moodlehq/moodle-local_codechecker.git ../moodle-local_codechecker'
                    }

                }
                container('composer') {
                    script {
                        sh 'composer config --global --auth github-oauth.github.com ${GITHUB_TOKEN}'
                        sh 'composer update'
                        sh './vendor/bin/phpcs --config-set installed_paths $(pwd)/../moodle-local_codechecker'
                        sh 'composer all'	
                    }
                }
                container('docker-compose') {
                    script {
                        sh 'docker login -u oauth2accesstoken -p $GCLOUD_TOKEN https://eu.gcr.io'
                        sh 'docker-compose up -d --build'
                        sh 'docker exec -t $CONTAINER_NAME dockerize -timeout 300s -wait tcp://localhost:80'
                        sh 'docker cp ./ $CONTAINER_NAME:/usr/local/src/'
                        sh 'docker exec -t $CONTAINER_NAME dockerize -timeout 300s -wait tcp://db:3306'
                        sh 'docker exec -t -w $MOODLE_ABSOLUTE_PATH $CONTAINER_NAME php admin/tool/phpunit/cli/init.php'
                        sh 'docker exec -t -w $MOODLE_ABSOLUTE_PATH $CONTAINER_NAME vendor/bin/phpunit --test-suffix="_test.php" --testdox --colors=always local/example/tests'
                        sh 'docker-compose down'
                    }
                }
            }
        }
    }
    post {
        success {
            setBuildStatus("Success", "SUCCESS", env.REPO_NAME)
        }
        failure {
            setBuildStatus("Failure", "FAILURE", env.REPO_NAME)
        }
    }
}
