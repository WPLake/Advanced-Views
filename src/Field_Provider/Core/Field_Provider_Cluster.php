<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Core;

defined( 'ABSPATH' ) || exit;

use DateTime;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Field_Type\Field_Type;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Loggable_Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;
use Org\Wplake\Advanced_Views\Plugin\Utils\Safe_Array_Arguments;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\File_System_Loader;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Source;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\arr;

abstract class Field_Provider_Cluster extends Loggable_Actor implements Actor {
	/**
	 * 1. must be more than the default 10, so it's executed after the data vendor plugins fully loaded themselves (e.g. MetaBox has loading inside this hook)
	 * 2. '15' gives the ability to shift back when it needs, while still been after the default one.
	 */
	const PLUGINS_LOADED_HOOK_PRIORITY = 15;

	use Safe_Array_Arguments;

	/**
	 * @var array<string, Field_Provider> name => instance
	 */
	private array $provider_cluster;
	/**
	 * Vendor => field_id => Field_Meta.
	 *
	 * @var array<string, array<string, Field_Meta>>
	 */
	private array $field_meta_cache;
	/**
	 * Vendor => field_type => Markup_Field.
	 *
	 * @var array<string, array<string, Markup_Field|null>>
	 */
	private array $markup_field_cache;
	private Instance_Container $container;
	/**
	 * @var class-string<Field_Provider>[]
	 */
	private array $provider_classes;

	/**
	 * @param class-string<Field_Provider>[] $provider_classes
	 */
	public function __construct( Logger $logger, Instance_Container $container, array $provider_classes ) {
		parent::__construct( $logger );

		$this->container          = $container;
		$this->provider_classes   = $provider_classes;
		$this->provider_cluster   = array();
		$this->field_meta_cache   = array();
		$this->markup_field_cache = array();
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		return true;
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		// 1. with the higher priority than the default one, to make sure all vendor codes are loaded.
		// 2. still small, to be earlier than the rest of AVF code listening to this hook
		self::add_action(
			'plugins_loaded',
			array( $this, 'load_available_vendors' ),
			self::PLUGINS_LOADED_HOOK_PRIORITY
		);

		$this->make_integration_instances( $route_detector );
	}

	/**
	 * @return  array<string, Field_Provider> name => instance
	 */
	public function get_provider_cluster(): array {
		return $this->provider_cluster;
	}

	/**
	 * @return array<string, string>
	 */
	public function get_group_choices( bool $is_only_meta_vendors = false ): array {
		$choices = array(
			'' => __( 'Select', 'acf-views' ),
		);

		foreach ( $this->provider_cluster as $data_vendor ) {
			$is_skipped = $is_only_meta_vendors && ! $data_vendor->is_meta_vendor();

			if ( $is_skipped ) {
				continue;
			}

			$vendor_choices = $data_vendor->get_group_choices();
			$choices        = array_merge( $choices, $vendor_choices );
		}

		return $choices;
	}

	/**
	 * @return array<string,string>
	 */
	public function get_field_choices(
		bool $is_only_meta_vendors = false,
		bool $is_only_types_with_sub_fields = false,
		bool $is_field_name_as_label = false
	): array {
		$choices = false === $is_only_types_with_sub_fields ?
			array(
				'' => __( 'Select', 'acf-views' ),
			) :
			array();

		foreach ( $this->provider_cluster as $data_vendor ) {
			$is_skipped = $is_only_meta_vendors && false === $data_vendor->is_meta_vendor();

			if ( $is_skipped ) {
				continue;
			}

			$only_field_types = $is_only_types_with_sub_fields ?
				$data_vendor->get_field_types_with_sub_fields() :
				array();

			// skip if types with subFields were requested, but vendor doesn't have such types.
			if ( $is_only_types_with_sub_fields &&
				array() === $only_field_types ) {
				continue;
			}

			$vendor_choices = $data_vendor->get_field_choices( $only_field_types );
			$label_choices  = self::get_label_choices( $vendor_choices, $is_field_name_as_label );
			$choices        = array_merge( $choices, $label_choices );
		}

		return $choices;
	}

