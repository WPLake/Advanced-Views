<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core;

defined( 'ABSPATH' ) || exit;

interface Template_Token {
	public function print_token(): void;

	public function display(): void;

	public function raw_display(): void;
}
