#! /bin/bash

echo "Début d'installation des dépendances"
sudo service mosquitto restart
mosquitto -h | grep version > /var/www/html/plugins/worxLandroidS/versionmosquitto.pp

echo "Fin installation des dépendances"