	/**
	 * @return array<string,string>
	 */
	public function get_sub_field_choices( bool $is_only_meta_vendors = false, bool $is_field_name_as_label = false ): array {
		$choices = array(
			'' => __( 'Select', 'acf-views' ),
		);

		foreach ( $this->provider_cluster as $data_vendor ) {
			$is_skipped = $is_only_meta_vendors && ! $data_vendor->is_meta_vendor();

			if ( $is_skipped ) {
				continue;
			}

			$vendor_choices = $data_vendor->get_sub_field_choices();
			$label_choices  = self::get_label_choices( $vendor_choices, $is_field_name_as_label );
			$choices        = array_merge( $choices, $label_choices );
		}

		return $choices;
	}

	/**
	 * @return array<string, array<int,string|int>>
	 */
	public function get_field_key_conditional_rules( bool $is_sub_fields = false ): array {
		$field_key_conditions = array();

		foreach ( $this->provider_cluster as $data_vendor ) {
			$vendor_field_key_conditional_rules = $data_vendor->get_field_key_conditional_rules( $is_sub_fields );

			foreach ( $vendor_field_key_conditional_rules as $vendor_field => $vendor_field_conditions ) {
				$field_key_conditions[ $vendor_field ] ??= array();
				$field_key_conditions[ $vendor_field ]   = array_merge(
					$field_key_conditions[ $vendor_field ],
					$vendor_field_conditions
				);
			}
		}

		return $field_key_conditions;
	}

	public function get_markup_field_instance(
		string $vendor_name,
		string $field_type
	): ?Markup_Field {
		if ( ! key_exists( $vendor_name, $this->provider_cluster ) ) {
			return null;
		}

		if ( ! key_exists( $field_type, $this->markup_field_cache[ $vendor_name ] ?? array() ) ) {
			$this->markup_field_cache[ $vendor_name ][ $field_type ] = $this->provider_cluster[ $vendor_name ]->get_markup_field_instance( $field_type );
		}

		return $this->markup_field_cache[ $vendor_name ][ $field_type ];
	}

	public function is_empty_value_supported_in_markup( string $vendor_name, string $field_type ): bool {
		if ( ! key_exists( $vendor_name, $this->provider_cluster ) ) {
			return false;
		}

		return $this->provider_cluster[ $vendor_name ]->is_empty_value_supported_in_markup( $field_type );
	}

	/**
	 * @param string $vendor_name
	 *
	 * @return string[]
	 */
	public function get_supported_field_types( string $vendor_name ): array {
		if ( ! key_exists( $vendor_name, $this->provider_cluster ) ) {
			return array();
		}

		return $this->provider_cluster[ $vendor_name ]->get_supported_field_types();
	}

	public function get_field_meta( string $vendor_name, string $field_id ): Field_Meta {
		$vendor = $this->provider_cluster[ $vendor_name ] ?? null;

		$this->field_meta_cache[ $vendor_name ] ??= array();

		if ( ! key_exists( $field_id, $this->field_meta_cache[ $vendor_name ] ) ) {
			$field_meta = new Field_Meta( $vendor_name, $field_id );

			if ( null !== $vendor ) {
				$vendor->fill_field_meta( $field_meta );
			}

			// it's okay if vendor isn't loaded (e.g. it's ACF field and ACF plugin is not present on the site)
			// so it'll have fieldMeta with isFieldExists() false.
			$this->field_meta_cache[ $vendor_name ][ $field_id ] = $field_meta;
		}

		return $this->field_meta_cache[ $vendor_name ][ $field_id ];
	}

