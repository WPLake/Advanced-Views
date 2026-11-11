<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Lightbox;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Lightbox\Asset_Providers\Style_Provider;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Lightbox\Asset_Providers\Script_Provider;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Lightbox\Patterns\Gallery_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Lightbox\Patterns\Lightbox_Pattern;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Builder\Script_Pattern_Builder;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Builder\Style_Builder;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Layout\Layout_Library_Base;

class Lightbox_Library extends Layout_Library_Base {
	const NAME = 'acf-views-lightbox';

	/**
	 * @var array<string,array<string,mixed>>
	 */
	private array $light_boxes;
	private Script_Provider $script_provider;
	private Style_Provider $style_provider;

	public function __construct( Field_Provider_Cluster $provider_cluster, Script_Pattern_Builder $script_pattern_builder, Style_Builder $style_builder ) {
		parent::__construct( $provider_cluster, $script_pattern_builder, $style_builder );

		$this->set_auto_discover_name( 'acf-views-lightbox' );

		$this->light_boxes = array();
		$this->script_provider  = new Script_Provider();
		$this->style_provider = new Style_Provider();
	}

	public function get_js_library(): Script_Provider {
		return $this->script_provider;
	}

	public function get_css_library(): Style_Provider {
		return $this->style_provider;
	}

	protected static function get_field_prefix( Layout_Settings $layout_settings, Field_Settings $field_settings ): string {
		$bem_name     = $layout_settings->get_bem_name();
		$field_prefix = sprintf( '%s__', $bem_name );

		if ( ! $layout_settings->is_with_common_classes ) {
			$field_prefix .= sprintf( '%s-', $field_settings->id );
		}

		return $field_prefix;
	}

	protected function add_light_box( Layout_Settings $layout_settings, Field_Settings $field_settings ): void {
		$is_gallery    = $field_settings->get_field_meta()->is_multiple();
		$item_selector = $this->get_item_selector( $layout_settings, $field_settings, true, false );
		$bem_name      = $layout_settings->get_bem_name();
		$field_prefix  = self::get_field_prefix( $layout_settings, $field_settings );

		$light_box = array(
			'selector'    => $item_selector,
			'bemName'     => $bem_name,
			'fieldPrefix' => $field_prefix,
			'isGallery'   => $is_gallery,
		);

		/**
		 * Selector as a key ensures that we've no duplicates -
		 * even if the same Layout->lightboxField appears several times on the page -
		 * as we need only list of distinct elements.
		 */
		$this->light_boxes[ $item_selector ] = $light_box;
	}

	protected function resolve_css_pattern( Field_Settings $field_settings, Layout_Settings $layout_settings ): ?string {
		return $field_settings->get_field_meta()->is_multiple() ?
			Gallery_Pattern::class :
			Lightbox_Pattern::class;
	}

	public function maybe_activate_layout( Layout_Settings $layout_settings ): void {
		$target_fields = $this->get_target_fields( $layout_settings );

		if ( array() !== $target_fields ) {
			foreach ( $target_fields as $target_field ) {
				$this->add_light_box( $layout_settings, $target_field );
			}

			$light_boxes = array_values( $this->light_boxes );

			$this->script_provider->enable();
			$this->script_provider->set_light_boxes( $light_boxes );
			$this->style_provider->enable();
		}
	}

	public function get_template_pattern( Field_Settings $field_settings, Layout_Settings $layout_settings ): ?string {
		return $field_settings->get_field_meta()->is_multiple() ?
			Gallery_Pattern::class :
			Lightbox_Pattern::class;
	}
}
