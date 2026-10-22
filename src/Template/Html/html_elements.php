<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html;

defined( 'ABSPATH' ) || exit;

/**
 * @param array<string,mixed> $attrs
 *
 * @return array{tag:string, attrs?:array<string,mixed>}
 */
function tag( string $name, array $attrs = array() ): array {
	return 0 === count( $attrs ) ?
		array( Html::TAG_NAME => $name ) :
		array(
			Html::TAG_NAME  => $name,
			Html::TAG_ATTRS => $attrs,
		);
}

/**
 * The first item is the parent, every next one goes inside the previous one.
 *
 * @param mixed|null ...$items Html_Node|null
 *
 * @return array{nest:list<mixed>}
 */
function nest( ...$items ): array {
	return array( Html::NEST => Html::exclude_null_nodes( $items ) );
}

/**
 * Sibling items rendered at the same level.
 *
 * @param mixed|null ...$items Html_Node|null
 *
 * @return array{stack:list<mixed>}
 */
function stack( ...$items ): array {
	return array( Html::STACK => Html::exclude_null_nodes( $items ) );
}
