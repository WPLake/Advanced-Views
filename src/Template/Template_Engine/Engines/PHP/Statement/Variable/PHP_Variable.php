<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\PHP\Statement\Variable;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;

final class PHP_Variable extends Variable_Token {
	public function get_token(): string {
		$escaped_name = $this->escaper->esc_html( $this->name );
		$token        = sprintf( '$%s', $escaped_name );

		foreach ( $this->item_path as $item_key ) {
			$token .= $this->is_object ?
				$this->object_key( $item_key ) :
				$this->array_key( $item_key );
		}

		return $token;
	}

	protected function array_key( string $key ): string {
		$escaped_key = $this->escaper->esc_html( $key );

		return sprintf( '["%s"]', $escaped_key );
	}

	protected function object_key( string $key ): string {
		$escaped_key = $this->escaper->esc_html( $key );

		return sprintf( '->%s', $escaped_key );
	}
}
