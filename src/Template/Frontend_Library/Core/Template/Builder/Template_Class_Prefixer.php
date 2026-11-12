<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Template\Builder;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\tag;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

/**
 * The only place that adds the generated (BEM) classes to the tags of the Template_Pattern declarations.
 */
final class Template_Class_Prefixer {
	/**
	 * The generated class goes before the static ones. An empty prefix keeps the tag as is.
	 */
	public static function prefix_class( Html_Tag $tag, string $class_prefix ): Html_Tag {
		$is_filled = strlen( $class_prefix ) > 0;

		if ( $is_filled ) {
			$attributes     = $tag->get_attributes();
			$static_classes = string( $attributes, 'class' );
			$class_list     = array(
				$class_prefix,
				$static_classes,
			);
			$classes        = self::join_classes( $class_list );
			$name           = $tag->get_name();

			$class_attributes = array( 'class' => $classes );
			$merged           = array_merge( $attributes, $class_attributes );

			return tag( $name )
				->attributes( $merged );
		}

		return $tag;
	}

	/**
	 * Position rule: the innermost wrapper is the 'list'.
	 *
	 * @param Html_Tag[] $outers
	 *
	 * @return Html_Tag[]
	 */
	public static function prefix_list_class( array $outers, string $list_class ): array {
		$outers = array_values( $outers );
		$last   = count( $outers ) - 1;

		if ( $last >= 0 ) {
			$outers[ $last ] = self::prefix_class( $outers[ $last ], $list_class );
		}

		return $outers;
	}

	/**
	 * Position rule: the innermost wrapper is the 'list', the one above it is the 'track'.
	 *
	 * @param Html_Tag[] $outers
	 *
	 * @return Html_Tag[]
	 */
	public static function prefix_selection_classes( array $outers, string $list_class, string $track_class ): array {
		$outers = self::prefix_list_class( $outers, $list_class );
		$track  = count( $outers ) - 2;

		if ( $track >= 0 ) {
			$outers[ $track ] = self::prefix_class( $outers[ $track ], $track_class );
		}

		return $outers;
	}

	/**
	 * @param Html_Tag[] $inners
	 *
	 * @return Html_Tag[]
	 */
	public static function prefix_item_class( array $inners, string $item_class ): array {
		$inners_list = array_values( $inners );

		return array_map(
			static fn( Html_Tag $inner ): Html_Tag => self::prefix_class( $inner, $item_class ),
			$inners_list
		);
	}

	/**
	 * @param string[] $classes
	 */
	public static function join_classes( array $classes ): string {
		$filled_classes = array_filter(
			$classes,
			static fn( string $class_name ): bool => strlen( $class_name ) > 0
		);

		return join( ' ', $filled_classes );
	}
}
