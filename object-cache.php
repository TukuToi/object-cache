<?php // phpcs:disable WordPress.Files.FileName.InvalidClassFileName,Universal.Files.SeparateFunctionsFromOO.Mixed
/**
 * Plugin Name: TukuToi WordPress APCu Object Cache
 * Description: WordPress APCu Object Cache implementation with small backend stats page.
 * Version: 1.0.0
 * Author: bedas
 *
 * Originally inspired by https://github.com/l3rady/object-cache-apcu.
 */

/**
 * WordPress object-cache drop-in backed by APCu.
 *
 * @package TukuToi\APCuCache
 */

namespace TukuToi\APCuCache;

defined( 'ABSPATH' ) || exit;

add_action(
	'admin_menu',
	function () {
		add_submenu_page(
			'tools.php',
			'Cache Stats',
			'Cache Stats',
			'manage_options',
			'cache-stats',
			array( WP_Object_Cache::instance(), 'stats' )
		);
	}
);

/**
 * Add data to the object cache.
 *
 * @param int|string $key    The cache key to use for retrieval later.
 * @param mixed      $data   The data to add to the cache.
 * @param string     $group  Optional. Where the cache contents are grouped. Default 'default'.
 * @param int        $expire Optional. Not used. Default 0.
 *
 * @return bool True on success, false on failure.
 */
function wp_cache_add( $key, $data, $group = 'default', $expire = 0 ) {
	return WP_Object_Cache::instance()->add( $key, $data, $group, $expire );
}

/**
 * Close the cache.
 *
 * @return true Always returns true.
 */
function wp_cache_close() {
	return true;
}

/**
 * Decrement numeric cache item's value.
 *
 * @param int|string $key    The cache key.
 * @param int        $offset Optional. The amount by which to decrement the item's value. Default 1.
 * @param string     $group  Optional. The group the key is in. Default 'default'.
 *
 * @return int|false Value on success, false on failure.
 */
function wp_cache_decr( $key, $offset = 1, $group = 'default' ) {
	return WP_Object_Cache::instance()->decr( $key, $offset, $group );
}

/**
 * Remove data from the object cache.
 *
 * @param int|string $key   The cache key.
 * @param string     $group Optional. The cache group. Default 'default'.
 *
 * @return bool True on success, false on failure.
 */
function wp_cache_delete( $key, $group = 'default' ) {
	return WP_Object_Cache::instance()->delete( $key, $group );
}

/**
 * Clear the object cache.
 *
 * @return bool True on success.
 */
function wp_cache_flush() {
	return WP_Object_Cache::instance()->flush();
}

/**
 * Retrieve data from the object cache.
 *
 * @param int|string $key   The cache key.
 * @param string     $group Optional. The cache group. Default 'default'.
 * @param bool       $force Optional. Whether to force an update from persistent cache. Default false.
 * @param bool       $found Optional. Whether the key was found in cache. Passed by reference.
 *
 * @return mixed The cached value or false if not found.
 */
function wp_cache_get( $key, $group = 'default', $force = false, &$found = null ) {
	return WP_Object_Cache::instance()->get( $key, $group, $force, $found );
}

/**
 * Retrieve multiple values from cache.
 *
 * @param array $groups Array of cache groups and keys.
 *
 * @return array|bool Array of found values, or false on invalid input.
 */
function wp_cache_get_multi( $groups ) {
	return WP_Object_Cache::instance()->get_multi( $groups );
}

/**
 * Increment numeric cache item's value.
 *
 * @param int|string $key    The cache key.
 * @param int        $offset Optional. The amount by which to increment the item's value. Default 1.
 * @param string     $group  Optional. The group the key is in. Default 'default'.
 *
 * @return int|false Value on success, false on failure.
 */
function wp_cache_incr( $key, $offset = 1, $group = 'default' ) {
	return WP_Object_Cache::instance()->incr( $key, $offset, $group );
}

/**
 * Initialize the global object cache instance.
 *
 * @return void
 */
function wp_cache_init() {
	// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Core object-cache drop-ins initialize this global.
	$GLOBALS['wp_object_cache'] = WP_Object_Cache::instance();
}

/**
 * Replace data in the object cache if it already exists.
 *
 * @param int|string $key    The cache key.
 * @param mixed      $data   The data to replace.
 * @param string     $group  Optional. The cache group. Default 'default'.
 * @param int        $expire Optional. Time to live in seconds. Default 0.
 *
 * @return bool True on success, false on failure.
 */
