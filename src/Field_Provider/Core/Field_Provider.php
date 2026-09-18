<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Core;

use DateTime;
use Org\Wplake\Advanced_Views\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Acf\Groups\Repeater_Field_Settings;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Plugin_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layouts\Cpt\Layout_Save_Actions;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layouts\Integrations\Layout_Shortcode;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layouts\Layout_Factory;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layouts\Source;

defined( 'ABSPATH' ) || exit;

interface Field_Provider {
	public function get_name(): string;

	public function is_meta_vendor(): bool;

	public function is_available(): bool;

	public function make_integration_instance(
		Item_Settings $item_settings,
		Layout_Settings_Storage $layouts_settings_storage,
		Field_Provider_Cluster $provider_cluster,
		Layout_Save_Actions $layouts_cpt_save_actions,
		Layout_Factory $layout_factory,
		Repeater_Field_Settings $repeater_field_settings,
		Layout_Shortcode $layout_shortcode,
		Settings_Storage $settings,
		Plugin_Cpt $plugin_cpt
	): ?Field_Provider_Integration;

	public function get_group_key( string $group_id ): string;

	/**
	 * @return array<string, string>
	 */
	public function get_group_choices(): array;

	/**
	 * @param string[] $include_only_types
	 *
	 * @return array<string|int, Field_Meta|string>
	 */
	public function get_field_choices(
		array $include_only_types = array(),
		bool $is_meta_format = false,
		bool $is_field_name_as_label = false
	): array;

	/**
	 * @return array<string|int, Field_Meta|string>
	 */
	public function get_sub_field_choices( bool $is_meta_format = false, bool $is_field_name_as_label = false ): array;

	/**
	 * @return array<string, array<int,string|int>>
	 */
	public function get_field_key_conditional_rules( bool $is_sub_fields = false ): array;

	/**
	 * @return string[]
	 */
	public function get_supported_field_types(): array;

	public function get_markup_field_instance( string $field_type ): ?Markup_Field;

	public function is_empty_value_supported_in_markup( string $field_type ): bool;

	/**
	 * @param mixed[] $data
	 */
	public function fill_field_meta( Field_Meta $field_meta, array $data = array() ): void;

	/**
	 * @param array<string|int,mixed>|null $local_data
	 *
	 * @return mixed
	 */
	public function get_field_value(
		Field_Settings $field_settings,
		Field_Meta $field_meta,
		Source $source,
		?Item_Settings $item_settings = null,
		bool $is_formatted = false,
		?array $local_data = null
	);

	public function convert_string_to_date_time( Field_Meta $field_meta, string $value ): ?DateTime;

	public function convert_date_to_string_for_db_comparison(
		DateTime $date_time,
		Field_Meta $field_meta
	): string;

	/**
	 * @return string[]
	 */
	public function get_field_front_assets( Field_Settings $field_settings ): array;

	/**
	 * @return string[]
	 */
	public function get_field_types_with_sub_fields(): array;

	/**
	 * @return null|array{title:string,url:string}
	 */
	public function get_group_link_by_group_id( string $group_id ): ?array;

	/**
	 * @return mixed[]|null
	 */
	public function get_group_export_data( string $group_id ): ?array;

	/**
	 * @param mixed[] $groups_data
	 *
	 * @return array<string, mixed>
	 */
	public function get_export_meta_data( array $groups_data ): array;

	/**
	 * @param mixed[] $group_data
	 * @param mixed[] $meta_data
	 */
	public function import_group( array $group_data, array $meta_data ): ?string;
}
