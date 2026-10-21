<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Utils\Html;

defined( 'ABSPATH' ) || exit;

/**
 * Literals mixed with tokens (e.g. 'bem--id--{{ id }}'). Being an object, it's passed to the attribute as is,
 * so the tokens aren't escaped; the literals are escaped here.
 */
final class Composed_Attribute implements Html_Printable {
	/**
	 * @var array<string|Html_Printable>
	 */
	protected array $parts;

	/**
	 * @param array<string|Html_Printable> $parts
	 */
	public function __construct( array $parts ) {
		$this->parts = $parts;
	}

	public function to_html(): string {
		$markups = array_map(
			static fn( $part ): string => is_string( $part ) ?
				esc_html( $part ) :
				$part->to_html(),
			$this->parts
		);

		return join( '', $markups );
	}
}
