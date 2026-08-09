<?php

namespace AsllanMaciel\WpReadmeValidator\Tests;

use AsllanMaciel\WpReadmeValidator\Validator;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase {
	public function test_matching_metadata_is_valid(): void {
		$plugin = array(
			'plugin name'       => 'Example',
			'version'           => '1.2.3',
			'requires at least' => '6.5',
			'requires php'      => '8.1',
			'license'           => 'GPL-2.0-or-later',
			'text domain'       => 'example',
		);
		$readme = array(
			'plugin name'       => 'Example',
			'contributors'      => 'example',
			'tags'              => 'example, development',
			'requires at least' => '6.5',
			'tested up to'      => '7.0',
			'requires php'      => '8.1',
			'stable tag'        => '1.2.3',
			'license'           => 'GPL-2.0-or-later',
		);

		$report = ( new Validator() )->validate( $plugin, $readme );

		self::assertFalse( $report->hasErrors() );
		self::assertSame( array(), $report->issues() );
	}

	public function test_readme_requirement_headers_are_optional(): void {
		$plugin = array(
			'plugin name'       => 'Example',
			'version'           => '1.2.3',
			'requires at least' => '6.5',
			'requires php'      => '8.1',
			'license'           => 'GPL-2.0-or-later',
			'text domain'       => 'example',
		);
		$readme = array(
			'plugin name'  => 'Example',
			'contributors' => 'example',
			'tags'         => 'example, development',
			'tested up to' => '7.0',
			'stable tag'   => '1.2.3',
			'license'      => 'GPL-2.0-or-later',
		);

		$report = ( new Validator() )->validate( $plugin, $readme );

		self::assertFalse( $report->hasErrors() );
		self::assertSame( array(), $report->issues() );
	}

	public function test_mismatches_are_reported(): void {
		$report = ( new Validator() )->validate(
			array(
				'plugin name'       => 'Example',
				'version'           => '2.0.0',
				'requires at least' => '6.5',
				'requires php'      => '8.1',
				'license'           => 'GPL-2.0-or-later',
				'text domain'       => 'example',
			),
			array(
				'plugin name'       => 'Example',
				'contributors'      => 'example',
				'tags'              => 'one, two, three, four, five, six',
				'requires at least' => '6.4',
				'tested up to'      => '7.0',
				'requires php'      => '7.4',
				'stable tag'        => '1.0.0',
				'license'           => 'GPL-2.0-or-later',
			)
		);

		self::assertTrue( $report->hasErrors() );
		self::assertSame( 3, $report->toArray()['errors'] );
		self::assertSame( 1, $report->toArray()['warnings'] );
	}
}
