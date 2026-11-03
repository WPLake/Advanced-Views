<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Formatting;

defined( 'ABSPATH' ) || exit;

interface Tag_Formatter {
	/**
	 * @param array<string,string> $escaped_attributes name => value
	 * @param int                  $tag_length length of the tag opening (`<` and the escaped name)
	 */
	public function format_attributes( array $escaped_attributes, int $tag_length ): string;
}
