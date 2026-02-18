## Local Standards
1. Artifact Contract
    1.1 Repository output must remain a WordPress object-cache drop-in delivered as `object-cache.php`.
        a. Public `wp_cache_*` function signatures and return-value contracts must remain backward compatible unless a deliberate breaking change is requested.
2. Integration Boundary
    2.1 The drop-in must stay directly loadable by WordPress from `wp-content/object-cache.php`.
        a. Changes that require additional bootstrap files or dependency loaders are out of scope unless explicitly requested.
3. Runtime Behavior
    3.1 Cache behavior must support APCu-backed persistence when APCu is available.
    3.2 When APCu is unavailable, behavior must continue to function through non-persistent in-memory caching semantics.
4. Multisite and Group Isolation
    4.1 Changes to cache key construction or namespace invalidation must preserve site and group isolation semantics for single-site and multisite installs.
5. Admin Security Surface
    5.1 The cache stats admin screen must remain capability-gated and output-escaped.

## Tooling and Validation
1. PHP Coding Standards Gate
    1.1 For PHP changes, run the following commands against modified files before finalizing work:
        a. `phpcbf --standard=WordPress-Extra object-cache.php`
        b. `phpcs --standard=WordPress-Extra object-cache.php`
    1.2 Final validation requires a zero-violation `phpcs` result.
2. Ignore-Rule Exception Policy
    2.1 Introducing or expanding `phpcs:ignore` or `phpcs:disable` usage is allowed only when it does not weaken security-relevant behavior.
    2.2 Each new ignore directive must include a specific inline justification comment.
