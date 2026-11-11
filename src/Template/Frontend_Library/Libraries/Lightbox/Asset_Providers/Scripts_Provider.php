<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Lightbox\Asset_Providers;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\JavaScript\Provider\Scripts_Provider_Base;

defined( 'ABSPATH' ) || exit;

final class Scripts_Provider extends Scripts_Provider_Base {
	protected const HANDLE = 'acf-views-lightbox';

	/**
	 * @var array<int,array<string,mixed>>
	 */
	private array $light_boxes;

	public function __construct() {
		$this->light_boxes = array();
	}

	public function enable(): void {
		$this->enable_handles( array( self::HANDLE ) );
	}

	/**
	 * @param array<int,array<string,mixed>> $light_boxes
	 */
	public function set_light_boxes( array $light_boxes ): void {
		$this->light_boxes = $light_boxes;
	}

	public function get_script_variables(): array {
		return array(
			self::HANDLE => array(
				'acfViewsLightBox' => $this->light_boxes,
			),
		);
	}
}
