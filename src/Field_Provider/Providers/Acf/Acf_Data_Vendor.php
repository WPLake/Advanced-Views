<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Acf;

use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use DateTime;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Group;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Acf\Fields\Color_Picker_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Acf\Fields\Icon_Picker_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Acf\Fields\Map_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Acf\Fields\Page_Link_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Base;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Integration;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Date_Picker_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\File_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Gallery_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Html_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Image_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Link_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Plain_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Post_Object_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Pro_Stub_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Select_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Tab_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Taxonomy_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\True_False_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Url_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\User_Field;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Cpt\Layout_Save_Actions;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Layout_Shortcode;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Layout_Factory;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Layout_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Source;

use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\arr;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\boolExtended;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\int;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\stringOrNull;

defined( 'ABSPATH' ) || exit;

class Acf_Data_Vendor extends Field_Provider_Base {
	// for back compatibility only.
	const NAME = 'acf';

	public function get_name(): string {
		return static::NAME;
	}

	public function is_meta_vendor(): bool {
		return true;
	}

	public function is_available(): bool {
		// do not mark available if it's inner ACF.
		return function_exists( 'get_field' ) &&
				! defined( 'ACF_VIEWS_INNER_ACF' );
	}

	public function make_integration_instance( Instance_Container $container ): ?Field_Provider_Integration {
		return new Acf_Integration(
			$container->resolve( Item_Settings::class ),
			$container->resolve( Layout_Settings_Storage::class ),
			$container->resolve( Field_Provider_Cluster::class ),
			$container->resolve( Layout_Save_Actions::class ),
			$container->resolve( Layout_Factory::class ),
			$this,
			$container->resolve( Layout_Shortcode::class ),
			$container->resolve( Settings_Storage::class ),
			$container->resolve( Layout_Cpt::class ),
			$container->resolve( Html_Renderer::class )
		);
	}

	/**
	 * @return array<string,Markup_Field>
	 */
	public function get_field_types(): array {
		$basic = array(
			'text'     => new Plain_Field(),
			// we don't need ACF to wrap every line of the value with <p> tags.
			'textarea' => new Html_Field( false, true ),
			'number'   => new Plain_Field(),
			'range'    => new Plain_Field(),
			'email'    => new Plain_Field(),
			'url'      => new Url_Field(),
			'password' => new Plain_Field(),
		);

		$content = array(
			'image'   => new Image_Field(),
			'file'    => new File_Field( new Link_Field() ),
			'wysiwyg' => new Html_Field(),
			'oembed'  => new Html_Field(),
			'gallery' => new Gallery_Field( new Image_Field() ),
		);

		$choice = array(
			'select'       => new Select_Field(),
			'checkbox'     => new Select_Field(),
			'radio'        => new Select_Field(),
			'button_group' => new Select_Field(),
			'true_false'   => new True_False_Field(),
		);

		$relational = array(
			'link'         => new Link_Field(),
			'post_object'  => new Post_Object_Field( new Link_Field() ),
			'page_link'    => new Page_Link_Field( new Link_Field() ),
			'relationship' => new Post_Object_Field( new Link_Field() ),
			'taxonomy'     => new Taxonomy_Field( new Link_Field() ),
			'user'         => new User_Field( new Link_Field() ),
		);

		$advanced = array(
			'google_map'       => new Map_Field(),
			// https://wordpress.org/plugins/acf-google-map-field-multiple-markers/.
			'google_map_multi' => new Map_Field(),
			// https://wordpress.org/plugins/acf-openstreetmap-field/.
			'open_street_map'  => new Map_Field(),
			'date_picker'      => new Date_Picker_Field(),
			'date_time_picker' => new Date_Picker_Field(),
			'time_picker'      => new Date_Picker_Field(),
			'color_picker'     => new Color_Picker_Field(),
			'icon_picker'      => new Icon_Picker_Field( new Image_Field() ),
		);

		$layout = array(
			'group'            => new Pro_Stub_Field(),
			'repeater'         => new Pro_Stub_Field(),
			'flexible_content' => new Pro_Stub_Field(),
			'tab'              => new Tab_Field(),
		);

		return array_merge( $basic, $content, $choice, $relational, $advanced, $layout );
	}

