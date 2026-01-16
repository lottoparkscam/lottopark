#!/bin/bash

cd /var/www/review-apps-router/

python3 router.py release $1 httpd
python3 router.py release $1 mailhog
python3 router.py release $1 phpmyadmin
python3 router.py release $1 mysql

cd /var/www/$1

docker-compose -p $1 down --volumes