function wp_cache_replace( $key, $data, $group = 'default', $expire = 0 ) {
	return WP_Object_Cache::instance()->replace( $key, $data, $group, $expire );
}

/**
 * Set data in the object cache.
 *
 * @param int|string $key    The cache key.
 * @param mixed      $data   The data to store.
 * @param string     $group  Optional. The cache group. Default 'default'.
 * @param int        $expire Optional. Time to live in seconds. Default 0.
 *
 * @return bool True on success, false on failure.
 */
function wp_cache_set( $key, $data, $group = 'default', $expire = 0 ) {
	return WP_Object_Cache::instance()->set( $key, $data, $group, $expire );
}

/**
 * Switch internal blog context.
 *
 * @param int $blog_id Blog ID to switch to.
 *
 * @return void
 */
function wp_cache_switch_to_blog( $blog_id ) {
	WP_Object_Cache::instance()->switch_to_blog( $blog_id );
}

/**
 * Register global cache groups.
 *
 * @param string[] $groups Cache groups that should be global.
 *
 * @return void
 */
function wp_cache_add_global_groups( $groups ) {
	WP_Object_Cache::instance()->add_global_groups( $groups );
}

/**
 * Register non-persistent cache groups.
 *
 * @param string[] $groups Cache groups that should bypass APCu persistence.
 *
 * @return void
 */
function wp_cache_add_non_persistent_groups( $groups ) {
	WP_Object_Cache::instance()->add_non_persistent_groups( $groups );
}

/**
 * Deprecated cache reset shim.
 *
 * @return false Always returns false.
 */
function wp_cache_reset() {
	_deprecated_function( __FUNCTION__, '3.5', 'wp_cache_switch_to_blog()' );
	return false;
}

/**
 * Invalidate one or more site cache namespaces.
 *
 * @param int[]|int|null $sites Optional. Site ID or array of site IDs.
 *
 * @return bool True on success, false on invalid input.
 */
function wp_cache_flush_site( $sites = null ) {
	return WP_Object_Cache::instance()->flush_sites( $sites );
}

/**
 * Invalidate one or more group cache namespaces.
 *
 * @param string[]|string $groups Optional. Group name or array of group names.
 *
 * @return bool True on success, false on invalid input.
 */
function wp_cache_flush_group( $groups = 'default' ) {
	return WP_Object_Cache::instance()->flush_groups( $groups );
}

/**
 * APCu-backed WordPress object cache implementation.
 */
class WP_Object_Cache {
	/**
	 * Absolute path hash used to namespace cache keys.
	 *
	 * @var string
	 */
	private $abspath;

	/**
	 * Whether APCu is available and enabled.
	 *
	 * @var bool
	 */
	private $apcu_available;

	/**
	 * Current blog prefix used in cache keys.
	 *
	 * @var int|string
	 */
	private $blog_prefix;

	/**
	 * Number of cache hits for this request.
	 *
	 * @var int
	 */
	private $cache_hits = 0;

	/**
	 * Number of cache misses for this request.
	 *
	 * @var int
	 */
	private $cache_misses = 0;

	/**
	 * Cache groups that are shared network-wide.
	 *
	 * @var array<string, bool>
	 */
	private $global_groups = array();

	/**
	 * In-memory group version map.
	 *
	 * @var array<string, int>
	 */
	private $group_versions = array();

	/**
	 * Whether multisite mode is enabled.
	 *
	 * @var bool
	 */
	private $multi_site;

	/**
	 * Runtime-only storage for non-persistent groups and fallback mode.
	 *
	 * @var array<string, mixed>
	 */
	private $non_persistent_cache = array();

	/**
	 * Registered non-persistent groups.
	 *
	 * @var array<string, bool>
	 */
	private $non_persistent_groups = array();

	/**
	 * Per-request local mirror of APCu keys.
	 *
	 * @var array<string, mixed>
	 */
	private $local_cache = array();

	/**
	 * In-memory site version map.
	 *
	 * @var array<int|string, int>
	 */
	private $site_versions = array();

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance;

	/**
	 * Get the singleton cache instance.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new WP_Object_Cache();
		}

		return self::$instance;
	}

	/**
	 * Prevent cloning.
	 *
	 * @return void
	 */
	private function __clone() {
	}

