Squeezer
========

[![Author](http://img.shields.io/badge/author-@mamuz_de-blue.svg?style=flat-square)](https://twitter.com/mamuz_de)
[![Build Status](https://img.shields.io/travis/mamuz/Squeezer.svg?style=flat-square)](https://travis-ci.org/mamuz/Squeezer)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/mamuz/Squeezer.svg?style=flat-square)](https://scrutinizer-ci.com/g/mamuz/Squeezer/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/mamuz/Squeezer.svg?style=flat-square)](https://scrutinizer-ci.com/g/mamuz/Squeezer)
[![Dependency Status](https://img.shields.io/versioneye/d/user/projects/5592fdaf6238390018000001.svg?style=flat-square)](https://www.versioneye.com/user/projects/5592fdaf6238390018000001)

[![Latest Stable Version](https://img.shields.io/packagist/v/mamuz/squeezer.svg?style=flat-square)](https://packagist.org/packages/mamuz/squeezer)
[![Total Downloads](https://img.shields.io/packagist/dt/mamuz/squeezer.svg?style=flat-square)](https://packagist.org/packages/mamuz/squeezer)
[![License](https://img.shields.io/packagist/l/mamuz/squeezer.svg?style=flat-square)](https://packagist.org/packages/mamuz/squeezer)

Squeezer is a minifier for php class files.
It will parse your directories to find valid classes, interfaces and traits and squeeze them to one file.
Autoloading in php is quite nice but also expensive. Sometimes too expensive for production.
Using a minified file reduces the execution time of your application but increases the memory usage.
Take care which directories or packages you want to squeeze. For production you should only squeeze
used packages.

## Installation

The recommended way to install
[`mamuz/squeezer`](https://packagist.org/packages/mamuz/squeezer) is through
[composer](http://getcomposer.org/) by adding dependency to your `composer.json`:

```json
{
    "require-dev": {
        "mamuz/squeezer": "0.*"
    }
}
```

## Features

- Minify classes, interfaces and traits to one compressed file.
- Validates availability of class dependencies.
- Removing comments and docblocks is optional to keep interoperability to annotation parser.
- PHP files with more than one class, interface or trait declarations will be skipped.
- PHP files with `include`, `include_once`, `require` or `require_once` statements will be skipped.
- PHP files with function calls to handle files like `fopen` or `mkdir`will be skipped.
- PHP files with a `declare` statement will be skipped.

## Usage

Run this command line to squeeze your library without comments to `classes.min.php`:

```sh
./vendor/bin/squeeze classes.min.php --source="module/*/src, vendor/zendframework/*/src" --exclude="zend-loader" --nocomments
```
*For instance, we are using a typical ZendFramework Application, but you can adapt this command to each environment*

After that you can include `classes.min.php` inside your `index.php`, but before loading composer autoloader.

For instance...

```php
//...
include_once 'classes.min.php';
include_once 'vendor/autoload.php'; // composer autoloader
//...
```

Use this command to get synopsis about all arguments

```sh
./vendor/bin/squeeze --help
```
