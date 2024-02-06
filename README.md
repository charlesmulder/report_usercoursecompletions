# Moodle Plugin Template

> If this is your first time building a Moodle plugin, please make sure to complete the free [Moodle Plugin Development Basics](https://learn.moodle.org/course/view.php?id=26428) course available on [Learn Moodle](https://learn.moodle.org) before diving in. 

## Getting Started

Create a new repo and select this repo as the template.

Open `composer.json` and:

- change the `name` property to the desired name of your plugin folder in Moodle, but prefixed with `fioru-software/`  eg. `fioru-software/veri`
- change the `type` property to match one of the types listed [here](https://github.com/composer/installers/blob/main/src/Composer/Installers/MoodleInstaller.php), prefixed with `moodle-` eg. `moodle-theme` 

Ensure the following is in your `/etc/hosts` file.

```hosts
127.0.0.1   moodle.veri.local
```

Ensure the database is up and running

```sh
docker-compose up -d db
docker logs -f moodle-plugin_template_db_1
```

Set the `MOODLE_PLUGIN_RELATIVE_PATH` to the path of the [Moodle module type](https://docs.moodle.org/dev/Plugin_types) appended with your plugin name. 

Build the Moodle image.

```sh
# no trailing slash
MOODLE_PLUGIN_RELATIVE_PATH=local/helloworld docker-compose build web
```

Run Moodle with your plugin mounted inside.

```sh 
docker-compose up
```

Open [http://moodle.veri.local:8080](http://moodle.veri.local:8080) in your browser.


## Codechecker

Download `moodle-local-codechecker` to an `$ABS_PATH` outside this project folder.

```sh
git clone --depth=1 git@github.com:moodlehq/moodle-local_codechecker.git
```

```sh
composer install
./vendor/bin/phpcs --config-set installed_paths $ABS_PATH/moodle-local_codechecker
./vendor/bin/phpcs -i
composer check
composer fix
```

## Automated Tests

### Moodle Environment

```sh
# up container
docker-compose up -d
# wait for MySQL to be ready
docker exec -t moodle-plugin_template_web_1 dockerize -timeout 300s -wait tcp://db:3306
# initialise moodle unit testing
docker exec -t -w /var/www/html moodle-plugin_template_web_1 php admin/tool/phpunit/cli/init.php
# run your plugin tests
docker exec -w /var/www/html  moodle-plugin_template_web_1 vendor/bin/phpunit --test-suffix="_test.php" --testdox --colors=always local/example/tests
```

### PHP Environment

```sh
composer test
```

## Moodle Administration

```sh
# shell into moodle web container
docker exec -ti -u www-data moodle-plugin_template_web_1 bash
```
### Users

```sh
# list users
moosh user-list
# change user password
moosh user-mod --email <email> --password <password> <username>
```

### Plugins

```sh
# list plugins
moosh plugin-list
# install plugins
moosh plugin-install mod_questionnaire
# install themes
moosh plugin-install theme_moove
```

## Release a new version

Before releasing a new version, make sure to get your changes merged into master and push to origin as per usual.

```sh
# list previously tagged releases
git tag -l
# create a new tag
git tag -a v1.1.3
# release the tag
git push origin v1.1.3
```

## Resources

### Moodle

- [Writing PHPUnit tests in Moodle](https://docs.moodle.org/dev/Writing_PHPUnit_tests)
- [Coding Style Guidelines](https://docs.moodle.org/dev/Coding_style)
- [Developer docs](https://docs.moodle.org/dev/Main_Page)
- [Core API's](https://docs.moodle.org/dev/Core_APIs)
- [How do I install a package to a custom path for my framework?](https://getcomposer.org/doc/faqs/how-do-i-install-a-package-to-a-custom-path-for-my-framework.md)
- [Core javascript modules](https://docs.moodle.org/dev/Useful_core_Javascript_modules)
- [Plugin files](https://docs.moodle.org/dev/Plugin_files)
- [Moosh](https://moosh-online.com/)
- [Non-core code from Moodle HQ](https://github.com/moodlehq/)
- [Core Moodle, and hacked projects used in core ](https://github.com/moodle)

### Deployment

- [Creating Multi-tenant Moodle service on Kubernetes using Operator Pattern](https://itnext.io/creating-multi-tenant-moodle-service-on-kubernetes-using-operator-pattern-a4fd418d47ad)