	/**
	 * Build the cache instance and initialize runtime settings.
	 *
	 * @return void
	 */
	private function __construct() {
		global $blog_id;

		if ( ! defined( 'WP_APCU_KEY_SALT' ) ) {
			define( 'WP_APCU_KEY_SALT', 'wp' );
		}

		if ( ! defined( 'WP_APCU_LOCAL_CACHE' ) ) {
			define( 'WP_APCU_LOCAL_CACHE', true );
		}

		$this->abspath        = md5( ABSPATH );
		$this->apcu_available = ( extension_loaded( 'apcu' ) && ini_get( 'apc.enabled' ) );
		$this->multi_site     = is_multisite();
		$this->blog_prefix    = $this->multi_site ? $blog_id : 1;
	}

	/**
	 * Render cache stats in wp-admin.
	 *
	 * @return void
	 */
	public function stats() {
		$cache_info = array();
		$sma_info   = array();

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Cache Stats', 'tukutoi-apcu-cache' ) . '</h1>';

		if ( $this->apcu_available ) {
			$apcu_version = phpversion( 'apcu' );
			if ( false === $apcu_version ) {
				$apcu_version = 'unknown';
			}

			printf(
				'<p>%s</p>',
				esc_html(
					sprintf(
						/* translators: %s is the APCu extension version. */
						__( 'APCu version %s is loaded.', 'tukutoi-apcu-cache' ),
						$apcu_version
					)
				)
			);

			$cache_info = apcu_cache_info();
			$sma_info   = apcu_sma_info();
		} else {
			echo '<p>' . esc_html__( 'APCu is not loaded.', 'tukutoi-apcu-cache' ) . '</p>';
		}

		printf(
			'<p><strong>%s</strong> %s</p>',
			esc_html__( 'Cache Hits:', 'tukutoi-apcu-cache' ),
			esc_html( number_format_i18n( $this->cache_hits ) )
		);
		printf(
			'<p><strong>%s</strong> %s</p>',
			esc_html__( 'Cache Misses:', 'tukutoi-apcu-cache' ),
			esc_html( number_format_i18n( $this->cache_misses ) )
		);

		if ( isset( $cache_info['start_time'] ) ) {
			printf(
				'<p><strong>%s</strong> %s</p>',
				esc_html__( 'Uptime:', 'tukutoi-apcu-cache' ),
				esc_html( gmdate( 'Y-m-d H:i:s', (int) $cache_info['start_time'] ) )
			);
		}

		if ( isset( $cache_info['mem_size'] ) ) {
			printf(
				'<p><strong>%s</strong> %s</p>',
				esc_html__( 'Memory Usage:', 'tukutoi-apcu-cache' ),
				esc_html( size_format( (int) $cache_info['mem_size'] ) )
			);
		}

		if ( isset( $sma_info['avail_mem'] ) ) {
			printf(
				'<p><strong>%s</strong> %s</p>',
				esc_html__( 'Memory Available:', 'tukutoi-apcu-cache' ),
				esc_html( size_format( (int) $sma_info['avail_mem'] ) )
			);
		}

		echo '<ul>';

		foreach ( $this->local_cache as $group => $cache ) {
			$encoded_cache = wp_json_encode( $cache );
			if ( false === $encoded_cache ) {
				$encoded_cache = '';
			}

			$size_in_kb = (float) strlen( $encoded_cache ) / KB_IN_BYTES;
			printf(
				'<li><strong>%s</strong> %s - (%s)</li>',
				esc_html__( 'Group:', 'tukutoi-apcu-cache' ),
				esc_html( (string) $group ),
				esc_html( number_format_i18n( $size_in_kb, 2 ) . 'k' )
			);
		}
		echo '</ul>';
		echo '</div>';
	}

	/**
	 * Add data to cache if it does not already exist.
	 *
	 * @param int|string $key   Cache key.
	 * @param mixed      $data  Data to cache.
	 * @param string     $group Optional. Cache group. Default 'default'.
	 * @param int        $ttl   Optional. Time to live in seconds. Default 0.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function add( $key, $data, $group = 'default', $ttl = 0 ) {
		if ( wp_suspend_cache_addition() ) {
			return false;
		}

		$key = $this->build_cache_key( $key, $group );

		if ( ! $this->apcu_available || $this->is_non_persistent_group( $group ) ) {
			return $this->add_to_non_persistent_cache( $key, $data );
		}

		return $this->add_to_apcu( $key, $data, $ttl );
	}

	/**
	 * Add data to APCu when key does not exist.
	 *
	 * @param string $key  Fully qualified cache key.
	 * @param mixed  $data Data to store.
	 * @param int    $ttl  Time to live in seconds.
	 *
	 * @return bool True on success, false on failure.
	 */
	private function add_to_apcu( $key, $data, $ttl ) {
		if ( apcu_add( $key, $data, max( (int) $ttl, 0 ) ) ) {
			if ( WP_APCU_LOCAL_CACHE ) {
				$this->local_cache[ $key ] = is_object( $data ) ? clone $data : $data;
			}
			return true;
		}
		return false;
	}

