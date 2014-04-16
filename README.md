## littleTodo, a simple task manager

littleTodo is a simple task manager which purpose is to try and train myself with Silex, Twig, Bootstrap, PHPUnit and more.

**This is under development**

## Installation

As a PHP application, it of course need a functional webserver (LAMP or similar).

1. Make sure you have [Composer](http://getcomposer.org/) installed. I recommend [installing it globally](http://getcomposer.org/doc/00-intro.md#globally).
2. Clone or download this repository into your webserver directory.
3. In a terminal, go into this project's directory with `cd`.
4. Install all dependencies by running `composer install`.
5. Install the database with `sqlite3 app.db < resources/sql/schema.sql`.
6. Run the application into your browser and create your own account.

## License

littleTodo is released under the GPL v3 (or later) license, see the [LICENSE](LICENSE)