<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Layout;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
final class Outers_Builder {
	/**
	 * @param Html_Tag[] $outers
	 * @param Html_Node[] $nodes
	 *
	 * @return Html_Node[]
	 */
	public static function compose_outers(
		array $outers,
		array $nodes
	): array {
		// the innermost outer goes first.
		$innermost_first = array_reverse( $outers );

		foreach ( $innermost_first as $outer ) {
			$outer_tag = self::compose_outer( $outer );

			$nodes = array( $outer_tag->content( ...$nodes ) );
		}

		return $nodes;
	}

	/**
	 * A fresh tag is composed, as the outers can be reused by several calls.
	 */
	protected static function compose_outer( Html_Tag $outer ): Html_Tag {
		$name  = $outer->get_name();
		$attrs = $outer->get_attributes();

		return tag( $name )
			->attributes( $attrs );
	}
}
