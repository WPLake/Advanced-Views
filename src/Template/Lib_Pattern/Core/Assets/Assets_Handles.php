<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Assets;

defined( 'ABSPATH' ) || exit;

class Assets_Handles {
	/**
	 * @var string[]
	 */
	public array $js;
	/**
	 * @var string[]
	 */
	public array $css;

	/**
	 * @param string[] $js
	 * @param string[] $css
	 */
	public function __construct( array $js = array(), array $css = array() ) {
		$this->js  = $js;
		$this->css = $css;
	}

	public function merge( Assets_Handles $handles ): Assets_Handles {
		$js  = self::merge_unique( $this->js, $handles->js );
		$css = self::merge_unique( $this->css, $handles->css );

		return new Assets_Handles( $js, $css );
	}

	/**
	 * @param string[] $first
	 * @param string[] $second
	 *
	 * @return string[]
	 */
	protected static function merge_unique( array $first, array $second ): array {
		$merged = array_merge( $first, $second );
		$unique = array_unique( $merged );

		return array_values( $unique );
	}
}
