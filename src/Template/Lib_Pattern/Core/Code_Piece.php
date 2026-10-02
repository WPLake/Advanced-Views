<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core;

defined( 'ABSPATH' ) || exit;

class Code_Piece {
	public string $css;
	public string $js;

	public function __construct( string $css = '', string $js = '' ) {
		$this->css = $css;
		$this->js  = $js;
	}
}
