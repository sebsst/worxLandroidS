#! /bin/bash

PROGRESS_FILE=/tmp/worxLandroidS_dep;
if [ ! -z $1 ]; then
    PROGRESS_FILE=$1
fi

INSTALL_MOSQUITTO=1
if [ ! -z $2 ] && [ $2 -eq 1 -o $2 -eq 0 ]; then
    INSTALL_MOSQUITTO=$2
fi

echo 0 > ${PROGRESS_FILE}

echo "********************************************************"
echo "* Install dependancies                                 *"
echo "********************************************************"
echo "Progress file: " ${PROGRESS_FILE}
echo "Install Mosquitto: " ${INSTALL_MOSQUITTO}
echo "*"
echo "* Update package source repository"
echo "*"
apt-get -y install lsb-release php-pear
archi=`lscpu | grep Architecture | awk '{ print $2 }'`
echo 10 > ${PROGRESS_FILE}


if [ "$archi" == "x86_64" ]; then
    cd /tmp
    if [ `lsb_release -i -s` == "Debian" ]; then
	wget http://repo.mosquitto.org/debian/mosquitto-repo.gpg.key
	apt-key add mosquitto-repo.gpg.key
	rm mosquitto-repo.gpg.key
	if [ `lsb_release -c -s` == "jessie" ]; then
	    wget http://repo.mosquitto.org/debian/mosquitto-jessie.list
	    mv -f mosquitto-jessie.list /etc/apt/sources.list.d/mosquitto-jessie.list
	fi
	if [ `lsb_release -c -s` == "stretch" ]; then
	    wget http://repo.mosquitto.org/debian/mosquitto-stretch.list
	    mv -f mosquitto-stretch.list /etc/apt/sources.list.d/mosquitto-stretch.list
	fi
    fi
fi
echo 20 > ${PROGRESS_FILE}

echo "*"
echo "* Synchronize the package index"
echo "*"
apt-get update
echo 40 > ${PROGRESS_FILE}

echo "*"
echo "* Install Mosquitto"
echo "*"
if [ ${INSTALL_MOSQUITTO} -eq 1 ]; then
    apt-get -y install mosquitto mosquitto-clients libmosquitto-dev
else
    apt-get -y install mosquitto-clients libmosquitto-dev
fi
echo 60 > ${PROGRESS_FILE}

#si version est toujours 1.3 alors on essaye de compiler une version plus récente
mosquitto -h | grep "version"
version=`mosquitto -h | grep "version 1.3"`
if [ -n "$version" ]; then
 if [ `lsb_release -i -s` == "Debian" ] || [ `lsb_release -i -s` == "Raspian" ]; then

#   #  sudo apt-get -y install build-essential python quilt devscripts python-setuptools python3 libssl-dev cmake libc-ares-dev uuid-dev daemon
     echo "La version de mosquitto $version n'est pas compatible. tentative d'installation d'une version plus récente"
     sudo apt-get -y install cmake libssl1.0-dev 
     sudo apt-get -y install libwebsockets-dev uuid-dev
     cd /tmp
     wget http://mosquitto.org/files/source/mosquitto-1.5.tar.gz
     tar xavf mosquitto-1.5.tar.gz
     cd mosquitto-1.5
     cmake -DWITH_WEBSOCKETS=YES .
     make -j4
     sudo make install
     apt-get -y install mosquitto mosquitto-clients
     service mosquitto restart
  

  fi
 fi




echo "*"
echo "* Install php mosquitto wrapper"
echo "*"
if [[ -d "/etc/php5/" ]]; then
    apt-get -y install php5-dev
    echo 80 > ${PROGRESS_FILE}
    if [[ -d "/etc/php5/cli/" && ! `cat /etc/php5/cli/php.ini | grep "mosquitto"` ]]; then
  	echo "" | pecl install Mosquitto-alpha
  	echo "extension=mosquitto.so" | tee -a /etc/php5/cli/php.ini
    fi
    if [[ -d "/etc/php5/fpm/" && ! `cat /etc/php5/fpm/php.ini | grep "mosquitto"` ]]; then
  	echo "extension=mosquitto.so" | tee -a /etc/php5/fpm/php.ini
	service php5-fpm reload
    fi
    if [[ -d "/etc/php5/apache2/" && ! `cat /etc/php5/apache2/php.ini | grep "mosquitto"` ]]; then
	echo "extension=mosquitto.so" | tee -a /etc/php5/apache2/php.ini
	service apache2 reload
    fi
else
    apt-get -y install php7.0-dev
    echo 80 > ${PROGRESS_FILE}
    if [[ -d "/etc/php/7.0/cli/" && ! `cat /etc/php/7.0/cli/php.ini | grep "mosquitto"` ]]; then
	echo "" | pecl install Mosquitto-alpha
	echo "extension=mosquitto.so" | tee -a /etc/php/7.0/cli/php.ini
    fi
    if [[ -d "/etc/php/7.0/fpm/" && ! `cat /etc/php/7.0/fpm/php.ini | grep "mosquitto"` ]]; then
	echo "extension=mosquitto.so" | tee -a /etc/php/7.0/fpm/php.ini
	service php5-fpm reload
    fi
    if [[ -d "/etc/php/7.0/apache2/" && ! `cat /etc/php/7.0/apache2/php.ini | grep "mosquitto"` ]]; then
	echo "extension=mosquitto.so" | tee -a /etc/php/7.0/apache2/php.ini
	service apache2 reload
    fi
fi

rm ${PROGRESS_FILE}

echo "********************************************************"
echo "*             End dependancy installation              *"
echo "********************************************************"

