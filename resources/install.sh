#! /bin/bash

echo "Début d'installation des dépendances"

touch /tmp/worxLandroidS_dep
echo 0 > /tmp/worxLandroidS_dep
apt-get -y install lsb-release php-pear
archi=`lscpu | grep Architecture | awk '{ print $2 }'`

if [ "$archi" == "x86_64" ]; then
if [ `lsb_release -i -s` == "Debian" ]; then
  wget http://repo.mosquitto.org/debian/mosquitto-repo.gpg.key
  apt-key add mosquitto-repo.gpg.key
  cd /etc/apt/sources.list.d/
  if [ `lsb_release -c -s` == "jessie" ]; then
    wget http://repo.mosquitto.org/debian/mosquitto-jessie.list
    rm /etc/apt/sources.list.d/mosquitto-jessie.list
    cp -r mosquitto-jessie.list /etc/apt/sources.list.d/mosquitto-jessie.list
  fi
  if [ `lsb_release -c -s` == "stretch" ]; then
    wget http://repo.mosquitto.org/debian/mosquitto-stretch.list
    rm /etc/apt/sources.list.d/mosquitto-stretch.list
    cp -r mosquitto-stretch.list /etc/apt/sources.list.d/mosquitto-stretch.list
  fi
fi
fi
echo 10 > /tmp/worxLandroidS_dep

apt-get update
echo 30 > /tmp/worxLandroidS_dep
apt-get -y install mosquitto mosquitto-clients libmosquitto-dev

#si version est toujours 1.3 alors on essaye de compiler une version plus récente
mosquitto -h | grep "version"
version=`mosquitto -h | grep "version 1.3"`
if [ -n "$version" ]; then
 if [ `lsb_release -i -s` == "Debian" ]; then

# if [ `lsb_release -i -s` == "Raspian" ]; then

#   if [ `lsb_release -c -s` == "jessie" ]; then

#   #  sudo apt-get -y install build-essential python quilt devscripts python-setuptools python3 libssl-dev cmake libc-ares-dev uuid-dev daemon
#     apt-get -y purge mosquitto
     echo "La version de mosquitto n'est pas compatible. tentative d'installation d'une version plus récente"
     sudo apt-get -y install cmake libssl1.0-dev 
     sudo apt-get -y install libwebsockets-dev uuid-dev
     cd /tmp
     wget http://mosquitto.org/files/source/mosquitto-1.5.tar.gz
     tar xavf mosquitto-1.4.14.tar.gz
     cd mosquitto-1.4.14
     cmake -DWITH_WEBSOCKETS=YES .
     make -j4
     sudo make install
     apt-get -y install mosquitto mosquitto-clients
     service mosquitto restart
     echo "un redémarrage du système peut-être nécessaire pour activer la nouvelle version de mosquitto"      

#   #fi
  fi
 fi

echo 60 > /tmp/worxLandroidS_dep

if [[ -d "/etc/php5/" ]]; then
  apt-get -y install php5-dev
  if [[ -d "/etc/php5/cli/" && ! `cat /etc/php5/cli/php.ini | grep "mosquitto"` ]]; then
  	echo "" | pecl install Mosquitto-alpha
    echo 80 > /tmp/worxLandroidS_dep
  	echo "extension=mosquitto.so" | tee -a /etc/php5/cli/php.ini
  fi
  if [[ -d "/etc/php5/fpm/" && ! `cat /etc/php5/fpm/php.ini | grep "mosquitto"` ]]; then
  	echo "extension=mosquitto.so" | tee -a /etc/php5/fpm/php.ini
    service php5-fpm restart
  fi
  if [[ -d "/etc/php5/apache2/" && ! `cat /etc/php5/apache2/php.ini | grep "mosquitto"` ]]; then
  	echo "extension=mosquitto.so" | tee -a /etc/php5/apache2/php.ini
    rm /tmp/worxLandroidS_dep
    echo "Fin installation des dépendances"
    service apache2 restart
  fi
else
  apt-get -y install php7.0-dev
  if [[ -d "/etc/php/7.0/cli/" && ! `cat /etc/php/7.0/cli/php.ini | grep "mosquitto"` ]]; then
    echo "" | pecl install Mosquitto-alpha
    echo 80 > /tmp/worxLandroidS_dep
    echo "extension=mosquitto.so" | tee -a /etc/php/7.0/cli/php.ini
  fi
  if [[ -d "/etc/php/7.0/fpm/" && ! `cat /etc/php/7.0/fpm/php.ini | grep "mosquitto"` ]]; then
    echo "extension=mosquitto.so" | tee -a /etc/php/7.0/fpm/php.ini
    service php5-fpm restart
  fi
  if [[ -d "/etc/php/7.0/apache2/" && ! `cat /etc/php/7.0/apache2/php.ini | grep "mosquitto"` ]]; then
    echo "extension=mosquitto.so" | tee -a /etc/php/7.0/apache2/php.ini
    rm /tmp/worxLandroidS_dep
    echo "Fin installation des dépendances"
    service apache2 restart
  fi
fi

rm /tmp/worxLandroidS_dep

echo "Fin installation des dépendances"
