# PHPMigrate

PHPMigrate is a minimum database migration tool for MySQL.

# Requrement

* PHP5.x or later.

# Installation

Just puts `migrate.php` file in the direcotry you like.

# Initial Configurateions.

You need to configure to connect your MySQL database to migrate.

Please open `migrate.php` downloaded. And Modiry below settings for your environment.

        MigrationConfig::set('database_dsn',      'mysql:dbname=yourdatabase;host=localhost');
        MigrationConfig::set('database_user',     'user');
        MigrationConfig::set('database_password', 'password');
        MigrationConfig::set('schema_version_table',  'schema_version');

# Command syntax

## create

Create new empty migration file.

* Exsamples

        php migrate.php create foo

## status

* Exsamples

        php migrate.php status

## migrate

* Exsamples

        php migrate.php migrate

# License

  Apache License 2.0
