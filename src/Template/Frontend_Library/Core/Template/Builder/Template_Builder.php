<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Template\Builder;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Template\Pattern\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;
use function Org\Wplake\Advanced_Views\Template\Html\tag;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

/**
 * The only place that merges the Template_Pattern declarations of several patterns,
 * and adds the generated classes to them.
 */
final class Template_Builder {
	/**
	 * @param class-string<Template_Pattern>[] $patterns
	 */
	public static function row_wrapper( array $patterns ): ?Html_Tag {
		$tags = array_map(
			static fn( string $pattern ): ?Html_Tag => $pattern::get_row_wrapper(),
			$patterns
		);

		return self::merge_tags( $tags );
	}

	/**
	 * @param class-string<Template_Pattern>[] $patterns
	 */
	public static function field_wrapper( array $patterns, Variable_Token $field ): ?Html_Tag {
		$tags = array_map(
			static fn( string $pattern ): ?Html_Tag => $pattern::get_field_wrapper( $field ),
			$patterns
		);

		return self::merge_tags( $tags );
	}

	/**
	 * @param class-string<Template_Pattern>[] $patterns
	 *
	 * @return Html_Tag[]
	 */
	public static function outer_wrappers( array $patterns, Variable_Token $field ): array {
		$patterns_outers = array_map(
			static fn( string $pattern ): array => $pattern::get_outer_wrappers( $field ),
			$patterns
		);

		return self::merge_outers( $patterns_outers );
	}

	/**
	 * @param class-string<Template_Pattern>[] $patterns
	 *
	 * @return Html_Tag[]
	 */
	public static function inner_wrappers( array $patterns, Variable_Token $item ): array {
		$patterns_inners = array_map(
			static fn( string $pattern ): array => $pattern::get_inner_wrappers( $item ),
			$patterns
		);

		return self::merge_outers( $patterns_inners );
	}

	/**
	 * @param class-string<Template_Pattern>[] $patterns
	 *
	 * @return array<string,string|Variable_Token>
	 */
	public static function item_attributes( array $patterns, Variable_Token $item ): array {
		$attributes = array();

		foreach ( $patterns as $pattern ) {
			$attributes = array_merge( $attributes, $pattern::get_item_attributes( $item ) );
		}

		return $attributes;
	}

	/**
	 * @param class-string<Template_Pattern>[] $patterns
	 */
	public static function items_wrapper_class( array $patterns ): string {
		$classes = array_map(
			static fn( string $pattern ): string => $pattern::get_items_wrapper_class(),
			$patterns
		);

		return self::join_classes( $classes );
	}

	/**
	 * @param class-string<Template_Pattern>[] $patterns
	 *
	 * @return array<string,string>
	 */
	public static function shortcode_attributes( array $patterns ): array {
		$attributes = array();

		foreach ( $patterns as $pattern ) {
			$attributes = array_merge( $attributes, $pattern::get_shortcode_attributes() );
		}

		return $attributes;
	}

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
				$merged_outers[ $depth ] = self::merge_tag(
					$merged_outers[ $depth ] ?? null,
					$pattern_outer
				);
			}
		}

		return $merged_outers;
	}

	/**
	 * The generated class goes before the static ones. An empty prefix keeps the tag as is.
	 */
	public static function prefix_class( Html_Tag $tag, string $class_prefix ): Html_Tag {
		if ( '' === $class_prefix ) {
			return $tag;
		}

		$attributes = $tag->get_attributes();
		$classes    = self::join_classes( array( $class_prefix, string( $attributes, 'class' ) ) );
		$name       = $tag->get_name();

		return tag( $name )
			->attributes( array_merge( $attributes, array( 'class' => $classes ) ) );
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
		return array_map(
			static fn( Html_Tag $inner ): Html_Tag => self::prefix_class( $inner, $item_class ),
			array_values( $inners )
		);
	}

	/**
	 * @param string[] $classes
	 */
	public static function join_classes( array $classes ): string {
		$filled_classes = array_filter(
			$classes,
			static fn( string $class_name ): bool => '' !== $class_name
		);

		return join( ' ', $filled_classes );
	}

	/**
	 * @param array<Html_Tag|null> $tags
	 */
	protected static function merge_tags( array $tags ): ?Html_Tag {
		$merged = null;

		foreach ( $tags as $tag ) {
			if ( $tag instanceof Html_Tag ) {
				$merged = self::merge_tag( $merged, $tag );
			}
		}

		return $merged;
	}

	protected static function merge_tag( ?Html_Tag $merged, Html_Tag $outer ): Html_Tag {
		$merged_attrs = $merged instanceof Html_Tag ?
			$merged->get_attributes() :
			array();
		$name         = $outer->get_name();
		$attrs        = array_merge( $merged_attrs, $outer->get_attributes() );

		return tag( $name )
			->attributes( $attrs );
	}
}
