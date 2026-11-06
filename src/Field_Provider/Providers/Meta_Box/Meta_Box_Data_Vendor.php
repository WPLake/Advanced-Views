<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Meta_Box;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use DateTime;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Base;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Integration;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Date_Picker_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Html_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Link_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Plain_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Post_Object_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Pro_Stub_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Select_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\True_False_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Url_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\User_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Meta_Box\Fields\Mb_File_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Meta_Box\Fields\Mb_Gallery_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Meta_Box\Fields\Mb_Image_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Meta_Box\Fields\Mb_Map_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Meta_Box\Fields\Mb_Taxonomy_Field;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Save_Actions;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Layout_Shortcode;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layout_Factory;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Source;
use RWMB_Field;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\arr;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\int;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;

class Meta_Box_Data_Vendor extends Field_Provider_Base {
	const NAME = 'meta-box';

	public function get_name(): string {
		return static::NAME;
	}

	public function is_meta_vendor(): bool {
		return true;
	}

	public function is_available(): bool {
		return function_exists( 'rwmb_get_value' );
	}

	public function make_integration_instance( Instance_Container $container ): ?Field_Provider_Integration {
		return new Meta_Box_Integration(
			$container->resolve( Item_Settings::class ),
			$container->resolve( Layout_Settings_Storage::class ),
			$container->resolve( Field_Provider_Cluster::class ),
			$container->resolve( Layout_Save_Actions::class ),
			$container->resolve( Layout_Factory::class ),
			$this,
			$container->resolve( Layout_Shortcode::class ),
			$container->resolve( Settings_Storage::class ),
			$container->resolve( Layouts_Cpt::class ),
			$container->resolve( Html_Renderer::class )
		);
	}

	public function get_field_types(): array {
		$basic = array(
			'checkbox'      => new True_False_Field(),
			'checkbox_list' => new Select_Field(),
			'radio'         => new Select_Field(),
			'select'        => new Select_Field(),
			'text'          => new Plain_Field(),
			'textarea'      => new Html_Field(),
		);

		$advanced = array(
			'autocomplete'    => new Select_Field(),
			// we need to use 'the_value' function, so HTML field fits here.
			'background'      => new Html_Field(),
			'button_group'    => new Select_Field(),
			'color'           => new Plain_Field(),
			'date'            => new Date_Picker_Field(),
			'datetime'        => new Date_Picker_Field(),
			'map'             => new Mb_Map_Field(),
			'hidden'          => new Plain_Field(),
			// FontAwesome or SVG icon.
			'icon'            => new Html_Field(),
			'image_select'    => new Select_Field(),
			'oembed'          => new Html_Field(),
			'osm'             => new Mb_Map_Field(),
			'password'        => new Plain_Field(),
			'select_advanced' => new Select_Field(),
			// it's a range number.
			'slider'          => new Plain_Field(),
			'switch'          => new True_False_Field(),
			'time'            => new Plain_Field(),
			'wysiwyg'         => new Html_Field(),
		);

		$html5 = array(
			'email'  => new Plain_Field(),
			'number' => new Plain_Field(),
			'range'  => new Plain_Field(),
			'url'    => new Url_Field(),
		);

		$wordpress = array(
			'post'              => new Post_Object_Field( new Link_Field() ),
			'sidebar'           => new Html_Field(),
			'taxonomy'          => new Mb_Taxonomy_Field( new Link_Field() ),
			'taxonomy_advanced' => new Mb_Taxonomy_Field( new Link_Field() ),
			'user'              => new User_Field( new Link_Field() ),
		);

		$upload = array(
			'file'           => new Mb_File_Field( new Link_Field() ),
			'file_advanced'  => new Mb_File_Field( new Link_Field() ),
			'file_input'     => new Mb_File_Field( new Link_Field() ),
			'file_upload'    => new Mb_File_Field( new Link_Field() ),
			'image'          => new Mb_Gallery_Field( new Mb_Image_Field() ),
			'image_advanced' => new Mb_Gallery_Field( new Mb_Image_Field() ),
			'image_upload'   => new Mb_Gallery_Field( new Mb_Image_Field() ),
			'single_image'   => new Mb_Image_Field(),
			'video'          => new Html_Field(),
		);

		$layout = array(
			'group' => new Pro_Stub_Field(),
		);

		return array_merge( $basic, $advanced, $html5, $wordpress, $upload, $layout );
	}

