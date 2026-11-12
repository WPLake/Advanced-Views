<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Template\Builder;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Template\Pattern\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

/**
 * The only place that merges the Template_Pattern declarations of several patterns.
 */
final class Template_Builder {
	/**
	 * @param class-string<Template_Pattern>[] $patterns
	 */
	public static function row_wrapper( array $patterns ): ?Html_Tag {
		$tags = array_map(
			static fn( string $pattern ): ?Html_Tag => $pattern::get_wrapper(),
			$patterns
		);

		return self::merge_tags( $tags );
	}

	/**
	 * @param class-string<Template_Pattern>[] $patterns
	 *
	 * @return Html_Tag[]
	 */
	public static function field_wrappers( array $patterns, Variable_Token $field ): array {
		$patterns_wrappers = array_map(
			static fn( string $pattern ): array => $pattern::get_outers( $field ),
			$patterns
		);

		return self::merge_outers( $patterns_wrappers );
	}

	/**
	 * @param class-string<Template_Pattern>[] $patterns
	 *
	 * @return Html_Tag[]
	 */
	public static function item_wrappers( array $patterns, Variable_Token $item ): array {
		$patterns_wrappers = array_map(
			static fn( string $pattern ): array => $pattern::get_item_wrappers( $item ),
			$patterns
		);

		return self::merge_outers( $patterns_wrappers );
	}

	/**
	 * @param class-string<Template_Pattern>[] $patterns
	 *
	 * @return array<string,string|Variable_Token>
	 */
	public static function item_attributes( array $patterns, Variable_Token $item ): array {
		$attributes = array();

		foreach ( $patterns as $pattern ) {
			$pattern_attributes = $pattern::get_item_attributes( $item );
			$attributes         = array_merge( $attributes, $pattern_attributes );
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
		$outer_attrs  = $outer->get_attributes();
		$name         = $outer->get_name();
		$attrs        = array_merge( $merged_attrs, $outer_attrs );

		return tag( $name )
			->attributes( $attrs );
	}
}
