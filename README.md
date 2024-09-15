# Laravel Learning Management System API

## Requirements
1. PHP 8.2
2. Mysql
3. Composer
4. Nodejs

## Setup for Ubuntu Linux

1. Fetch the updates from linux repo
```sh
sudo apt update
```
2. Install the latest updates
```sh
sudo apt upgrade -y
```
3. Install this 
```sh
sudo apt install software-properties-common
```
4. Add this repository
```sh
sudo add-apt-repository ppa:ondrej/php
```
5. Update package list again:
```sh
sudo apt update
```
6. Install php and pdo_mysql extension
```sh
sudo apt install php8.2 php8.2-mysql php8.2-dom
```
7. Install the php and nodejs libraries
```sh
composer install
npm install
```
8. Add .env file
```
FRONTEND_APP_URL = frontend url required for CORS(cross-origin)
APP_URL = backend url required for building the asset using vite

see .env.example for more
```

## Build

1. Build frontend asset if you are using web of laravel
```sh
npm run build
```
2. Use Apache or Nginx Server

## Dev

### Github codespace development mode vite
-  Changes made to vite.config.js to work
1. Change vite dev port if it didn't work
```js
server:{
        port: 4444,
        hmr:{
            host: 'silver-orbit-pg95qvq9v95f56g-4444.app.github.dev', //Exposed public github port url
            port: 4444,
            clientPort: 443 //Connect to https
        }
}
```
2. Build frontend asset if you are using web of laravel
```sh
npm run dev
```
3. If there are many version of php and you want to run specific version
```bash
which php8.2
/usr/bin/php8.2 artisan serve
```

## List the php.ini file

```bash
php --ini
```

## List the php extensions

```bash
php -m
```

## About PHP

### PHP different types and categories of PHP:

**1. PHP Versions:**

**_a. PHP Core Versions:_**

-   PHP 5.x: Released in 2004, this version introduced significant improvements over PHP 4, including better object-oriented programming (OOP) support, improved performance, and the introduction of the mysqli extension.
-   PHP 7.x: Introduced in December 2015, PHP 7 brought major performance improvements and new features like scalar type declarations, return type declarations, and the null coalescing operator (??).
-   PHP 8.x: The latest major version, PHP 8.0 was released in November 2020, bringing new features like the Just-In-Time (JIT) compiler, union types, attributes (annotations), and named arguments. PHP 8.1 and PHP 8.2 introduced further enhancements and new features.

**2. PHP Implementations:**

**_a. PHP-FPM (FastCGI Process Manager):_**

-   A PHP implementation designed to handle high traffic loads and provide better performance and process management compared to traditional CGI methods. It’s widely used with web servers like Nginx.
-   PHP-CGI (Common Gateway Interface):
    An older method for running PHP scripts. It spawns a new PHP process for each request, which can be less efficient than PHP-FPM.
-   PHP CLI (Command-Line Interface):
    This allows PHP scripts to be executed from the command line or terminal. It’s useful for running scripts for tasks such as cron jobs, scripting, and administrative tasks.
-   PHP Embed:
    Allows PHP code to be embedded within other applications or web servers. It’s not commonly used, but some applications and environments support this method.

**3. PHP Configurations:**

-   Default PHP:
    The standard installation of PHP with default settings. Suitable for basic use and development environments.
-   Custom PHP:
    PHP installations that are customized with specific settings or compiled with additional options and modules for particular needs.

**4. PHP Extensions:**

-   Core Extensions:
    Built into the PHP core and always available. Examples include standard, pdo, and filter.
-   Standard Extensions:
    Commonly included extensions like mysqli, curl, mbstring, and xml.
-   PECL Extensions:
    Extensions available through the PHP Extension Community Library (PECL). Examples include xdebug (for debugging) and redis (for interfacing with Redis).
-   Custom Extensions:
    Extensions written by developers to add functionality not provided by standard or PECL extensions.
