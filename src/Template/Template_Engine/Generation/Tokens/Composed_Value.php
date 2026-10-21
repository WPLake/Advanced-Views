<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens;

use Stringable;

defined( 'ABSPATH' ) || exit;

/**
 * Literals mixed with tokens (e.g. 'bem--id--{{ id }}'). Being an object, it's passed to the attribute as is,
 * so the tokens aren't escaped; the literals are escaped here.
 */
final class Composed_Value implements Stringable {
	/**
	 * @var array<string|Stringable>
	 */
	protected array $parts;

	/**
	 * @param array<string|Stringable> $parts
	 */
	public function __construct( array $parts ) {
		$this->parts = $parts;
	}

	public function __toString(): string {
		$markups = array_map(
			static fn( $part ): string => is_string( $part ) ?
				esc_html( $part ) :
				(string) $part,
			$this->parts
		);

		return join( '', $markups );
	}
}
