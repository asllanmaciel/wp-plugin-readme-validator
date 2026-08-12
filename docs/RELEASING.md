# Releasing

This repository is both a PHP CLI and a GitHub composite Action. Releases therefore need a stable immutable tag and a maintained major-version ref.

## First stable release

Target: `v1.0.0`.

Before releasing:

```bash
composer install
composer check
php bin/wp-readme-validator --help
```

Also verify the README example works against the exact commit being tagged.

## Tags and Action refs

For each stable v1 release:

```text
v1.0.0   immutable release tag
v1       floating major ref used by consumers
```

Consumers should normally use:

```yaml
uses: asllanmaciel/wp-plugin-readme-validator@v1
```

Security-sensitive or fully reproducible workflows may pin the exact version tag or commit SHA.

## Release order

1. Make sure `composer check` passes locally.
2. Update `CHANGELOG.md`, moving relevant items from Unreleased into the release version/date.
3. Create the immutable version tag, for example `v1.0.0`.
4. Create the GitHub Release from that tag.
5. Move/create the `v1` ref to the same commit.
6. Verify a consumer workflow using `@v1`.
7. Only then update README examples from `@main` to `@v1`.

## CI cost policy

The repository workflow is currently manual-only. A release does not require enabling automatic CI globally; local validation plus a deliberate manual compatibility run is sufficient while Actions usage is being controlled.
