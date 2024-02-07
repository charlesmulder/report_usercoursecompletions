FROM "moodlehq/moodle-php-apache:8.3"

ARG MOODLE_VERSION
ARG MOODLE_PLUGIN_RELATIVE_PATH
ARG MOODLE_CODECHECKER_VERSION

RUN apt update && apt install -y wget vim default-mysql-client cron

# copy composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN chown -R www-data:www-data /var/www /usr/local/src; \
  usermod -u 1000 www-data; \
  groupmod -g 1000 www-data

# make sure apache owns its home dir
RUN chown -R www-data:www-data /var/www

# install moosh
RUN curl -Lo /tmp/moosh.zip https://moodle.org/plugins/download.php/29895/moosh_moodle42_2023090700.zip;\
	unzip /tmp/moosh.zip -d /opt;\
	ln -s /opt/moosh/moosh.php /usr/local/bin/moosh;\
	chmod +x /usr/local/bin/moosh

# install dockerize
RUN curl -sfL $(curl -s https://api.github.com/repos/powerman/dockerize/releases/latest | grep -i /dockerize-$(uname -s)-$(uname -m)\" | cut -d\" -f4) | install /dev/stdin /usr/local/bin/dockerize

COPY etc/php/php.ini /usr/local/etc/php/php.ini

USER www-data

WORKDIR /var/www/html

# download moodle
RUN git clone --depth 1 --branch ${MOODLE_VERSION} https://github.com/moodle/moodle.git /var/www/html
COPY config.php /var/www/html/config.php

RUN mkdir -p /var/www/moodledata

RUN ln -s /usr/local/src/ /var/www/html/${MOODLE_PLUGIN_RELATIVE_PATH}

WORKDIR /usr/local/src

USER root

# cron
RUN echo '* * * * * /usr/local/bin/php /var/www/html/admin/cli/cron.php > /var/log/apache2/cron.log 2>&1' > /etc/cron.d/moodle;\
    crontab -u www-data /etc/cron.d/moodle

# use dockerize to wait for db server
CMD dockerize -timeout 300s -wait tcp://db:3306 && apache2-foreground
