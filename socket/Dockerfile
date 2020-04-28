FROM php:7.4-cli

COPY . /usr/src/websocket
WORKDIR /usr/src/websocket

RUN docker-php-ext-install sockets

CMD [ "php", "./WebsocketServer.php" ]