	/**
	 * Add data to the runtime-only cache when key does not exist.
	 *
	 * @param string $key  Fully qualified cache key.
	 * @param mixed  $data Data to store.
	 *
	 * @return bool True on success, false on failure.
	 */
	private function add_to_non_persistent_cache( $key, $data ) {
		if ( $this->non_persistent_key_exists( $key ) ) {
			return false;
		}

		return $this->set_to_non_persistent_cache( $key, $data );
	}

	/**
	 * Register cache groups that are shared across blogs.
	 *
	 * @param string[] $groups Group names to register.
	 *
	 * @return void
	 */
	public function add_global_groups( $groups ) {
		foreach ( (array) $groups as $group ) {
			$this->global_groups[ $group ] = true;
		}
	}

	/**
	 * Register cache groups that should never be persisted to APCu.
	 *
	 * @param string[] $groups Group names to register.
	 *
	 * @return void
	 */
	public function add_non_persistent_groups( $groups ) {
		foreach ( (array) $groups as $group ) {
			$this->non_persistent_groups[ $group ] = true;
		}
	}

	/**
	 * Decrement numeric cache item's value
	 *
	 * @param int|string $key The cache key to increment.
	 * @param int        $offset The amount by which to decrement the item's value. Default is 1.
	 * @param string     $group The group the key is in.
	 *
	 * @return false|int False on failure, the item's new value on success.
	 */
	public function decr( $key, $offset = 1, $group = 'default' ) {
		$key = $this->build_cache_key( $key, $group );

		if ( ! $this->apcu_available || $this->is_non_persistent_group( $group ) ) {
			return $this->decrement_non_persistent_cache( $key, $offset );
		}

		return $this->decrement_apcu( $key, $offset );
	}

	/**
	 * Decrement numeric APCu cache item's value
	 *
	 * @param string $key The cache key to increment.
	 * @param int    $offset The amount by which to decrement the item's value. Default is 1.
	 *
	 * @return false|int False on failure, the item's new value on success.
	 */
	private function decrement_apcu( $key, $offset ) {
		$this->get_from_apcu( $key, $success );
		if ( ! $success ) {
			return false;
		}

		$value = apcu_dec( $key, max( (int) $offset, 0 ) );
		if ( false !== $value && WP_APCU_LOCAL_CACHE ) {
			$this->local_cache[ $key ] = $value;
		}
		return $value;
	}

	/**
	 * Decrement numeric non persistent cache item's value
	 *
	 * @param string $key The cache key to increment.
	 * @param int    $offset The amount by which to decrement the item's value. Default is 1.
	 *
	 * @return false|int False on failure, the item's new value on success.
	 */
	private function decrement_non_persistent_cache( $key, $offset ) {
		if ( ! $this->non_persistent_key_exists( $key ) ) {
			return false;
		}

		$offset = max( (int) $offset, 0 );
		$data   = $this->get_from_non_persistent_cache( $key );
		$data   = is_numeric( $data ) ? $data : 0;
		$data  -= $offset;

		$this->set_to_non_persistent_cache( $key, $data );
		return $data;
	}

	/**
	 * Remove the contents of the cache key in the group
	 *
	 * If the cache key does not exist in the group, then nothing will happen.
	 *
	 * @param int|string $key What the contents in the cache are called.
	 * @param string     $group Where the cache contents are grouped.
	 * @param bool       $deprecated Deprecated.
	 *
	 * @return bool False if the contents weren't deleted and true on success
	 */
	public function delete( $key, $group = 'default', $deprecated = false ) {
		if ( $deprecated ) {
			_deprecated_argument( __METHOD__, '2.0.0', '$deprecated' );
		}

		$key = $this->build_cache_key( $key, $group );

		if ( ! $this->apcu_available || $this->is_non_persistent_group( $group ) ) {
			return $this->delete_from_non_persistent_cache( $key );
		}

		return $this->delete_from_apcu( $key );
	}

