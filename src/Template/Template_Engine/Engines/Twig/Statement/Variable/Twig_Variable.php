<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Twig\Statement\Variable;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;

final class Twig_Variable extends Variable_Token {
	public function get_token(): string {
		$path    = array_merge( array( $this->name ), $this->item_path );
		$escaped = array_map(
			fn( string $part ) => $this->escaper->esc_html( $part ),
			$path
		);

		return join( '.', $escaped );
	}
}