	/**
	 * @param array<string|int,mixed> $local_data
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
	) {
		$vendor_name = $field_meta->get_vendor_name();

		if ( ! key_exists( $vendor_name, $this->provider_cluster ) ) {
			return null;
		}

		return $this->provider_cluster[ $vendor_name ]->get_field_value(
			$field_settings,
			$field_meta,
			$source,
			$item_settings,
			$is_formatted,
			$local_data
		);
	}

	// use $isForceLoading for tests only.
	public function load_available_vendors( bool $is_force_loading = false ): void {
		$providers = $this->resolve_providers();

		foreach ( $providers as $vendor ) {
			$is_skipped = ! $vendor->is_available() && ! $is_force_loading;

			if ( $is_skipped ) {
				continue;
			}

			$vendor_name = $vendor->get_name();

			$this->provider_cluster[ $vendor_name ] = $vendor;
		}
	}

	/**
	 * @return string[]
	 */
	public function get_field_front_assets( string $vendor_name, Field_Settings $field_settings ): array {
		if ( ! key_exists( $vendor_name, $this->provider_cluster ) ) {
			return array();
		}

		$field_front_assets = $this->provider_cluster[ $vendor_name ]->get_field_front_assets( $field_settings );

		// avoid duplicates (can be in case of the inheritance chain).
		return array_unique( $field_front_assets );
	}

	/**
	 * @return array{0:Field_Settings[],1:Field_Settings[]}
	 */
	public function get_fields_with_pattern( string $asset_name, Layout_Settings $layout_settings ): array {
		$fields = array(
			array(),
			array(),
		);

		foreach ( $layout_settings->items as $item ) {
			foreach ( $item->repeater_fields as $repeater_field ) {
				$vendor_name  = $this->get_vendor_name_by_key( $repeater_field->key );
				$front_assets = $this->get_field_front_assets( $vendor_name, $repeater_field );

				if ( ! in_array( $asset_name, $front_assets, true ) ) {
					continue;
				}

				$fields[1][] = $repeater_field;
			}

			$vendor_name  = $this->get_vendor_name_by_key( $item->field->key );
			$front_assets = $this->get_field_front_assets( $vendor_name, $item->field );

			if ( ! in_array( $asset_name, $front_assets, true ) ) {
				continue;
			}

			$fields[0][] = $item->field;
		}

		return $fields;
	}

	/**
	 * @return string[]
	 */
	public static function get_all_conditional_fields(): array {
		return array(
			Field_Settings::FIELD_LINK_LABEL,
			Field_Settings::FIELD_IS_LINK_TARGET_BLANK,
			Field_Settings::FIELD_ACF_VIEW_ID,
			Field_Settings::FIELD_SLIDER_TYPE,
			Field_Settings::FIELD_MAP_MARKER_ICON,
			Field_Settings::FIELD_MAP_MARKER_ICON_TITLE,
			Field_Settings::FIELD_MAP_ADDRESS_FORMAT,
			Field_Settings::FIELD_IS_MAP_WITH_ADDRESS,
			Field_Settings::FIELD_IS_MAP_WITHOUT_GOOGLE_MAP,
			Field_Settings::FIELD_IMAGE_SIZE,
			Field_Settings::FIELD_LIGHTBOX_TYPE,
			Field_Settings::FIELD_GALLERY_WITH_LIGHT_BOX,
			Field_Settings::FIELD_GALLERY_TYPE,
			Field_Settings::FIELD_OPTIONS_DELIMITER,
		);
	}

	public function is_field_type_with_sub_fields( string $vendor, string $field_type ): bool {
		if ( ! key_exists( $vendor, $this->provider_cluster ) ) {
			return false;
		}

		$types_with_sub_fields = $this->provider_cluster[ $vendor ]->get_field_types_with_sub_fields();

		return in_array(
			$field_type,
			$types_with_sub_fields,
			true
		);
	}

	public function convert_date_to_string_for_db_comparison(
		string $vendor,
		DateTime $date_time,
		Field_Meta $field_meta
	): string {
		if ( ! key_exists( $vendor, $this->provider_cluster ) ) {
			return '';
		}

		return $this->provider_cluster[ $vendor ]->convert_date_to_string_for_db_comparison(
			$date_time,
			$field_meta
		);
	}

