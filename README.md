# PHP Event Booking
> A website that offers the possibility to create and book events.

![PHP Version][php-image]
![Apache Version][apache-image]
![Postgres Version][postgres-image]
![Docker Version][docker-image]
![Composer Version][composer-image]

This project contains a website created with HTML, JavaScript and PHP. It is hosted on an Apache Server with a PostgreSQL database, startable via docker. Created as a PHP lecture assignment at DHBW Stuttgart.

## Development Setup

### Dependencies

The projects dependencies are managed with composer. The composer.json file defines the used dependencies. If a dependency is referenced in the source folder (e.g. CSS or JavaScript files), the needed files of the composer dependency package needs to be copied from ```/vendor/<package>``` to ```/public/assets/<package>```.

To achieve this, every package is remove and copied to the src folder upon running ```composer install```. This is done via a script in ```composer.json```:
```
"scripts": {
  "post-install-cmd": [
    "rm -rf src/resources/assets/<package>",
    "cp -R vendor/<package> src/resouces/assets/<package>"
  ]
}
```

Thus, when running the command

```
composer install
```
all dependencies will be available to reference in ```src/resouces/assets/```.

### Docker

A Dockerfile and docker-compose is available for running the apache server and PostgreSQL database in a docker container. In order to set this up, the following two steps are needed:
1. Building the image
```
docker build -t event-booking:2020 --build-arg TARGET=<target> .
```

IMPORTANT: the TARGET argument must be corresponding to your OS:
* Arch Linux: ```/srv/http```
* Debian: ```/var/www/html```

2. Running docker-compose
```
docker-compose up -d
```

IMPORTANT: A .env file needs to be available in the same directory as the .yml file. It needs to contain the same TARGET argument as in 1. during build.

## Release History

* 0.1
    * t.b.a.
* 0.2
    * t.b.a.
* 0.3
    * t.b.a.

<!-- Markdown link & img dfn's -->
[php-image]: https://img.shields.io/badge/php-v7.4.3-brightgreen?style=flat-square&logo=php
[composer-image]: https://img.shields.io/badge/composer-v1.9.3-brightgreen?style=flat-square&logo=composer
[bootstrap-image]: https://img.shields.io/badge/bootstrap-v4.3.1-brightgreen?style=flat-square&logo=bootstrap
[postgres-image]: https://img.shields.io/badge/postgres-v12.2-brightgreen?style=flat-square&logo=postgresql
[docker-image]: https://img.shields.io/badge/docker-v19.03.6+-brightgreen?style=flat-square&logo=docker
[apache-image]: https://img.shields.io/badge/apache-v2.4.41+-brightgreen?style=flat-square&logo=apache
