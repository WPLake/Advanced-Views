<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Formatting;

defined( 'ABSPATH' ) || exit;

interface Html_Formatter {
	/**
	 * @param callable():mixed $print_content
	 */
	public function print_content( callable $print_content ): void;

	public function print_interleave(): void;
}
