<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Patterns;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Template\Html_Wrapper;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Template\Template_Pattern_Base;

class Lightbox_Pattern extends Template_Pattern_Base {
	const NAME = 'acf-views-lightbox';

	/**
	 * @var array<string,array<string,mixed>>
	 */
	private array $light_boxes;

	public function __construct( Asset_Resolver $asset_resolver, Field_Provider_Cluster $provider_cluster ) {
		parent::__construct( $asset_resolver, $provider_cluster );

		$this->set_auto_discover_name( 'acf-views-lightbox' );
		$this->set_js_handles(
			array(
				'acf-views-lightbox' => false,
			)
		);

		$this->light_boxes = array();
	}

	public function enqueue_active(): string {
		$css_code = parent::enqueue_active();

		if ( ! $this->is_enabled_js_handle( 'acf-views-lightbox' ) ) {
			return $css_code;
		}

		$wp_handle   = self::get_wp_handle( 'acf-views-lightbox' );
		$light_boxes = array_values( $this->light_boxes );

		wp_localize_script( $wp_handle, 'acfViewsLightBox', $light_boxes );

		if ( array() === $this->light_boxes ) {
			return $css_code;
		}

		$css_code .= '.acf-views-light-box{position:fixed;top:0;left:0;right:0;bottom:0;z-index:999999;display: flex;justify-content: center;align-items: center;background:rgba(0, 0, 0, 0.9);padding:5%;}';
		$css_code .= '.acf-views-light-box__image{max-width:100%;max-height:100%;}';
		$css_code .= '.acf-views-light-box__image:hover{cursor:zoom-out;}';
		$css_code .= '.acf-views-light-box__icon{stroke: currentColor;stroke-linecap: square;stroke-width: 6px;fill:none;position: absolute;z-index: 9;bottom: 15px;left: 50%;transform: translateX(50%);color:white;opacity:.5;transition:all ease .3s;}';
		$css_code .= '.acf-views-light-box__icon:hover{cursor:pointer;opacity:.7;}';
		$css_code .= '.acf-views-light-box__icon-left{transform: scaleX(-1) translateX(150%);}';
		$css_code .= '.acf-views-light-box__icon--inactive{opacity:.3;pointer-events:none;}';

		return $css_code;
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

	protected function print_css_code(
		string $field_selector,
		Field_Settings $field_settings,
		Layout_Settings $layout_settings
	): void {
		$is_gallery = $field_settings->get_field_meta()->is_multiple();

		printf(
			"%s {\n\tlist-style: none;\n}\n\n",
			esc_html( $field_selector )
		);

		if ( $is_gallery ) {
			printf(
				"%s img:hover {\n\tcursor: zoom-in;\n}",
				esc_html( $field_selector )
			);
		} else {
			printf(
				"%s:hover {\n\tcursor: zoom-in;\n}",
				esc_html( $field_selector )
			);
		}
	}

	public function maybe_activate( Cpt_Settings $cpt_settings ): void {
		if ( $cpt_settings instanceof Layout_Settings ) {
			$target_fields = $this->get_target_fields( $cpt_settings );

			if ( array() !== $target_fields ) {
				foreach ( $target_fields as $target_field ) {
					$this->add_light_box( $cpt_settings, $target_field );
				}

				$this->enable_js_handle( 'acf-views-lightbox' );
			}
		}
	}

	public function get_field_wrapper_tag( Field_Settings $field_settings, string $row_type ): string {
		return $field_settings->get_field_meta()->is_multiple() ?
			'ul' :
			'div';
	}

	/**
	 * @return Html_Wrapper[]
	 */
	public function get_item_outers(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		string $field_id,
		string $item_id
	): array {
		return array(
			new Html_Wrapper( 'li', array() ),
		);
	}

	public function get_inner_variable_attributes( Field_Settings $field_settings, string $field_id ): array {
		return array(
			'data-full-size' => array(
				'field_id' => $field_id,
				'item_key' => 'full_size',
			),
		);
	}
}