	/**
	 * @return array<string, string>
	 */
	public function get_group_choices(): array {
		$source_label = __( '(Meta Box)', 'acf-views' );
		$groups       = array();
		$groups_info  = $this->get_groups();

		foreach ( $groups_info as $group_info ) {
			$group_id = $this->get_string_arg( 'id', $group_info );
			$title    = $this->get_string_arg( 'title', $group_info );

			$group_key = $this->get_group_key( $group_id );
			$parts     = array(
				$title,
				$source_label,
			);

			$groups[ $group_key ] = join( ' ', $parts );
		}

		return $groups;
	}

	/**
	 * @param string[] $include_only_types
	 *
	 * @return array<string, Field_Meta>
	 */
	public function get_field_choices( array $include_only_types = array() ): array {
		$field_choices         = array();
		$supported_field_types = $this->get_supported_field_types();
		$groups_info           = $this->get_groups();

		foreach ( $groups_info as $group_info ) {
			$fields   = $this->get_array_arg( 'fields', $group_info );
			$group_id = $this->get_string_arg( 'id', $group_info );

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

	/**
	 * @param mixed[] $data
	 */
	public function fill_field_meta( Field_Meta $field_meta, array $data = array() ): void {
		if ( array() === $data ) {
			$data = $this->get_field_info( $field_meta->get_field_id() );
		}

		$field_type      = $this->get_string_arg( 'type', $data );
		$supported_types = $this->get_supported_field_types();

		$is_supported = array() !== $data && in_array(
			$field_type,
			$supported_types,
			true
		);

		if ( ! $is_supported ) {
			return;
		}

		$name           = $this->get_string_arg( 'name', $data );
		$identifier     = $this->get_string_arg( 'id', $data );
		$field_class    = $this->get_field_class( $field_type );
		$choices        = $this->get_array_arg( 'options', $data );
		$is_multiple    = $this->get_bool_arg( 'multiple', $data );
		$save_format    = $this->get_string_arg( 'save_format', $data );
		$is_repeatable  = $this->get_bool_arg( 'clone', $data );
		$pro_stub_types = $this->get_pro_stub_field_types();
		$timestamp      = (bool) ( $data['timestamp'] ?? false );
		$label          = 'hidden' !== $field_type ?
			$name :
			__( 'Hidden', 'acf-views' );

		$is_pro_stub = in_array(
			$field_type,
			$pro_stub_types,
			true
		);

		$field_meta->set_is_native_type( true );
		$field_meta->set_name( $name );
		$field_meta->set_type( $field_type );
		$field_meta->set_label( $label );
		$field_meta->set_identifier( $identifier );
		$field_meta->set_field_class( $field_class );
		$field_meta->set_choices( $choices );
		$field_meta->set_is_multiple( $is_multiple );
		$field_meta->set_return_format( $save_format );
		$field_meta->set_display_format( $save_format );

		if ( $timestamp ) {
			$field_meta->set_return_format( 'U' );
		}

		if ( key_exists( 'std', $data ) ) {
			if ( is_array( $data['std'] ) ) {
				$field_meta->set_default_value( $data['std'] );
			} else {
				$default_value = $this->get_string_arg( 'std', $data );

				$field_meta->set_default_value( $default_value );
			}
		}

		$set_multiple = fn() => $field_meta->set_is_multiple( true );

		$type_fillers = array(
			'file'          => $set_multiple,
			'file_advanced' => $set_multiple,
			'file_upload'   => $set_multiple,
			'date'          => fn() => $this->fill_date_formats( $field_meta, 'Y-m-d' ),
			'datetime'      => fn() => $this->fill_date_formats( $field_meta, 'Y-m-d H:i' ),
		);

		if ( key_exists( $field_type, $type_fillers ) ) {
			$type_fillers[ $field_type ]();
		}

		if ( 'group' === $field_type ) {
			$field_meta->set_is_group( true );
		}

		$field_meta->set_is_field_exist( true );

		$is_repeatable_not_supported = $is_repeatable && $this->is_repeatable_not_supported();

		$field_meta->set_is_pro_only( $is_pro_stub || $is_repeatable_not_supported );

		// some fields don't require the repeater trick, as they use the HTML field with the formatted value.
		// so markup will already include all the content.
		$field_types_without_self_repeatable = array(
			'video',
			'sidebar',
			'background',
			'oembed',
			'wysiwyg',
		);

		$is_self_repeatable = $is_repeatable && ! in_array(
			$field_type,
			$field_types_without_self_repeatable,
			true
		);

		if ( $is_self_repeatable ) {
			if ( 'group' === $field_type ) {
				$field_meta->set_is_group( false );
				$field_meta->set_is_repeater( true );
			} else {
				$field_meta->set_self_repeatable_meta( clone $field_meta );
				$field_meta->set_type( 'group' );
			}
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
		$field_id = $field_meta->get_field_id();

		$source_id = $source->get_id();
		$args      = $this->get_field_request_args(
			$field_settings,
			$field_meta,
			$source,
			$source_id
		);
		$is_block  = $source->is_block();

		if ( is_array( $local_data ) ) {
			return $this->get_local_field_value( $local_data, $field_meta, $is_formatted, $args, $source_id );
		}

		if ( $is_block && $is_formatted ) {
			return function_exists( 'mb_the_block_field' ) ?
				mb_the_block_field(
					$field_id,
					$args,
					false
				) :
				null;
		}

		if ( $is_block ) {
			return function_exists( 'mb_get_block_field' ) ?
				mb_get_block_field( $field_id, $args ) :
				null;
		}

		if ( $is_formatted ) {
			return $this->get_formatted_field_value(
				$field_settings,
				$field_meta,
				$source,
				$item_settings,
				$args,
				$source_id
			);
		}

		return $this->get_raw_field_value( $field_meta, $args, $source_id );
	}

	public function convert_string_to_date_time( Field_Meta $field_meta, string $value ): ?DateTime {
		$date_types = array( 'date', 'datetime' );
		$type       = $field_meta->get_type();

		$is_date = in_array(
			$type,
			$date_types,
			true
		);

		if ( $is_date ) {
			$return_format = $field_meta->get_return_format();
			$date_time     = DateTime::createFromFormat( $return_format, $value );

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
		return $date_time->format( $field_meta->get_return_format() );
	}

	/**
	 * @return null|array{title:string,url:string}
	 */
	public function get_group_link_by_group_id( string $group_id ): ?array {
		$post = get_page_by_path( $group_id, OBJECT, 'meta-box' );

		if ( is_object( $post ) ) {
			return array(
				'title' => $post->post_title,
				'url'   => (string) get_edit_post_link( $post->ID ),
			);
		}

		return null;
	}

	/**
	 * @return array<string, Field_Meta>
	 */
	public function get_sub_field_choices(): array {
		$sub_field_choices     = array();
		$supported_field_types = $this->get_supported_field_types();
		$groups_info           = $this->get_groups();

		foreach ( $groups_info as $group_info ) {
			$fields = $this->get_array_arg( 'fields', $group_info );

			$group_id = $this->get_string_arg( 'id', $group_info );

			foreach ( $fields as $field ) {
				$is_group = is_array( $field ) && 'group' === $this->get_string_arg( 'type', $field );

				if ( ! $is_group ) {
					continue;
				}

				$sub_fields = $this->get_array_arg( 'fields', $field );
				$field_id   = $this->get_string_arg( 'id', $field );

				$field_key = $this->get_field_key( $group_id, $field_id );

				$sub_field_choices = $this->get_field_choices_recursively(
					$sub_field_choices,
					$sub_fields,
					$supported_field_types,
					array(),
					$group_id,
					$field_key
				);
			}
		}

		return $sub_field_choices;
	}

	/**
	 * @return mixed[]|null
	 */
	public function get_group_export_data( string $group_id ): ?array {
		$post   = get_page_by_path( $group_id, OBJECT, 'meta-box' );
		$logger = $this->get_logger();

		if ( is_object( $post ) ) {
			$post_data = array(
				'post_type'    => $post->post_type,
				'post_name'    => $post->post_name,
				'post_title'   => $post->post_title,
				'post_date'    => $post->post_date,
				'post_status'  => $post->post_status,
				'post_content' => $post->post_content,
			);

			$meta_keys = self::get_export_meta_keys();

			foreach ( $meta_keys as $meta_key ) {
				$post_data[ $meta_key ] = get_post_meta(
					$post->ID,
					$meta_key,
					true
				);
			}

			return $post_data;
		}

		$logger->warning(
			'failed to get MetaBox group by path',
			array(
				'path' => $group_id,
			)
		);

		return null;
	}

	/**
	 * @param mixed[] $group_data
	 * @param mixed[] $meta_data
	 */
	public function import_group( array $group_data, array $meta_data ): ?string {
		if ( false === function_exists( 'rwmb_get_registry' ) ) {
			return null;
		}

		$logger   = $this->get_logger();
		$registry = rwmb_get_registry( 'meta_box' );

		$is_unusable = false === is_object( $registry ) || false === method_exists( $registry, 'make' );

		if ( $is_unusable ) {
			return null;
		}

		$post_name = $this->get_string_arg_if_present( 'post_name', $group_data );

		if ( null === $post_name ) {
			$logger->warning(
				'failed to get post_name from MetaBox group data',
				array(
					'group_data' => $group_data,
				)
			);

			return null;
		}

		$existing_post = get_page_by_path( $post_name, OBJECT, 'meta-box' );

		// set existing post id, so it'll be updated instead of inserted.
		if ( null !== $existing_post ) {
			$group_data['ID'] = $existing_post->ID;
		}

		// @phpstan-ignore-next-line
		$post_id = wp_insert_post( $group_data, true );

		if ( is_wp_error( $post_id ) ) {
			$logger->warning(
				'failed to insert MetaBox group',
				array(
					'error_message' => $post_id->get_error_message(),
					'error_code'    => $post_id->get_error_code(),
					'group_data'    => $group_data,
				)
			);

			return null;
		}

		$post = get_post( $post_id );

		if ( null === $post ) {
			$logger->warning(
				'failed to get MetaBox group by id',
				array(
					'post_id' => $post_id,
				)
			);

			return null;
		}

		$meta_keys = self::get_export_meta_keys();

		foreach ( $meta_keys as $meta_key ) {
			if ( ! key_exists( $meta_key, $group_data ) ) {
				continue;
			}

			update_post_meta(
				$post_id,
				$meta_key,
				$group_data[ $meta_key ]
			);
		}

		// manually signup in MetaBox registry,
		// otherwise won't be available for query within this request.
		$registry->make( $group_data );

		return $post->post_name;
	}

	/**
	 * @return array<int,array<string,mixed>>
	 */
	protected function get_groups(): array {
		if ( function_exists( 'rwmb_get_registry' ) ) {
			$meta_box_registry = rwmb_get_registry( 'meta_box' );
			$groups_info       = $meta_box_registry->all();

			$core_group_ids = array(
				'relationship-id',
				'settings-page',
				'field-group-id',
				'mbb-documentation',
				'mbup-email-confirmation',
				'mbv-template-editor',
				'mbv-settings',
				'mbv-shortcode',
			);

			$groups = array();

			foreach ( $groups_info as $group_id => $group_info ) {
				$is_core = in_array(
					$group_id,
					$core_group_ids,
					true
				);

				// only added by users.
				if ( $is_core ) {
					continue;
				}

				$groups[] = $group_info->meta_box ?? array();
			}

			return $groups;
		}

		return array();
	}

	/**
	 * @param int|string|false $source_id
	 *
	 * @return array<string,string>
	 */
	protected function get_field_request_args(
		Field_Settings $field_settings,
		Field_Meta $field_meta,
		Source $source,
		&$source_id
	): array {
		$args = array();

		$is_block        = $source->is_block();
		$is_prefixed_key = is_string( $source_id ) && false === is_numeric( $source_id ) && false === $is_block;

		if ( $is_prefixed_key ) {
			$object_types = array(
				'term_'    => 'term',
				'user_'    => 'user',
				'comment_' => 'comment',
			);

			// option pages.
			$args['object_type'] = 'setting';

			foreach ( $object_types as $prefix => $object_type ) {
				$is_matched = 0 === strpos( $source_id, $prefix );

				if ( $is_matched ) {
					$source_id           = str_replace( $prefix, '', $source_id );
					$args['object_type'] = $object_type;

					break;
				}
			}
		}

		return $args;
	}

	/**
	 * @param array<string,Field_Meta> $field_choices
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

		foreach ( $fields as $field ) {
			if ( false === is_array( $field ) ) {
				continue;
			}

			$field_type = $this->get_string_arg( 'type', $field );

			$is_supported = in_array(
				$field_type,
				$supported_field_types,
				true
			);
			$is_included  = array() === $include_only_types || in_array(
				$field_type,
				$include_only_types,
				true
			);

			if ( ! $is_supported || ! $is_included ) {
				continue;
			}

			$field_id = $this->get_string_arg( 'id', $field );

			$field_key = '' === $parent_choice_value ?
				$this->get_field_key( $group_id, $field_id ) :
				Field_Settings::create_field_key( $parent_choice_value, $field_id );

			$field_meta = new Field_Meta( $vendor_name, $field_id );
			$this->fill_field_meta( $field_meta, $field );

			$field_choices[ $field_key ] = $field_meta;

			$sub_fields = $this->get_array_arg( 'fields', $field );

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
	 * @param array<string|int,mixed> $fields
	 *
	 * @return array<string|int,mixed>
	 */
	protected function get_field_recursively( string $target_field_id, array $fields ): array {
		foreach ( $fields as $field ) {
			if ( false === is_array( $field ) ) {
				continue;
			}

			$field_id = $this->get_string_arg( 'id', $field );

			if ( $field_id === $target_field_id ) {
				return $field;
			}

			$sub_fields = $this->get_array_arg( 'fields', $field );

			if ( array() !== $sub_fields ) {
				$sub_field_info = $this->get_field_recursively( $target_field_id, $sub_fields );

				if ( array() !== $sub_field_info ) {
					return $sub_field_info;
				}
			}
		}

		return array();
	}

	/**
	 * There is 'rwmb_get_field_settings' function, but it's a context specific function
	 * (required postId of the post, where current field is attached...).
	 *
	 * @return array<string|int,mixed>
	 */
	protected function get_field_info( string $target_field_id ): array {
		$groups_info = $this->get_groups();

		foreach ( $groups_info as $group_info ) {
			$fields = $this->get_array_arg( 'fields', $group_info );

			$data = $this->get_field_recursively( $target_field_id, $fields );

			if ( array() !== $data ) {
				return $data;
			}
		}

		return array();
	}

	/**
	 * @param mixed $raw_value
	 *
	 * @return mixed
	 */
	protected function format_group_item_value( $raw_value, Field_Settings $field_settings, Field_Meta $field_meta, Source $source ) {
		$type      = $field_meta->get_type();
		$map_types = array( 'osm', 'map' );

		$is_map = in_array(
			$type,
			$map_types,
			true
		);

		/**
		 * Hand fix for maps.
		 * https://docs.metabox.io/fields/osm/#outputting-a-map-in-a-group
		 * https://support.metabox.io/topic/bug-clonable-map-field-not-displaying-data/
		 */
		if ( $is_map ) {
			$render_map_callback = 'osm' === $type ?
				array( 'RWMB_OSM_Field', 'render_map' ) :
				array( 'RWMB_Map_Field', 'render_map' );

			$field_request_args = $this->get_field_request_args(
				$field_settings,
				$field_meta,
				$source,
				$source_id
			);

			return is_callable( $render_map_callback ) ?
				call_user_func(
					$render_map_callback,
					$raw_value,
					$field_request_args
				) :
				$raw_value;
		}

		return $raw_value;
	}

	/**
	 * @param mixed $raw_value
	 *
	 * @return array<int|string,mixed>
	 */
	protected function format_group_value( $raw_value, Field_Settings $field_settings, Field_Meta $field_meta, Item_Settings $item_settings, Source $source ): array {
		$raw_value = is_array( $raw_value ) ?
			$raw_value :
			array();

		$formatted_value = array();

		// repeater.
		if ( $field_meta->is_repeater() ) {
			foreach ( $raw_value as $raw_row ) {
				$formatted_row = array();
				$raw_row       = arr( $raw_row );

				foreach ( $item_settings->repeater_fields as $repeater_field ) {
					$repeater_meta       = $repeater_field->get_field_meta();
					$repeater_field_id   = $repeater_meta->get_field_id();
					$repeater_field_name = $repeater_meta->get_name();

					$field_value = $raw_row[ $repeater_field_id ] ?? '';

					$formatted_row[ $repeater_field_name ] = $this->format_group_item_value(
						$field_value,
						$repeater_field,
						$repeater_meta,
						$source
					);
				}

				$formatted_value[] = $formatted_row;
			}

			return $formatted_value;
		}

		$self_repeatable = $field_meta->get_self_repeatable_meta();

		// self-repeatable.
		if ( null !== $self_repeatable ) {
			foreach ( $raw_value as $item ) {
				$formatted_value[] = $this->format_group_item_value(
					$item,
					$field_settings,
					$self_repeatable,
					$source
				);
			}

			return $formatted_value;
		}

		// group.
		foreach ( $item_settings->repeater_fields as $repeater_field ) {
			$repeater_meta       = $repeater_field->get_field_meta();
			$repeater_field_id   = $repeater_meta->get_field_id();
			$repeater_field_name = $repeater_meta->get_name();

			$field_value = $raw_value[ $repeater_field_id ] ?? '';

			$formatted_value[ $repeater_field_name ] = $this->format_group_item_value(
				$field_value,
				$repeater_field,
				$repeater_meta,
				$source
			);
		}

		return $formatted_value;
	}

	/**
	 * Repeatable is supported only if the group field isn't a Pro stub.
	 */
	protected function is_repeatable_not_supported(): bool {
		return ( $this->get_registered_field_types()['group'] ?? null ) instanceof Pro_Stub_Field;
	}

	protected function fill_date_formats( Field_Meta $field_meta, string $format ): void {
		$return_format = $field_meta->get_return_format();

		// set the default value (from MetaBox)
		// if user changes the format using the jsOptions setting, we can't get it, so he must define 'save_format' in that case.
		if ( '' === $return_format ) {
			$field_meta->set_return_format( $format );
			$field_meta->set_display_format( $format );
		} elseif ( 'U' === $return_format ) {
			$field_meta->set_display_format( $format );
		}
	}

	/**
	 * @param array<string|int,mixed> $local_data
	 * @param array<string,string> $args
	 * @param int|string|false $source_id
	 *
	 * @return mixed
	 */
	protected function get_local_field_value(
		array $local_data,
		Field_Meta $field_meta,
		bool $is_formatted,
		array $args,
		$source_id
	) {
		$field_id = $field_meta->get_field_id();
		$value    = $local_data[ $field_id ] ?? null;

		$is_formatting_needed = $is_formatted &&
								function_exists( 'rwmb_get_field_settings' ) &&
								class_exists( 'RWMB_Field' ) &&
								is_callable( array( 'RWMB_Field', 'call' ) );

		if ( $is_formatting_needed ) {
			$field           = $this->get_field_info( $field_id );
			$self_repeatable = $field_meta->get_self_repeatable_meta();

			$action = null !== $self_repeatable ?
				'format_clone_value' :
				'format_single_value';

			/**
			 * @var mixed $value
			 */
			$value = RWMB_Field::call(
				$action,
				$field,
				$value,
				$args,
				$source_id
			);
		}

		return $value;
	}

	/**
	 * @param array<string,string> $args
	 * @param int|string|false $source_id
	 *
	 * @return mixed
	 */
	protected function get_formatted_field_value(
		Field_Settings $field_settings,
		Field_Meta $field_meta,
		Source $source,
		?Item_Settings $item_settings,
		array $args,
		$source_id
	) {
		$field_id = $field_meta->get_field_id();

		if ( 'group' === $field_meta->get_type() ) {
			$post_id = int( $source_id );

			/**
			 * Rwmb_the_value() doesn't fit for groups, repeaters and self-repeatable.
			 * Instead of looping through each field and formatting it, it returns HTML for the whole array as single string.
			 * Note: get_post_meta() works with clonable map, while rwmb_get_value not - https://support.metabox.io/topic/bug-clonable-map-field-not-displaying-data/
			 */
			$value = get_post_meta(
				$post_id,
				$field_id,
				true
			);

			// It's impossible to format the group value without the item data.
			return null !== $item_settings ?
				$this->format_group_value( $value, $field_settings, $field_meta, $item_settings, $source ) :
				$value;
		}

		return function_exists( 'rwmb_the_value' ) ?
			rwmb_the_value(
				$field_id,
				$args,
				$source_id,
				false
			) :
			null;
	}

	/**
	 * @param array<string,string> $args
	 * @param int|string|false $source_id
	 *
	 * @return mixed
	 */
	protected function get_raw_field_value( Field_Meta $field_meta, array $args, $source_id ) {
		$field_id        = $field_meta->get_field_id();
		$self_repeatable = $field_meta->get_self_repeatable_meta();
		$map_types       = array( 'osm', 'map' );

		$self_repeatable_type = null !== $self_repeatable ?
			$self_repeatable->get_type() :
			'';

		$is_self_repeatable_map = in_array(
			$self_repeatable_type,
			$map_types,
			true
		);

		if ( $is_self_repeatable_map ) {
			// self repeatable maps can't be read via rwmb_get_value - https://support.metabox.io/topic/bug-clonable-map-field-not-displaying-data/.
			$post_id = int( $source_id );

			return get_post_meta(
				$post_id,
				$field_id,
				true
			);
		}

		return function_exists( 'rwmb_get_value' ) ?
			rwmb_get_value(
				$field_id,
				$args,
				$source_id
			) :
			null;
	}

	/**
	 * @return string[]
	 */
	protected static function get_export_meta_keys(): array {
		return array( 'settings', 'fields', 'data', 'meta_box' );
	}
}
