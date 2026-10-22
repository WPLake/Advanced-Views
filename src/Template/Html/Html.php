<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Htmlable;

defined( 'ABSPATH' ) || exit;

/**
 * Named constructors for the Html_Renderer nodes. Nodes nest freely.
 *
 * Tag: a single element.
 * Tag_Stack: items rendered at the same level.
 * Tag_Wrap: every next item goes inside the previous one, so all items but the last must be Tags.
 * Tag_Content: a string (printed escaped), an Htmlable, or a Closure printer (printed as is, so it must escape itself).
 * Html_Node: any of them. A `null` next to it means 'no element' and is ignored everywhere.
 * Stack/wrap items are 'mixed' as PHPStan doesn't support recursive aliases.
 *
 * @phpstan-type Tag array{tag:string, attrs?:array<string,mixed>}
 * @phpstan-type Tag_Content string|Htmlable|Closure():(void|mixed)
 * @phpstan-type Tag_Stack array{stack:list<mixed>}
 * @phpstan-type Tag_Wrap array{wrap:list<mixed>}
 * @phpstan-type Html_Node Tag|Tag_Content|Tag_Stack|Tag_Wrap
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
	 * @param Html_Node|null ...$items
	 *
	 * @return Tag_Wrap
	 */
	public static function wrap( ...$items ): array {
		return array( self::WRAP => $items );
	}

	/**
	 * Sibling items rendered at the same level.
	 *
	 * @param Html_Node|null ...$items
	 *
	 * @return Tag_Stack
	 */
	public static function stack( ...$items ): array {
		return array( self::STACK => $items );
	}
}
