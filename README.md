# PHPMigrate

PHPMigrate is a minimum database migration tool for MySQL.

Uses plain SQL to change schema. And runs some PHP codes post and previous executing SQL.

# Requrement

* PHP5.x or later (Probably).

# Installation

Just puts `migrate.php` file in the direcotry you like.

    wget https://raw.github.com/kohkimakimoto/phpmigrate/master/migrate.php

# Getting Started

You need to configure to connect your MySQL database to migrate.

Please open `migrate.php` downloaded. And modify below settings for your environment.

    MigrationConfig::set('database_dsn',         'mysql:dbname=yourdatabase;host=localhost');
    MigrationConfig::set('database_user',        'user');
    MigrationConfig::set('database_password',    'password');
    MigrationConfig::set('schema_version_table', 'schema_version');

or

    MigrationConfig::set('mysql_command_enable',    true);
    MigrationConfig::set('mysql_command_cli',       "/usr/bin/mysql");
    MigrationConfig::set('mysql_command_tmpsqldir', "/tmp");
    MigrationConfig::set('mysql_command_host',      "localhost");
    MigrationConfig::set('mysql_command_user',      "user");
    MigrationConfig::set('mysql_command_password',  "password");
    MigrationConfig::set('mysql_command_database',  "yourdatabase");
    MigrationConfig::set('mysql_command_options',   "--default-character-set=utf8");
    MigrationConfig::set('schema_version_table', 'schema_version');

Difference between settings of `database_xxx` and `mysql_command_xxx` is database connection to execute SQL.
At default, it uses `database_xxx` settings to connect database using PDO.
You set up that `mysql_command_enable` is **true**. It uses `mysql_command_xxx` settings to connect databse using mysql client command.
If you use `delimeter` command in your SQL. You need to use `mysql_command_xxx` settings. Because `delimeter` command is not a SQL.
It's a mysql client command.

And create migration class file. Run the following command

    php migrate.php create create_sample_table

You would get the following messages and the skeleton migration file.

    INFO Created 1362341603_create_sample_table.php

Open the `xxxxxxxxxx_create_sample_table.php`. And modify `getUpSQL` method like this.

      /**
       * Return the SQL statements for the Up migration
       *
       * @return string The SQL string to execute for the Up migration.
       */
      public function getUpSQL()
      {
         return <<<END

    CREATE TABLE `sample` (
      `id` INT UNSIGNED NOT NULL,
      PRIMARY KEY (`id`) )
    ENGINE = InnoDB
    DEFAULT CHARACTER SET = utf8
    COLLATE = utf8_bin;

    END;
      }

OK. You are ready to execute migrate command. Run the following command.

    php migrate.php migrate

You would get below messages. and table created in your mysql database.

    INFO Current schema version is 0
    INFO Proccesing migrate up by 1362341603_create_sample_table.php

# Command Usage

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

Create new skeleton migration file.

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

# Infomation

My Blog post (written in Japanese)

 * http://kohkimakimoto.hatenablog.com/entry/2013/04/02/201308

