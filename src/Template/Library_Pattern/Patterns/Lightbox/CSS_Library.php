<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Patterns\Lightbox;

use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Css\Library\CSS_Library_Base;

defined( 'ABSPATH' ) || exit;

final class CSS_Library extends CSS_Library_Base {
	private bool $is_active;

	public function __construct() {
		$this->is_active = false;
	}

	public function enable(): void {
		$this->is_active = true;
	}

	public function get_inline_css(): string {
		if ( ! $this->is_active ) {
			return '';
		}

		$css_code = '';

		$css_code .= '.acf-views-light-box{position:fixed;top:0;left:0;right:0;bottom:0;z-index:999999;display: flex;justify-content: center;align-items: center;background:rgba(0, 0, 0, 0.9);padding:5%;}';
		$css_code .= '.acf-views-light-box__image{max-width:100%;max-height:100%;}';
		$css_code .= '.acf-views-light-box__image:hover{cursor:zoom-out;}';
		$css_code .= '.acf-views-light-box__icon{stroke: currentColor;stroke-linecap: square;stroke-width: 6px;fill:none;position: absolute;z-index: 9;bottom: 15px;left: 50%;transform: translateX(50%);color:white;opacity:.5;transition:all ease .3s;}';
		$css_code .= '.acf-views-light-box__icon:hover{cursor:pointer;opacity:.7;}';
		$css_code .= '.acf-views-light-box__icon-left{transform: scaleX(-1) translateX(150%);}';
		$css_code .= '.acf-views-light-box__icon--inactive{opacity:.3;pointer-events:none;}';

		return $css_code;
	}
}
