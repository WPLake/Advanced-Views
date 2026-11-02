<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Twig\Tokens\Variable;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Tokens\Statement\Variable\Literal_Token;

final class Twig_Literal extends Literal_Token {
	protected function array_literal( array $value ): string {
		$items = array();

		foreach ( $value as $key => $item ) {
			$key_literal  = $this->literally( $key );
			$item_literal = $this->literally( $item );

			$items[] = sprintf( '%s:%s', $key_literal, $item_literal );
		}

		$joined_items = join( ', ', $items );

		return sprintf( '{%s}', $joined_items );
	}
}
