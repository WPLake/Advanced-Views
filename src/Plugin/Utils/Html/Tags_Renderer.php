<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Utils\Html;

defined( 'ABSPATH' ) || exit;

/**
 * Tag_Chain: every next item goes inside the previous one.
 * Tag_Group: its members (a Tag, a Tag_Text_Printer or a Tag_Chain) are rendered at the same level, and it ends the chain.
 * Tag_Text_Printer returns 'void|mixed': the 'mixed' is allowed to accept print callbacks, e.g. `fn() => print( $text )`.
 * Tag_Group is 'mixed' as PHPStan doesn't support recursive aliases.
 *
 * @phpstan-type Tag array{tag:string, attrs?:array<string,mixed>}
 * @phpstan-type Tag_Text_Printer callable():(void|mixed)
 * @phpstan-type Tag_Group list<mixed>
 * @phpstan-type Tag_Chain_Item Tag|Tag_Text_Printer|Tag_Group
 * @phpstan-type Tag_Chain list<Tag_Chain_Item>
 */
final class Tags_Renderer {
	/**
	 * @param Tag_Chain $tags
	 */
	public static function render_tags( array $tags ): string {
		ob_start();
		self::print_tags( $tags );

		return (string) ob_get_clean();
	}

	/**
	 * @param Tag_Chain $tags
	 */
	public static function print_tags( array $tags ): void {
		if ( count( $tags ) > 0 ) {
			$head = array_shift( $tags );

			self::print_item( $head, $tags );
		}
	}

	/**
	 * @param Tag_Chain_Item $item
	 * @param Tag_Chain $nested_tags
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
	 * @param Tag_Chain $nested_tags
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
	 * @param Tag_Group $members
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
