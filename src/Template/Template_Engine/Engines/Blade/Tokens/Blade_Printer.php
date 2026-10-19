<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Blade\Tokens;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Template_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Token_Printer;

final class Blade_Printer implements Token_Printer {
	public static function display( Template_Token $token ): void {
		echo '{{ ';

		$token->print_token();

		echo ' }}';
	}

	public static function raw_display( Template_Token $token ): void {
		echo '{!! ';

		$token->print_token();

		echo ' !!}';
	}
}
