#! /bin/bash

echo "Début d'installation des dépendances"
sudo apt-get -y update
sudo apt-get install mosquitto mosquitto-clients
echo "Fin installation des dépendances"
