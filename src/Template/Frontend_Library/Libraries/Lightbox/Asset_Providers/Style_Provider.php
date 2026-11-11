<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Lightbox\Asset_Providers;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Provider\Style_Provider_Base;

defined( 'ABSPATH' ) || exit;

final class Style_Provider extends Style_Provider_Base {
	private bool $is_active;

	public function __construct() {
		$this->is_active = false;
	}

	public function enable(): void {
		$this->is_active = true;
	}

	public function has_active_styles(): bool {
		return $this->is_active;
	}

	public function get_inline_styles(): array {
		return array(
			'.acf-views-light-box'                       => array(
				'position'        => 'fixed',
				'top'             => '0',
				'left'            => '0',
				'right'           => '0',
				'bottom'          => '0',
				'z-index'         => '999999',
				'display'         => 'flex',
				'justify-content' => 'center',
				'align-items'     => 'center',
				'background'      => 'rgba(0, 0, 0, 0.9)',
				'padding'         => '5%',
			),
			'.acf-views-light-box__image'                => array(
				'max-width'  => '100%',
				'max-height' => '100%',
			),
			'.acf-views-light-box__image:hover'          => array(
				'cursor' => 'zoom-out',
			),
			'.acf-views-light-box__icon'                 => array(
				'stroke'         => 'currentColor',
				'stroke-linecap' => 'square',
				'stroke-width'   => '6px',
				'fill'           => 'none',
				'position'       => 'absolute',
				'z-index'        => '9',
				'bottom'         => '15px',
				'left'           => '50%',
				'transform'      => 'translateX(50%)',
				'color'          => 'white',
				'opacity'        => '.5',
				'transition'     => 'all ease .3s',
			),
			'.acf-views-light-box__icon:hover'           => array(
				'cursor'  => 'pointer',
				'opacity' => '.7',
			),
			'.acf-views-light-box__icon-left'            => array(
				'transform' => 'scaleX(-1) translateX(150%)',
			),
			'.acf-views-light-box__icon--inactive'       => array(
				'opacity'        => '.3',
				'pointer-events' => 'none',
			),
		);
	}
}
