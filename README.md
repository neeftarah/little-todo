little-todo, a simple task manager
=================================

little-todo is a simple task manager which purpose is to try and train myself with Silex, Twig, Bootstrap, PHPUnit and more.

**This is under development**


Requirements
------------

As a PHP application, it of course need a functional webserver.
  * PHP 5.3.3 or greater
  * A functional webserver (Apache, NGinx...)

**To be defined...**


Installation
------------

### Install via Composer

1. Make sure you have [Composer](http://getcomposer.org/) installed. I recommend [installing it globally](http://getcomposer.org/doc/00-intro.md#globally).
2. Clone or download this repository into your webserver directory.
3. In a terminal, go into this project's directory with `cd`.
4. Install all dependencies by running `composer install`.
5. Install the database with `sqlite3 app.db < resources/sql/schema.sql`.
6. Run the application into your browser and create your own account.


### Install by download

Alternatively, you can (will be able to) download the full package (not created yet) with all dependences and a created database.

1. Download the package.
2. Extract it to your webserver directory.
3. In order to write to the database, you may need to `chmod` the resources directory to make it writable.
4. Run the application into your browser and create your own account.

License
-------

little-todo is released under the GPL v3 (or later) license, see the [LICENSE](LICENSE)

Status
------
[![Build Status](https://travis-ci.org/neeftarah/little-todo.svg?branch=master)](https://travis-ci.org/neeftarah/little-todo)
