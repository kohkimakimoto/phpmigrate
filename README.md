# PHPMigrate

PHPMigrate is a minimum database migration tool for MySQL.

Uses plain SQL to change schema. And runs some PHP codes post and previous executing SQL.

# Requrement

* PHP5.x or later (Probably).

# Installation

Just puts `migrate.php` file in your direcotry.

    wget https://raw.github.com/kohkimakimoto/phpmigrate/master/migrate.php

# Getting Started

Configure to connect your MySQL database to migrate.

Please open `migrate.php` downloaded. And modify below settings for your environment.

    $database_config = array(
      // Database settings.
      'yourdatabase' => array(
        // PDO Connection settings.
        'database_dsn'      => 'mysql:dbname=yourdatabase;host=localhost',
        'database_user'     => 'user',
        'database_password' => 'password',

        // schema version table
        'schema_version_table' => 'schema_version'
      ),

or

    $database_config = array(
      // Database settings.
      'yourdatabase' => array(
        // mysql client command settings.
        'mysql_command_enable'    => true,
        'mysql_command_cli'       => "/usr/bin/mysql",
        'mysql_command_tmpsqldir' => "/tmp",
        'mysql_command_host'      => "localhost",
        'mysql_command_user'      => "user",
        'mysql_command_password'  => "password",
        'mysql_command_database'  => "yourdatabase",
        'mysql_command_options'   => "--default-character-set=utf8",

        // schema version table
        'schema_version_table' => 'schema_version'
      ),

Difference between settings of `database_xxx` and `mysql_command_xxx` is database connection to execute SQL.
At default, it uses `database_xxx` settings to connect database using PDO.
You set up that `mysql_command_enable` is **true**. It uses `mysql_command_xxx` settings to connect databse using mysql client command.
If you use `delimeter` command in your SQL. You need to use `mysql_command_xxx` settings. Because `delimeter` command is not a SQL.
It's a mysql client command.

And create migration class file. Run the following command

    php migrate.php create create_sample_table

You would get the following messages and the skeleton migration file.
`20130422155835` timestamp part depeneds on your environment.

    Created 20130422155835_create_sample_table.php

Open the `20130422155835_create_sample_table.php`. And modify `getUpSQL` and `getDownSQL` method like below.

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

    /**
     * Return the SQL statements for the Down migration
     *
     * @return string The SQL string to execute for the Down migration.
     */
    public function getDownSQL()
    {
        return <<<END

       DROP TABLE `sample`;

    END;
    }

OK. You are ready to execute migrate command. Run the following command.

    php migrate.php migrate

You would get below messages. and table be created in your mysql database.

    [yourdatabase] Current schema version is 0
    [yourdatabase] Proccesing migrate up by 20130422155835_create_sample_table.php

Also you can run migration `down` command like the following.

    php migrate.php down

This commad drop your sample table.


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

## status [DATABASENAME ...]

List the migrations yet to be executed.

* Exsamples

        php migrate.php status

## migrate [DATABASENAME ...]

Execute the next migrations up.

* Exsamples

        php migrate.php migrate

## up [DATABASENAME ...]

Execute the next migration up.

* Exsamples

        php migrate.php up

## down [DATABASENAME ...]

Execute the next migration down.

* Exsamples

        php migrate.php down

# License

  Apache License 2.0

# Infomation

My Blog post (written in Japanese)

 * http://kohkimakimoto.hatenablog.com/entry/2013/04/02/201308

