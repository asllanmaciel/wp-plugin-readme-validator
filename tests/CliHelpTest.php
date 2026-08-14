<?php

namespace AsllanMaciel\WpReadmeValidator\Tests;

use PHPUnit\Framework\TestCase;

final class CliHelpTest extends TestCase {
	public function test_help_documents_options_and_exit_codes(): void {
		$output = array();
		$exit_code = 0;
		$command = sprintf(
			'%s %s --help 2>&1',
			escapeshellarg( PHP_BINARY ),
			escapeshellarg( dirname( __DIR__ ) . '/bin/wp-readme-validator' )
		);

		exec( $command, $output, $exit_code );
		$help = implode( "\n", $output );

		self::assertSame( 0, $exit_code, $help );
		foreach (
			array(
				'--plugin=<path>  Path to the main plugin PHP file (required).',
				'--readme=<path>  Path to readme.txt (required for direct CLI use).',
				'--json           Output the validation report as JSON.',
				'--help           Show this help message.',
				'0  Metadata is valid; warnings are allowed.',
				'1  Validation errors were found.',
				'2  Arguments are invalid or an input file is unreadable.',
			) as $expected
		) {
			self::assertStringContainsString( $expected, $help );
		}
	}
}
