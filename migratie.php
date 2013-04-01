#!/usr/bin/env php
<?php
/**
 * PHPMIgratie
 *
 * @author     Kohki Makimoto <kohki.makimoto@gmail.com>
 * @license    MIT License
 * @copyright  2010 - 2013 Kohki Makimoto <kohki.makimoto@gmail.com>
 */

/**
 * NOTICE:
 * You need to configure your database settings below section
 */
////////// BEGIN OF CONFIG AREA //////////////////////////////

$configs['database']['dsn']      = 'mysql:dbname=yourdatabase;host=127.0.0.1';
$configs['database']['user']     = 'your';
$configs['database']['password'] = 'password';

$configs['settings']['migrations_table']     = 'migrations';

////////// END OF CONFIG AREA ////////////////////////////////



/**
 * Migration Class
 *
 * @author kohkimakimoto <kohki.makimoto@gmail.com>
 * @version $Revision$
 */
class Migration
{
  const VERSION = '0.1.0';

  protected $options;
  protected $arguments;
  protected $command;

  /**
   * Main method.
   */
  public static function main()
  {
    $options = getopt("hd");
    $argv = $_SERVER['argv'];
    $raw_arguments = $argv;

    // Remove program name.
    if (isset($raw_arguments[0])) {
      array_shift($raw_arguments);
    }

    // Process arguments
    $arguments = array();
    $i = 0;
    while ($raw_argument = array_shift($raw_arguments)) {
      if ('-' == substr($raw_argument, 0, 1)) {

      } else {
        $arguments[] = $raw_argument;
      }
      $i++;
    }
    $command = array_shift($arguments);

    // Run.
    $instance = new Migration();
    $instance->execute($command, $options, $arguments);
  }

  /**
   * Execute.
   * @param unknown $task
   * @param unknown $options
   */
  public function execute($command, $options, $arguments)
  {
    // Show help
    if (array_key_exists('h', $options)) {
      $this->usage();
      return;
    }

    if (count($options) === 0 && $command == null) {
      $this->usage();
      return;
    }

    $this->command = $command;
    $this->options = $options;
    $this->arguments = $arguments;

  }

  /**
   * Output usage.
   */
  protected function usage()
  {
    echo "\n";
    echo "PHPMigration is a minimum migration tool. version ".Migration::VERSION.".\n";
    echo "\n";
    echo "Copyright (c) Kohki Makimoto <kohki.makimoto@gmail.com>\n";
    echo "Apache License 2.0\n";
    echo "\n";
    echo "Usage:\n";
    echo "  php ".basename(__FILE__)." [-h|-d] COMMAND\n";
    echo "\n";
    echo "Options:\n";
    echo "  -d         : Switch the debug mode to output log on the debug level.\n";
    echo "  -h         : List available command line options (this page).\n";
    echo "  -c         : List configurations.\n";
    echo "\n";
    echo "Commands:\n";
    echo "  status     : List the migrations yet to be executed.\n";
    echo "  migrate    : Execute the next migrations up.\n";
    echo "  up         : Execute the next migration up.\n";
    echo "  down       : Execute the next migration down.\n";
    echo "\n";
  }

}



Migration::main();