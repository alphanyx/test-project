<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Abstract Multidomain Class
 */
class Multidomain_Abstract {

	private static $_instances = array();

	/**
	 * creates an instance of the called class
	 * @param  array  $config    the configurations
	 * @return class             instance of the called class
	 */
	public static function instance($config = array()) {
		$class = get_called_class();

		if (isset(self::$_instances[$class])) {
			return self::$_instances[$class];
		}

		$reflect = new ReflectionClass($class);
		return self::$_instances[$class] = $reflect->newInstanceArgs(func_get_args());
	}

	public function __construct() { }
}