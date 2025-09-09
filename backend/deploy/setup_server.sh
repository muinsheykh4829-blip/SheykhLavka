#!/usr/bin/env bash
set -e

echo "[1/5] Update packages"
sudo apt-get update -y
sudo apt-get upgrade -y

echo "[2/5] Install Nginx, PHP 8.2, Composer"
sudo apt-get install -y software-properties-common curl unzip git
sudo add-apt-repository ppa:ondrej/php -y
sudo apt-get update -y
sudo apt-get install -y nginx php8.2 php8.2-fpm php8.2-cli php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-bcmath php8.2-intl php8.2-mysql

EXPECTED=/usr/local/bin/composer
if ! command -v composer &> /dev/null; then
  echo "[Composer] Installing..."
  curl -sS https://getcomposer.org/installer -o composer-setup.php
  php composer-setup.php --install-dir=/usr/local/bin --filename=composer
  rm composer-setup.php
fi

echo "[3/5] PHP-FPM config tweaks (optional)"
sudo sed -i 's/^;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/' /etc/php/8.2/fpm/php.ini || true
sudo systemctl restart php8.2-fpm

echo "[4/5] Nginx enable"
sudo systemctl enable nginx
sudo systemctl start nginx

echo "[5/5] Create web root (if not exists)"
sudo mkdir -p /var/www/dastovka
sudo chown -R $USER:www-data /var/www/dastovka
echo "Done. Copy project to /var/www/dastovka and configure nginx." 
