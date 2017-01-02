# Laravel 5's Package for Dreamfactory Connector

A custom made Laravel5 package used as the connector to Dreamfactory API

## Usage

1- Require package in composer.json
```

"require": {
            ....
            "gdce/laravel-dreamfactory":"dev-master"
        }

```
2- Run the following command

```
composer update
```

3- Register the service provider in config/app.php 

```

GDCE\LaravelDreamfactory\LaravelDreamfactoryServiceProvider::class

```

4- Publish config file

Run the following command to publish the config file

```
php artisan vendor:publish
```

5- Have fun!

## Package dependencies

```
"guzzlehttp/guzzle":"~6.0",
"yajra/laravel-datatables-oracle": "~6.0"
```