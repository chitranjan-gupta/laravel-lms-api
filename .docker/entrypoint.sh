#!/bin/bash

php artisan migrate
php artisan db:seed

apache2-foreground