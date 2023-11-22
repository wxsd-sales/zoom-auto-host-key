ARG COMPOSER_VERSION="2"
ARG NODE_VERSION="18-bookworm"
ARG PHP_VERSION="8.2-fpm-bookworm"
########################################################################################################################


FROM composer:${COMPOSER_VERSION} as composer

WORKDIR /app

COPY composer.json composer.lock /app/
RUN composer install  \
    --ignore-platform-reqs \
    --no-ansi \
    --no-autoloader \
    --no-dev \
    --no-interaction \
    --no-scripts

COPY . /app/

RUN composer dump-autoload --optimize --classmap-authoritative


FROM node:${NODE_VERSION} as node

WORKDIR /app

COPY package.json package-lock.json /app/
RUN npm install

COPY --from=composer /app/vendor/tightenco/ziggy/dist /app/vendor/tightenco/ziggy/dist
COPY resources/css /app/resources/css
COPY resources/js /app/resources/js
COPY resources/markdown /app/resources/markdown
COPY vite.config.js tsconfig.json /app/

RUN npx vite build && npx vite build --ssr


FROM php:${PHP_VERSION} as php

WORKDIR /var/www/html

ENV APP_ENV=production
ENV TZ=UTC

RUN ln -snf "/usr/share/zoneinfo/$TZ" "/etc/localtime" && echo "$TZ" > "/etc/timezone"

RUN apt update

RUN apt install -y libzip-dev zip && docker-php-ext-install bcmath opcache pdo_mysql zip

RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" &&  \
    sed -i 's/^memory_limit =.*$/memory_limit = -1/g' "$PHP_INI_DIR/php.ini" && \
    sed -i 's/^max_execution_time =.*$/max_execution_time = 300/g' "$PHP_INI_DIR/php.ini" &&  \
    sed -i 's/^disable_functions =.*$/disable_functions = proc_open, popen, disk_free_space, diskfreespace, set_time_limit, leak, tmpfile, exec, system, shell_exec, passthru, show_source, system, phpinfo, pcntl_alarm, pcntl_fork, pcntl_waitpid, pcntl_wait, pcntl_wifexited, pcntl_wifstopped, pcntl_wifsignaled, pcntl_wexitstatus, pcntl_wtermsig, pcntl_wstopsig, pcntl_signal, pcntl_signal_dispatch, pcntl_get_last_error, pcntl_strerror, pcntl_sigprocmask, pcntl_sigwaitinfo, pcntl_sigtimedwait, pcntl_exec, pcntl_getpriority, pcntl_setpriority/g' "$PHP_INI_DIR/php.ini"

COPY --from=composer /app /var/www/html
COPY --from=node /app/public /var/www/html/public

RUN mkdir -p "/var/www/html/database/sqlite" && touch "/var/www/html/database/sqlite/app.sqlite3"

RUN chown -R www-data:www-data /var/www/html && chmod g+s /var/www/html

RUN apt install -y debian-keyring debian-archive-keyring apt-transport-https
RUN curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' | gpg --dearmor -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg
RUN curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' | tee /etc/apt/sources.list.d/caddy-stable.list
RUN apt update && apt install -y caddy

COPY /docker/zzz-docker.conf /usr/local/etc/php-fpm.d/zzz-docker.conf
COPY /docker/Caddyfile /etc/caddy/Caddyfile

RUN caddy validate --config /etc/caddy/Caddyfile --adapter caddyfile

RUN apt install -y supervisor

COPY /docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN mkdir -p "/var/log"

EXPOSE 80

COPY /docker/entrypoint.sh /usr/local/bin/entrypoint

RUN chmod +x /usr/local/bin/entrypoint

ENTRYPOINT  ["/usr/local/bin/entrypoint"]
