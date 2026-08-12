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
- contribution, security and conduct guidance.

### Changed

- repository CI is manual-only while local `composer check` remains the default validation loop.

## Release policy

The first stable GitHub Action release is planned as `v1.0.0`. After it exists, the floating `v1` ref should point to the latest compatible v1 release.
