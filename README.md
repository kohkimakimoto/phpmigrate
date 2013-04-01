# PHPMigrate

PHPMigrate is a minimum database migration tool for MySQL.

It's under the development version.

# Requrement

* PHP5.x or later.

# Installation

Just puts `migrate.php` file in the direcotry you like.

    wget https://raw.github.com/kohkimakimoto/phpmigrate/master/migrate.php


# Initial Configurateions.

You need to configure to connect your MySQL database to migrate.

Please open `migrate.php` downloaded. And Modiry below settings for your environment.

    MigrationConfig::set('database_dsn',      'mysql:dbname=yourdatabase;host=localhost');
    MigrationConfig::set('database_user',     'user');
    MigrationConfig::set('database_password', 'password');
    MigrationConfig::set('schema_version_table',  'schema_version');

# Usage

    php migrate.php [-h|-d|-c] COMMAND

# Options

## -d

Switch the debug mode to output log on the debug level.

## -h

List available command line options.

## -c

List configurations.

# Commands

## create

Create new empty migration file.

* Exsamples

        php migrate.php create foo

## status

List the migrations yet to be executed.

* Exsamples

        php migrate.php status

## migrate

Execute the next migrations up.

* Exsamples

        php migrate.php migrate

## up

Execute the next migration up.

* Exsamples

        php migrate.php up

## down

Execute the next migration down.

* Exsamples

        php migrate.php down

# License

  Apache License 2.0