	/**
	 * Remove the contents of the APCu cache key in the group
	 *
	 * If the cache key does not exist in the group, then nothing will happen.
	 *
	 * @param string $key What the contents in the cache are called.
	 *
	 * @return bool False if the contents weren't deleted and true on success
	 */
	private function delete_from_apcu( $key ) {
		unset( $this->local_cache[ $key ] );
		return apcu_delete( $key );
	}

	/**
	 * Remove the contents of the non persistent cache key in the group
	 *
	 * If the cache key does not exist in the group, then nothing will happen.
	 *
	 * @param string $key What the contents in the cache are called.
	 *
	 * @return bool False if the contents weren't deleted and true on success
	 */
	private function delete_from_non_persistent_cache( $key ) {
		if ( array_key_exists( $key, $this->non_persistent_cache ) ) {
			unset( $this->non_persistent_cache[ $key ] );

			return true;
		}

		return false;
	}

	/**
	 * Checks if the cached non persistent key exists
	 *
	 * @param string $key What the contents in the cache are called.
	 *
	 * @return bool True if cache key exists else false
	 */
	private function non_persistent_key_exists( $key ) {
		return array_key_exists( $key, $this->non_persistent_cache );
	}

	/**
	 * Clears the object cache of all data
	 *
	 * @return bool Always returns true
	 */
	public function flush() {
		$this->non_persistent_cache = array();

		if ( WP_APCU_LOCAL_CACHE ) {
			$this->local_cache = array();
		}

		if ( $this->apcu_available ) {
			apcu_clear_cache();
		}

		return true;
	}

	/**
	 * Invalidate a groups object cache
	 *
	 * @param mixed $groups A group or an array of groups to invalidate.
	 *
	 * @return bool
	 */
	public function flush_groups( $groups ) {
		$groups = (array) $groups;

		if ( empty( $groups ) ) {
			return false;
		}

		foreach ( $groups as $group ) {
			$version = $this->get_group_cache_version( $group );
			$this->set_group_cache_version( $group, $version + 1 );
		}

		return true;
	}

	/**
	 * Invalidate a site's object cache
	 *
	 * @param mixed $sites Sites ID's that want flushing.
	 *                     Do not pass a site to flush current site.
	 *
	 * @return bool
	 */
	public function flush_sites( $sites ) {
		$sites = (array) $sites;

		if ( empty( $sites ) ) {
			$sites = array( $this->blog_prefix );
		}

		// Add global groups (site 0) to be flushed.
		if ( ! in_array( 0, $sites, true ) ) {
			$sites[] = 0;
		}

		foreach ( $sites as $site ) {
			$version = $this->get_site_cache_version( $site );
			$this->set_site_cache_version( $site, $version + 1 );
		}

		return true;
	}

	/**
	 * Retrieves the cache contents, if it exists
	 *
	 * The contents will be first attempted to be retrieved by searching by the
	 * key in the cache key. If the cache is hit (success) then the contents
	 * are returned.
	 *
	 * On failure, the number of cache misses will be incremented.
	 *
	 * @param int|string $key What the contents in the cache are called.
	 * @param string     $group Where the cache contents are grouped.
	 * @param bool       $force Not used.
	 * @param bool       &$success Whether the key was found.
	 *
	 * @return bool|mixed False on failure to retrieve contents or the cache contents on success
	 */
	public function get( $key, $group = 'default', $force = false, &$success = null ) {
		$key = $this->build_cache_key( $key, $group );

		if ( ! $this->apcu_available || $this->is_non_persistent_group( $group ) ) {
			$data = $this->get_from_non_persistent_cache( $key, $success );
		} else {
			$data = $this->get_from_apcu( $key, $success );
		}

		if ( $success ) {
			++$this->cache_hits;
		} else {
			++$this->cache_misses;
		}

		return $data;
	}

	/**
	 * Retrieves the APCu cache contents, if it exists
	 *
	 * @param string $key What the contents in the cache are called.
	 * @param bool   &$success Whether the key was found.
	 *
	 * @return bool|mixed False on failure to retrieve contents or the cache contents on success
	 */
	private function get_from_apcu( $key, &$success = null ) {
		if ( WP_APCU_LOCAL_CACHE && array_key_exists( $key, $this->local_cache )
		) {
			$success = true;
			$data    = $this->local_cache[ $key ];
		} else {
			$data = apcu_fetch( $key, $success );
			if ( $success && WP_APCU_LOCAL_CACHE ) {
				$this->local_cache[ $key ] = $data;
			}
		}

		if ( is_object( $data ) ) {
			$data = clone $data;
		}

		return $data;
	}

