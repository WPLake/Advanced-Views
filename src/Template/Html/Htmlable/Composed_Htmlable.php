<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Htmlable;

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
	protected function __construct( array $parts ) {
		$this->parts = $parts;
	}

	/**
	 * @param array<string|Htmlable> $parts
	 */
	public static function compose( array $parts ): Htmlable {
		return new self( $parts );
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