	public function get_vendor_name_by_key( string $key ): string {
		// for ACF and custom fields source isn't set.
		$separator_position = strpos( $key, ':' );

		if ( is_int( $separator_position ) ) {
			return explode( ':', $key )[0];
		}

		$field_id = Field_Settings::get_field_id_by_key( $key );

		return $this->resolve_legacy_vendor_name( $field_id );
	}

	/**
	 * @return null|array{title:string,url:string}
	 */
	public function get_group_link_by_group_id( string $group_id, string $vendor_name = '' ): ?array {
		if ( '' === $vendor_name ) {
			$fake_key                       = sprintf( '%s|fake-field-id', $group_id );
			$vendor_name                    = $this->get_vendor_name_by_key( $fake_key );
			$group_id_without_vendor_prefix = explode( ':', $group_id )[1] ?? $group_id;
		} else {
			$group_id_without_vendor_prefix = $group_id;
		}

		if ( ! key_exists( $vendor_name, $this->provider_cluster ) ) {
			return null;
		}

		return $this->provider_cluster[ $vendor_name ]->get_group_link_by_group_id( $group_id_without_vendor_prefix );
	}

	public function convert_string_to_date_time( Field_Meta $field_meta, string $value ): ?DateTime {
		$vendor_name = $field_meta->get_vendor_name();

		if ( ! key_exists( $vendor_name, $this->provider_cluster ) ) {
			return null;
		}

		return $this->provider_cluster[ $vendor_name ]->convert_string_to_date_time( $field_meta, $value );
	}

	/**
	 * @param array<string, string> $import_files name => content
	 */
	public function import_related_group_files( array $import_files ): Related_Groups_Import_Result {
		$related_groups_import_result = new Related_Groups_Import_Result();

		$providers = $this->get_provider_cluster();

		foreach ( $import_files as $file_name => $file_content ) {
			$json_position = strpos( $file_name, '.json' );

			if ( ! is_int( $json_position ) ) {
				continue;
			}

			$file_vendor = str_replace( '.json', '', $file_name );

			if ( ! key_exists( $file_vendor, $providers ) ) {
				continue;
			}

			$import_data = json_decode( $file_content, true );

			if ( false === is_array( $import_data ) ) {
				continue;
			}

			$vendor = $providers[ $file_vendor ];

			$meta_data = arr( $import_data, 'meta' );
			// compatibility with the old export format, which didn't have meta at all.
			$groups_data = arr( $import_data, 'groups' );

			foreach ( $groups_data as $group_data ) {
				$group_data        = arr( $group_data );
				$imported_group_id = $vendor->import_group( $group_data, $meta_data );

				if ( null === $imported_group_id ) {
					continue;
				}

				$vendor_name = $vendor->get_name();

				$related_groups_import_result->add_group( $vendor_name, $imported_group_id );
			}
		}

		return $related_groups_import_result;
	}

	/**
	 * @return string[] uniqueIds
	 */
	public function get_related_view_unique_ids( Layout_Settings $layout_settings ): array {
		$related_view_unique_ids      = array();
		$fields_with_active_view_link = $layout_settings->get_fields_with_view_link();

		foreach ( $fields_with_active_view_link as $field_data ) {
			$field_meta  = $field_data->get_field_meta();
			$vendor_name = $field_meta->get_vendor_name();
			$field_type  = $field_meta->get_type();

			$is_active_link = $this->is_field_with_active_view_link(
				$field_data,
				$vendor_name,
				$field_type
			);

			if ( $is_active_link ) {
				$related_view_unique_ids[] = $field_data->acf_view_id;
			}
		}

		// remove duplicates.
		$related_view_unique_ids = array_unique( $related_view_unique_ids );

		return array_values( $related_view_unique_ids );
	}

