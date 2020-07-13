FROM ubuntu:16.04
MAINTAINER Jason Burns <jason.burns@126interactive.com>

RUN apt-get update \
        && apt-get install -yq \
        acl \
        apache2 \
        apt-transport-https \
        cron \
        curl \
        git \
        git-core \
        libapache2-mod-php7.0 \
        libsasl2-dev \
        nano \
        nginx \
        openssl \
        wget \
        varnish \
        php7.0 \
        php-curl \
        php-gd \
        php-imagick \
        php7.0-json \
        php-mbstring \
        php-mcrypt \
        php-mongodb \
        php-mysql \
        php-odbc \
        php-dev \
        php-cli \
        php-http-request \
        php-soap \
        php-pear \
        php7.0-zip \
        && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install and Configure New Relic
RUN wget -O - https://download.newrelic.com/548C16BF.gpg | apt-key add - \
    && echo "deb http://apt.newrelic.com/debian/ newrelic non-free" > /etc/apt/sources.list.d/newrelic.list \
    && apt-get update \
    && echo newrelic-php5 newrelic-php5/application-name string "api.eendorsements.com" | debconf-set-selections \
    && echo newrelic-php5 newrelic-php5/license-key string "a3e4b57556ffd8da9ac92b3005ac0f1ad43e684f" | debconf-set-selections \
    && apt-get install -yq newrelic-php5

# Configure Apache
RUN a2enmod headers \
    && a2enmod rewrite \
    && a2enmod ssl

ADD etc/apache/vhost.conf /etc/apache2/sites-available/000-default.conf
ADD etc/apache/ports.conf /etc/apache2/ports.conf
RUN rm -rf /var/www/html/*
ADD . /var/www/html/

ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_PID_FILE /var/run/apache2.pid
ENV APACHE_RUN_DIR /var/run/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2

RUN usermod -u 1000 www-data

# Configure PHP for video uploads
RUN sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 20M/' /etc/php/7.0/apache2/php.ini
RUN sed -i 's/post_max_size = 8M/post_max_size = 20M/' /etc/php/7.0/apache2/php.ini

# Configure Varnish
ADD etc/varnish/default.vcl /etc/varnish/default.vcl
ADD etc/varnish/varnish /etc/default/varnish

# Configure Nginx
Add etc/nginx/default /etc/nginx/sites-enabled/default
Add etc/nginx/nginx.conf /etc/nginx/nginx.conf

# Configure Cron
RUN crontab /var/www/html/etc/cron.d/crons
RUN touch /var/log/cron.log
# Fixes Debian/Docker CRON Bug
ADD etc/pam.d/cron /etc/pam.d/cron

# Define working directory.
WORKDIR /var/www/html

# Install composer deps
RUN composer install --no-scripts --no-suggest

# Update ownship
RUN chown -R www-data:www-data /var/www/html

# Make our run shell executable
RUN chmod a+x etc/symfonize.sh

# Open up ports to traffic between host and container
EXPOSE 80 443

CMD ["etc/symfonize.sh"]
