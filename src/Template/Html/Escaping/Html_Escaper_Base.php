<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Escaping;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Htmlable;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Stringifier;

abstract class Html_Escaper_Base implements Html_Escaper {
	protected Stringifier $attribute_stringifier;

	public function __construct() {
		$attribute_handlers          = array(
			Htmlable::class => fn( object $value ) => $value instanceof Htmlable ?
				$value->to_html( $this ) :
				'',
		);
		$this->attribute_stringifier = new Stringifier( $this, $attribute_handlers );
	}

	/**
	 * @param mixed $value
	 */
	public function esc_attr( $value ): string {
		return $this->attribute_stringifier->stringify( $value );
	}
}
