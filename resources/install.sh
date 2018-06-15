#! /bin/bash

echo "Début d'installation des dépendances"
sudo apt-get purge mosquitto
apt-get --purge remove mosquitto
mkdir mosquitto
cd mosquitto

wget http://repo.mosquitto.org/debian/mosquitto-repo.gpg.key
apt-key add mosquitto-repo.gpg.key

wget http://repo.mosquitto.org/debian/mosquitto-jessie.list
apt-get update
apt-get install mosquitto mosquitto-clients




echo "Fin installation des dépendances"
