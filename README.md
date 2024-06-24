# Laravel Learning Management System API

# Setup for Linux

1. Install php and pdo_mysql extension

```bash
sudo apt install php8.2 php8.2-mysql php8.2-dom
```

2. If there are many version of php and you want to run specific version

```bash
where php8.2
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

# Github codespace development mode vite

Changes made to vite.config.js to work
Change vite dev port if it didn't work
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
