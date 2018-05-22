#!/bin/sh
export DEBIAN_FRONTEND=noninteractive
apt-get update
apt-get install -y python-software-properties
add-apt-repository -y ppa:openjdk-r/ppa
add-apt-repository -y ppa:ondrej/php
wget https://cdn.crate.io/downloads/deb/DEB-GPG-KEY-crate
apt-key add DEB-GPG-KEY-crate
touch /etc/apt/sources.list.d/crate.list
cat <<EOT >> /etc/apt/sources.list.d/crate.list
deb https://cdn.crate.io/downloads/deb/stable/ trusty main
deb-src https://cdn.crate.io/downloads/deb/stable/ trusty main
EOT
apt-get update
apt-get install -y crate php7.2-cli php7.2-xml php7.2-curl php7.2-mbstring
