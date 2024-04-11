<?php
namespace ShortLinker;

use Exception;

/**
 * The Singleton class defines the `get_instance` method that serves as an
 * alternative to constructor and lets clients access the same instance of this
 * class over and over.
 */
class Singleton {

	/**
	 * The actual singleton's instance almost always resides inside a static
	 * field. In this case, the static field is an array, where each subclass of
	 * the Singleton stores its own instance.
	 *
	 * @var array $instances
	 * @since 1.0.0
	 */
	private static array $instances = array();

	/**
	 * Singleton's constructor should not be public. However, it can't be
	 * private either if we want to allow subclassing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
	}

	/**
	 * Cloning and unserialization are not permitted for singletons.
	 *
	 * @since 1.0.0
	 */
	protected function __clone() {
	}

	/**
	 * Should not be recoverable from strings.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		throw new Exception( 'Cannot unserialize singleton' );
	}

	/**
	 * The method you use to get the Singleton's instance.
	 *
	 * @since 1.0.0
	 */
	public static function get_instance(): Singleton {
		$subclass = static::class;
		if ( ! isset( self::$instances[ $subclass ] ) ) {
			self::$instances[ $subclass ] = new static();
		}
		return self::$instances[ $subclass ];
	}
}
