<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Htmlable;

defined( 'ABSPATH' ) || exit;

/**
 * Named constructors for the Tags_Renderer structures, so the nesting reads by its meaning, not by the array shape.
 *
 * The renderer reads an array by its position: after the head of a tree it's a group, inside a group it's a tree.
 * So use stack() for a wrap() item and wrap() for a stack() member; for a stack inside a stack, wrap it: wrap( stack() ).
 *
 * Tag_Tree: every next item goes inside the previous one.
 * Tag_Group: its members (a Tag, a Tag_Text, a Tag_HTML or a Tag_Tree) are rendered at the same level, and it ends the tree.
 * Tag_Text is a plain string, printed escaped. It is never called, even if it's a callable name.
 * Tag_HTML is an Htmlable (e.g. a template token) or a print callback, e.g. `fn() => print( $html )`, printed as
 * is, so a callback must escape its output itself. The callback is a Closure on purpose: a string is always text and
 * an array is always a group, so other callables (names, [object, method]) can't be told apart and aren't supported.
 * The callback returns 'void|mixed': the 'mixed' is allowed to accept
 * such callbacks.
 * Tag_Group is 'mixed' as PHPStan doesn't support recursive aliases.
 *
 * @phpstan-type Tag array{tag:string, attrs?:array<string,mixed>}
 * @phpstan-type Tag_Text string
 * @phpstan-type Tag_HTML Htmlable|Closure():(void|mixed)
 * @phpstan-type Tag_Group list<mixed>
 * @phpstan-type Tag_Tree_Item Tag|Tag_Text|Tag_HTML|Tag_Group
 * @phpstan-type Tag_Tree list<Tag_Tree_Item>
 */
final class Html {
	/**
	 * @param array<string,mixed> $attrs
	 *
	 * @return Tag
	 */
	public static function tag( string $name, array $attrs = array() ): array {
		return 0 === count( $attrs ) ?
			array( 'tag' => $name ) :
			array(
				'tag'   => $name,
				'attrs' => $attrs,
			);
	}

	/**
	 * The first item is the parent, every next one goes inside the previous one.
	 *
	 * @param mixed ...$items
	 *
	 * @return list<mixed>
	 */
	public static function wrap( ...$items ): array {
		return $items;
	}

	/**
	 * Sibling items rendered at the same level.
	 *
	 * @param Tag_Tree_Item ...$items
	 *
	 * @return Tag_Group
	 */
	public static function stack( ...$items ): array {
		return $items;
	}
}
