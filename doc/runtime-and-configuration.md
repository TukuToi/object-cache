# Runtime and Configuration

## Runtime Requirements

- WordPress environment with drop-in support for `wp-content/object-cache.php`.
- PHP with APCu extension for persistent object-cache behavior.
- Without APCu, the drop-in remains functional using non-persistent in-request caching.

## Constants

The drop-in initializes defaults when constants are undefined:

- `WP_APCU_KEY_SALT` default: `wp`
- `WP_APCU_LOCAL_CACHE` default: `true`

## APCu Availability Detection

APCu persistence is enabled only when both conditions are true:

- `extension_loaded( 'apcu' )`
- `ini_get( 'apc.enabled' )`

If either check fails, all operations use the non-persistent runtime array semantics.

## Group Persistence Rules

- Global groups are shared across blogs using site prefix `0`.
- Non-persistent groups bypass APCu even when APCu is available.
- All other groups follow blog-aware key prefixing and APCu persistence when available.

## Object Handling

Cached objects are cloned on write and on read paths where applicable to avoid unintended shared mutable references.

## Stats Screen

The admin stats surface provides runtime visibility into:

- APCu availability and extension version.
- Request cache hits and misses.
- APCu uptime and memory metrics when available.
- Group-local memory estimates for local cache content.

The screen is capability-gated and output-escaped.
