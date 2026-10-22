<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Htmlable;
use Org\Wplake\Advanced_Views\Template\Html\Tag\Tag_Renderer;

/**
 * @phpstan-import-type Tag from Html
 * @phpstan-import-type Html_Node from Html
 */
final class Html_Renderer {
	/**
	 * @param Html_Node|null $node
	 */
	public static function print( $node ): void {
		self::print_item( $node );
	}

	/**
	 * @param Html_Node|null $node
	 */
	public static function render( $node ): string {
		ob_start();
		self::print( $node );

		return (string) ob_get_clean();
	}

	/**
	 * @param mixed $item
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

			return;
		}

		self::print_content( $item );
	}

	/**
	 * @param list<mixed> $items
	 */
	protected static function print_wrap( array $items ): void {
		$items = array_values( array_filter( $items, fn( $item ) => null !== $item ) );

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
	 * @param list<mixed> $items
	 */
	protected static function print_stack( array $items ): void {
		foreach ( $items as $item ) {
			self::print_item( $item );
		}
	}

	/**
	 * @param Tag $item
	 * @param list<mixed> $nested_items
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
	 * @param mixed $content
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
