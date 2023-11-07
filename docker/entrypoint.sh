#!/usr/bin/env sh

set -eo pipefail

ENV_FILE="$1" && test -f "$ENV_FILE" && set -o allexport && source "$ENV_FILE" && set +o allexport

php /var/www/html/artisan telescope:publish && \
php /var/www/html/artisan config:cache && \
php /var/www/html/artisan event:cache && \
php /var/www/html/artisan route:cache && \
php /var/www/html/artisan view:cache

php /var/www/html/artisan storage:link --force
php /var/www/html/artisan migrate --force

/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
