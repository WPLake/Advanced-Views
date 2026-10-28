<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Rendering\Attribute\Normalizer;

defined( 'ABSPATH' ) || exit;

interface Attribute_Normalizer {
	/**
	 * @param mixed $value
	 */
	public function normalize_attribute( string $name, $value ): string;
}
