#!/bin/sh

php artisan config:clear
php artisan cache:clear
php artisan migrate
php artisan db:seed

echo " ----- Running test cases ------- "
php ./vendor/bin/phpunit ./tests/Unit
php ./vendor/bin/phpunit ./tests/Feature