	/**
	 * Retrieves the non persistent cache contents, if it exists
	 *
	 * @param string $key What the contents in the cache are called.
	 * @param bool   &$success Whether the key was found.
	 *
	 * @return bool|mixed False on failure to retrieve contents or the cache contents on success
	 */
	private function get_from_non_persistent_cache( $key, &$success = null ) {
		if ( array_key_exists( $key, $this->non_persistent_cache ) ) {
			$success = true;
			return $this->non_persistent_cache[ $key ];
		}

		$success = false;
		return false;
	}

	/**
	 * Get the cache version of a given key
	 *
	 * @param string $key Cache version key.
	 *
	 * @return int cache version
	 */
	private function get_cache_version( $key ) {
		if ( $this->apcu_available ) {
			$version = (int) apcu_fetch( $key );
		} elseif ( array_key_exists( $key, $this->non_persistent_cache ) ) {
			$version = (int) $this->non_persistent_cache[ $key ];
		} else {
			$version = 0;
		}

		return $version;
	}

	/**
	 * Build cache version key
	 *
	 * @param string $type Type of key, for site or group.
	 * @param mixed  $value the group or site id.
	 *
	 * @return string The key
	 */
	private function get_cache_version_key( $type, $value ) {
		return WP_APCU_KEY_SALT . ':' . $this->abspath . ':' . $type . ':' . $value;
	}

	/**
	 * Get the groups cache version
	 *
	 * @param string $group The group to get version for.
	 *
	 * @return int The group cache version
	 */
	private function get_group_cache_version( $group ) {
		if ( ! isset( $this->group_versions[ $group ] ) ) {
			$this->group_versions[ $group ] = $this->get_cache_version(
				$this->get_cache_version_key(
					'GroupVersion',
					$group
				)
			);
		}

		return $this->group_versions[ $group ];
	}

	/**
	 * Retrieve multiple values from cache.
	 *
	 * Gets multiple values from cache, including across multiple groups
	 *
	 * Usage: array( 'group0' => array( 'key0', 'key1', 'key2', ), 'group1' => array( 'key0' ) )
	 *
	 * @param array $groups Array of groups and keys to retrieve.
	 *
	 * @return array|bool Array of cached values as
	 *    array( 'group0' => array( 'key0' => 'value0', 'key1' => 'value1', 'key2' => 'value2', ) )
	 *    Non-existent keys are not returned.
	 */
	public function get_multi( $groups ) {
		if ( empty( $groups ) || ! is_array( $groups ) ) {
			return false;
		}

		$datas   = array();
		$success = false;

		foreach ( $groups as $group => $keys ) {
			$datas[ $group ] = array();

			foreach ( $keys as $key ) {
				$data = $this->get( $key, $group, false, $success );

				if ( $success ) {
					$datas[ $group ][ $key ] = $data;
				}
			}
		}

		return $datas;
	}

	/**
	 * Get the sites cache version
	 *
	 * @param int $site The site to get version for.
	 *
	 * @return int The site cache version
	 */
	private function get_site_cache_version( $site ) {
		if ( ! isset( $this->site_versions[ $site ] ) ) {
			$this->site_versions[ $site ] = $this->get_cache_version(
				$this->get_cache_version_key(
					'SiteVersion',
					$site
				)
			);
		}

		return $this->site_versions[ $site ];
	}

	/**
	 * Increment numeric cache item's value
	 *
	 * @param int|string $key The cache key to increment.
	 * @param int        $offset The amount by which to increment the item's value. Default is 1.
	 * @param string     $group The group the key is in.
	 *
	 * @return false|int False on failure, the item's new value on success.
	 */
	public function incr( $key, $offset = 1, $group = 'default' ) {
		$key = $this->build_cache_key( $key, $group );

		if ( ! $this->apcu_available || $this->is_non_persistent_group( $group ) ) {
			return $this->increment_non_persistent_cache( $key, $offset );
		}

		return $this->increment_apcu( $key, $offset );
	}

