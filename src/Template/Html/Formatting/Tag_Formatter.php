<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Formatting;

defined( 'ABSPATH' ) || exit;

interface Tag_Formatter {
	/**
	 * @param int                  $tag_spaces_count length of the tag opening (`<` and the escaped name)
	 * @param array<string,string> $attributes escaped name => escaped value
	 */
	public function format_attributes( int $tag_spaces_count, array $attributes ): string;
}
