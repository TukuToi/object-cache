<?php
/*
Plugin Name: TukuToi WordPress APCu Object Cache
Description: WordPress APCu Object Cache implementation with small backend stats page.
Version: 1.0.0
Author: bedas
*/

/**
 * Originally inspired by https://github.com/l3rady/object-cache-apcu
 */

namespace TukuToi\APCuCache;

defined('ABSPATH') || exit;

add_action('admin_menu', function() {
    add_submenu_page(
        'tools.php',
        'Cache Stats',
        'Cache Stats',
        'manage_options',
        'cache-stats',
        array( __NAMESPACE__ . \WP_Object_Cache::instance(), 'stats' )
    );
});

function wp_cache_add($key, $data, $group = 'default', $expire = 0)
{
    return WP_Object_Cache::instance()->add($key, $data, $group, $expire);
}

function wp_cache_close()
{
    return true;
}

function wp_cache_decr($key, $offset = 1, $group = 'default')
{
    return WP_Object_Cache::instance()->decr($key, $offset, $group);
}

function wp_cache_delete($key, $group = 'default')
{
    return WP_Object_Cache::instance()->delete($key, $group);
}

function wp_cache_flush()
{
    return WP_Object_Cache::instance()->flush();
}

function wp_cache_get($key, $group = 'default', $force = false, &$found = null)
{
    return WP_Object_Cache::instance()->get($key, $group, $force, $found);
}

function wp_cache_get_multi($groups)
{
    return WP_Object_Cache::instance()->get_multi($groups);
}

function wp_cache_incr($key, $offset = 1, $group = 'default')
{
    return WP_Object_Cache::instance()->incr($key, $offset, $group);
}

function wp_cache_init()
{
    $GLOBALS['wp_object_cache'] = WP_Object_Cache::instance();
}

function wp_cache_replace($key, $data, $group = 'default', $expire = 0)
{
    return WP_Object_Cache::instance()->replace($key, $data, $group, $expire);
}

function wp_cache_set($key, $data, $group = 'default', $expire = 0)
{
    return WP_Object_Cache::instance()->set($key, $data, $group, $expire);
}

function wp_cache_switch_to_blog($blog_id)
{
    WP_Object_Cache::instance()->switch_to_blog($blog_id);
}

function wp_cache_add_global_groups($groups)
{
    WP_Object_Cache::instance()->add_global_groups($groups);
}

function wp_cache_add_non_persistent_groups($groups)
{
    WP_Object_Cache::instance()->add_non_persistent_groups($groups);
}

function wp_cache_reset()
{
    _deprecated_function(__FUNCTION__, '3.5', 'wp_cache_switch_to_blog()');
    return false;
}

function wp_cache_flush_site($sites = null)
{
    return WP_Object_Cache::instance()->flush_sites($sites);
}

function wp_cache_flush_group($groups = 'default')
{
    return WP_Object_Cache::instance()->flush_groups($groups);
}

class WP_Object_Cache
{

