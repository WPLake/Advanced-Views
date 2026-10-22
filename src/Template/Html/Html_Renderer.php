<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Htmlable;
use Org\Wplake\Advanced_Views\Template\Html\Tag\Tag_Renderer;

defined( 'ABSPATH' ) || exit;

/**
 * @phpstan-import-type Tag from Html
 * @phpstan-import-type Tag_Item from Html
 * @phpstan-import-type Tag_Content from Html
 * @phpstan-import-type Tag_Stack from Html
 * @phpstan-import-type Tag_Wrap from Html
 */
final class Html_Renderer {
	/**
	 * @param Tag|Tag_Stack|Tag_Wrap $node
	 */
	public static function print_tags( array $node ): void {
		self::print_item( $node );
	}

	/**
	 * @param Tag|Tag_Stack|Tag_Wrap $node
	 */
	public static function render_tags( array $node ): string {
		ob_start();
		self::print_tags( $node );

		return (string) ob_get_clean();
	}

	/**
	 * @param Tag_Item $item
	 */
	protected static function print_item( $item ): void {
		if ( is_array( $item ) ) {
			if ( key_exists( Html::TAG_NAME, $item ) ) {
				self::print_tag( $item, array() );
			} elseif ( key_exists( Html::STACK, $item ) ) {
				self::print_stack( $item[ Html::STACK ] );
			} elseif ( key_exists( Html::WRAP, $item ) ) {
				self::print_wrap( $item[ Html::WRAP ] );
			}
		}

		self::print_content( $item );
	}

	/**
	 * @param list<Tag_Item> $items
	 */
	protected static function print_wrap( array $items ): void {
		if ( count( $items ) > 0 ) {
			$head = array_shift( $items );

			if ( is_array( $head ) && key_exists( Html::TAG_NAME, $head ) ) {
				self::print_tag( $head, $items );

				return;
			}

			// only a tag can have nested items, so the rest is ignored.
			self::print_item( $head );
		}
	}

	/**
	 * @param list<Tag_Item> $items
	 */
	protected static function print_stack( array $items ): void {
		foreach ( $items as $item ) {
			self::print_item( $item );
		}
	}

	/**
	 * @param Tag $item
	 * @param list<Tag_Item> $nested_items
	 */
	protected static function print_tag( array $item, array $nested_items ): void {
		$tag        = $item[ Html::TAG_NAME ];
		$attributes = $item[ Html::TAG_ATTRS ] ?? array();

		$content_printer = count( $nested_items ) > 0 ?
			fn() => self::print_wrap( $nested_items ) :
			null;

		Tag_Renderer::print_tag( $tag, $attributes, $content_printer );
	}

	/**
	 * @param Tag_Content $content
	 */
	protected static function print_content( $content ): void {
		if ( is_string( $content ) ) {
			echo esc_html( $content );

			return;
		}

		if ( $content instanceof Htmlable ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $content->to_html();

			return;
		}

		if ( $content instanceof Closure ) {
			$content();
		}
	}
}
