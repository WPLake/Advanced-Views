<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Escaping;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Stringifier;

abstract class Html_Escaper_Base implements Html_Escaper {
	protected Html_Stringifier $attribute_stringifier;

	public function __construct() {
		$this->attribute_stringifier = new Html_Stringifier( $this );
	}

	/**
	 * @param mixed $value
	 */
	public function esc_attr( $value ): string {
		return $this->attribute_stringifier->stringify( $value );
	}
}