    private $abspath;
    private $apcu_available;
    private $blog_prefix;
    private $cache_hits = 0;
    private $cache_misses = 0;
    private $global_groups = [];
    private $group_versions = [];
    private $multi_site;
    private $non_persistent_cache = [];
    private $non_persistent_groups = [];
    private $local_cache = [];
    private $site_versions = [];
    private static $instance;

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new WP_Object_Cache();
        }

        return self::$instance;
    }

    private function __clone()
    {
    }

    private function __construct()
    {
        global $blog_id;

        if (!defined('WP_APCU_KEY_SALT')) {
            define('WP_APCU_KEY_SALT', 'wp');
        }

        if (!defined('WP_APCU_LOCAL_CACHE')) {
            define('WP_APCU_LOCAL_CACHE', true);
        }

        $this->abspath = md5(ABSPATH);
        $this->apcu_available = (extension_loaded('apcu') && ini_get('apc.enabled'));
        $this->multi_site = is_multisite();
        $this->blog_prefix = $this->multi_site ? $blog_id : 1;
    }
    
    public function stats()
    {
        echo '<h1>Cache Stats</h1>';

		if ( $this->apcu_available ) {
			echo "APCu version " . phpversion('apcu') . " is loaded";
		} else {
			echo "APCu is not loaded\n";
		}

        echo '<p>';
        echo "<strong>Cache Hits:</strong> {$this->cache_hits}<br />";
        echo "<strong>Cache Misses:</strong> {$this->cache_misses}<br />";
		echo '<p>Uptime: ' . date('Y-m-d H:i:s', apcu_cache_info()['start_time'] ). '</p>';
        echo '<p>Memory Usage: ' . size_format(apcu_cache_info()['mem_size']) . '</p>';
        echo '<p>Memory Available: ' . size_format(apcu_sma_info()['avail_mem']) . '</p>';
        echo '</p>';
        echo '<ul>';

        foreach ( $this->local_cache as $group => $cache ) {
            echo '<li><strong>Group:</strong> ' . esc_html( $group ) . ' - ( ' . number_format( strlen( serialize( $cache ) ) / KB_IN_BYTES, 2 ) . 'k )</li>';
        }
        echo '</ul>';
    }

    public function add($key, $var, $group = 'default', $ttl = 0)
    {
        if (wp_suspend_cache_addition()) {
            return false;
        }

        $key = $this->_key($key, $group);

        if (!$this->apcu_available || $this->_is_non_persistent_group($group)) {
            return $this->_add_np($key, $var);
        }

        return $this->_add($key, $var, $ttl);
    }

    private function _add($key, $var, $ttl)
    {
        if (apcu_add($key, $var, max((int)$ttl, 0))) {
            if (WP_APCU_LOCAL_CACHE) {
                $this->local_cache[$key] = is_object($var) ? clone $var : $var;
            }
            return true;
        }
        return false;
    }

    private function _add_np($key, $var)
    {
        if ($this->_exists_np($key)) {
            return false;
        }

        return $this->_set_np($key, $var);
    }

    public function add_global_groups($groups)
    {
        foreach ((array)$groups as $group) {
            $this->global_groups[$group] = true;
        }
    }

    public function add_non_persistent_groups($groups)
    {
        foreach ((array)$groups as $group) {
            $this->non_persistent_groups[$group] = true;
        }
    }

    /**
     * Decrement numeric cache item's value
     *
     * @param int|string $key The cache key to increment
     * @param int $offset The amount by which to decrement the item's value. Default is 1.
     * @param string $group The group the key is in.
     *
     * @return false|int False on failure, the item's new value on success.
     */
    public function decr($key, $offset = 1, $group = 'default')
    {
        $key = $this->_key($key, $group);

        if (!$this->apcu_available || $this->_is_non_persistent_group($group)) {
            return $this->_decr_np($key, $offset);
        }

        return $this->_decr($key, $offset);
    }

    /**
     * Decrement numeric APCu cache item's value
     *
     * @param string $key The cache key to increment
     * @param int $offset The amount by which to decrement the item's value. Default is 1.
     *
     * @return false|int False on failure, the item's new value on success.
     */
    private function _decr($key, $offset)
    {
        $this->_get($key, $success);
        if (!$success) {
            return false;
        }

        $value = apcu_dec($key, max((int)$offset, 0));
        if ($value !== false && WP_APCU_LOCAL_CACHE) {
            $this->local_cache[$key] = $value;
        }
        return $value;
    }

    /**
     * Decrement numeric non persistent cache item's value
     *
     * @param string $key The cache key to increment
     * @param int $offset The amount by which to decrement the item's value. Default is 1.
     *
     * @return false|int False on failure, the item's new value on success.
     */
    private function _decr_np($key, $offset)
    {
        if (!$this->_exists_np($key)) {
            return false;
        }

        $offset = max((int)$offset, 0);
        $var = $this->_get_np($key);
        $var = is_numeric($var) ? $var : 0;
        $var -= $offset;

        return $this->_set_np($key, $var);
    }

    /**
     * Remove the contents of the cache key in the group
     *
     * If the cache key does not exist in the group, then nothing will happen.
     *
     * @param int|string $key What the contents in the cache are called
     * @param string $group Where the cache contents are grouped
     * @param bool $deprecated Deprecated.
     *
     * @return bool False if the contents weren't deleted and true on success
     */
    public function delete($key, $group = 'default', $deprecated = false)
    {
        $key = $this->_key($key, $group);

        if (!$this->apcu_available || $this->_is_non_persistent_group($group)) {
            return $this->_delete_np($key);
        }

        return $this->_delete($key);
    }

    /**
     * Remove the contents of the APCu cache key in the group
     *
     * If the cache key does not exist in the group, then nothing will happen.
     *
     * @param string $key What the contents in the cache are called
     *
     * @return bool False if the contents weren't deleted and true on success
     */
    private function _delete($key)
    {
        unset($this->local_cache[$key]);
        return apcu_delete($key);
    }

    /**
     * Remove the contents of the non persistent cache key in the group
     *
     * If the cache key does not exist in the group, then nothing will happen.
     *
     * @param string $key What the contents in the cache are called
     *
     * @return bool False if the contents weren't deleted and true on success
     */
    private function _delete_np($key)
    {
        if (array_key_exists($key, $this->non_persistent_cache)) {
            unset($this->non_persistent_cache[$key]);

            return true;
        }

        return false;
    }

    /**
     * Checks if the cached non persistent key exists
     *
     * @param string $key What the contents in the cache are called
     *
     * @return bool True if cache key exists else false
     */
    private function _exists_np($key)
    {
        return array_key_exists($key, $this->non_persistent_cache);
    }

    /**
     * Clears the object cache of all data
     *
     * @return bool Always returns true
     */
    public function flush()
    {
        $this->non_persistent_cache = [];

        if (WP_APCU_LOCAL_CACHE) {
            $this->local_cache = [];
        }

        if ($this->apcu_available) {
            apcu_clear_cache();
        }

        return true;
    }

    /**
     * Invalidate a groups object cache
     *
     * @param mixed $groups A group or an array of groups to invalidate
     *
     * @return bool
     */
    public function flush_groups($groups)
    {
        $groups = (array)$groups;

        if (empty($groups)) {
            return false;
        }

        foreach ($groups as $group) {
            $version = $this->_get_group_cache_version($group);
            $this->_set_group_cache_version($group, $version + 1);
        }

        return true;
    }

    /**
     * Invalidate a site's object cache
     *
     * @param mixed $sites Sites ID's that want flushing.
     *                     Don't pass a site to flush current site
     *
     * @return bool
     */
    public function flush_sites($sites)
    {
        $sites = (array)$sites;

        if (empty($sites)) {
            $sites = [$this->blog_prefix];
        }

        // Add global groups (site 0) to be flushed.
        if (!in_array(0, $sites, false)) {
            $sites[] = 0;
        }

        foreach ($sites as $site) {
            $version = $this->_get_site_cache_version($site);
            $this->_set_site_cache_version($site, $version + 1);
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
     * @param int|string $key What the contents in the cache are called
     * @param string $group Where the cache contents are grouped
     * @param bool $force Not used.
     * @param bool       &$success
     *
     * @return bool|mixed False on failure to retrieve contents or the cache contents on success
     */
    public function get($key, $group = 'default', $force = false, &$success = null)
    {
        $key = $this->_key($key, $group);

        if (!$this->apcu_available || $this->_is_non_persistent_group($group)) {
            $var = $this->_get_np($key, $success);
        } else {
            $var = $this->_get($key, $success);
        }

        if ($success) {
            $this->cache_hits++;
        } else {
            $this->cache_misses++;
        }

        return $var;
    }

    /**
     * Retrieves the APCu cache contents, if it exists
     *
     * @param string $key What the contents in the cache are called
     * @param bool   &$success
     *
     * @return bool|mixed False on failure to retrieve contents or the cache contents on success
     */
    private function _get($key, &$success = null)
    {
        if (WP_APCU_LOCAL_CACHE && array_key_exists($key, $this->local_cache)
        ) {
            $success = true;
            $var = $this->local_cache[$key];
        } else {
            $var = apcu_fetch($key, $success);
            if ($success && WP_APCU_LOCAL_CACHE) {
                $this->local_cache[$key] = $var;
            }
        }

        if (is_object($var)) {
            $var = clone $var;
        }

        return $var;
    }

    /**
     * Retrieves the non persistent cache contents, if it exists
     *
     * @param string $key What the contents in the cache are called
     * @param bool   &$success
     *
     * @return bool|mixed False on failure to retrieve contents or the cache contents on success
     */
    private function _get_np($key, &$success = null)
    {
        if (array_key_exists($key, $this->non_persistent_cache)) {
            $success = true;
            return $this->non_persistent_cache[$key];
        }

        $success = false;
        return false;
    }

    /**
     * Get the cache version of a given key
     *
     * @param string $key
     *
     * @return int cache version
     */
    private function _get_cache_version($key)
    {
        if ($this->apcu_available) {
            $version = (int)apcu_fetch($key);
        } elseif (array_key_exists($key, $this->non_persistent_cache)) {
            $version = (int)$this->non_persistent_cache[$key];
        } else {
            $version = 0;
        }

        return $version;
    }

    /**
     * Build cache version key
     *
     * @param string $type Type of key, for site or group
     * @param mixed $value the group or site id
     *
     * @return string The key
     */
    private function _get_cache_version_key($type, $value)
    {
        return WP_APCU_KEY_SALT . ':' . $this->abspath . ':' . $type . ':' . $value;
    }

    /**
     * Get the groups cache version
     *
     * @param string $group The group to get version for
     *
     * @return int The group cache version
     */
    private function _get_group_cache_version($group)
    {
        if (!isset($this->group_versions[$group])) {
            $this->group_versions[$group] = $this->_get_cache_version(
                $this->_get_cache_version_key(
                    'GroupVersion',
                    $group
                )
            );
        }

        return $this->group_versions[$group];
    }

    /**
     * Retrieve multiple values from cache.
     *
     * Gets multiple values from cache, including across multiple groups
     *
     * Usage: array( 'group0' => array( 'key0', 'key1', 'key2', ), 'group1' => array( 'key0' ) )
     *
     * @param array $groups Array of groups and keys to retrieve
     *
     * @return array|bool Array of cached values as
     *    array( 'group0' => array( 'key0' => 'value0', 'key1' => 'value1', 'key2' => 'value2', ) )
     *    Non-existent keys are not returned.
     */
    public function get_multi($groups)
    {
        if (empty($groups) || !is_array($groups)) {
            return false;
        }

        $vars = [];
        $success = false;

        foreach ($groups as $group => $keys) {
            $vars[$group] = [];

            foreach ($keys as $key) {
                $var = $this->get($key, $group, false, $success);

                if ($success) {
                    $vars[$group][$key] = $var;
                }
            }
        }

        return $vars;
    }

    /**
     * Get the sites cache version
     *
     * @param int $site The site to get version for
     *
     * @return int The site cache version
     */
    private function _get_site_cache_version($site)
    {
        if (!isset($this->site_versions[$site])) {
            $this->site_versions[$site] = $this->_get_cache_version(
                $this->_get_cache_version_key(
                    'SiteVersion',
                    $site
                )
            );
        }

        return $this->site_versions[$site];
    }

    /**
     * Increment numeric cache item's value
     *
     * @param int|string $key The cache key to increment
     * @param int $offset The amount by which to increment the item's value. Default is 1.
     * @param string $group The group the key is in.
     *
     * @return false|int False on failure, the item's new value on success.
     */
    public function incr($key, $offset = 1, $group = 'default')
    {
        $key = $this->_key($key, $group);

        if (!$this->apcu_available || $this->_is_non_persistent_group($group)) {
            return $this->_incr_np($key, $offset);
        }

        return $this->_incr($key, $offset);
    }

    /**
     * Increment numeric APCu cache item's value
     *
     * @param string $key The cache key to increment
     * @param int $offset The amount by which to increment the item's value. Default is 1.
     *
     * @return false|int False on failure, the item's new value on success.
     */
    private function _incr($key, $offset)
    {
        $this->_get($key, $success);
        if (!$success) {
            return false;
        }

        $value = apcu_inc($key, max((int)$offset, 0));
        if ($value !== false && WP_APCU_LOCAL_CACHE) {
            $this->local_cache[$key] = $value;
        }
        return $value;
    }

    /**
     * Increment numeric non persistent cache item's value
     *
     * @param string $key The cache key to increment
     * @param int $offset The amount by which to increment the item's value. Default is 1.
     *
     * @return false|int False on failure, the item's new value on success.
     */
    private function _incr_np($key, $offset)
    {
        if (!$this->_exists_np($key)) {
            return false;
        }

        $offset = max((int)$offset, 0);
        $var = $this->_get_np($key);
        $var = is_numeric($var) ? $var : 0;
        $var += $offset;

        return $this->_set_np($key, $var);
    }

    /**
     * Checks if the given group is a non persistent group
     *
     * @param string $group The group to be checked
     *
     * @return bool True if the group is a non persistent group else false
     */
    private function _is_non_persistent_group($group)
    {
        return isset($this->non_persistent_groups[$group]);
    }

    /**
     * Works out a cache key based on a given key and group
     *
     * @param int|string $key The key
     * @param string $group The group
     *
     * @return string Returns the calculated cache key
     */
    private function _key($key, $group)
    {
        if (empty($group)) {
            $group = 'default';
        }

        $prefix = 0;

        if (!isset($this->global_groups[$group])) {
            $prefix = $this->blog_prefix;
        }

        $group_version = $this->_get_group_cache_version($group);
        $site_version = $this->_get_site_cache_version($prefix);

        return WP_APCU_KEY_SALT . ':' . $this->abspath . ':' . $prefix . ':' . $group . ':' . $key . ':v' . $site_version . '.' . $group_version;
    }

    /**
     * Replace the contents in the cache, if contents already exist
     *
     * @param int|string $key What to call the contents in the cache
     * @param mixed $var The contents to store in the cache
     * @param string $group Where to group the cache contents
     * @param int $ttl When to expire the cache contents
     *
     * @return bool False if not exists, true if contents were replaced
     */
    public function replace($key, $var, $group = 'default', $ttl = 0)
    {
        $key = $this->_key($key, $group);

        if (!$this->apcu_available || $this->_is_non_persistent_group($group)) {
            return $this->_replace_np($key, $var);
        }

        return $this->_replace($key, $var, $ttl);
    }

    /**
     * Replace the contents in the APCu cache, if contents already exist
     *
     * @param string $key What to call the contents in the cache
     * @param mixed $var The contents to store in the cache
     * @param int $ttl When to expire the cache contents
     *
     * @return bool False if not exists, true if contents were replaced
     */
    private function _replace($key, $var, $ttl)
    {
        $this->_get($key, $success);
        if ($success) {
            return false;
        }

        return $this->_set($key, $var, $ttl);
    }

    /**
     * Replace the contents in the non persistent cache, if contents already exist
     *
     * @param string $key What to call the contents in the cache
     * @param mixed $var The contents to store in the cache
     *
     * @return bool False if not exists, true if contents were replaced
     */
    private function _replace_np($key, $var)
    {
        if (!$this->_exists_np($key)) {
            return false;
        }

        return $this->_set_np($key, $var);
    }

    /**
     * Sets the data contents into the cache
     *
     * @param int|string $key What to call the contents in the cache
     * @param mixed $var The contents to store in the cache
     * @param string $group Where to group the cache contents
     * @param int $ttl When the cache data should be expired
     *
     * @return bool True if cache set successfully else false
     */
    public function set($key, $var, $group = 'default', $ttl = 0)
    {
        $key = $this->_key($key, $group);

        if (!$this->apcu_available || $this->_is_non_persistent_group($group)) {
            return $this->_set_np($key, $var);
        }

        return $this->_set($key, $var, $ttl);
    }

    /**
     * Sets the data contents into the APCu cache
     *
     * @param string $key What to call the contents in the cache
     * @param mixed $var The contents to store in the cache
     * @param int $ttl When the cache data should be expired
     *
     * @return bool True if cache set successfully else false
     */
    private function _set($key, $var, $ttl)
    {
        if (is_object($var)) {
            $var = clone $var;
        }

        if (apcu_store($key, $var, max((int)$ttl, 0))) {
            if (WP_APCU_LOCAL_CACHE) {
                $this->local_cache[$key] = $var;
            }
            return true;
        }

        return false;
    }

    /**
     * Sets the data contents into the non persistent cache
     *
     * @param string $key What to call the contents in the cache
     * @param mixed $var The contents to store in the cache
     *
     * @return bool True if cache set successfully else false
     */
    private function _set_np($key, $var)
    {
        if (is_object($var)) {
            $var = clone $var;
        }

        return $this->non_persistent_cache[$key] = $var;
    }

    /**
     * Set the cache version for a given key
     *
     * @param string $key
     * @param int $version
     *
     * @return mixed
     */
    private function _set_cache_version($key, $version)
    {
        if ($this->apcu_available) {
            return apcu_store($key, $version);
        }

        return $this->non_persistent_cache[$key] = $version;
    }

    /**
     * Set the version for a groups cache
     *
     * @param string $group
     * @param int $version
     */
    private function _set_group_cache_version($group, $version)
    {
        $this->_set_cache_version($this->_get_cache_version_key('GroupVersion', $group), $version);
    }

    /**
     * Set the version for a sites cache
     *
     * @param int $site
     * @param int $version
     */
    private function _set_site_cache_version($site, $version)
    {
        $this->_set_cache_version($this->_get_cache_version_key('SiteVersion', $site), $version);
    }

    /**
     * Switch the internal blog id.
     *
     * This changes the blog id used to create keys in blog specific groups.
     *
     * @param int $blog_id Blog ID
     */
    public function switch_to_blog($blog_id)
    {
        $this->blog_prefix = $this->multi_site ? $blog_id : 1;
    }

    /**
     * @return string
     */
    public function getAbspath()
    {
        return $this->abspath;
    }

    /**
     * @return bool
     */
    public function getApcuAvailable()
    {
        return $this->apcu_available;
    }

    /**
     * @return int
     */
    public function getBlogPrefix()
    {
        return $this->blog_prefix;
    }

    /**
     * @return int
     */
    public function getCacheHits()
    {
        return $this->cache_hits;
    }

    /**
     * @return int
     */
    public function getCacheMisses()
    {
        return $this->cache_misses;
    }

    /**
     * @return array
     */
    public function getGlobalGroups()
    {
        return $this->global_groups;
    }

    /**
     * @return array
     */
    public function getGroupVersions()
    {
        return $this->group_versions;
    }

    /**
     * @return bool
     */
    public function getMultiSite()
    {
        return $this->multi_site;
    }

    /**
     * @return array
     */
    public function getNonPersistentCache()
    {
        return $this->non_persistent_cache;
    }

    /**
     * @return array
     */
    public function getNonPersistentGroups()
    {
        return $this->non_persistent_groups;
    }

    /**
     * @return array
     */
    public function getSiteVersions()
    {
        return $this->site_versions;
    }
}
