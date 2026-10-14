<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core;

defined( 'ABSPATH' ) || exit;

interface Frontend_Library {
	public static function get_id(): string;

	public static function get_label(): string;

	public static function get_docs_url(): string;

	/**
	 * [ Markup_Element_Base::KIND => [patterns] ]
	 *
	 * @return array<string, class-string<Frontend_Pattern>[]>
	 */
	public static function get_patterns(): array;
}
