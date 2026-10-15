<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core;

use Org\Wplake\Advanced_Views\Template\Markup_Element\Markup_Element;

defined( 'ABSPATH' ) || exit;

interface Frontend_Library {
	public static function get_id(): string;

	public static function get_label(): string;

	public static function get_docs_url(): string;

	/**
	 * @return array<class-string<Markup_Element>, class-string<Frontend_Pattern>[]>
	 */
	public static function get_patterns(): array;
}
