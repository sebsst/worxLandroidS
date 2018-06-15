#! /bin/bash

echo "Début d'installation des dépendances"

wget http://mosquitto.org/files/source/mosquitto-1.4.2.tar.gz
tar zxvf mosquitto-1.4.2.tar.gz

cd mosquitto-1.4.2
make
make install

cp mosquitto.conf /etc/mosquitto

echo "Fin installation des dépendances"
