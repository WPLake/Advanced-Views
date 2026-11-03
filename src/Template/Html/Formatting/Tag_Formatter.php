<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Formatting;

defined( 'ABSPATH' ) || exit;

interface Tag_Formatter {
	/**
	 * @param array<string,string> $attributes escaped name => escaped value
	 */
	public function format_attributes( array $attributes ): string;
}
