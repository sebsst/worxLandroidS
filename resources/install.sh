#! /bin/bash

echo "Début d'installation des dépendances"
sudo apt-get -y install build-essential python quilt devscripts python-setuptools python3 libssl-dev cmake libc-ares-dev uuid-dev daemon

wget https://libwebsockets.org/git/libwebsockets/snapshot/libwebsockets-1.4-chrome43-firefox-36.tar.gz
tar zxvf libwebsockets-1.4-chrome43-firefox-36.tar.gz
cd libwebsockets-1.4-chrome43-firefox-36
mkdir build
cd build
sudo apt-get install zlibc zlib1g zlib1g-dev
cmake ..
sudo make install
sudo ldconfig
cd

wget http://mosquitto.org/files/source/mosquitto-1.4.2.tar.gz
tar zxvf mosquitto-1.4.2.tar.gz
cd mosquitto-1.4.2

make
sudo make install
sudo cp mosquitto.conf /etc/mosquitto

echo "Fin installation des dépendances"
