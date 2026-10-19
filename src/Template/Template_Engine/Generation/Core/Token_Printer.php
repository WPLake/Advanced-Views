<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core;

defined( 'ABSPATH' ) || exit;

interface Token_Printer {
	public static function display( Template_Token $token ): void;

	public static function raw_display( Template_Token $token ): void;
}
