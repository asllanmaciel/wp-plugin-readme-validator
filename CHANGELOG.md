# Changelog

All notable changes to this project will be documented here.

## [1.0.0] - 2026-08-14

### Added

- dependency-free CLI for validating WordPress plugin/readme metadata;
- JSON output mode and deterministic exit codes;
- GitHub composite Action wrapper;
- checks for plugin/readme version consistency;
- WordPress/PHP requirement consistency checks;
- recommended tag-count validation;
- PHPUnit and PHPStan development checks;
- parser-specific regression coverage for the WordPress 8 KB raw plugin-header boundary, CRLF, lone CR, UTF-8 BOM and duplicate headers;
- local static guard for safe composite Action input handling;
- real CLI smoke coverage for valid metadata, validation errors and JSON output;
- contribution, security and conduct guidance;
- PHP 8.1–8.4 release-gate validation and versioned release evidence.

### Fixed

- plugin metadata parsing now ignores headers outside the first 8192 raw bytes, matching WordPress core's practical plugin-header scope;
- CRLF and lone-CR metadata are normalized in memory only after the raw plugin byte boundary is preserved;
- UTF-8 BOM is ignored for parsing without shifting the raw 8192-byte plugin boundary;
- duplicate metadata headers preserve the first occurrence instead of allowing later comments to silently replace it;
- composite Action inputs are passed through environment variables instead of being interpolated directly into the shell command.

### Changed

- repository CI is manual-only while local `composer check` remains the default validation loop;
- `composer check` includes the Action safety guard and the end-to-end CLI smoke;
- the manual compatibility workflow runs the CLI smoke across PHP 8.1–8.4 and the Action safety guard on PHP 8.3;
- stable consumers should use the floating `v1` ref only after it is deliberately aligned with this release commit.

## Release policy

`v1.0.0` is the first stable GitHub Action release. The historical `v1` ref must be moved/recreated to the exact `v1.0.0` release commit and verified from a consumer workflow before the README begins recommending `@v1`.
