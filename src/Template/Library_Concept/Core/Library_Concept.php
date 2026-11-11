<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Concept\Core;

defined( 'ABSPATH' ) || exit;

interface Library_Concept {
	public static function get_id(): string;

	public static function get_label(): string;

	public static function get_docs_url(): string;

	/**
	 * @return array<class-string, class-string<Frontend_Pattern>[]>
	 */
	public static function get_patterns(): array;
}
