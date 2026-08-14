<?php

namespace AsllanMaciel\WpReadmeValidator;

use RuntimeException;

final class MetadataParser {
	private const PLUGIN_HEADER_BYTES = 8192;

	/**
	 * @return array<string, string>
	 */
	public function plugin( string $path ): array {
		$content = substr( $this->read( $path ), 0, self::PLUGIN_HEADER_BYTES );

		return $this->headers( $this->normalize( $this->removeBom( $content ) ) );
	}

	/**
	 * @return array<string, string>
	 */
	public function readme( string $path ): array {
		$content = $this->normalize( $this->removeBom( $this->read( $path ) ) );
		$intro   = preg_split( '/^==\s+/m', $content, 2 )[0] ?? $content;
		$headers = $this->headers( $intro );

		if ( preg_match( '/^===\s*(.+?)\s*===$/m', $intro, $matches ) ) {
			$headers['plugin name'] = trim( $matches[1] );
		}

		return $headers;
	}

	private function read( string $path ): string {
		if ( ! is_file( $path ) || ! is_readable( $path ) ) {
			throw new RuntimeException( sprintf( 'Cannot read file: %s', $path ) );
		}

		$content = file_get_contents( $path );

		if ( false === $content ) {
			throw new RuntimeException( sprintf( 'Cannot read file: %s', $path ) );
		}

		return $content;
	}

	private function removeBom( string $content ): string {
		if ( str_starts_with( $content, "\xEF\xBB\xBF" ) ) {
			return substr( $content, 3 );
		}

		return $content;
	}

	private function normalize( string $content ): string {
		return str_replace( array( "\r\n", "\r" ), "\n", $content );
	}

	/**
	 * @return array<string, string>
	 */
	private function headers( string $content ): array {
		$headers = array();

		if ( preg_match_all( '/^[\s\/*#@]*([A-Za-z][A-Za-z ]+):\s*(.*?)\s*(?:\*\/)?$/m', $content, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				$key = strtolower( trim( $match[1] ) );

				if ( ! array_key_exists( $key, $headers ) ) {
					$headers[ $key ] = trim( $match[2] );
				}
			}
		}

		return $headers;
	}
}
