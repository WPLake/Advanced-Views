<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\PHP\Statement\Variable;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;

final class PHP_Variable extends Variable_Token {
	public function get_token(): string {
		$token = sprintf( '$%s', esc_html( $this->name ) );

		foreach ( $this->item_path as $item_key ) {
			$token .= $this->is_object ?
				$this->object_key( $item_key ) :
				$this->array_key( $item_key );
		}

		return $token;
	}

	protected function array_key( string $key ): string {
		return sprintf( '["%s"]', esc_html( $key ) );
	}

	protected function object_key( string $key ): string {
		return sprintf( '->%s', esc_html( $key ) );
	}
}
