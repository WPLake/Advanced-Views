<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Htmlable;

defined( 'ABSPATH' ) || exit;

/**
 * Html_Renderer node types and keys. The nodes are built by the functions of Tag_Element.php. Nodes nest freely.
 *
 * Tag: a single element.
 * Tag_Stack: items rendered at the same level.
 * Tag_Wrap: every next item goes inside the previous one, so all items but the last must be Tags.
 * Tag_Content: a string (printed escaped), an Htmlable, or a Closure printer (printed as is, so it must escape itself).
 * Tag_Element: a Tag, Tag_Stack or Tag_Wrap, i.e. what Html_Renderer accepts as a root.
 * Html_Node: any member of a stack/wrap. A `null` next to it means 'no element' and is dropped by the constructors.
 * Stack/wrap items are 'mixed' as PHPStan doesn't support recursive aliases.
 *
 * @phpstan-type Tag array{tag:string, attrs?:array<string,mixed>}
 * @phpstan-type Tag_Content string|Htmlable|Closure():(void|mixed)
 * @phpstan-type Tag_Stack array{stack:list<mixed>}
 * @phpstan-type Tag_Wrap array{wrap:list<mixed>}
 * @phpstan-type Tag_Element Tag|Tag_Stack|Tag_Wrap
 * @phpstan-type Html_Node Tag_Element|Tag_Content
 */
final class Html {
	const TAG_NAME  = 'tag';
	const TAG_ATTRS = 'attrs';
	const STACK     = 'stack';
	const WRAP      = 'wrap';

	/**
	 * @param array<Html_Node|null> $items
	 *
	 * @return list<Html_Node>
	 */
	public static function exclude_null_nodes( array $items ): array {
		$nodes = array();

		foreach ( $items as $item ) {
			if ( ! is_null( $item ) ) {
				$nodes[] = $item;
			}
		}

		return $nodes;
	}
}
