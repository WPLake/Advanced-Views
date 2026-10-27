<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Htmlable;

use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;

defined( 'ABSPATH' ) || exit;

/**
 * Literals mixed with tokens (e.g. 'bem--id--{{ id }}'). Being an object, it's passed to the attribute as is,
 * so the tokens aren't escaped; the literals are escaped here.
 */
final class Composed_Htmlable implements Htmlable {
	/**
	 * @var array<string|Htmlable>
	 */
	protected array $parts;

	/**
	 * @param array<string|Htmlable> $parts
	 */
	public function __construct( array $parts ) {
		$this->parts = $parts;
	}

	public function to_html( Html_Escaper $escaper ): string {
		$markups = array_map(
			fn( $part ): string => is_string( $part ) ?
				$escaper->esc_html( $part ) :
				$part->to_html( $escaper ),
			$this->parts
		);

		return join( '', $markups );
	}
}
