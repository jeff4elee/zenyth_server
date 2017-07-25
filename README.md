# zenyth_server

Setting up Laravel 5.4 & PHP7.0
=======
ppa for php7.0 packages

Install php/mysql packages
-------------------------
sudo apt-get install php7.0-mysqlnd php7.0 <br>
sudo apt-get install mysql-server-5.7 <br>
sudo apt-get install apache2 <br>
sudo apt-get install mcrypt php7.0-mcrypt <br>
sudo apt-get install -y php7.0-mbstring php7.0-xml --force-yes <br>
sudo apt-get install libapache2-mod-php7.0 <br>

Install and setup composer with curl
--------------------------------------
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

Create a new laravel project at the given dir.
----------------------------------------------
cd /var/www/html
sudo composer create-project laravel/laravel your-project --prefer-dist

Configure permissions (enable read/write privileges to apache2)
--------------------------------------------------
sudo chgrp -R www-data /var/www/html/project
sudo chmod -R 775 /var/www/html/project/*
sudo chmod -R 777 /var/www/html/project/storage
sudo chmod -R 777 /var/www/html/project/bootstrap

Setup new path to directory containing laravel project in apache
-------------------------------------------------------
cd /etc/apache2/sites-available
sudo vim laravel.conf

```config
<VirtualHost *:80>
    ServerName localhost

    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html/project/public

	<Directory /var/www/html/project>
    	AllowOverride All
	</Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

---------------------------------
sudo a2dissite 000-default.conf <br>
sudo a2ensite laravel.conf <br>
sudo a2enmod rewrite <br>
sudo service apache2 restart <br>



Setting up PHPRedis
==================

Install Redis
-----------------
sudo apt-get install redis-server <br>
sudo service redis-server start

Install requirements for phpredis for Laravel
--------------------
sudo apt-get update
git clone -b php7 https://github.com/phpredis/phpredis.git
sudo mv phpredis/ /etc/
cd /etc/phpredis
phpize
./configure
make && make install

NOTE This Extension needs To be Enabled in PHP ini to ENABLE it on your Command Line Interface Such As Artisan.
The Usual Problem You Will Face If You Wont do this is Get a Redis Class Not Found

sudo vim /etc/php/7.0/cli/php.ini
then Look for this Word CLI Server
Type
/CLI Server 

Add to the last line of extension

extension=/etc/phpredis/modules/redis.so
:w!     ->write/save
:q  ->exit vim

To Test if PhpRedis Extension is Working
php -r "if (new Redis() == true){ echo \"\r\n OK \r\n\"; }"

It Should Return OK!

Replace Laravel Predis with PHPRedis
------------------------------------
composer require predis/predis
composer update

In config/app.php
Uncomment Redis Service Provider
// Illuminate\Redis\RedisServiceProvider::class,

Uncomment Redis Facade
// 'Redis'     => Illuminate\Support\Facades\Redis::class,

Add this as a Replacement for the Uncommented Lines
Nardev\PhpRedis\PhpRedisServiceProvider::class,
'PHPRedis'  => Illuminate\Support\Facades\Redis::class,


