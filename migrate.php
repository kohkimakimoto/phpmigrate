#!/usr/bin/env php
<?php
/**
 * PHPMigrate
 *
 * @author     Kohki Makimoto <kohki.makimoto@gmail.com>
 * @copyright  2010 - 2013 Kohki Makimoto <kohki.makimoto@gmail.com>
 */

/**
 * NOTICE:
 * You need to configure your database settings below section
 */
////////// BEGIN OF CONFIG AREA //////////////////////////////

// Create Test User like below:
//   > GRANT ALL PRIVILEGES ON *.* TO user@'localhost' IDENTIFIED BY 'password';
//   > FLUSH PRIVILEGES;

MigrationConfig::set('database', 'mysql:dbname=yourdatabase;host=localhost');
MigrationConfig::set('database', 'user');
MigrationConfig::set('database', 'password');

MigrationConfig::set('schema_version_table',  'schema_version');

////////// END OF CONFIG AREA ////////////////////////////////


////////// BIGIN PROGRAM AREA (Do not modify!) ///////////////
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

      } elseif ($this->command == 'create') {

        $this->runCreate();

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

  /**
   * Run Status Command
   */
  protected function runStatus()
  {
    $this->getSchemaVersionTable();
  }

  /**
   * Run Create Command
   */
  protected function runCreate()
  {
    if (count($this->arguments) > 0) {
      $name = $this->arguments[0];
    } else {
      throw new Exception("You need to pass the argument for migration name. (ex php ".basename(__FILE__)." create foo");
    }

    $timestamp = mktime();
    $filename = $timestamp."_".$name.".php";
    $filepath = __DIR__."/".$filename;
    $camelize_name = MigrationUtils::camelize($name);

    $content = <<<EOF
<?php
/**
 * Migration class.
 */
class $camelize_name
{
  public function preUp()
  {
      // add the pre-migration code here
  }

  public function postUp()
  {
      // add the post-migration code here
  }

  public function preDown()
  {
      // add the pre-migration code here
  }

  public function postDown()
  {
      // add the post-migration code here
  }

  /**
   * Return the SQL statements for the Up migration
   *
   * @return string The SQL string to execute for the Up migration.
   */
  public function getUpSQL()
  {
     return "";
  }

  /**
   * Return the SQL statements for the Down migration
   *
   * @return string The SQL string to execute for the Down migration.
   */
  public function getDownSQL()
  {
     return "";
  }

}
EOF;

    file_put_contents($filename, $content);

    MigrationLogger::log("Created ".$filename);

  }

  protected function getSchemaVersionTable()
  {
    $table = MigrationConfig::get('schema_version_table', 'schema_version');
    $sql = 'show tables like '.$table;

    $conn = $this->getConnection();
    $stmt = $conn->prepare($sql);

    if (!$stmt->execute()) {
      MigrationLogger::log("Table [".$table."] is not found. This schema hasn't been managed yet by PHPMigrate.");
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
    echo "PHPMigrate is a minimum migration tool. version ".Migration::VERSION.".\n";
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
    echo "  create NAME    : Create new empty migration file.\n";
    echo "  status         : List the migrations yet to be executed.\n";
    echo "  migrate        : Execute the next migrations up.\n";
    echo "  up             : Execute the next migration up.\n";
    echo "  down           : Execute the next migration down.\n";
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

  /*
  The Following Methods are copied from symfony web application framework version 1.4. (http://symfony.com/).
  */

  /*
  Copyright (c) 2004-2010 Fabien Potencier

  Permission is hereby granted, free of charge, to any person obtaining a copy
  of this software and associated documentation files (the "Software"), to deal
  in the Software without restriction, including without limitation the rights
  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
  copies of the Software, and to permit persons to whom the Software is furnished
  to do so, subject to the following conditions:

  The above copyright notice and this permission notice shall be included in all
  copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
  THE SOFTWARE.
  */

  /**
   * Returns a camelized string from a lower case and underscored string by replaceing slash with
   * double-colon and upper-casing each letter preceded by an underscore.
   *
   * @param  string $lower_case_and_underscored_word  String to camelize.
   *
   * @return string Camelized string.
   */
  public static function camelize($lower_case_and_underscored_word)
  {
  	$tmp = $lower_case_and_underscored_word;
  	$tmp = self::pregtr($tmp, array('#/(.?)#e'    => "'::'.strtoupper('\\1')",
  			'/(^|_|-)+(.)/e' => "strtoupper('\\2')"));

  	return $tmp;
  }

  /**
   * Returns an underscore-syntaxed version or the CamelCased string.
   *
   * @param  string $camel_cased_word  String to underscore.
   *
   * @return string Underscored string.
   */
  public static function underscore($camel_cased_word)
  {
  	$tmp = $camel_cased_word;
  	$tmp = str_replace('::', '/', $tmp);
  	$tmp = self::pregtr($tmp, array('/([A-Z]+)([A-Z][a-z])/' => '\\1_\\2',
  			'/([a-z\d])([A-Z])/'     => '\\1_\\2'));

  	return strtolower($tmp);
  }

  /**
   * Returns subject replaced with regular expression matchs
   *
   * @param mixed $search        subject to search
   * @param array $replacePairs  array of search => replace pairs
   */
  public static function pregtr($search, $replacePairs)
  {
  	return preg_replace(array_keys($replacePairs), array_values($replacePairs), $search);
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
        echo "DEBUG ".$msg."\n";
      }
    } else {
      echo "INFO ".$msg."\n";
    }
  }
}

// Run Command.
Migration::main();
