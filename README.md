# PHP Event Booking
> A website that offers the possibility to create and book events.

![PHP Version][php-image]
![Apache Version][apache-image]
![Postgres Version][postgres-image]
![Docker Version][docker-image]
![Composer Version][composer-image]

This project contains a website created with HTML, JavaScript and PHP. It is hosted on an Apache Server with a
PostgreSQL database, startable via docker. Created as a PHP lecture assignment at DHBW Stuttgart.

## Development Setup

### A shell script (`run.sh`) is supplied to automatically do steps 1. and 2.
It is located in the project root. Be sure to make it executable with `chmod +x ./run.sh` beforehand.
You'll need Composer and Docker to run this script, as defined by the above versioning tags.

If you want to use this script, run it and skip ahead to 3. Configuration.

If you'd like to do it manually, please follow steps 1. trough 3.:

### 1. Dependencies

![Composer Version][composer-image] needed

The projects dependencies are managed with composer. The composer.json file defines the used dependencies.
All needed dependency files are copied from `/vendor` to
`/src/resources/assets` in order for them to be available in the webserver container.

To automatically make all dependencies available on the webserver, run

```
composer install
```


### 2. Docker

![Docker Version][docker-image] needed

A Dockerfile and docker-compose is available for running the PostgreSQL database, Apache, SMTP and Websocket Server
in a docker container. In order to set this up, the following steps are needed:
1. Build the apache image
```
docker build -t event-booking-apache:2020 .
```

2. Change directory to `/socket`

3. Build the websocket image
```
docker build -t event-booking-websocket:2020 .
```

4. Run docker-compose
```
docker-compose up -d
```

### 3. Configuration

**All configuration files are set up to work with the existing project as is.** However, depending on your needs you
might want to adjust some things. The config.ini(.php) files are the way to do this. There are two config files:

#### 3.1 Website

The configuration for the website can be found in `/src/config.ini.php`. It defines the connection parameters to the
PostgreSQL database and the SMTP Server as well as global settings such as Email sending or a login timeout limit.
Notable settings are:
  * EMAIL_ENABLED

    This settings controls whether emails will be sent or not. Further Email configuration requires this setting to be true.

  * PHP_MAILER_ENABLED

    This controls whether or not the PHPMailer framework should be used to send Emails. **SMTP Emails currently only work with this set to true.**

  * LOGIN_TIMEOUT

    The login timeout is the time in seconds that controls how long a user must go without activity (page reload or page switch) before they are logged out.


#### 3.2 Websocket Server

To configure the websocket server, see the settings in `/socket/config.ini`.
Notable settings are:
  * TIMEOUT

    **This is a value in seconds. The higher it is, the more the server will wait between each listen loop.**
    If your CPU is melting the heatsink, you might want to increase this number. Please note that the higher the timeout
    is set, the more something can break on client side. Messages might not be sent or connections might be faulty.
  * TRACE_ENABLED

    Turning this on and of lets you see Trace logs in the server, such as message payloads or verbose
    process progress.

## PHPStorm

### Docker

#### Dockerfile

Run Dockerfile to create image for docker-compose.yml.

1. Create new "Dockerfile" configuration
2. Context folder: "."
3. Image tag: "event-booking:2020"

#### Docker-compose

Run database and webserver.

1. Create new "Docker-compose" configuration
2. Compose file(s): "./docker-compose.yml;"

### Debugging

Debugging via xdebug, all required packages are installed in the Dockerfile.

Installation:
1. Create "PHP Remote Debug" configuration
2. Install [browser extension](https://www.jetbrains.com/help/phpstorm/2019.3/browser-debugging-extensions.html?utm_campaign=PS&utm_content=2019.3&utm_medium=link&utm_source=product)
3. Run new created debug configuration
4. Open installed browser extension and enable debug

## Release History

* **Version 0.1** (12.03.2020)
  * Implemented:
    * Docker Setup for webserver and Database
    * Website MVC structure with router and autoloader
    * User Registration
    * Event Create
    * Event Overview
    * Event Detail View


* **Version 0.2** (30.03.2020)
  * Implemented:
    * User Login
    * User Sessions
    * Email Sending  
    * Event Attending
    * Event Editing
    * Event Cancelling
    * Event Inviting
    * Error Pages
    * Header and Footer
  * Fixed:
    * Root URL throws error
    * Footer is not always at the bottom


* **Version 0.3** (20.04.2020)
  * Implemented:
    * Websocket Real-Time Chat
    * Event Filtering
    * Logout
    * Imprint
  * Fixed:
    * Insecure password encrypting
    * Event creator can book and unbook own events
    * Email verification issue fixed
    * Login time of session does not refresh on user activity


* **Version 0.4** (26.05.2020)
  * Implemented:
    * Home page
    * Profile
    * Profile Edit
    * Password Reset
  * Fixed:
    * Composer post-install does not work on windows
    * Booking user feedback showed wrong information
    * Current page was not correctly highlighted in the header
    * Inviting a non-existent user does not redirect back to the edit page
    * User did not have no verify their email after changing it
    * Chat partner shown as offline even when online
    * Creator can invite themselves to their own event
    * Bootstrap throws console errors
    * Chat homepage does not show error messages

<!-- Markdown link & img dfn's -->
[php-image]: https://img.shields.io/badge/php-v7.4.3-brightgreen?style=flat-square&logo=php
[composer-image]: https://img.shields.io/badge/composer-v1.9.3-brightgreen?style=flat-square&logo=composer
[bootstrap-image]: https://img.shields.io/badge/bootstrap-v4.3.1-brightgreen?style=flat-square&logo=bootstrap
[postgres-image]: https://img.shields.io/badge/postgres-v12.2-brightgreen?style=flat-square&logo=postgresql
[docker-image]: https://img.shields.io/badge/docker-v19.03.6+-brightgreen?style=flat-square&logo=docker
[apache-image]: https://img.shields.io/badge/apache-v2.4.41+-brightgreen?style=flat-square&logo=apache
