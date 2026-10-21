<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Utils\Html;

use Stringable;

defined( 'ABSPATH' ) || exit;

/**
 * Tag_Chain: every next item goes inside the previous one.
 * Tag_Group: its members (a Tag, a Tag_Text or a Tag_Chain) are rendered at the same level, and it ends the chain.
 * Tag_Text is a plain string, a Stringable object (e.g. a template token) or a print callback, e.g. `fn() => print( $text )`.
 * Only a string is escaped, while an object and a callback are printed as is. A string is never called, even if it's a
 * callable name.
 * The callback returns 'void|mixed': the 'mixed' is allowed to accept such callbacks.
 * Tag_Group is 'mixed' as PHPStan doesn't support recursive aliases.
 *
 * @phpstan-type Tag array{tag:string, attrs?:array<string,mixed>}
 * @phpstan-type Tag_Text string|Stringable|callable():(void|mixed)
 * @phpstan-type Tag_Group list<mixed>
 * @phpstan-type Tag_Chain_Item Tag|Tag_Text|Tag_Group
 * @phpstan-type Tag_Chain list<Tag_Chain_Item>
 */
final class Tags_Renderer {
	/**
	 * Use when the call starts with a tag: the rest of the items go inside it, e.g. `array( $ul, $li, $text )`.
	 * For sibling items (a group) use print_tag_group() instead.
	 *
	 * @param Tag_Chain $tags
	 */
	public static function print_tags( array $tags ): void {
		if ( count( $tags ) > 0 ) {
			$head = array_shift( $tags );

			self::print_item( $head, $tags );
		}
	}

	/**
	 * @param Tag_Chain $tags
	 */
	public static function render_tags( array $tags ): string {
		ob_start();
		self::print_tags( $tags );

		return (string) ob_get_clean();
	}

	/**
	 * Use when the items are siblings (a group), e.g. `array( $before_text, $tag_chain, $after_text )`: every member is
	 * rendered at the same level. It replaces `print_tags( array( $group ) )`, where the extra array only marks the head
	 * as a group.
	 *
	 * @param Tag_Group $group
	 */
	public static function print_tag_group( array $group ): void {
		self::print_group( $group );
	}

	/**
	 * @param Tag_Group $group
	 */
	public static function render_tag_group( array $group ): string {
		ob_start();
		self::print_tag_group( $group );

		return (string) ob_get_clean();
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

		self::print_text( $item );
	}

	/**
	 * @param Tag_Text $text
	 */
	protected static function print_text( $text ): void {
		if ( is_string( $text ) ) {
			echo esc_html( $text );

			return;
		}

		if ( $text instanceof Stringable ) {
			echo $text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			return;
		}

		$text();
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
