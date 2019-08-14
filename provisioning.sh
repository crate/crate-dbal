#!/bin/sh
export DEBIAN_FRONTEND=noninteractive
apt-get update
apt-get upgrade
add-apt-repository -y ppa:ondrej/php
wget https://cdn.crate.io/downloads/deb/DEB-GPG-KEY-crate
apt-key add DEB-GPG-KEY-crate
. /etc/os-release
echo "deb https://cdn.crate.io/downloads/deb/stable/ $UBUNTU_CODENAME main" > /etc/apt/sources.list.d/crate-stable-$UBUNTU_CODENAME.list
apt-get update
# test classes of DBAL which we depend on are only available inside the source, so DON'T install zip|unzip to force
# composer to install depencenies from source
apt-get install -y crate php7.2-cli php7.2-xml php7.2-curl php7.2-mbstring
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
cd /vagrant && su vagrant -c 'composer install'