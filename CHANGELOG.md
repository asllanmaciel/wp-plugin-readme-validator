# Changelog

All notable changes to this project will be documented here.

## [Unreleased]

### Added

- dependency-free CLI for validating WordPress plugin/readme metadata;
- JSON output mode and deterministic exit codes;
- GitHub composite Action wrapper;
- checks for plugin/readme version consistency;
- WordPress/PHP requirement consistency checks;
- recommended tag-count validation;
- PHPUnit and PHPStan development checks;
- parser-specific regression coverage for the WordPress 8 KB header boundary, CRLF, UTF-8 BOM and duplicate headers;
- local static guard for safe composite Action input handling;
- real CLI smoke coverage for valid metadata, validation errors and JSON output;
- contribution, security and conduct guidance.

### Fixed

- plugin metadata parsing now ignores headers outside the first 8192 bytes, matching WordPress core's practical header scope;
- UTF-8 BOM is ignored in memory so valid plugin/readme metadata is not missed;
- duplicate metadata headers preserve the first occurrence instead of allowing later comments to silently replace it;
- composite Action inputs are passed through environment variables instead of being interpolated directly into the shell command.

### Changed

- repository CI is manual-only while local `composer check` remains the default validation loop;
- `composer check` now includes the Action safety guard and the end-to-end CLI smoke;
- the manual compatibility workflow runs the CLI smoke across PHP 8.1–8.4 and the Action safety guard on PHP 8.3.

## Release policy

The first stable GitHub Action release is planned as `v1.0.0`. After it exists, the floating `v1` ref should point to the latest compatible v1 release.
