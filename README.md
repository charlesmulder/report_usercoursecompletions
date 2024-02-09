# Report User Course Completion Plugin

An [admin report](https://moodledev.io/docs/apis/core/reportbuilder) plugin to view a user's course completion status, across all courses with [course completion](https://docs.moodle.org/403/en/Course_completion) enabled.

## Quickstart

```sh
# start container and wait for Apache to be running
docker-compose up 
```

Open [http://localhost:8080](http://localhost:8080) in your browser.

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
docker exec -ti report_usercoursecompletions-web-1 composer install
# run moodle codesniffer checks
docker exec -ti report_usercoursecompletions-web-1 composer check
```

## Behat Tests

```sh
# initialise moodle behat testing
docker exec -ti -u www-data:www-data -w /var/www/html report_usercoursecompletions-web-1 php admin/tool/behat/cli/init.php
# run plugin tests
docker exec -ti -u www-data:www-data -w /var/www/html report_usercoursecompletions-web-1 vendor/bin/behat --config /var/www/behatdata/behatrun/behat/behat.yml --tags=report_usercoursecompletions
```

## PHPUnit Tests

```sh
# initialise moodle unit testing
docker exec -ti -u www-data:www-data -w /var/www/html report_usercoursecompletions-web-1 php admin/tool/phpunit/cli/init.php
# run plugin tests
docker exec -ti -u www-data:www-data -w /var/www/html report_usercoursecompletions-web-1 vendor/bin/phpunit --test-suffix="_test.php" --testdox --colors=always report/usercoursecompletions/tests/unit
```

# References

- [Selenium Standalone Firefox](https://hub.docker.com/r/selenium/standalone-firefox)
