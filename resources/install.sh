#! /bin/bash

echo "Début d'installation des dépendances"
wget http://repo.mosquitto.org/debian/mosquitto-repo.gpg.key
apt-key add mosquitto-repo.gpg.key
cd /etc/apt/sources.list.d/
wget http://repo.mosquitto.org/debian/mosquitto-jessie.list
sudo apt-get update
apt-get -y install mosquitto
apt-get -y install mosquitto mosquitto-clients




echo "Fin installation des dépendances"
