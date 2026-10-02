<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core;

defined( 'ABSPATH' ) || exit;

interface Assets_Location {
	public function url( string $file ): string;

	public function path( string $file ): string;

	public function version(): string;
}
