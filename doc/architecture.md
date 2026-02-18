# Architecture

## Scope and Boundary

The repository output is a single WordPress object-cache drop-in file: `object-cache.php`.

The drop-in is loaded directly by WordPress from `wp-content/object-cache.php` and does not require additional bootstrap files.

## High-Level Design

The implementation provides:

- WordPress-compatible `wp_cache_*` function shims.
- A singleton cache engine class (`WP_Object_Cache`).
- Optional APCu-backed persistence when APCu is available and enabled.
- Request-scoped in-memory behavior when APCu is unavailable or when non-persistent groups are used.

## Data Paths

Cache operations route through `WP_Object_Cache::instance()` and then choose one of two storage paths:

- Persistent path: APCu (`apcu_add`, `apcu_store`, `apcu_fetch`, `apcu_inc`, `apcu_dec`, `apcu_delete`).
- Non-persistent path: an internal PHP array used for request-local caching semantics.

If `WP_APCU_LOCAL_CACHE` is true, a local runtime mirror is used to reduce repeated APCu fetch operations during a request.

## Key Structure and Isolation

Each cache key is composed from:

- `WP_APCU_KEY_SALT`
- `md5(ABSPATH)` namespace
- Site prefix (`0` for global groups, current blog ID for site-specific groups)
- Group name
- User key
- Version suffix (`site_version.group_version`)

This model preserves:

- Installation-level isolation through `ABSPATH` hashing.
- Group-level isolation.
- Site-level isolation for multisite and single-site contexts.

## Namespace Invalidation

Invalidation uses versioned namespaces instead of direct key scans:

- `flush_groups()` increments group namespace versions.
- `flush_sites()` increments site namespace versions and includes site `0` for global groups.
- `flush()` clears request-local structures and clears APCu when available.

## Multisite Behavior

- On multisite, blog-specific groups use the current blog ID as key prefix.
- Global groups use prefix `0`.
- `switch_to_blog()` updates the internal prefix used for key construction.

## Admin Surface

An admin page is registered under `Tools -> Cache Stats` and calls `WP_Object_Cache::stats()`.

Security and output handling characteristics:

- Access is capability-gated (`manage_options`) at menu registration.
- Output is escaped before rendering.
- APCu metadata reads are guarded for missing fields.