	/**
	 * Increment numeric APCu cache item's value
	 *
	 * @param string $key The cache key to increment.
	 * @param int    $offset The amount by which to increment the item's value. Default is 1.
	 *
	 * @return false|int False on failure, the item's new value on success.
	 */
	private function increment_apcu( $key, $offset ) {
		$this->get_from_apcu( $key, $success );
		if ( ! $success ) {
			return false;
		}

		$value = apcu_inc( $key, max( (int) $offset, 0 ) );
		if ( false !== $value && WP_APCU_LOCAL_CACHE ) {
			$this->local_cache[ $key ] = $value;
		}
		return $value;
	}

	/**
	 * Increment numeric non persistent cache item's value
	 *
	 * @param string $key The cache key to increment.
	 * @param int    $offset The amount by which to increment the item's value. Default is 1.
	 *
	 * @return false|int False on failure, the item's new value on success.
	 */
	private function increment_non_persistent_cache( $key, $offset ) {
		if ( ! $this->non_persistent_key_exists( $key ) ) {
			return false;
		}

		$offset = max( (int) $offset, 0 );
		$data   = $this->get_from_non_persistent_cache( $key );
		$data   = is_numeric( $data ) ? $data : 0;
		$data  += $offset;

		$this->set_to_non_persistent_cache( $key, $data );
		return $data;
	}

	/**
	 * Checks if the given group is a non persistent group
	 *
	 * @param string $group The group to be checked.
	 *
	 * @return bool True if the group is a non persistent group else false
	 */
	private function is_non_persistent_group( $group ) {
		return isset( $this->non_persistent_groups[ $group ] );
	}

	/**
	 * Works out a cache key based on a given key and group
	 *
	 * @param int|string $key The key.
	 * @param string     $group The group.
	 *
	 * @return string Returns the calculated cache key
	 */
	private function build_cache_key( $key, $group ) {
		if ( empty( $group ) ) {
			$group = 'default';
		}

		$prefix = 0;

		if ( ! isset( $this->global_groups[ $group ] ) ) {
			$prefix = $this->blog_prefix;
		}

		$group_version = $this->get_group_cache_version( $group );
		$site_version  = $this->get_site_cache_version( $prefix );

		return WP_APCU_KEY_SALT . ':' . $this->abspath . ':' . $prefix . ':' . $group . ':' . $key . ':v' . $site_version . '.' . $group_version;
	}

	/**
	 * Replace the contents in the cache, if contents already exist
	 *
	 * @param int|string $key What to call the contents in the cache.
	 * @param mixed      $data The contents to store in the cache.
	 * @param string     $group Where to group the cache contents.
	 * @param int        $ttl When to expire the cache contents.
	 *
	 * @return bool False if not exists, true if contents were replaced
	 */
	public function replace( $key, $data, $group = 'default', $ttl = 0 ) {
		$key = $this->build_cache_key( $key, $group );

		if ( ! $this->apcu_available || $this->is_non_persistent_group( $group ) ) {
			return $this->replace_in_non_persistent_cache( $key, $data );
		}

		return $this->replace_in_apcu( $key, $data, $ttl );
	}

	/**
	 * Replace the contents in the APCu cache, if contents already exist
	 *
	 * @param string $key What to call the contents in the cache.
	 * @param mixed  $data The contents to store in the cache.
	 * @param int    $ttl When to expire the cache contents.
	 *
	 * @return bool False if not exists, true if contents were replaced
	 */
	private function replace_in_apcu( $key, $data, $ttl ) {
		$this->get_from_apcu( $key, $success );
		if ( ! $success ) {
			return false;
		}

		return $this->set_to_apcu( $key, $data, $ttl );
	}

	/**
	 * Replace the contents in the non persistent cache, if contents already exist
	 *
	 * @param string $key What to call the contents in the cache.
	 * @param mixed  $data The contents to store in the cache.
	 *
	 * @return bool False if not exists, true if contents were replaced
	 */
	private function replace_in_non_persistent_cache( $key, $data ) {
		if ( ! $this->non_persistent_key_exists( $key ) ) {
			return false;
		}

		return $this->set_to_non_persistent_cache( $key, $data );
	}

	/**
	 * Sets the data contents into the cache
	 *
	 * @param int|string $key What to call the contents in the cache.
	 * @param mixed      $data The contents to store in the cache.
	 * @param string     $group Where to group the cache contents.
	 * @param int        $ttl When the cache data should be expired.
	 *
	 * @return bool True if cache set successfully else false
	 */
	public function set( $key, $data, $group = 'default', $ttl = 0 ) {
		$key = $this->build_cache_key( $key, $group );

		if ( ! $this->apcu_available || $this->is_non_persistent_group( $group ) ) {
			return $this->set_to_non_persistent_cache( $key, $data );
		}

		return $this->set_to_apcu( $key, $data, $ttl );
	}

