<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Blade\Tokens;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Tokens\Statement\Statement_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Tokens\Core\Token_Printer;

final class Blade_Printer implements Token_Printer {
	public static function statement( Statement_Token $token ): string {
		$bare_token = $token->get_token();

		return sprintf( '{{ %s }}', $bare_token );
	}

	public static function raw_statement( Statement_Token $token ): string {
		$bare_token = $token->get_token();

		return sprintf( '{!! %s !!}', $bare_token );
	}
}
