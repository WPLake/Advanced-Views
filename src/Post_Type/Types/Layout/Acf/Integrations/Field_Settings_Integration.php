<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Integrations;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Acf_Utils;
use Org\Wplake\Advanced_Views\Acf\Integration\Groups\Acf_Integration_Base;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Repeater_Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Layout_Cpt;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\arr;

final class Field_Settings_Integration extends Acf_Integration_Base {
	private Field_Provider_Cluster $provider_cluster;

	public function __construct(
		Field_Provider_Cluster $provider_cluster,
		Layout_Cpt $plugin_cpt
	) {
		parent::__construct( $plugin_cpt->cpt_name() );

		$this->provider_cluster = $provider_cluster;
	}

	/**
	 * @param array<int,string|int> $target_choices
	 */
	protected function add_conditional_filter(
		string $field_name,
		array $target_choices,
		bool $is_sub_field = false
	): void {
		$acf_field_name = $is_sub_field ?
			Repeater_Field_Settings::getAcfFieldName( $field_name ) :
			Field_Settings::getAcfFieldName( $field_name );
		$acf_key        = $is_sub_field ?
			Repeater_Field_Settings::getAcfFieldName( Repeater_Field_Settings::FIELD_KEY ) :
			Field_Settings::getAcfFieldName( Field_Settings::FIELD_KEY );

		$rules_getter = fn( array $field ) => self::get_conditional_rules(
			$field,
			$acf_key,
			$target_choices
		);

		Acf_Utils::override_field_settings( $acf_field_name, $rules_getter );
	}

	protected function set_masonry_field_rules(): void {
		$masonry_fields = array(
			Field_Settings::FIELD_MASONRY_ROW_MIN_HEIGHT,
			Field_Settings::FIELD_MASONRY_GUTTER,
			Field_Settings::FIELD_MASONRY_MOBILE_GUTTER,
		);

		$gallery_type_name = Field_Settings::getAcfFieldName( Field_Settings::FIELD_GALLERY_TYPE );
		$rules_getter      = fn( array $field ) => self::get_conditional_rules(
			$field,
			$gallery_type_name,
			array( 'masonry' )
		);

		foreach ( $masonry_fields as $masonry_field ) {
			$acf_field_name = Field_Settings::getAcfFieldName( $masonry_field );

			Acf_Utils::override_field_settings( $acf_field_name, $rules_getter );
		}
	}

	protected function set_masonry_repeater_field_rules(): void {
		$masonry_repeater_fields = array(
			Repeater_Field_Settings::FIELD_MASONRY_ROW_MIN_HEIGHT,
			Repeater_Field_Settings::FIELD_MASONRY_GUTTER,
			Repeater_Field_Settings::FIELD_MASONRY_MOBILE_GUTTER,
		);

		$gallery_type_name = Repeater_Field_Settings::getAcfFieldName( Repeater_Field_Settings::FIELD_GALLERY_TYPE );
		$rules_getter      = fn( array $field ) => self::get_conditional_rules(
			$field,
			$gallery_type_name,
			array( 'masonry' )
		);

		foreach ( $masonry_repeater_fields as $masonry_repeater_field ) {
			$acf_field_name = Repeater_Field_Settings::getAcfFieldName( $masonry_repeater_field );

			Acf_Utils::override_field_settings( $acf_field_name, $rules_getter );
		}
	}

	protected function set_repeater_tab_rules(): void {
		$repeater_tab_name = Item_Settings::getAcfFieldName( Item_Settings::FIELD_REPEATER_FIELDS_TAB );
		$rules_getter      = fn( array $field ) => $this->get_repeater_tab_rules( $field );

		Acf_Utils::override_field_settings( $repeater_tab_name, $rules_getter );
	}

	/**
	 * @param array<string|int,mixed> $field
	 *
	 * @return array<string,mixed>
	 */
	protected function get_repeater_tab_rules( array $field ): array {
		// using exactly the negative (excludeTypes) filter,
		// otherwise if there are no such fields the field will be visible.
		$sub_field_choices = $this->provider_cluster->get_field_choices( false, true );
		$sub_field_choices = array_keys( $sub_field_choices );

		// if there are no repeater fields, then we add a dummy option to hide the field.
		if ( array() === $sub_field_choices ) {
			$sub_field_choices[] = '_not_exising_option';
		}

		return self::get_conditional_rules(
			$field,
			Field_Settings::getAcfFieldName( Field_Settings::FIELD_KEY ),
			$sub_field_choices
		);
	}

