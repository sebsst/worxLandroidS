#! /bin/bash

echo "Début d'installation des dépendances"
apt-key del 30993623

apt-key list > /var/www/html/plugins/worxLandroidS/keylist.php


sudo apt-get -y --purge remove mosquitto mosquitto-clients

wget http://repo.mosquitto.org/debian/mosquitto-repo.gpg.key
sudo apt-key add mosquitto-repo.gpg.key
rm mosquitto-repo.gpg.key
cd /etc/apt/sources.list.d/
sudo wget http://repo.mosquitto.org/debian/mosquitto-jessie.list
sudo apt-get -y update
sudo apt-get -y install mosquitto mosquitto-clients



echo "Fin installation des dépendances"
