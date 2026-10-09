<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin;

defined( 'ABSPATH' ) || exit;

final class Plugin_File {
	private string $path;

	public function __construct( string $path ) {
		$this->path = $path;
	}

	public function path(): string {
		return $this->path;
	}
}
