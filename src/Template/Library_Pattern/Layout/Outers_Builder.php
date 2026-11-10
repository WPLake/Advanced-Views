<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Layout;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\tag;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
final class Outers_Builder {
	/**
	 * Merges the outers of every pattern by depth: the n-th outers of all patterns form the n-th result item.
	 * The later pattern overrides the tag, the attributes are merged.
	 *
	 * @param Html_Tag[][] $patterns_outers
	 *
	 * @return Html_Tag[]
	 */
	public static function merge_outers( array $patterns_outers ): array {
		/**
		 * @var Html_Tag[] $merged_outers
		 */
		$merged_outers = array();

		foreach ( $patterns_outers as $pattern_outers ) {
			$depth_outers = array_values( $pattern_outers );

			foreach ( $depth_outers as $depth => $pattern_outer ) {
				$merged_outers[ $depth ] = self::merge_outer(
					$merged_outers[ $depth ] ?? null,
					$pattern_outer
				);
			}
		}

		return $merged_outers;
	}

	/**
	 * @param Html_Tag[] $outers
	 * @param Html_Node[] $nodes
	 * @param string|null $default_class The class of an outer without its own one (item outers); null means no class
	 *
	 * @return Html_Node[]
	 */
	public static function compose_outers(
		array $outers,
		array $nodes,
		?string $default_class = null
	): array {
		// the innermost outer goes first.
		$innermost_first = array_reverse( $outers );

		foreach ( $innermost_first as $outer ) {
			$outer_tag = self::compose_outer( $outer, $default_class );

			$nodes = array( $outer_tag->content( ...$nodes ) );
		}

		return $nodes;
	}

	/**
	 * A fresh tag is composed, as the outers can be reused by several calls.
	 */
	protected static function compose_outer( Html_Tag $outer, ?string $default_class ): Html_Tag {
		$attrs = $outer->get_attributes();

		if ( is_string( $default_class ) ) {
			$attr_class  = string( $attrs, 'class' );
			$is_filled   = strlen( $attr_class ) > 0;
			$class       = $is_filled ?
				$attr_class :
				$default_class;
			$class_attrs = array( 'class' => $class );
			$attrs       = array_merge( $class_attrs, $attrs );
		}

		$name = $outer->get_name();

		return tag( $name )
			->attributes( $attrs );
	}

	protected static function merge_outer( ?Html_Tag $merged, Html_Tag $outer ): Html_Tag {
		$merged_attrs = $merged instanceof Html_Tag ?
			$merged->get_attributes() :
			array();
		$outer_attrs  = $outer->get_attributes();

		$name  = $outer->get_name();
		$attrs = array_merge( $merged_attrs, $outer_attrs );

		return tag( $name )
			->attributes( $attrs );
	}
}
