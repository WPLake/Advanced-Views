<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Patterns\Lightbox;

use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\JS_Library\JS_Pattern_Base;

defined( 'ABSPATH' ) || exit;

final class Lightbox_JS extends JS_Pattern_Base {
	/**
	 * @var array<int,array<string,mixed>>
	 */
	private array $light_boxes;

	public function __construct() {
		parent::__construct( array( 'acf-views-lightbox' ) );

		$this->light_boxes = array();
	}

	/**
	 * @param array<int,array<string,mixed>> $light_boxes
	 */
	public function set_light_boxes( array $light_boxes ): void {
		$this->light_boxes = $light_boxes;
	}

	public function get_js_data(): array {
		return array(
			'acf-views-lightbox' => array(
				'acfViewsLightBox' => $this->light_boxes,
			),
		);
	}
}
