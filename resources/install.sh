#! /bin/bash

echo "Début d'installation des dépendances"
sudo apt-get -y purge mosquitto
sudo apt-get --purge remove mosquitto
cd mosquitto

wget http://repo.mosquitto.org/debian/mosquitto-repo.gpg.key
apt-key add mosquitto-repo.gpg.key

wget http://repo.mosquitto.org/debian/mosquitto-jessie.list
apt-get update
apt-get install mosquitto mosquitto-clients




echo "Fin installation des dépendances"
