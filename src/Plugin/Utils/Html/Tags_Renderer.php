<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Utils\Html;

defined( 'ABSPATH' ) || exit;

/**
 * Chain: every next item goes inside the previous one.
 * Group: its members (a Tag, a Text_Printer or a Chain) are rendered at the same level, and it ends the chain.
 * Text_Printer returns 'void|mixed': the 'mixed' is allowed to accept print callbacks, e.g. `fn() => print( $text )`.
 * Group is 'mixed' as PHPStan doesn't support recursive aliases.
 *
 * @phpstan-type Tag array{tag:string, attrs?:array<string,mixed>}
 * @phpstan-type Text_Printer callable():(void|mixed)
 * @phpstan-type Group list<mixed>
 * @phpstan-type Chain_Item Tag|Text_Printer|Group
 * @phpstan-type Chain list<Chain_Item>
 */
final class Tags_Renderer {
	/**
	 * @param Chain $tags
	 */
	public static function render_tags( array $tags ): string {
		ob_start();
		self::print_tags( $tags );

		return (string) ob_get_clean();
	}

	/**
	 * @param Chain $tags
	 */
	public static function print_tags( array $tags ): void {
		if ( count( $tags ) > 0 ) {
			$head = array_shift( $tags );

			self::print_item( $head, $tags );
		}
	}

	/**
	 * @param Chain_Item $item
	 * @param Chain $nested_tags
	 */
	protected static function print_item( $item, array $nested_tags ): void {
		if ( is_array( $item ) ) {
			if ( key_exists( 'tag', $item ) ) {
				self::print_tag( $item, $nested_tags );
			} else {
				self::print_group( array_values( $item ) );
			}

			return;
		}

		$item();
	}

	/**
	 * @param Tag $item
	 * @param Chain $nested_tags
	 */
	protected static function print_tag( array $item, array $nested_tags ): void {
		$tag        = $item['tag'];
		$attributes = $item['attrs'] ?? array();

		$content_printer = count( $nested_tags ) > 0 ?
			fn() => self::print_tags( $nested_tags ) :
			null;

		Tag_Renderer::print_tag( $tag, $attributes, $content_printer );
	}

	/**
	 * @param Group $members
	 */
	protected static function print_group( array $members ): void {
		foreach ( $members as $member ) {
			$chain = is_array( $member ) && ! key_exists( 'tag', $member ) ?
				array_values( $member ) :
				array( $member );

			self::print_tags( $chain );
		}
	}
}
