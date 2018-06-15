#! /bin/bash

echo "Début d'installation des dépendances"

sudo apt-get -y purge mosquitto
sudo apt-get -y purge mosquitto  mmosquitto-clients


wget http://repo.mosquitto.org/debian/mosquitto-repo.gpg.key
sudo apt-key add mosquitto-repo.gpg.key && rm mosquitto-repo.gpg.key


sudo wget http://repo.mosquitto.org/debian/mosquitto-jessie.list -O /etc/apt/sources.list.d/mosquitto-jessie.list
sudo apt-get update
sudo apt-get install -y mosquitto mosquitto-clients


echo "Fin installation des dépendances"
