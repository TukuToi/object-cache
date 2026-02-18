# Changelog 
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.1] - 2026-01-18

### Added
- Added a modular `doc/` documentation set with architecture, public API, runtime configuration, and validation workflow references.
- Added `doc/README.md` as the documentation entrypoint for project documentation navigation.

## [1.1.0] - 2026-01-18

### Added
- Added repository-specific `AGENTS.md` policy for drop-in boundaries, runtime expectations, and WordPress Extra PHPCS and PHPCBF validation requirements.

### Changed
- Refactored `object-cache.php` to satisfy WordPress Coding Standards across naming, formatting, and inline documentation.
- Hardened wp-admin cache stats output with escaped rendering, APCu metadata guards, and UTC-safe time formatting.

### Fixed
- Corrected `replace()` behavior on APCu-backed keys so values are replaced only when a key already exists.
- Preserved numeric return behavior for non-persistent `incr()` and `decr()` paths.
- Enforced strict site ID membership checks when adding the global site namespace to `flush_sites()`.

## [1.0.0] - 2024-07-08

### Added
- First Release
