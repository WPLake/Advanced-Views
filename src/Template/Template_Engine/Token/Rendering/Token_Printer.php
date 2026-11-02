<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Rendering;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Statement_Token;

interface Token_Printer {
	public static function statement( Statement_Token $token ): string;

	public static function raw_statement( Statement_Token $token ): string;
}
