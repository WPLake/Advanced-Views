<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html;

defined( 'ABSPATH' ) || exit;

function tag( string $name ): Html_Tag {
	return new Html_Tag( $name );
}

function raw_html( string $html ): Raw_Html {
	return new Raw_Html( $html );
}

/**
 * @param array<string|object> $items
 */
function compose_attribute( array $items, string $delimiter = '' ): Composite_Attribute {
	$attribute = new Composite_Attribute();

	$attribute->items     = $items;
	$attribute->delimiter = $delimiter;

	return $attribute;
}
