#! bin/bash

#!/bin/bash

# Update the package list
sudo apt update

# Install Apache
sudo apt install -y apache2

# Install PHP 7.4 and its modules
sudo apt install -y php7.4 libapache2-mod-php7.4 php7.4-common php7.4-mysql php7.4-gd php7.4-cli php7.4-curl php7.4-json php7.4-mbstring php7.4-intl php7.4-xml php7.4-zip

# Enable PHP module for Apache
sudo a2enmod php7.4

# Restart Apache to apply changes
sudo systemctl restart apache2

# Configure Apache Virtual Host for the domain
sudo tee /etc/apache2/sites-available/kash.conf >/dev/null <<EOF
<VirtualHost *:80>
    ServerName kash.jiggabytehub.site
    DocumentRoot /var/www/kashmore/public

    <Directory /var/www/kashmore/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

# Enable the Virtual Host
sudo a2ensite kash.conf

# Disable default Apache site
sudo a2dissite 000-default.conf

sudo a2enmod rewrite

# Restart Apache to apply configuration changes
sudo systemctl restart apache2

# Install Certbot for Let's Encrypt
sudo apt install -y certbot python3-certbot-apache

# Obtain and install Let's Encrypt SSL certificate
sudo certbot --apache --agree-tos --email jigga.e10@gmail.com --no-eff-email -d kash.jiggabytehub.site

# Enable the Virtual Host
sudo a2ensite kash-le-ssl.conf

# Verify Apache and PHP installation
echo "Apache and PHP 7.4 have been successfully installed and configured for kash.jiggabytehub.site."

# Display PHP version
php -v

# Update the system
sudo apt update

# Install Node.js 14
curl -sL https://deb.nodesource.com/setup_14.x | sudo -E bash -
sudo apt install -y nodejs

# Install build-essential package for compiling native addons
sudo apt install -y build-essential

# Install Composer
sudo apt install -y php-cli php-mbstring unzip
cd /home/jigga
EXPECTED_SIGNATURE=$(wget -q -O - https://composer.github.io/installer.sig)
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
ACTUAL_SIGNATURE=$(php -r "echo hash_file('SHA384', 'composer-setup.php');")

if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ]; then
    echo >&2 'ERROR: Invalid installer signature'
    rm composer-setup.php
    exit 1
fi

php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# Clean up
rm composer-setup.php

cd ~

# Verify Node.js and Composer installations
node --version
npm --version
composer --version

sudo rm -rf /var/www/kashmore || echo 'kashmore not found' && cd /var/www && sudo mv /home/jigga/kashmore . && sudo chmod -R +x /var/www && cd kashmore

sudo chown -R www-data:www-data /var/www
sudo chmod -R 0775 /var/www

sudo mv /var/www/kashmore/index.env /var/www/kashmore/.env

sudo npm install

sudo composer install

sudo php artisan migrate --force

sudo php artisan passport:install

sudo php artisan passport:keys --force

sudo php artisan storage:link

sudo systemctl restart apache2
