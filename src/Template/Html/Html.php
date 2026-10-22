<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Htmlable;

defined( 'ABSPATH' ) || exit;

/**
 * Named constructors for the Html_Renderer structures, so the nesting reads by its meaning, not by the array shape.
 *
 * Every node is recognized by its own key (`tag`, `stack`, `wrap`), never by its position, so nodes nest freely.
 *
 * Tag: a single element.
 * Tag_Stack (stack): its members are rendered at the same level.
 * Tag_Wrap (wrap): every next item goes inside the previous one, so all items but the last are parents, and must be Tags.
 * A non-Tag parent is printed as is, and the rest of the wrap is ignored.
 * Tag_Content is either a plain string, printed escaped (never called, even if it's a callable name), or an Htmlable
 * (e.g. a template token) or a print callback, e.g. `fn() => print( $html )`, printed as is, so a callback must escape
 * its output itself. The callback is a Closure on purpose: a string is always text and an array is always a node, so
 * other callables (names, [object, method]) can't be told apart and aren't supported.
 * The callback returns 'void|mixed': the 'mixed' is allowed to accept
 * such callbacks.
 * Tag_Stack and Tag_Wrap items are 'mixed' as PHPStan doesn't support recursive aliases.
 *
 * @phpstan-type Tag array{tag:string, attrs?:array<string,mixed>}
 * @phpstan-type Tag_Content string|Htmlable|Closure():(void|mixed)
 * @phpstan-type Tag_Stack array{stack:list<mixed>}
 * @phpstan-type Tag_Wrap array{wrap:list<mixed>}
 * @phpstan-type Tag_Item Tag|Tag_Content|Tag_Stack|Tag_Wrap
 */
final class Html {
	const TAG_NAME  = 'tag';
	const TAG_ATTRS = 'attrs';
	const STACK     = 'stack';
	const WRAP      = 'wrap';

	/**
	 * @param array<string,mixed> $attrs
	 *
	 * @return Tag
	 */
	public static function tag( string $name, array $attrs = array() ): array {
		return 0 === count( $attrs ) ?
			array( self::TAG_NAME => $name ) :
			array(
				self::TAG_NAME  => $name,
				self::TAG_ATTRS => $attrs,
			);
	}

	/**
	 * The first item is the parent, every next one goes inside the previous one.
	 *
	 * @param mixed ...$items
	 *
	 * @return Tag_Wrap
	 */
	public static function wrap( ...$items ): array {
		return array( self::WRAP => $items );
	}

	/**
	 * Sibling items rendered at the same level.
	 *
	 * @param mixed ...$items
	 *
	 * @return Tag_Stack
	 */
	public static function stack( ...$items ): array {
		return array( self::STACK => $items );
	}
}
