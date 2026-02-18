# Public API

## Compatibility Contract

The drop-in exposes WordPress object cache function names and maintains backward-compatible signatures and return contracts unless a deliberate breaking change is requested.

## Function Surface

### Core read/write operations

- `wp_cache_add( $key, $data, $group = 'default', $expire = 0 ): bool`
- `wp_cache_set( $key, $data, $group = 'default', $expire = 0 ): bool`
- `wp_cache_replace( $key, $data, $group = 'default', $expire = 0 ): bool`
- `wp_cache_get( $key, $group = 'default', $force = false, &$found = null ): mixed`
- `wp_cache_delete( $key, $group = 'default' ): bool`
- `wp_cache_get_multi( $groups ): array|bool`

### Numeric operations

- `wp_cache_incr( $key, $offset = 1, $group = 'default' ): int|false`
- `wp_cache_decr( $key, $offset = 1, $group = 'default' ): int|false`

### Cache invalidation

- `wp_cache_flush(): bool`
- `wp_cache_flush_group( $groups = 'default' ): bool`
- `wp_cache_flush_site( $sites = null ): bool`

### Group and context configuration

- `wp_cache_add_global_groups( $groups ): void`
- `wp_cache_add_non_persistent_groups( $groups ): void`
- `wp_cache_switch_to_blog( $blog_id ): void`
- `wp_cache_init(): void`
- `wp_cache_close(): true`

### Deprecated shim

- `wp_cache_reset(): false` and triggers deprecation notice in favor of `wp_cache_switch_to_blog()`.

## Behavioral Notes

- `replace` only succeeds when a target key already exists.
- `incr` and `decr` return `false` when the key does not exist.
- `get_multi` returns `false` for invalid or empty input and omits missing keys from result payloads.
- `wp_cache_flush_site()` defaults to current site namespace invalidation when called without explicit site IDs.
