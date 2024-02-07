# Report User Course Completion Plugin

An [admin report](https://moodledev.io/docs/apis/core/reportbuilder) plugin to view a user's course completion status, across all courses with [course completion](https://docs.moodle.org/403/en/Course_completion) enabled.

## Quickstart

```sh
# start container and wait for Apache to be running
docker-compose up 
```

Open [http://localhost:8080](http://localhost:8080) in your browser.

## [Generate test site data](https://docs.moodle.org/403/en/Test_site_generator)

```sh
php admin/tool/generator/cli/maketestsite.php --size=XS
```

## Logs

```sh
# cron
docker exec -ti report_usercoursecompletions-web-1 tail -f /var/log/apache2/cron.log
# php 
docker exec -ti report_usercoursecompletions-web-1 tail -f /var/www/php_errors.log
# apache
docker logs -f report_usercoursecompletions-web-1
```

## Moodle Code Sniffer

```sh
# install deps
docker exec -ti -u www-data:www-data report_usercoursecompletions-web-1 composer install
# run moodle codesniffer checks
docker exec -ti -u www-data:www-data report_usercoursecompletions-web-1 composer check
```

## PHPUnit Tests

```sh
# run unit tests
composer test
```
