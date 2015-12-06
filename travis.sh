#!/bin/bash

echo "DB_DRIVER = 'mysql'" > tests/_files/.env
echo "DB_HOST = '127.0.0.1'" >> tests/_files/.env
echo "DB_NAME = 'yii2_eloquent_test'" >> tests/_files/.env
echo "DB_USERNAME = 'travis'" >> tests/_files/.env
echo "DB_PASSWORD = ''" >> tests/_files/.env