	protected function set_conditional_field_rules_by_values(): void {
		$this->set_masonry_field_rules();
		$this->set_masonry_repeater_field_rules();
		$this->set_repeater_tab_rules();
	}

	protected function set_conditional_field_rules(): void {
		$field_key_conditional_rules     = $this->provider_cluster->get_field_key_conditional_rules();
		$sub_field_key_conditional_rules = $this->provider_cluster->get_field_key_conditional_rules( true );

		foreach ( $field_key_conditional_rules as $field_name => $target_choices ) {
			$this->add_conditional_filter( $field_name, $target_choices );
		}

		foreach ( $sub_field_key_conditional_rules as $field_name => $target_choices ) {
			$this->add_conditional_filter( $field_name, $target_choices, true );
		}

		$all_conditional_fields = Field_Provider_Cluster::get_all_conditional_fields();
		$used_fields            = array_keys( $field_key_conditional_rules );
		$used_sub_fields        = array_keys( $sub_field_key_conditional_rules );

		$missing_conditional_fields     = array_diff( $all_conditional_fields, $used_fields );
		$missing_sub_conditional_fields = array_diff( $all_conditional_fields, $used_sub_fields );

		// make sure that unused conditional fields are hidden
		// (we use positive check, if field = x, so without this, if we don't have Map fields,
		// then all Map related options will be visible).

		foreach ( $missing_conditional_fields as $missing_conditional_field ) {
			$this->add_conditional_filter( $missing_conditional_field, array( '_not_existing_option' ) );
		}

		foreach ( $missing_sub_conditional_fields as $missing_sub_conditional_field ) {
			$this->add_conditional_filter( $missing_sub_conditional_field, array( '_not_existing_option' ), true );
		}

		$this->set_conditional_field_rules_by_values();
	}

	/**
	 * @return array<string,callable(): array<string,string>>
	 */
	protected function get_field_choices_callbacks(): array {
		return array(
			Field_Settings::FIELD_KEY        =>
				fn() => $this->provider_cluster->get_field_choices(),
			Field_Settings::FIELD_IMAGE_SIZE =>
				fn() => self::get_image_sizes(),
		);
	}

	/**
	 * @return array<string,callable(): array<string,string>>
	 */
	protected function get_repeater_choices_callbacks(): array {
		return array(
			Repeater_Field_Settings::FIELD_KEY        =>
				fn() => $this->provider_cluster->get_sub_field_choices(),
			Repeater_Field_Settings::FIELD_IMAGE_SIZE =>
				fn() => self::get_image_sizes(),
		);
	}

	protected function set_field_choices(): void {
		$field_choices_callbacks = $this->get_field_choices_callbacks();

		Acf_Utils::bind_field_choices( Field_Settings::class, $field_choices_callbacks );

		$repeater_choices_callbacks = $this->get_repeater_choices_callbacks();

		Acf_Utils::bind_field_choices( Repeater_Field_Settings::class, $repeater_choices_callbacks );
	}

	/**
	 * @param array<string|int,mixed> $field
	 * @param array<int, int|string> $equal_values
	 *
	 * @return array<string,mixed>
	 */
	protected static function get_conditional_rules(
		array $field,
		string $target_field,
		array $equal_values
	): array {
		// multiple calls of this method are allowed.
		$conditional_logic = arr( $field, 'conditional_logic' );

		foreach ( $equal_values as $equal_value ) {
			// using the OR rule.
			$conditional_logic[] = array(
				array(
					'field'    => $target_field,
					'operator' => '==',
					'value'    => $equal_value,
				),
			);
		}

		return array( 'conditional_logic' => $conditional_logic );
	}

	/**
	 * @return array<string,string>
	 */
	protected static function get_image_sizes(): array {
		$image_size_choices = array();
		$image_sizes        = get_intermediate_image_sizes();

		foreach ( $image_sizes as $image_size ) {
			$image_size_choices[ $image_size ] = ucfirst( $image_size );
		}

		$image_size_choices['full'] = __( 'Full', 'acf-views' );

		return $image_size_choices;
	}
}
