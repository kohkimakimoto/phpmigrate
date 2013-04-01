#!/usr/bin/env php
<?php
/**
 * PHPMIgratie
 *
 * @author     Kohki Makimoto <kohki.makimoto@gmail.com>
 * @copyright  2010 - 2013 Kohki Makimoto <kohki.makimoto@gmail.com>
 */

/**
 * NOTICE:
 * You need to configure your database settings below section
 */
////////// BEGIN OF CONFIG AREA //////////////////////////////

// Create Test User:
//   > GRANT ALL PRIVILEGES ON *.* TO user@'localhost' IDENTIFIED BY 'password';
//   > FLUSH PRIVILEGES;

MigrationConfig::set('database_dsn',      'mysql:dbname=yourdatabase;host=localhost');
MigrationConfig::set('database_user',     'user');
MigrationConfig::set('database_password', 'password');

// MigrationConfig::set('migrations_table',  'migrations');

////////// END OF CONFIG AREA ////////////////////////////////

// Run Command.
Migration::main();

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
  protected $conn;

  /**
   * Main method.
   */
  public static function main()
  {
    $options = getopt("hdc");
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

    if (array_key_exists('d', $options)) {
      MigrationConfig::set('debug', true);
    }

    // Show config
    if (array_key_exists('c', $options)) {
      $this->listConfig();
      return;
    }

    if (count($options) === 0 && $command == null) {
      $this->usage();
      return;
    }

    try {

      $this->command = $command;
      $this->options = $options;
      $this->arguments = $arguments;

      if ($this->command == 'status') {

        $this->runStatus();

      } elseif ($this->command == 'migrate') {

      } elseif ($this->command == 'up') {

      } elseif ($this->command == 'down') {

      } else {
        fputs(STDERR, 'Unknown command: '.$this->command."\n");
        exit(1);
      }

    } catch (Exception $e) {

      if (MigrationConfig::get('debug')) {
        fputs(STDERR, $e);
      } else {
        fputs(STDERR, $e->getMessage()."\n");
      }

      exit(1);
    }
  }


  protected function getConnection()
  {
    if (!$this->conn) {
      $dsn      = MigrationConfig::get('database_dsn');
      $user     = MigrationConfig::get('database_user');
      $password = MigrationConfig::get('database_password');

      $this->conn = new PDO($dsn, $user, $password);
    }

    return $this->conn;
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
    echo "  php ".basename(__FILE__)." [-h|-d|-c] COMMAND\n";
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

  /**
   * List config
   */
  public function listConfig()
  {
    $largestLength = MigrationUtils::arrayKeyLargestLength(MigrationConfig::getAllOnFlatArray());
    echo "\n";
    echo "Configurations :\n";
    foreach (MigrationConfig::getAllOnFlatArray() as $key => $val) {
      if ($largestLength === strlen($key)) {
        $sepalator = str_repeat(" ", 0);
      } else {
        $sepalator = str_repeat(" ", $largestLength - strlen($key));
      }

      echo "  [".$key."] ";
      echo $sepalator;
      if (is_callable($val)) {
        echo "=> function()\n";
      } else if (is_array($val)) {
        echo "=> array()\n";
      } else {
        echo "=> ".$val."\n";
      }
    }
    echo "\n";
  }
}

/**
 * Migration Connfig Class
 *
 * @author kohkimakimoto <kohki.makimoto@gmail.com>
 * @version $Revision$
 */
class MigrationConfig
{
  /**
   * Array of configuration values.
   * @var unknown
   */
  protected static $config = array();

  /**
   * Get a config parameter.
   * @param unknown $name
   * @param string $default
   */
  public static function get($name, $default = null, $delimiter = '/')
  {
    $config = self::$config;
    foreach (explode($delimiter, $name) as $key) {
      $config = isset($config[$key]) ? $config[$key] : $default;
    }
    return $config;
  }

  /**
   * Set a config parameter.
   * @param unknown $name
   * @param unknown $value
   */
  public static function set($name, $value)
  {
    self::$config[$name] = $value;
  }

  public static function delete($name)
  {
    unset(self::$config[$name]);
  }

  /**
   * Get All config parameters.
   * @return multitype:
   */
  public static function getAll()
  {
    return self::$config;
  }

  public static function getAllOnFlatArray($namespace = null, $key = null, $array = null, $delimiter = '/')
  {
    $ret = array();

    if ($array === null) {
      $array = self::$config;
    }

    foreach ($array as $key => $val) {
      if (is_array($val) && $val) {
        if ($namespace === null) {
          $ret = array_merge($ret, self::getAllOnFlatArray($key, $key, $val, $delimiter));
        } else {
          $ret = array_merge($ret, self::getAllOnFlatArray($namespace.$delimiter.$key, $key, $val, $delimiter));
        }
      } else {
        if ($namespace !== null) {
          $ret[$namespace.$delimiter.$key] = $val;
        } else {
          $ret[$key] = $val;
        }
      }
    }

    return $ret;
  }
}

/**
 * Migration Utility Class
 *
 * @author kohkimakimoto <kohki.makimoto@gmail.com>
 * @version $Revision$
 */
class MigrationUtils
{
  /**
   * Gets largest length of the array.
   * @param unknown $array
   */
  public static function arrayKeyLargestLength($array)
  {
    $ret = 0;
    $keys = array_keys($array);
    foreach ($keys as $key) {
      if (strlen($key) > $ret) {
        $ret = strlen($key);
      }
    }
    return $ret;
  }
}

/**
 * Migration Logger Class
 *
 * @author kohkimakimoto <kohki.makimoto@gmail.com>
 * @version $Revision$
 */
class MigrationLogger
{
  public static function log($msg, $level = 'info')
  {
  	if (!MigrationConfig::get('log', true)) {
      return;
    }

    if ($level == 'debug') {
      if (MigrationConfig::get('debug')) {
        echo "[".date_create()->format('c')."] DEBUG ".$msg."\n";
      }
    } else {
      echo "[".date_create()->format('c')."] INFO ".$msg."\n";
    }
  }
}
