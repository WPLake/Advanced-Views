<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Map;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Map\Asset_Providers\Script_Provider;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Builder\Script_Pattern_Builder;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Builder\Style_Builder;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Layout\Layout_Library_Base;

defined( 'ABSPATH' ) || exit;

class Map_Library extends Layout_Library_Base {
	const NAME = 'acf-views-maps';

	/**
	 * @var string[]
	 */
	private array $maps;
	private Script_Provider $script_provider;

	public function __construct( Field_Provider_Cluster $provider_cluster, Script_Pattern_Builder $script_pattern_builder, Style_Builder $style_builder ) {
		parent::__construct( $provider_cluster, $script_pattern_builder, $style_builder );


		$this->maps       = array();
		$this->script_provider = $this->create_js_library();
	}

	public function get_js_library(): Script_Provider {
		return $this->script_provider;
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
			$this->script_provider->enable();
			$this->script_provider->set_maps( $this->maps );
		}
	}

	protected function create_js_library(): Script_Provider {
		return new Script_Provider();
	}
}
