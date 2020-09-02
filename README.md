# Dogs-API

## Table of contents
* [General info](#general-info)
* [Requirements](#requirements)
* [Setup](#setup)
  - [Database create and migration](#database-create-and-migration)
* [Usage](#setup)

## General Info
REST API written with Symfony 5. 

## Requirements
* PHP 7.4
* Symfony 5 with all its requirements
* Database

## Setup
```
$ git clone git@github.com:MJankoo/Dogs-API.git
$ cd Dogs-API
$ composer install
```
You also have to open .env file, and edit your database data.

### Database create and migration
```
$ php bin/console doctrine:database:create
$ php bin/console doctrine:migrations:diff
$ php bin/console doctrine:migrations:migrate
```

## Usage
Execute this command:
```
$ php bin/console server:start
```
After that application is ready to use. You can send your requests to http://localhost:8000
