<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Formatting;

defined( 'ABSPATH' ) || exit;

interface Html_Formatter {
	/**
	 * @param callable():mixed $print_content
	 */
	public function print_block_content( callable $print_content ): void;

	/**
	 * @param callable():mixed $print_element
	 */
	public function print_block( callable $print_element ): void;
}
