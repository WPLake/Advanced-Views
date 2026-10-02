<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Lib_Pattern\Layout;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Active_Libraries;
use Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Code_Piece_Printer;
use Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Library;
use Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Library_Assets;
use Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Library_Code;
use Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Library_Structure;
use Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Structure;
use Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Target;

/**
 * Translates a Layout into the neutral library vocabulary (Target); generic across all libraries.
 */
class Layout_Library_Adapter {
	protected Field_Provider_Cluster $provider_cluster;

	public function __construct( Field_Provider_Cluster $provider_cluster ) {
		$this->provider_cluster = $provider_cluster;
	}

	/**
	 * @return Target[]
	 */
	public function find_targets( Library $library, Layout_Settings $layout_settings ): array {
		$library_name          = $library->get_name();
		[$fields, $sub_fields] = $this->provider_cluster->get_fields_with_pattern( $library_name, $layout_settings );

		$targets = array();

		foreach ( $fields as $field ) {
			$targets[] = $this->build_target( $library, $layout_settings, $field, false );
		}

		foreach ( $sub_fields as $sub_field ) {
			$targets[] = $this->build_target( $library, $layout_settings, $sub_field, true );
		}

		return $targets;
	}

	public function activate( Library_Assets $library, Layout_Settings $layout_settings, Active_Libraries $active_libraries ): void {
		if ( array() !== $this->find_targets( $library, $layout_settings ) ) {
			$active_libraries->activate( $library->get_name(), $library->get_handles() );
		}
	}

	public function is_web_component_required( Library $library, Layout_Settings $layout_settings ): bool {
		return $library->is_web_component_required() &&
				array() !== $this->find_targets( $library, $layout_settings );
	}

	/**
	 * @return array{css:array<string,string>,js:array<string,string>}
	 */
	public function generate_code( Library_Code $library, Layout_Settings $layout_settings ): array {
		$code = array(
			'css' => array(),
			'js'  => array(),
		);

		$targets = $this->find_targets( $library, $layout_settings );

		foreach ( $targets as $target ) {
			$piece = $library->generate_code( $target );

			if ( '' !== $piece->js ) {
				$code['js'][ $target->field_id ] = Code_Piece_Printer::print_js( $library, $target, $piece->js );
			}

			if ( '' !== $piece->css ) {
				$code['css'][ $target->field_id ] = Code_Piece_Printer::print_css( $library, $target, $piece->css );
			}
		}

		return $code;
	}

	/**
	 * @param string $item_id template variable of the current item (the markup builder knows it)
	 */
	public function get_structure(
		Library_Structure $library,
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		string $item_id
	): Structure {
		$target = $this->build_target( $library, $layout_settings, $field_settings, false, $item_id );

		return $library->get_structure( $target );
	}

	/**
	 * @return array<string,callable(Field_Settings):string> library name => resolver of the library variant
	 */
	protected function get_variant_resolvers(): array {
		return array();
	}

	protected function build_target(
		Library $library,
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		bool $is_in_repeater,
		string $item_id = ''
	): Target {
		$bem_name     = $layout_settings->get_bem_name();
		$library_name = $library->get_name();
		$resolvers    = $this->get_variant_resolvers();
		$resolver     = $resolvers[ $library_name ] ?? null;

		$css_selector = self::build_selector( $layout_settings, $field_settings, true );
		$js_selector  = self::build_selector( $layout_settings, $field_settings, false );
		$field_id     = $field_settings->get_template_field_id();
		$var_name     = $is_in_repeater ?
			'item' :
			$field_id;
		$bem_element  = sprintf( '%s__%s', $bem_name, $field_settings->id );
		$field_meta   = $field_settings->get_field_meta();
		$is_multiple  = $field_meta->is_multiple();
		$variant      = is_callable( $resolver ) ?
			$resolver( $field_settings ) :
			'';

		return new Target(
			$css_selector,
			$js_selector,
			$var_name,
			$field_id,
			$bem_name,
			$bem_element,
			Layout_Settings::MAGIC_CSS_SELECTOR,
			$is_multiple,
			$is_in_repeater,
			$item_id,
			$variant
		);
	}

	/**
	 * Short form (the last part) isn't available with common classes, e.g. ".acf-view__name .acf-view__field" requires full.
	 * The magic form (CSS) is bound to the root id, so it has a higher specificity.
	 */
	protected static function build_selector( Layout_Settings $layout_settings, Field_Settings $field_settings, bool $is_magic ): string {
		$selector = $layout_settings->get_item_selector( $field_settings, 'field', false, true );

		if ( ! $layout_settings->is_with_common_classes ) {
			$parts      = explode( ' ', $selector );
			$last_index = count( $parts ) - 1;
			$selector   = $parts[ $last_index ];
		}

		if ( $is_magic ) {
			$bem_name      = $layout_settings->get_bem_name();
			$bem_prefix    = sprintf( '.%s__', $bem_name );
			$prefix_length = strlen( $bem_prefix );
			$short_part    = substr( $selector, $prefix_length );

			return sprintf( '#%s__%s', Layout_Settings::MAGIC_CSS_SELECTOR, $short_part );
		}

		return $selector;
	}
}