	/**
	 * @return array<string, string> fileName => content
	 */
	public function get_related_group_export_files( Layout_Settings $layout_settings ): array {
		$related_groups            = array();
		$related_group_export_data = array();
		$providers                 = $this->get_provider_cluster();

		// 1. get all related group ids (not unique)
		foreach ( $layout_settings->items as $item ) {
			$group_id = $item->field->get_group_id();

			$vendor_name = $this->get_vendor_name_by_key( $item->field->key );

			if ( ! key_exists( $vendor_name, $providers ) ) {
				continue;
			}

			$related_groups[ $vendor_name ] ??= array();
			$related_groups[ $vendor_name ][] = $group_id;
		}

		// 2. get export data for each related group
		foreach ( $related_groups as $vendor_name => $group_ids ) {
			if ( ! key_exists( $vendor_name, $providers ) ) {
				continue;
			}

			$vendor = $providers[ $vendor_name ];
			// remove duplicates.
			$group_ids = array_unique( $group_ids );

			foreach ( $group_ids as $group_id ) {
				// feature can be not supported by the vendor (e.g. WP or Woo vendors).
				$group_export_data = $vendor->get_group_export_data( $group_id );

				if ( null === $group_export_data ) {
					continue;
				}

				$related_group_export_data[ $vendor_name ] ??= array();
				$related_group_export_data[ $vendor_name ][] = $group_export_data;
			}
		}

		// 3. prepare export files
		$related_group_export_files = array();

		foreach ( $related_group_export_data as $vendor_name => $groups_export_data ) {
			if ( ! key_exists( $vendor_name, $providers ) ) {
				continue;
			}

			$vendor           = $providers[ $vendor_name ];
			$export_meta_data = $vendor->get_export_meta_data( $groups_export_data );

			$export_data = array(
				'meta'   => $export_meta_data,
				'groups' => $groups_export_data,
			);

			$groups_export_content = wp_json_encode(
				$export_data,
				JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
			);

			if ( ! is_string( $groups_export_content ) ) {
				continue;
			}

			$file_name = sprintf( '%s.json', $vendor_name );

			$related_group_export_files[ $file_name ] = $groups_export_content;
		}

		return $related_group_export_files;
	}

	/**
	 * @return string[]
	 */
	public function get_export_file_names(): array {
		$export_file_names = array();

		$providers    = $this->get_provider_cluster();
		$vendor_names = array_keys( $providers );

		foreach ( $vendor_names as $vendor_name ) {
			$export_file_names[] = sprintf( '%s.json', $vendor_name );
		}

		return $export_file_names;
	}

	/**
	 * @return Field_Provider[]
	 */
	protected function resolve_providers(): array {
		return array_map(
			fn( string $provider_class ): Field_Provider => $this->container->resolve( $provider_class ),
			$this->provider_classes
		);
	}

	/**
	 * Back compatibility: keys without the vendor prefix.
	 */
	abstract protected function resolve_legacy_vendor_name( string $field_id ): string;

	protected function load_integration_instance(
		Route_Detector $route_detector,
		Field_Provider_Integration $data_vendor_integration,
		Layout_Settings_Storage $layouts_settings_storage
	): void {
		// functions below only for the admin part.
		if ( false === $route_detector->is_admin_route() ) {
			return;
		}

		$data_vendor_integration->add_tab_to_meta_group();
		$data_vendor_integration->add_column_to_list_table();
		$data_vendor_integration->validate_related_views_on_group_change();
		$data_vendor_integration->maybe_create_view_for_group();
	}

