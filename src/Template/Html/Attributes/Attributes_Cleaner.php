<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Attributes;

defined( 'ABSPATH' ) || exit;

interface Attributes_Cleaner {
	/**
	 * Returns the escaped attribute values
	 *
	 * @param array<string,mixed> $attributes
	 *
	 * @return array<string,string>
	 */
	public function clean_attributes( array $attributes ): array;
}
