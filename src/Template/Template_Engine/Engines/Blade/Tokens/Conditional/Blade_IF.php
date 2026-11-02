<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Blade\Tokens\Conditional;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Tokens\Nested\Tokens\IF_Branch;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Tokens\Nested\Tokens\IF_Token;

final class Blade_IF extends IF_Token {
	protected function get_branch_html( string $type, IF_Branch $branch ): string {
		$condition = self::bare_condition( $branch );

		if ( strlen( $condition ) > 0 ) {
			return sprintf( '@%s (%s)', $type, $condition );
		}

		return sprintf( '@%s', $type );
	}

	protected function get_end_html(): string {
		return '@endif';
	}
}
