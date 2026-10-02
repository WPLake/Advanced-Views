<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Template;

use Org\Wplake\Advanced_Views\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Library_Pattern_Base;

defined( 'ABSPATH' ) || exit;

abstract class Template_Pattern_Base extends Library_Pattern_Base implements Template_Pattern {
	private Field_Provider_Cluster $provider_cluster;

	public function __construct( Asset_Resolver $asset_resolver, Field_Provider_Cluster $provider_cluster ) {
		parent::__construct( $asset_resolver );

		$this->provider_cluster = $provider_cluster;
	}

	protected function print_css_code(
		string $field_selector,
		Field_Settings $field_settings,
		Layout_Settings $layout_settings
	): void {
	}

	protected function print_js_code(
		string $var_name,
		Field_Settings $field_settings,
		Layout_Settings $layout_settings
	): void {
	}

	protected function get_item_selector(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		bool $is_full,
		bool $is_with_magic_selector,
		string $target = 'field'
	): string {
		if ( $this->is_label_out_of_row() ) {
			$target = '';
		}

		$item_selector = $layout_settings->get_item_selector(
			$field_settings,
			$target,
			false,
			! $is_full
		);

		// short version isn't available when common classes are used
		// e.g. ".acf-view__name .acf-view__field" required full.
		if ( ! $is_full &&
			! $layout_settings->is_with_common_classes ) {
			$selector_parts = explode( ' ', $item_selector );
			$last_index     = count( $selector_parts ) - 1;
			$item_selector  = $selector_parts[ $last_index ];
		}

		if ( $is_with_magic_selector ) {
			$bem_name      = $layout_settings->get_bem_name();
			$bem_prefix    = sprintf( '.%s__', $bem_name );
			$prefix_length = strlen( $bem_prefix );
			$item_tail     = substr( $item_selector, $prefix_length );
			$item_selector = sprintf( '#%s__%s', Layout_Settings::MAGIC_CSS_SELECTOR, $item_tail );
		}

		return $item_selector;
	}

	public function get_provider_cluster(): Field_Provider_Cluster {
		return $this->provider_cluster;
	}

	/**
	 * @return array{css:array<string,string>,js:array<string,string>}
	 */
	public function generate_code( Cpt_Settings $cpt_settings ): array {
		if ( $cpt_settings instanceof Layout_Settings ) {
			return $this->generate_layout_code( $cpt_settings );
		}

		return array(
			'css' => array(),
			'js'  => array(),
		);
	}

	public function get_row_wrapper_class( string $row_type ): string {
		return '';
	}

	public function get_row_wrapper_tag( Field_Settings $field_settings, string $row_type ): string {
		return '';
	}

	public function get_field_wrapper_tag( Field_Settings $field_settings, string $row_type ): string {
		return '';
	}

	/**
	 * @return array<string,string>
	 */
	public function get_field_wrapper_attrs( Field_Settings $field_settings, string $field_id ): array {
		return array();
	}

	/**
	 * @return Html_Wrapper[]
	 */
	public function get_field_outers(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		string $field_id,
		string $row_type
	): array {
		return array();
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
		return array();
	}

	public function get_inner_variable_attributes( Field_Settings $field_settings, string $field_id ): array {
		return array();
	}

	public function is_label_out_of_row(): bool {
		return false;
	}

	public function is_web_component_required( Cpt_Settings $cpt_settings ): bool {
		if ( $cpt_settings instanceof Layout_Settings &&
			$this->is_with_web_component() ) {
			[$target_fields, $target_sub_fields] = $this->provider_cluster->get_fields_with_pattern(
				static::NAME,
				$cpt_settings
			);

			return array() !== $target_fields ||
					array() !== $target_sub_fields;
		}

		return false;
	}

	/**
	 * @return array{css:array<string,string>,js:array<string,string>}
	 */
	protected function generate_layout_code( Layout_Settings $layout_settings ): array {
		$code = array(
			'css' => array(),
			'js'  => array(),
		);

		[$target_fields, $target_sub_fields] = $this->provider_cluster->get_fields_with_pattern(
			static::NAME,
			$layout_settings
		);

		foreach ( $target_fields as $field ) {
			$js_field_selector  = $this->get_item_selector( $layout_settings, $field, false, false );
			$css_field_selector = $this->get_item_selector( $layout_settings, $field, false, true );

			$var_name = $field->get_template_field_id();

			ob_start();
			$this->print_js_code( $var_name, $field, $layout_settings );
			$js_code_safe = (string) ob_get_clean();

			ob_start();
			$this->print_css_code( $css_field_selector, $field, $layout_settings );
			$css_code_safe = (string) ob_get_clean();

			if ( '' !== $js_code_safe ) {
				ob_start();
				$this->print_js_code_piece( $var_name, $js_code_safe, $js_field_selector, false );
				$code['js'][ $var_name ] = (string) ob_get_clean();
			}

			if ( '' !== $css_code_safe ) {
				ob_start();
				$this->print_code_piece( $var_name, $css_code_safe );
				$code['css'][ $var_name ] = (string) ob_get_clean();
			}
		}

		foreach ( $target_sub_fields as $field ) {
			$js_field_selector  = $this->get_item_selector( $layout_settings, $field, false, false );
			$css_field_selector = $this->get_item_selector( $layout_settings, $field, false, true );

			ob_start();
			$this->print_js_code( 'item', $field, $layout_settings );
			$js_code_safe = (string) ob_get_clean();

			ob_start();
			$this->print_css_code( $css_field_selector, $field, $layout_settings );
			$css_code_safe = (string) ob_get_clean();

			$var_name = $field->get_template_field_id();

			if ( '' !== $js_code_safe ) {
				ob_start();
				$this->print_js_code_piece( $var_name, $js_code_safe, $js_field_selector, true );
				$code['js'][ $var_name ] = (string) ob_get_clean();
			}

			if ( '' !== $css_code_safe ) {
				ob_start();
				$this->print_code_piece( $var_name, $css_code_safe );
				$code['css'][ $var_name ] = (string) ob_get_clean();
			}
		}

		return $code;
	}

	/**
	 * @return Field_Settings[]
	 */
	protected function get_target_fields( Layout_Settings $layout_settings ): array {
		[$target_fields, $target_sub_fields] = $this->provider_cluster->get_fields_with_pattern(
			static::NAME,
			$layout_settings
		);

		return array_merge( $target_fields, $target_sub_fields );
	}
}
