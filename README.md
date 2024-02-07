# Local User Course Completion Report Plugin

```sh
# start container and wait for Apache to be running
docker-compose up 
```

Open [http://localhost:8080](http://localhost:8080) in your browser.


## Codechecker

```sh
# install deps
composer install
# run moodle codesniffer checks
composer check
```

## Automated Tests

```sh
# run unit tests
composer test
```

## PHP Error Log

```sh
docker exec -ti report_usercoursecompletions-web-1 tail -f /var/www/php_errors.log
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
moosh user-mod -n --password <password> <username>
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

- [Local plugins](https://moodledev.io/docs/apis/plugintypes/local)
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