	/**
	 * Sets the data contents into the APCu cache
	 *
	 * @param string $key What to call the contents in the cache.
	 * @param mixed  $data The contents to store in the cache.
	 * @param int    $ttl When the cache data should be expired.
	 *
	 * @return bool True if cache set successfully else false
	 */
	private function set_to_apcu( $key, $data, $ttl ) {
		if ( is_object( $data ) ) {
			$data = clone $data;
		}

		if ( apcu_store( $key, $data, max( (int) $ttl, 0 ) ) ) {
			if ( WP_APCU_LOCAL_CACHE ) {
				$this->local_cache[ $key ] = $data;
			}
			return true;
		}

		return false;
	}

	/**
	 * Sets the data contents into the non persistent cache
	 *
	 * @param string $key What to call the contents in the cache.
	 * @param mixed  $data The contents to store in the cache.
	 *
	 * @return bool True if cache set successfully else false
	 */
	private function set_to_non_persistent_cache( $key, $data ) {
		if ( is_object( $data ) ) {
			$data = clone $data;
		}

		$this->non_persistent_cache[ $key ] = $data;
		return true;
	}

	/**
	 * Set the cache version for a given key
	 *
	 * @param string $key     Cache version key.
	 * @param int    $version Cache version value.
	 *
	 * @return mixed
	 */
	private function set_cache_version( $key, $version ) {
		if ( $this->apcu_available ) {
			return apcu_store( $key, $version );
		}

		$this->non_persistent_cache[ $key ] = $version;
		return true;
	}

	/**
	 * Set the version for a groups cache
	 *
	 * @param string $group   Group name.
	 * @param int    $version New group cache version.
	 */
	private function set_group_cache_version( $group, $version ) {
		$this->set_cache_version( $this->get_cache_version_key( 'GroupVersion', $group ), $version );
	}

	/**
	 * Set the version for a sites cache
	 *
	 * @param int $site    Site ID.
	 * @param int $version New site cache version.
	 */
	private function set_site_cache_version( $site, $version ) {
		$this->set_cache_version( $this->get_cache_version_key( 'SiteVersion', $site ), $version );
	}

	/**
	 * Switch the internal blog id.
	 *
	 * This changes the blog id used to create keys in blog specific groups.
	 *
	 * @param int $blog_id Blog ID.
	 */
	public function switch_to_blog( $blog_id ) {
		$this->blog_prefix = $this->multi_site ? $blog_id : 1;
	}

	/**
	 * Get the ABSPATH hash used in cache keys.
	 *
	 * @return string
	 */
	public function get_abspath() {
		return $this->abspath;
	}

	/**
	 * Determine whether APCu is available.
	 *
	 * @return bool
	 */
	public function get_apcu_available() {
		return $this->apcu_available;
	}

	/**
	 * Get the current blog prefix.
	 *
	 * @return int
	 */
	public function get_blog_prefix() {
		return $this->blog_prefix;
	}

	/**
	 * Get the in-request cache hit count.
	 *
	 * @return int
	 */
	public function get_cache_hits() {
		return $this->cache_hits;
	}

	/**
	 * Get the in-request cache miss count.
	 *
	 * @return int
	 */
	public function get_cache_misses() {
		return $this->cache_misses;
	}

	/**
	 * Get registered global groups.
	 *
	 * @return array
	 */
	public function get_global_groups() {
		return $this->global_groups;
	}

	/**
	 * Get the in-memory group version map.
	 *
	 * @return array
	 */
	public function get_group_versions() {
		return $this->group_versions;
	}

	/**
	 * Determine whether multisite mode is enabled.
	 *
	 * @return bool
	 */
	public function get_multi_site() {
		return $this->multi_site;
	}

	/**
	 * Get non-persistent cache values for the current request.
	 *
	 * @return array
	 */
	public function get_non_persistent_cache() {
		return $this->non_persistent_cache;
	}

	/**
	 * Get non-persistent group registrations.
	 *
	 * @return array
	 */
	public function get_non_persistent_groups() {
		return $this->non_persistent_groups;
	}

	/**
	 * Get the in-memory site version map.
	 *
	 * @return array
	 */
	public function get_site_versions() {
		return $this->site_versions;
	}
}
