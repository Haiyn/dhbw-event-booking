composer install
docker build -t event-booking-apache:2020 .
cd ./socket || exit
docker build -t event-booking-websocket:2020 .
docker-compose up -d