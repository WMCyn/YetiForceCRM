<?php

namespace App\Cache;

/**
 * Base caching class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Base
{
	private static $cache = [];

	/**
	 * Is apcu is available.
	 *
	 * @return bool
	 */
	public static function isSupported()
	{
		return true;
	}

	/**
	 * Returns a cache item representing the specified key.
	 *
	 * @param string|array $key Cache ID
	 *
	 * @return string|array
	 */
	public function get($key)
	{
		return static::$cache[$key] ?? false;
	}

	/**
	 * Confirms if the cache contains specified cache item.
	 *
	 * @param string|array $key Cache ID
	 *
	 * @return bool
	 */
	public function has($key)
	{
		return isset(static::$cache[$key]);
	}

	/**
	 * Cache save.
	 *
	 * @param string       $key      Cache ID
	 * @param string|array $value    Data to store
	 * @param int          $duration Cache TTL (in seconds)
	 *
	 * @return bool
	 */
	public function save($key, $value = null, $duration = false)
	{
		static::$cache[$key] = $value;
		unset($duration);
		return true;
	}

	/**
	 * Removes the item from the cache.
	 *
	 * @param string|array $key Cache ID
	 *
	 * @return bool
	 */
	public function delete($key)
	{
		unset(static::$cache[$key]);
	}

	/**
	 * Deletes all items in the cache.
	 *
	 * @return bool
	 */
	public function clear()
	{
		static::$cache = [];
	}
}
