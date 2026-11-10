<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Patterns\Map;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\JS_Library\JS_Library;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Layout\Layout_Pattern_Base;

defined( 'ABSPATH' ) || exit;

class Map_Pattern extends Layout_Pattern_Base {
	const NAME = 'acf-views-maps';

	/**
	 * @var string[]
	 */
	private array $maps;
	private Map_JS $js_library;

	public function __construct( Field_Provider_Cluster $provider_cluster ) {
		parent::__construct( $provider_cluster );


		$this->maps       = array();
		$this->js_library = $this->create_js_library();
	}

	public function get_js_library(): ?JS_Library {
		return $this->js_library;
	}

	protected function is_google_map_selector_inner( Field_Settings $field_settings ): bool {
		return false;
	}

	public function maybe_activate_layout( Layout_Settings $layout_settings ): void {
		$target_fields      = $this->get_target_fields( $layout_settings );
		$is_with_google_map = false;

		foreach ( $target_fields as $map_field ) {
			$map_type = $map_field->get_field_meta()->get_type();

			if ( 'open_street_map' !== $map_type ) {
				$is_with_google_map = true;
				$is_inner_target    = $this->is_google_map_selector_inner( $map_field );
				$this->maps[]       = $layout_settings->get_item_selector( $map_field, 'map', $is_inner_target );
			}
		}

		// only google map requires it.
		if ( $is_with_google_map ) {
			$this->js_library->enable();
			$this->js_library->set_maps( $this->maps );
		}
	}

	protected function create_js_library(): Map_JS {
		return new Map_JS();
	}
}