	/**
	 * @return array<string, string>
	 */
	public function get_group_choices(): array {
		if ( function_exists( 'acf_get_field_groups' ) ) {
			$acf_source_label = __( '(ACF)', 'acf-views' );
			$acf_groups       = $this->get_groups();

			$group_choices = array();

			foreach ( $acf_groups as $acf_group ) {
				$key   = string( $acf_group, 'key' );
				$title = string( $acf_group, 'title' );

				$group_key = $this->get_group_key( $key );
				$parts     = array(
					$title,
					$acf_source_label,
				);

				$group_choices[ $group_key ] = join( ' ', $parts );
			}

			return $group_choices;
		}

		return array();
	}

	/**
	 * @param string[] $include_only_types
	 *
	 * @return array<string, Field_Meta>
	 */
	public function get_field_choices( array $include_only_types = array() ): array {
		if ( function_exists( 'acf_get_fields' ) ) {
			$field_choices         = array();
			$supported_field_types = $this->get_supported_field_types();
			$groups                = $this->get_groups();

			foreach ( $groups as $group ) {
				$fields = acf_get_fields( $group );
				$fields = $this->paste_clone_fields( $fields );

				$group_id = string( $group, 'key' );

				$field_choices = $this->get_field_choices_recursively(
					$field_choices,
					$fields,
					$supported_field_types,
					$include_only_types,
					$group_id
				);
			}

			return $field_choices;
		}

		return array();
	}

