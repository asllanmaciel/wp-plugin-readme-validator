<?php

namespace AsllanMaciel\WpReadmeValidator;

final class Validator {
	/**
	 * @param array<string, string> $plugin
	 * @param array<string, string> $readme
	 */
	public function validate( array $plugin, array $readme ): ValidationReport {
		$issues = array();

		foreach ( array( 'plugin name', 'version', 'requires at least', 'requires php', 'license', 'text domain' ) as $field ) {
			if ( empty( $plugin[ $field ] ) ) {
				$issues[] = $this->issue( 'error', 'plugin.missing_header', sprintf( 'Plugin header is missing "%s".', $field ) );
			}
		}

		foreach ( array( 'plugin name', 'contributors', 'tags', 'requires at least', 'tested up to', 'requires php', 'stable tag', 'license' ) as $field ) {
			if ( empty( $readme[ $field ] ) ) {
				$issues[] = $this->issue( 'error', 'readme.missing_header', sprintf( 'readme.txt is missing "%s".', $field ) );
			}
		}

		$this->compare( $issues, $plugin, $readme, 'version', 'stable tag', 'version.mismatch' );
		$this->compare( $issues, $plugin, $readme, 'requires php', 'requires php', 'requires_php.mismatch' );
		$this->compare( $issues, $plugin, $readme, 'requires at least', 'requires at least', 'requires_wp.mismatch' );

		if ( ! empty( $readme['tags'] ) ) {
			$tags = array_filter( array_map( 'trim', explode( ',', $readme['tags'] ) ) );

			if ( count( $tags ) > 5 ) {
				$issues[] = $this->issue( 'warning', 'readme.too_many_tags', 'WordPress.org uses at most five plugin tags.' );
			}
		}

		foreach ( array( 'version' => $plugin['version'] ?? '', 'requires php' => $plugin['requires php'] ?? '', 'tested up to' => $readme['tested up to'] ?? '' ) as $field => $value ) {
			if ( '' !== $value && ! preg_match( '/^\d+\.\d+(?:\.\d+)?(?:[-+][A-Za-z0-9.-]+)?$/', $value ) ) {
				$issues[] = $this->issue( 'warning', 'version.invalid_format', sprintf( '"%s" has an unusual version format: %s.', $field, $value ) );
			}
		}

		return new ValidationReport( $issues );
	}

	/**
	 * @param list<array{level: string, code: string, message: string}> $issues
	 * @param array<string, string> $plugin
	 * @param array<string, string> $readme
	 */
	private function compare( array &$issues, array $plugin, array $readme, string $plugin_key, string $readme_key, string $code ): void {
		$plugin_value = $plugin[ $plugin_key ] ?? '';
		$readme_value = $readme[ $readme_key ] ?? '';

		if ( 'stable tag' === $readme_key && 'trunk' === strtolower( $readme_value ) ) {
			return;
		}

		if ( '' !== $plugin_value && '' !== $readme_value && $plugin_value !== $readme_value ) {
			$issues[] = $this->issue(
				'error',
				$code,
				sprintf( 'Plugin "%s" is %s but readme "%s" is %s.', $plugin_key, $plugin_value, $readme_key, $readme_value )
			);
		}
	}

	/**
	 * @return array{level: string, code: string, message: string}
	 */
	private function issue( string $level, string $code, string $message ): array {
		return compact( 'level', 'code', 'message' );
	}
}

