# Validation Workflow

## Coding Standards Gate

For PHP changes in this repository, run:

```bash
phpcbf --standard=WordPress-Extra object-cache.php
phpcs --standard=WordPress-Extra object-cache.php
```

Final validation requires zero violations from `phpcs`.

## Ignore Rule Policy

- Avoid introducing new `phpcs:ignore` and `phpcs:disable` directives unless required.
- Never weaken security-relevant behavior when applying ignore directives.
- Every new ignore directive must include a specific inline justification.

## Documentation Workflow

- Keep documentation modular under `doc/`.
- Keep `doc/README.md` as the single documentation entrypoint.
- Update `CHANGELOG.md` `[Unreleased]` categories for meaningful user-visible or architectural changes.