	protected function is_field_with_active_view_link(
		Field_Settings $field_settings,
		string $vendor_name,
		string $field_type
	): bool {
		// a) early return if field doesn't have the value in the field.
		if ( ! $field_settings->has_external_layout() ) {
			return false;
		}

		// b) check if the field has acfViewId in the conditional fields
		// (as can leave from the previous field type, e.g. after clone).

		$providers = $this->get_provider_cluster();

		if ( ! key_exists( $vendor_name, $providers ) ) {
			return false;
		}

		$markup_instance = $providers[ $vendor_name ]->get_markup_field_instance( $field_type );

		if ( null === $markup_instance ) {
			return false;
		}

		$field_meta         = $field_settings->get_field_meta();
		$conditional_fields = $markup_instance->get_conditional_fields( $field_meta );

		return in_array(
			Field_Settings::FIELD_ACF_VIEW_ID,
			$conditional_fields,
			true
		);
	}

	protected function make_integration_instances( Route_Detector $route_detector ): void {
		// 1. must on or later 'plugins_load', when meta plugins are loaded
		// 2. must be on or later 'after_setup_theme', when FS only Layouts and Post Selections are available
		File_System_Loader::instance()
							->add_loaded_callback(
								function () use ( $route_detector ): void {
									$layouts_settings_storage = $this->container->resolve( Layout_Settings_Storage::class );

									foreach ( $this->provider_cluster as $vendor ) {
										$integration_instance = $vendor->make_integration_instance( $this->container );

										// integration instance is optional (e.g. Woo and WP don't have).
										if ( null === $integration_instance ) {
											continue;
										}

										$this->load_integration_instance( $route_detector, $integration_instance, $layouts_settings_storage );
									}
								}
							);
	}

	/**
	 * @param array<string,Field_Meta> $choices
	 *
	 * @return array<string,string>
	 */
	protected static function get_label_choices( array $choices, bool $is_identifier ): array {
		$label_choices = array();

		foreach ( $choices as $key => $field_meta ) {
			$label_choices[ $key ] = $is_identifier ?
				$field_meta->get_identifier() :
				self::get_choice_label( $field_meta );
		}

		return $label_choices;
	}

	protected static function get_choice_label( Field_Meta $field_meta ): string {
		$type_label = self::resolve_type_label( $field_meta );
		$label      = $field_meta->get_label();
		$is_typed   = strlen( $type_label ) > 0;

		if ( $is_typed ) {
			$label = sprintf( '%s (%s)', $label, $type_label );
		}

		if ( $field_meta->is_pro_only() ) {
			$pro_only_label = __( '[PRO only]', 'acf-views' );
			$parts          = array(
				$label,
				$pro_only_label,
			);

			return join( ' ', $parts );
		}

		return $label;
	}

	/**
	 * Format is 'common:specific', e.g. 'list:file' for the vendor's 'file' type rendered by a List-based field class.
	 * The specific part is shown only for the vendor-native types (built-in fields like Woo's or WP's have internal ones),
	 * the common part exists only for the Field_Type classes, and equal parts are shown once.
	 */
	protected static function resolve_type_label( Field_Meta $field_meta ): string {
		// repeatable fields are 'group' in the meta, while the original is kept in the self-repeatable one.
		$self_repeatable_meta = $field_meta->get_self_repeatable_meta();
		$type_meta            = $self_repeatable_meta ?? $field_meta;
		$field_class          = $type_meta->get_field_class();

		$is_field_type_class = is_a(
			$field_class,
			Field_Type::class,
			true
		);

		$common   = $is_field_type_class ?
			$field_class::_TYPE_ :
			'';
		$specific = $type_meta->is_native_type() ?
			$type_meta->get_type() :
			'';

		$candidates = array( $common, $specific );
		$parts      = array_filter(
			$candidates,
			static fn( string $part ): bool => strlen( $part ) > 0
		);
		$unique     = array_unique( $parts );
		$type       = implode( ':', $unique );
		$is_filled  = strlen( $type ) > 0;

		if ( $is_filled && null !== $self_repeatable_meta ) {
			$repeatable_label = __( 'repeatable', 'acf-views' );
			$labeled_parts    = array(
				$repeatable_label,
				$type,
			);

			return join( ' ', $labeled_parts );
		}

		return $type;
	}
}