	/**
	 * @param mixed[] $data
	 */
	public function fill_field_meta( Field_Meta $field_meta, array $data = array() ): void {
		if ( false === function_exists( 'get_field_object' ) ) {
			return;
		}

		if ( array() === $data ) {
			$data = get_field_object( $field_meta->get_field_id() );
			$data = is_array( $data ) ?
				$data :
				array();
		}

		$name           = string( $data, 'name' );
		$type           = string( $data, 'type' );
		$label          = string( $data, 'label' );
		$pro_stub_types = $this->get_pro_stub_field_types();
		$return_format  = string( $data, 'return_format' );
		$choices        = arr( $data, 'choices' );
		$display_format = string( $data, 'display_format' );
		$is_multiple    = boolExtended( $data, 'multiple' );
		$zoom           = int( $data, 'zoom' );
		$center_lat     = string( $data, 'center_lat' );
		$center_lng     = string( $data, 'center_lng' );
		$is_pro_only    = in_array(
			$type,
			$pro_stub_types,
			true
		);
		$is_filled      = strlen( $type ) > 0;

		$field_meta
			->set_is_native_type( true )
			->set_name( $name )
			->set_type( $type )
			->set_label( $label )
			->set_identifier( $name )
			->set_is_pro_only( $is_pro_only )
			->set_return_format( $return_format )
			->set_choices( $choices )
			->set_display_format( $display_format )
			->set_is_multiple( $is_multiple )
			->set_zoom( $zoom )
			->set_center_lat( $center_lat )
			->set_center_lng( $center_lng )
			->set_is_field_exist( $is_filled );

		if ( key_exists( 'default_value', $data ) ) {
			if ( is_array( $data['default_value'] ) ) {
				$field_meta->set_default_value( $data['default_value'] );
			} else {
				$default_value = string( $data, 'default_value' );

				$field_meta->set_default_value( $default_value );
			}
		}

		$set_multiple = fn() => $field_meta->set_is_multiple( true );
		$set_repeater = fn() => $field_meta->set_is_repeater( true );

		$type_fillers = array(
			'checkbox'                 => $set_multiple,
			'relationship'             => $set_multiple,
			'google_map_multi'         => $set_multiple,
			'open_street_map'          => $set_multiple,
			'gallery'                  => $set_multiple,
			'taxonomy'                 => fn() => $this->fill_taxonomy_meta( $field_meta, $data ),
			'repeater'                 => $set_repeater,
			'_flexible_content_layout' => $set_repeater,
			'group'                    => fn() => $field_meta->set_is_group( true ),
			'tab'                      => fn() => $field_meta->set_is_ui_only( true ),
		);

		if ( key_exists( $type, $type_fillers ) ) {
			$type_fillers[ $type ]();
		}
	}

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
	) {
		if ( function_exists( 'get_field' ) ) {
			$field_id  = $field_meta->get_field_id();
			$source_id = $source->get_id();
			$is_block  = $source->is_block();

			if ( is_array( $local_data ) ) {
				return $this->get_local_field_value( $local_data, $field_id, $source_id, $is_formatted );
			}

			if ( $is_formatted ) {
				return $is_block ?
					get_field( $field_id ) :
					get_field( $field_id, $source_id );
			}

			return $is_block ?
				get_field(
					$field_id,
					false,
					false
				) :
				get_field(
					$field_id,
					$source_id,
					false
				);
		}

		return null;
	}

	public function convert_string_to_date_time( Field_Meta $field_meta, string $value ): ?DateTime {
		$formats = array(
			'date_picker'      => 'Ymd',
			'date_time_picker' => 'Y-m-d H:i:s',
			'time_picker'      => 'H:i:s',
		);

		$type = $field_meta->get_type();

		if ( key_exists( $type, $formats ) ) {
			$date_time = DateTime::createFromFormat( $formats[ $type ], $value );

			return $date_time instanceof DateTime ?
				$date_time :
				null;
		}

		return null;
	}

	public function convert_date_to_string_for_db_comparison(
		DateTime $date_time,
		Field_Meta $field_meta
	): string {
		$formats = array(
			'date_picker'      => 'Ymd',
			'time_picker'      => 'H:i:s',
			'date_time_picker' => 'Y-m-d H:i:s',
		);

		$type = $field_meta->get_type();
		// any other field type (e.g. text/number) storing a date/timestamp in a custom format:
		// fall back to a generic, widely-comparable format instead of an empty value.
		$format = $formats[ $type ] ?? 'Y-m-d H:i:s';

		return $date_time->format( $format );
	}

	/**
	 * @return array<string, Field_Meta>
	 */
	public function get_sub_field_choices(): array {
		if ( function_exists( 'acf_get_fields' ) ) {
			$sub_field_choices           = array();
			$supported_field_types       = $this->get_supported_field_types();
			$field_types_with_sub_fields = array( 'repeater', 'group', 'flexible_content', 'clone' );
			$groups                      = $this->get_groups();

			foreach ( $groups as $group ) {
				$fields = acf_get_fields( $group );

				$group_key = string( $group, 'key' );

				foreach ( $fields as $group_field ) {
					$field_type = string( $group_field, 'type' );

					$is_supported = in_array(
						$field_type,
						$supported_field_types,
						true
					);

					// ignore 'clone' if it isn't supported (Lite version).
					if ( ! $is_supported ) {
						continue;
					}

					$sub_fields = $this->get_sub_fields( $group_field );

					$is_with_sub_fields = in_array(
						$field_type,
						$field_types_with_sub_fields,
						true
					);

					if ( ! $is_with_sub_fields || array() === $sub_fields ) {
						continue;
					}

					$group_field_id = string( $group_field, 'key' );
					$field_key      = $this->get_field_key( $group_key, $group_field_id );

					$sub_field_choices = $this->get_field_choices_recursively(
						$sub_field_choices,
						$sub_fields,
						$supported_field_types,
						array(),
						$group_key,
						$field_key
					);
				}
			}

			return $sub_field_choices;
		}

		return array();
	}

	/**
	 * @return null|array{title:string,url:string}
	 */
	public function get_group_link_by_group_id( string $group_id ): ?array {
		$post = get_page_by_path( $group_id, OBJECT, 'acf-field-group' );

		if ( is_object( $post ) ) {
			return array(
				'title' => $post->post_title,
				'url'   => (string) get_edit_post_link( $post ),
			);
		}

		return null;
	}

	/**
	 * @return mixed[]|null
	 */
	public function get_group_export_data( string $group_id ): ?array {
		$is_supported = function_exists( 'acf_get_field_group' ) &&
						function_exists( 'acf_get_fields' ) &&
						function_exists( 'acf_prepare_internal_post_type_for_export' );

		$group_info = $is_supported ?
			$this->get_group_with_fields( $group_id ) :
			null;

		if ( is_array( $group_info ) ) {
			return $this->prepare_group_for_export( $group_info, $group_id );
		}

		return null;
	}

	/**
	 * @param mixed[] $group_data
	 * @param mixed[] $meta_data
	 */
	public function import_group( array $group_data, array $meta_data ): ?string {
		$is_supported = function_exists( 'acf_get_field_group' ) &&
						function_exists( 'acf_import_field_group' );

		if ( ! $is_supported ) {
			return null;
		}

		$logger              = $this->get_logger();
		$group_key           = string( $group_data, 'key' );
		$existing_group_info = acf_get_field_group( $group_key );

		if ( is_array( $existing_group_info ) ) {
			$group_id         = int( $existing_group_info, 'ID' );
			$group_data['ID'] = $group_id;

			$logger->debug(
				'ACF group to import already exists: overriding instead of creation',
				array(
					'key' => $group_key,
					'ID'  => $group_id,
				)
			);
		}

		$group_data = acf_import_field_group( $group_data );

		if ( false === is_array( $group_data ) ) {
			$logger->warning(
				'failed to import ACF field group',
				array(
					'group_data' => $group_data,
				)
			);

			return null;
		}

		return stringOrNull( $group_data, 'key' );
	}

	// for back compatibility only.
	protected function is_without_name_in_keys(): bool {
		return true;
	}

	/**
	 * @return array<int,array<string,mixed>>
	 */
	protected function get_groups(): array {
		if ( function_exists( 'acf_get_field_groups' ) ) {
			$acf_groups = acf_get_field_groups();

			// Important! To avoid recursion, otherwise within 'getChoices()' will be available the same group as the current
			// and this class will call 'acf_get_fields()' that will call 'getChoices()'.
			$is_available = function ( $acf_group ) {
				$group_key  = string( $acf_group, 'key' );
				$is_private = boolExtended( $acf_group, 'private' );
				$is_own     = 0 === strpos( $group_key, Group::GROUP_NAME_PREFIX );
				// don't check for 'local' at all, as 'local' not presented only when json is disabled.
				// in other cases contains 'php' or 'json'.

				return ( ! $is_private &&
						! $is_own );
			};

			return array_filter( $acf_groups, $is_available );
		}

		return array();
	}

	/**
	 * @return array<string|int,mixed>|null
	 */
	protected function get_group_with_fields( string $group_id ): ?array {
		if ( false === function_exists( 'acf_get_field_group' ) || false === function_exists( 'acf_get_fields' ) ) {
			return null;
		}

		$logger      = $this->get_logger();
		$log_context = array(
			'group_id' => $group_id,
		);

		$group_info = acf_get_field_group( $group_id );

		if ( false === is_array( $group_info ) ) {
			$logger->warning( 'failed to get ACF field group info', $log_context );

			return null;
		}

		// fields are missing (empty) in the 'acf_get_field_group' call.
		$fields_info = acf_get_fields( $group_info );

		if ( false === is_array( $fields_info ) ) {
			$logger->warning( 'failed to get ACF fields info', $log_context );

			return null;
		}

		$group_info['fields'] = $fields_info;

		return $group_info;
	}

	/**
	 * @param array<string|int,mixed> $group_info
	 *
	 * @return mixed[]|null
	 */
	protected function prepare_group_for_export( array $group_info, string $group_id ): ?array {
		if ( false === function_exists( 'acf_prepare_internal_post_type_for_export' ) ) {
			return null;
		}

		$prepared_info = acf_prepare_internal_post_type_for_export( $group_info );

		if ( is_array( $prepared_info ) ) {
			return $prepared_info;
		}

		$logger      = $this->get_logger();
		$log_context = array(
			'group_id' => $group_id,
		);

		$logger->warning( 'failed to prepare ACF field group for export', $log_context );

		return null;
	}

	/**
	 * @param array<string|int,mixed> $fields
	 *
	 * @return array<string|int,mixed>
	 */
	protected function paste_clone_fields( array $fields ): array {
		// stub for the Pro version (for clone fields).
		return $fields;
	}

	/**
	 * @param array<string|int,mixed> $group_field
	 *
	 * @return array<string|int,mixed>
	 */
	protected function get_sub_fields( array $group_field ): array {
		$sub_fields = arr( $group_field, 'sub_fields' );
		$sub_fields = $this->paste_clone_fields( $sub_fields );

		$layouts = arr( $group_field, 'layouts' );
		// by default, layouts don't have any type.
		foreach ( $layouts as &$layout ) {
			if ( false === is_array( $layout ) ) {
				continue;
			}

			$layout['type'] = '_flexible_content_layout';
		}

		return array_merge( $sub_fields, $layouts );
	}

	/**
	 * @param array<string, Field_Meta> $field_choices
	 * @param array<string|int,mixed> $fields
	 * @param string[] $supported_field_types
	 * @param string[] $include_only_types
	 *
	 * @return array<string, Field_Meta>
	 */
	protected function get_field_choices_recursively(
		array $field_choices,
		array $fields,
		array $supported_field_types,
		array $include_only_types,
		string $group_id,
		string $parent_choice_value = ''
	): array {
		$vendor_name = $this->get_name();

		foreach ( $fields as $group_field ) {
			if ( false === is_array( $group_field ) ) {
				continue;
			}

			$type = string( $group_field, 'type' );

			$is_supported = in_array(
				$type,
				$supported_field_types,
				true
			);
			$is_included  = array() === $include_only_types || in_array(
				$type,
				$include_only_types,
				true
			);

			if ( ! $is_supported || ! $is_included ) {
				continue;
			}

			$field_id  = string( $group_field, 'key' );
			$field_key = '' === $parent_choice_value ?
				$this->get_field_key( $group_id, $field_id ) :
				Field_Settings::create_field_key( $parent_choice_value, $field_id );

			$field_meta = new Field_Meta( $vendor_name, $field_id );
			$this->fill_field_meta( $field_meta, $group_field );

			$field_choices[ $field_key ] = $field_meta;

			$sub_fields = $this->get_sub_fields( $group_field );

			if ( array() !== $sub_fields ) {
				$field_choices = $this->get_field_choices_recursively(
					$field_choices,
					$sub_fields,
					$supported_field_types,
					$include_only_types,
					$group_id,
					$field_key
				);
			}
		}

		return $field_choices;
	}

	/**
	 * @param mixed[] $data
	 */
	protected function fill_taxonomy_meta( Field_Meta $field_meta, array $data ): void {
		$appearance          = string( $data, 'field_type' );
		$multiple_appearance = array( 'checkbox', 'multi_select' );

		$is_multiple = in_array(
			$appearance,
			$multiple_appearance,
			true
		);

		$field_meta->set_is_multiple( $is_multiple );
	}

	/**
	 * @param array<string|int,mixed> $local_data
	 * @param int|string $source_id
	 *
	 * @return mixed
	 */
	protected function get_local_field_value(
		array $local_data,
		string $field_id,
		$source_id,
		bool $is_formatted
	) {
		$value = $local_data[ $field_id ] ?? null;

		$is_formatting_needed = $is_formatted && function_exists( 'acf_get_field' );

		if ( $is_formatting_needed ) {
			$acf_field = acf_get_field( $field_id );

			/**
			 * There is 'acf_format_value()' function, but we can't use it
			 * because it uses field value cache, and cache key is equal to post_id:field_id
			 * which is wrong in this case, as we've the inner view, with a piece of field (e.g. repeater row), not the whole field value
			 * so 'acf/format_value' filter is the only proper way to format the value in our case.
			 */
			$value = apply_filters(
				'acf/format_value',
				$value,
				$source_id,
				$acf_field,
				false
			);
		}

		return $value;
	}
}
