<?php

namespace AsllanMaciel\WpReadmeValidator\Tests;

use AsllanMaciel\WpReadmeValidator\MetadataParser;
use PHPUnit\Framework\TestCase;

final class MetadataParserTest extends TestCase {
	/** @var list<string> */
	private array $temporary_files = array();

	protected function tearDown(): void {
		foreach ( $this->temporary_files as $file ) {
			if ( is_file( $file ) ) {
				unlink( $file );
			}
		}

		$this->temporary_files = array();
	}

	public function test_plugin_headers_near_top_are_parsed(): void {
		$file = $this->temporaryPlugin(
			"<?php\n/**\n * Plugin Name: Example Plugin\n * Version: 1.2.3\n * Requires at least: 6.5\n * Requires PHP: 8.1\n * License: GPL-2.0-or-later\n * Text Domain: example-plugin\n */\n"
		);

		$headers = ( new MetadataParser() )->plugin( $file );

		self::assertSame( 'Example Plugin', $headers['plugin name'] ?? null );
		self::assertSame( '1.2.3', $headers['version'] ?? null );
		self::assertSame( '8.1', $headers['requires php'] ?? null );
	}

	public function test_header_after_first_8192_bytes_is_ignored(): void {
		$file = $this->temporaryPlugin(
			"<?php\n/**\n * Plugin Name: Example Plugin\n */\n" .
			str_repeat( 'x', 8300 ) .
			"\n/**\n * Version: 9.9.9\n * Requires PHP: 8.4\n */\n"
		);

		$headers = ( new MetadataParser() )->plugin( $file );

		self::assertSame( 'Example Plugin', $headers['plugin name'] ?? null );
		self::assertArrayNotHasKey( 'version', $headers );
		self::assertArrayNotHasKey( 'requires php', $headers );
	}

	public function test_late_comment_cannot_replace_missing_real_header(): void {
		$file = $this->temporaryPlugin(
			"<?php\n/**\n * Plugin Name: Example Plugin\n */\n" .
			str_repeat( "// application code\n", 600 ) .
			"/**\n * Version: 7.7.7\n */\n"
		);

		$headers = ( new MetadataParser() )->plugin( $file );

		self::assertArrayNotHasKey( 'version', $headers );
	}

	private function temporaryPlugin( string $contents ): string {
		$file = tempnam( sys_get_temp_dir(), 'wp-readme-validator-' );
		self::assertNotFalse( $file );

		$result = file_put_contents( $file, $contents );
		self::assertNotFalse( $result );

		$this->temporary_files[] = $file;

		return $file;
	}
}
