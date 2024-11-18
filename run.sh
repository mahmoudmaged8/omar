#!/bin/sh

# don't remove this commands please ------------
# php artisan migrate:fresh --seed
chown -R www-data:www-data *
#chown -R www-data:www-data /var/www/html/public/files
#chown -R www-data:www-data /var/www/html/storag
php artisan migrate --force
#php artisan storage:link 
php artisan queue:work database --sleep=10 --daemon --quiet --queue="default" &
php artisan optimize:clear
php artisan cache:clear
php artisan route:cache
php artisan config:clear
php artisan view:clear
php artisan config:cache
#php artisan app:install
#php artisan shield:super-admin
# till here ------------------------------------

# don't remove this commands please ------------
service nginx start
php-fpm
#/usr/bin/supervisord -c /etc/supervisord.conf
# till here ------------------------------------
