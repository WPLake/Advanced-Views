<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Patterns\Lightbox;

use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\JS_Library\JS_Library_Base;

defined( 'ABSPATH' ) || exit;

final class JS_Library extends JS_Library_Base {
	protected const HANDLE = 'acf-views-lightbox';

	/**
	 * @var array<int,array<string,mixed>>
	 */
	private array $light_boxes;

	public function __construct() {
		parent::__construct( array( self::HANDLE ) );

		$this->light_boxes = array();
	}

	public function enable(): void {
		$this->enable_handle( self::HANDLE );
	}

	/**
	 * @param array<int,array<string,mixed>> $light_boxes
	 */
	public function set_light_boxes( array $light_boxes ): void {
		$this->light_boxes = $light_boxes;
	}

	public function get_js_data(): array {
		return array(
			self::HANDLE => array(
				'acfViewsLightBox' => $this->light_boxes,
			),
		);
	}
}
