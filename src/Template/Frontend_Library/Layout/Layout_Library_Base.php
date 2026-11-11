<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Layout;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Provider\Style_Provider;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Pattern\Style_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Builder\Script_Pattern_Builder;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Builder\Style_Builder;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Provider\Script_Provider;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Pattern\Script_Pattern;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory;

defined( 'ABSPATH' ) || exit;

abstract class Layout_Library_Base implements Layout_Library {
	const NAME = '';

	private Field_Provider_Cluster $provider_cluster;
	private string $auto_discover_name;
	protected Script_Pattern_Builder $script_pattern_builder;
	protected Style_Builder $css_builder;

	public function __construct( Field_Provider_Cluster $provider_cluster, Script_Pattern_Builder $script_pattern_builder, Style_Builder $css_builder ) {
		$this->provider_cluster    = $provider_cluster;
		$this->script_pattern_builder  = $script_pattern_builder;
		$this->css_builder = $css_builder;
		$this->auto_discover_name  = '';
	}

	public function get_name(): string {
		return static::NAME;
	}

	public function get_auto_discover_name(): string {
		return $this->auto_discover_name;
	}

	public function get_js_library(): ?Script_Provider {
		return null;
	}

	public function get_css_library(): ?Style_Provider {
		return null;
	}

	protected function set_auto_discover_name( string $auto_discover_name ): void {
		$this->auto_discover_name = $auto_discover_name;
	}

	/**
	 * The CSS declaration of the field, or null when the pattern has no CSS code for it.
	 *
	 * @return class-string<Style_Pattern>|null
	 */
	protected function resolve_css_pattern( Field_Settings $field_settings, Layout_Settings $layout_settings ): ?string {
		return null;
	}

	/**
	 * The JS declaration of the field, or null when the pattern has no JS code for it.
	 *
	 * @return class-string<Script_Pattern>|null
	 */
	protected function resolve_js_pattern( Field_Settings $field_settings, Layout_Settings $layout_settings ): ?string {
		return null;
	}

	/**
	 * The field's JS code with its wrapper, or an empty string when the pattern has no JS code for it.
	 */
	protected function create_js_snippet(
		string $var_name,
		Field_Settings $field_settings,
		Layout_Settings $layout_settings,
		string $js_field_selector,
		bool $is_multiple
	): string {
		$js_pattern = $this->resolve_js_pattern( $field_settings, $layout_settings );

		if ( is_string( $js_pattern ) ) {
			if ( $is_multiple ) {
				return $this->script_pattern_builder->build_multiple_items( $js_pattern, $js_field_selector );
			}

			return $this->script_pattern_builder->build_single_item( $js_pattern, $var_name, $js_field_selector );
		}

		return '';
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
	 * @return Html_Tag[]
	 */
	public function get_field_outers(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		string $field_id,
		string $row_type,
		Token_Factory $token_factory
	): array {
		return array();
	}

	/**
	 * @return Html_Tag[]
	 */
	public function get_item_outers(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		string $field_id,
		string $item_id,
		Token_Factory $token_factory
	): array {
		return array();
	}

	public function get_inner_variable_attributes( Field_Settings $field_settings, string $field_id ): array {
		return array();
	}

	public function is_label_out_of_row(): bool {
		return false;
	}

	/**
	 * @return array<string,string>
	 */
	public function generate_layout_css( Layout_Settings $layout_settings ): array {
		$css_pieces = array();
		$fields     = $this->get_target_fields( $layout_settings );

		foreach ( $fields as $field ) {
			$field_selector = $this->get_item_selector( $layout_settings, $field, false, true );
			$var_name       = $field->get_template_field_id();
			$css_pattern    = $this->resolve_css_pattern( $field, $layout_settings );

			if ( is_string( $css_pattern ) ) {
				$css_pieces[ $var_name ] = $this->css_builder->build( $css_pattern::get_css_rules(), $field_selector );
			}
		}

		return $css_pieces;
	}

	/**
	 * @return array<string,string>
	 */
	public function generate_layout_js( Layout_Settings $layout_settings ): array {
		[$target_fields, $target_sub_fields] = $this->provider_cluster->get_fields_with_pattern(
			static::NAME,
			$layout_settings
		);

		$field_pieces     = $this->create_js_snippets( $layout_settings, $target_fields, false );
		$sub_field_pieces = $this->create_js_snippets( $layout_settings, $target_sub_fields, true );

		return array_merge( $field_pieces, $sub_field_pieces );
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

	/**
	 * @param Field_Settings[] $fields
	 *
	 * @return array<string,string>
	 */
	protected function create_js_snippets( Layout_Settings $layout_settings, array $fields, bool $is_multiple ): array {
		$js_snippets = array();

		foreach ( $fields as $field ) {
			$field_selector = $this->get_item_selector( $layout_settings, $field, false, false );
			$var_name       = $field->get_template_field_id();
			$js_snippet     = $this->create_js_snippet(
				$var_name,
				$field,
				$layout_settings,
				$field_selector,
				$is_multiple
			);

			if ( '' !== $js_snippet ) {
				$js_snippets[ $var_name ] = $js_snippet;
			}
		}

		return $js_snippets;
	}
}
