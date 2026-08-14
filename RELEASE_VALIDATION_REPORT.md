# WP Plugin README Validator — v1.0.0 Validation Report

## Candidate

- Original `main` candidate SHA: `e13709604b08db573eafe3b6cf5fedaded32ff57`
- Validation branch: `codex/v1-release-validation`

## Environment

Validation was performed on Windows with Docker Desktop using disposable official
`php:<version>-cli` containers. Composer was downloaded inside each disposable
container and dependencies were held in version-specific Docker volumes, keeping
the repository working tree free of generated dependencies. No GitHub Actions
workflow was run.

## Problem found

The original `MetadataParser::read()` removed a UTF-8 BOM but passed CRLF input
directly to the header parser. Its line-oriented regular expression therefore did
not parse the CRLF fixture correctly: the existing CRLF regression test returned
`null` instead of `Example Plugin`.

## Correction

The initial correction normalized `\r\n` and lone `\r` to `\n` in memory before
parsing. It was subsequently refined by the raw-byte-boundary review below. The
source file is only read; it is never rewritten.

## Composer quality gate

After the correction, `composer check` passed in the primary container:

- PHPStan: no errors;
- PHPUnit: 13 tests, 55 assertions after the raw-byte-boundary revalidation;
- composite Action safety guard: passed;
- CLI smoke: passed.

## Parser regression evidence

The pre-existing CRLF parser regression test was the reproducer and initially
failed against the original candidate. It passed after the minimal in-memory
normalization. The suite also covers BOM input, duplicate headers (first occurrence
wins), and the plugin-header 8192-byte boundary.

## CLI contract

Direct invocation of `bin/wp-readme-validator` used temporary fixtures outside the
repository in a directory whose path contains spaces.

| Case | Expected exit | Actual exit | Result |
| --- | ---: | ---: | --- |
| Consistent plugin/readme | 0 | 0 | PASS |
| Version mismatch | 1 | 1 | PASS |
| Missing input file | 2 | 2 | PASS |
| Missing required arguments | 2 | 2 | PASS |
| Valid JSON output | 0 | 0 | PASS |
| JSON mismatch with `version.mismatch` | 1 | 1 | PASS |

## Composite Action command contract proof

This is a **COMPOSITE ACTION COMMAND CONTRACT PROOF**, not a GitHub Actions run.
The exact environment-variable contract in `action.yml` was reproduced with Action,
plugin, and readme paths containing spaces:

- `WP_README_VALIDATOR_ACTION_PATH`;
- `WP_README_VALIDATOR_PLUGIN_FILE`;
- `WP_README_VALIDATOR_README_FILE`.

The exact equivalent command returned exit 0 for consistent metadata and exit 1 for
the mismatch fixture. The Action command quotes every expanded path. A source scan
found `${{ inputs.plugin-file }}` and `${{ inputs.readme-file }}` only in the `env`
mapping, not directly in `run`, so an input is not interpreted as shell source.

## PHP compatibility

| PHP version | composer install | PHPUnit | CLI smoke | Result |
| --- | --- | --- | --- | --- |
| 8.1.34 | PASS | PASS (13 tests, 55 assertions) | PASS | PASS |
| 8.2.33 | PASS | PASS (13 tests, 55 assertions) | PASS | PASS |
| 8.3.33 | PASS | PASS (13 tests, 55 assertions) | PASS | PASS |
| 8.4.24 | PASS | PASS (13 tests, 55 assertions) | PASS | PASS |

PHP 8.4.24 additionally passed `composer analyse` (no PHPStan errors) and
`composer action:safety`. An initial parallel PHP 8.3 dependency download produced
a corrupted temporary Composer ZIP; the isolated rerun in a new Docker volume
completed successfully, so it was treated as an environmental download artifact,
not a repository failure.

## README / metadata consistency

- `composer.json` requires PHP `>=8.1` and declares MIT;
- `README.md` declares MIT, gives correct CLI examples, and still recommends
  `@main` before the first stable release;
- `action.yml` exposes the correct `plugin-file` and `readme-file` inputs;
- no `v1.0.0` tag or GitHub Release exists;
- `CHANGELOG.md` remains `Unreleased` and explicitly preserves the historical `v1`;
- historical `v1` still resolves to `8b1987c508cd2fc1d1a4487d7dc49f8ff1d424bb`;
- `docs/RELEASING.md` remains coherent: create `v1.0.0`, verify consumer use, then
  deliberately align the major ref and README guidance.

No README, changelog, or release-status text was changed by this validation.

## PR #9 raw-byte boundary review revalidation

Review found that the initial CRLF correction normalized an entire plugin file
before `plugin()` applied its 8192-byte limit. Because CRLF becomes shorter LF,
that could pull a header physically beyond the WordPress raw-file window into the
parser window.

New regressions first reproduced the defect: a CRLF `Version` header physically
after byte 8192 was incorrectly parsed, and early BOM removal could shift a
truncated header across the raw boundary. The parser now reads the file unchanged,
cuts the first 8192 raw bytes for plugin metadata (including any UTF-8 BOM), then
removes a leading BOM and normalizes line endings inside that fixed window. This
matches WordPress' first-8-KiB raw-file rule while retaining CRLF and lone-CR
support. `readme.txt` still normalizes the complete file and has no plugin-header
byte limit.

The refreshed test suite explicitly covers valid LF, CRLF, lone-CR, and BOM plugin
metadata; LF and CRLF raw-byte boundaries; no CRLF-induced boundary shift; duplicate
headers; late header behavior; CRLF readmes; and readmes beyond 8192 bytes. PHP
8.1–8.4 were revalidated after this correction.

## Remaining blockers

None identified in the validated scope.

## Release recommendation

READY FOR v1.0.0 RELEASE
