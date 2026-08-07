<?php

namespace AsllanMaciel\WpReadmeValidator;

final class ValidationReport {
	/**
	 * @param list<array{level: string, code: string, message: string}> $issues
	 */
	public function __construct( private array $issues ) {}

	/**
	 * @return list<array{level: string, code: string, message: string}>
	 */
	public function issues(): array {
		return $this->issues;
	}

	public function hasErrors(): bool {
		foreach ( $this->issues as $issue ) {
			if ( 'error' === $issue['level'] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return array{valid: bool, errors: int, warnings: int, issues: list<array{level: string, code: string, message: string}>}
	 */
	public function toArray(): array {
		$errors   = 0;
		$warnings = 0;

		foreach ( $this->issues as $issue ) {
			'error' === $issue['level'] ? ++$errors : ++$warnings;
		}

		return array(
			'valid'    => 0 === $errors,
			'errors'   => $errors,
			'warnings' => $warnings,
			'issues'   => $this->issues,
		);
	}
}

