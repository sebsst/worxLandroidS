#! /bin/bash

echo "Début d'installation des dépendances"

sudo apt-get -y --purge remove mosquitto
wget http://repo.mosquitto.org/debian/mosquitto-repo.gpg.key
sudo apt-key add mosquitto-repo.gpg.key
rm mosquitto-repo.gpg.key
cd /etc/apt/sources.list.d/
sudo wget http://repo.mosquitto.org/debian/mosquitto-jessie.list
sudo apt-get update
sudo apt-get install mosquitto mosquitto-clients



echo "Fin installation des dépendances"
