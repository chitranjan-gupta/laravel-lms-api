#!/bin/bash

php artisan migrate
php artisan db:seed
npm run build

php artisan serve --host=0.0.0.0 --port=8000