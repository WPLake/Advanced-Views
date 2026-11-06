<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Pods;

defined( 'ABSPATH' ) || exit;

use DateTime;
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
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Plain_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Post_Object_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Select_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Taxonomy_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\True_False_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Url_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\User_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Pods\Fields\Pods_Pick_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Pods\Fields\Pods_Upload_Field;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Save_Actions;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Layout_Shortcode;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layout_Factory;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Source;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use Pods_Migrate_Packages;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

class Pods_Data_Vendor extends Field_Provider_Base {
	const NAME = 'pods';

	/**
	 * @var array<string|int,mixed>|null
	 */
	private ?array $pick_related_objects;

	public function __construct( Logger $logger ) {
		parent::__construct( $logger );

		$this->pick_related_objects = null;
	}

	// groupId without the type doesn't allow to identify the group itself.
	public static function get_pods_group_id( string $pod_name, string $group_id ): string {
		$parts = array(
			$pod_name,
			$group_id,
		);

		return join( '.', $parts );
	}

	// fieldId without the type doesn't allow to identify the field itself.
	public static function get_pods_field_id( string $type, string $field_id ): string {
		$parts = array(
			$type,
			$field_id,
		);

		return join( '.', $parts );
	}

	public function get_name(): string {
		return static::NAME;
	}

	public function is_meta_vendor(): bool {
		return true;
	}

	public function is_available(): bool {
		return function_exists( 'pods' );
	}

	public function make_integration_instance( Instance_Container $container ): ?Field_Provider_Integration {
		return new Pods_Integration(
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
		$text = array(
			'text'     => new Plain_Field(),
			'website'  => new Url_Field(),
			'phone'    => new Plain_Field(),
			'email'    => new Plain_Field(),
			'password' => new Plain_Field(),
		);

		$paragraph = array(
			'paragraph' => new Html_Field( true, true ),
			'wysiwyg'   => new Html_Field( true, true ),
			'code'      => new Html_Field(),
		);

		$date = array(
			'datetime' => new Date_Picker_Field(),
			'date'     => new Date_Picker_Field(),
			'time'     => new Date_Picker_Field(),
		);

		$number = array(
			'number'   => new Plain_Field(),
			'currency' => new Html_Field(),
		);

		$relationship = array(
			'file'   => new Pods_Upload_Field(
				new Image_Field(),
				new File_Field( new Link_Field() ),
				new Gallery_Field( new Image_Field() )
			),
			'oembed' => new Html_Field(),
			'pick'   => new Pods_Pick_Field(
				new Select_Field(),
				new User_Field( new Link_Field() ),
				new Post_Object_Field( new Link_Field() ),
				new Taxonomy_Field( new Link_Field() ),
				new File_Field( new Link_Field() )
			),
		);

		$other = array(
			'boolean' => new True_False_Field(),
			'color'   => new Plain_Field(),
		);

		return array_merge( $text, $paragraph, $date, $number, $relationship, $other );
	}

	/**
	 * @return array<string, string>
	 */
	public function get_group_choices(): array {
		$source_label  = __( '(Pods)', 'acf-views' );
		$group_choices = array();
		$groups_by_pod = $this->get_groups();

		foreach ( $groups_by_pod as $type => $groups ) {
			foreach ( $groups as $group ) {
				if ( false === method_exists( $group, 'get_args' ) ) {
					continue;
				}

				$group_info = $group->get_args();
				$group_info = is_array( $group_info ) ?
					$group_info :
					array();

				$group_id    = $this->get_string_arg( 'name', $group_info );
				$group_title = $this->get_string_arg( 'label', $group_info );

				$pods_group_id = self::get_pods_group_id( $type, $group_id );
				$group_key     = $this->get_group_key( $pods_group_id );

				$group_choices[ $group_key ] = sprintf(
					'[%s] %s %s',
					$type,
					$group_title,
					$source_label
				);
			}
		}

		return $group_choices;
	}

	/**
	 * @param string[] $include_only_types
	 *
	 * @return array<string, Field_Meta>
	 */
	public function get_field_choices( array $include_only_types = array() ): array {
		$field_choices = array();
		$groups_by_pod = $this->get_groups();

		foreach ( $groups_by_pod as $type => $groups ) {
			foreach ( $groups as $group ) {
				$group_choices = $this->get_group_field_choices( $group, $type, $include_only_types );
				$field_choices = array_merge( $field_choices, $group_choices );
			}
		}

		return $field_choices;
	}

	/**
	 * @param mixed[] $data
	 */
	public function fill_field_meta( Field_Meta $field_meta, array $data = array() ): void {
		if ( array() === $data ) {
			$data = $this->find_field_info( $field_meta->get_field_id() );
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
		$label          = $this->get_string_arg( 'label', $data );
		$field_class    = $this->get_field_class( $field_type );
		$display_format = $this->get_display_format( $data );
		$choices        = $this->get_choices( $data );
		$is_repeatable  = $this->get_bool_arg( 'repeatable', $data );
		$pro_stub_types = $this->get_pro_stub_field_types();
		$registered     = $this->get_registered_field_types();

		// for file and pick fields.
		$return_format = $this->get_string_arg( 'file_type', $data );
		$return_format = '' === $return_format ?
			$this->get_string_arg( 'pick_object', $data ) :
			$return_format;

		// for file and pick fields.
		$format_type = $this->get_string_arg( 'file_format_type', $data );
		$format_type = '' === $format_type ?
			$this->get_string_arg( 'pick_format_type', $data ) :
			$format_type;

		$is_pro_stub                 = in_array(
			$field_type,
			$pro_stub_types,
			true
		);
		$is_repeatable_not_supported = $is_repeatable && ! key_exists( 'group', $registered );

		$field_meta->set_is_native_type( true );
		$field_meta->set_name( $name );
		$field_meta->set_type( $field_type );
		$field_meta->set_label( $label );
		$field_meta->set_identifier( $name );
		$field_meta->set_field_class( $field_class );
		$field_meta->set_display_format( $display_format );
		$field_meta->set_return_format( $return_format );
		$field_meta->set_is_multiple( 'multi' === $format_type );
		$field_meta->set_choices( $choices );
		$field_meta->set_is_field_exist( true );
		$field_meta->set_is_pro_only( $is_pro_stub || $is_repeatable_not_supported );

		if ( $is_repeatable ) {
			$field_meta->set_self_repeatable_meta( clone $field_meta );
			$field_meta->set_type( 'group' );
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
		if ( function_exists( 'pods' ) ) {
			$field_id          = $field_meta->get_field_id();
			$field_source_type = explode( '.', $field_id )[0];
			$field_name        = explode( '.', $field_id )[1] ?? '';
			$source_id         = $source->get_id();

			$value = $this->get_pods_value(
				$field_name,
				$field_source_type,
				$source_id,
				$is_formatted,
				$local_data
			);

			switch ( $field_meta->get_type() ) {
				case 'oembed':
					$is_filled = is_string( $value ) && strlen( $value ) > 0;
					$value     = $is_filled ?
						wp_oembed_get( $value ) :
						'';
					$value     = is_string( $value ) ?
						$value :
						'';
					break;
			}

			return $value;
		}

		return null;
	}

	public function convert_string_to_date_time( Field_Meta $field_meta, string $value ): ?DateTime {
		$formats = self::get_date_formats();
		$type    = $field_meta->get_type();

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
		$formats = self::get_date_formats();
		$type    = $field_meta->get_type();

		if ( key_exists( $type, $formats ) ) {
			return $date_time->format( $formats[ $type ] );
		}

		return '';
	}

	/**
	 * @return null|array{title:string,url:string}
	 */
	public function get_group_link_by_group_id( string $group_id ): ?array {
		$pod   = $this->get_pod_object_by_group_id( $group_id );
		$group = $this->get_group_object_by_group_id( $group_id );

		$is_valid = is_object( $pod ) &&
					is_object( $group ) &&
					method_exists( $pod, 'get_args' ) &&
					method_exists( $group, 'get_args' );

		if ( $is_valid ) {
			$pod_info   = $pod->get_args();
			$pod_info   = is_array( $pod_info ) ?
				$pod_info :
				array();
			$group_info = $group->get_args();
			$group_info = is_array( $group_info ) ?
				$group_info :
				array();

			$pod_id      = $this->get_int_arg( 'id', $pod_info );
			$group_label = $this->get_string_arg( 'label', $group_info );
			$edit_path   = sprintf( 'admin.php?page=pods&action=edit&id=%d', $pod_id );

			return array(
				'title' => $group_label,
				'url'   => admin_url( $edit_path ),
			);
		}

		return null;
	}

	/**
	 * @return array<string, Field_Meta>
	 */
	public function get_sub_field_choices(): array {
		return array();
	}

	/**
	 * Exports of the whole pod
	 * (as the group is dependent part, and won't be exported if the pod is missing)
	 *
	 * @return mixed[]|null
	 */
	public function get_group_export_data( string $group_id ): ?array {
		$pod = $this->get_pod_object_by_group_id( $group_id );

		$is_supported = is_object( $pod ) &&
						is_callable( array( $pod, 'get_id' ) ) &&
						defined( 'PODS_DIR' ) &&
						class_exists( 'Pods_Migrate_Packages' ) &&
						is_callable( array( 'Pods_Migrate_Packages', 'export' ) );

		if ( ! $is_supported ) {
			return null;
		}

		$logger      = $this->get_logger();
		$log_context = array(
			'group_id' => $group_id,
		);

		$migrate_file = sprintf( '%scomponents/Migrate-Packages/Migrate-Packages.php', constant( 'PODS_DIR' ) );

		include_once $migrate_file;

		$pod_id      = $pod->get_id();
		$export_args = array(
			'pods' => array( $pod_id ),
		);

		$export_data = Pods_Migrate_Packages::export( $export_args );

		if ( false === is_string( $export_data ) ) {
			$logger->warning( 'pods export failed', $log_context );

			return null;
		}

		$export_data = json_decode( $export_data, true );

		if ( false === is_array( $export_data ) ) {
			$logger->warning( 'pods export data is not an array', $log_context );

			return null;
		}

		$pods_export_data = $this->get_array_arg_if_present( 'pods', $export_data );

		if ( null === $pods_export_data ) {
			$logger->warning( 'pods missing in the export data', $log_context );

			return null;
		}

		$is_filled = count( $pods_export_data ) > 0 && is_array( $pods_export_data[0] );

		return $is_filled ?
			$pods_export_data[0] :
			null;
	}

	/**
	 * @param mixed[] $groups_data
	 *
	 * @return array<string, mixed>
	 */
	public function get_export_meta_data( array $groups_data ): array {
		$pods_version = defined( 'PODS_VERSION' ) ?
			constant( 'PODS_VERSION' ) :
			'';

		return array(
			'version' => $pods_version,
			'build'   => time(),
		);
	}

	/**
	 * @param mixed[] $group_data
	 * @param mixed[] $meta_data
	 */
	public function import_group( array $group_data, array $meta_data ): ?string {
		$is_supported = class_exists( 'Pods_Migrate_Packages' ) &&
						defined( 'PODS_DIR' ) &&
						is_callable( array( 'Pods_Migrate_Packages', 'import_pod' ) );

		if ( ! $is_supported ) {
			return null;
		}

		$migrate_file = sprintf( '%scomponents/Migrate-Packages/Migrate-Packages.php', constant( 'PODS_DIR' ) );

		include_once $migrate_file;

		// for compatibility with our old export format, which didn't have meta.
		if ( array() === $meta_data ) {
			$meta_data = $this->get_export_meta_data( array() );
		}

		$import_args = array(
			'@meta' => $meta_data,
			'pods'  => array(
				$group_data,
			),
		);

		$imported_pod = Pods_Migrate_Packages::import( $import_args );

		if ( false === is_array( $imported_pod ) ) {
			$logger      = $this->get_logger();
			$log_context = array(
				'group_data' => $group_data,
			);

			$logger->warning( 'Pods import failed', $log_context );

			return null;
		}

		return $this->get_string_arg_if_present( 'name', $group_data );
	}

	/**
	 * @return array<string,array<int,object>>
	 */
	protected function get_groups(): array {
		$pods_api = self::get_pods_api();

		if ( is_object( $pods_api ) && method_exists( $pods_api, 'load_pods' ) ) {
			$pods = $pods_api->load_pods();

			$groups = array();

			foreach ( $pods as $type => $pod ) {
				$is_pod = is_object( $pod ) && method_exists( $pod, 'get_groups' );

				if ( ! $is_pod ) {
					continue;
				}

				$type            = (string) $type;
				$pod_groups      = $pod->get_groups();
				$groups[ $type ] = array();

				foreach ( $pod_groups as $pod_group ) {
					$groups[ $type ][] = $pod_group;
				}
			}

			return $groups;
		}

		return array();
	}

	/**
	 * @param array<string,mixed> $data
	 */
	protected function get_display_format( array $data ): string {
		$field_type = $this->get_string_arg( 'type', $data );

		$resolvers = array(
			'datetime' => fn() => $this->get_datetime_display_format( $data ),
			'date'     => fn() => $this->get_date_display_format( $data ),
			'time'     => fn() => $this->get_time_display_format( $data ),
		);

		if ( key_exists( $field_type, $resolvers ) ) {
			return $resolvers[ $field_type ]();
		}

		return '';
	}

	/**
	 * @param array<string,mixed> $data
	 */
	protected function get_datetime_display_format( array $data ): string {
		$date_time_type = $this->get_string_arg( 'datetime_type', $data );

		if ( 'wp' === $date_time_type ) {
			$date_format = get_option( 'date_format' );
			$time_format = get_option( 'time_format' );

			$date_format = string( $date_format );
			$time_format = string( $time_format );

			return sprintf( '%s %s', $date_format, $time_format );
		}

		$display_format = $this->get_string_arg( 'datetime_format', $data );
		$is_date_filled = strlen( $display_format ) > 0;
		$display_format = $is_date_filled ?
			self::get_php_date_format( $display_format ) :
			'';

		$date_time_type = $this->get_string_arg( 'datetime_time_type', $data );

		$display_time_format = '24' === $date_time_type ?
			$this->get_string_arg( 'datetime_time_format_24', $data ) :
			$this->get_string_arg( 'datetime_time_format', $data );
		$is_time_filled      = strlen( $display_time_format ) > 0;
		$display_time_format = $is_time_filled ?
			self::get_php_time_format( $display_time_format ) :
			'';

		$is_time_filled = strlen( $display_time_format ) > 0;

		if ( $is_time_filled ) {
			$parts          = array(
				$display_format,
				$display_time_format,
			);
			$display_format = join( ' ', $parts );
		}

		return $display_format;
	}

	/**
	 * @param array<string,mixed> $data
	 */
	protected function get_date_display_format( array $data ): string {
		$date_type = $this->get_string_arg( 'date_type', $data );

		if ( 'wp' === $date_type ) {
			$format = get_option( 'date_format' );

			return string( $format );
		}

		$display_format = $this->get_string_arg( 'date_format', $data );

		return self::get_php_date_format( $display_format );
	}

	/**
	 * @param array<string,mixed> $data
	 */
	protected function get_time_display_format( array $data ): string {
		$time_type = $this->get_string_arg( 'time_type', $data );

		if ( 'wp' === $time_type ) {
			$format = get_option( 'time_format' );

			return string( $format );
		}

		$display_time_format = '24' === $time_type ?
			$this->get_string_arg( 'time_format_24', $data ) :
			$this->get_string_arg( 'time_format', $data );

		return self::get_php_time_format( $display_time_format );
	}

	/**
	 * @return array<string|int,mixed>
	 */
	protected function get_pick_related_objects(): array {
		// cache.
		if ( is_array( $this->pick_related_objects ) ) {
			return $this->pick_related_objects;
		}

		$this->pick_related_objects = self::load_pick_related_objects();

		return $this->pick_related_objects;
	}

	/**
	 * @return mixed[]
	 */
	protected function get_dynamic_choices( string $pick_object ): array {
		$pick_related_objects = $this->get_pick_related_objects();

		$is_related     = key_exists( $pick_object, $pick_related_objects ) &&
							is_array( $pick_related_objects[ $pick_object ] );
		$related_object = $is_related ?
			$pick_related_objects[ $pick_object ] :
			array();

		$has_callback = key_exists( 'data_callback', $related_object ) &&
						is_callable( $related_object['data_callback'] );
		$options      = $has_callback ?
			call_user_func( $related_object['data_callback'] ) :
			array();

		return is_array( $options ) ?
			$options :
			array();
	}

	/**
	 * @param array<string,mixed> $data
	 *
	 * @return mixed[]
	 */
	protected function get_choices( array $data ): array {
		$pick_object = $this->get_string_arg( 'pick_object', $data );

		if ( 'custom-simple' === $pick_object ) {
			$pick_choices = $this->get_string_arg( 'pick_custom', $data );
			$is_filled    = strlen( $pick_choices ) > 0;
			$choices      = array();

			if ( $is_filled ) {
				$pick_choices = explode( "\n", $pick_choices );

				foreach ( $pick_choices as $pick_choice ) {
					$pick_choice = explode( '|', $pick_choice );
					$choice_key  = trim( $pick_choice[0] );
					// optional, can be just one value.
					$choice_value = key_exists( 1, $pick_choice ) ?
						trim( $pick_choice[1] ) :
						$choice_key;

					$choices[ $choice_key ] = $choice_value;
				}
			}

			return $choices;
		}

		// skip non-select fields (in Fields/Pods_Pick_Field.php they don't employ the Select field).
		$non_select_objects = array( 'user', 'post_type', 'taxonomy', 'media' );

		$is_non_select = in_array(
			$pick_object,
			$non_select_objects,
			true
		);

		if ( $is_non_select ) {
			return array();
		}

		return $this->get_dynamic_choices( $pick_object );
	}

	/**
	 * @param int|string $source_id
	 * @param array<string|int,mixed>|null $local_data
	 *
	 * @return mixed
	 */
	protected function get_pods_value(
		string $field_name,
		string $field_source_type,
		$source_id,
		bool $is_formatted,
		?array $local_data = null
	) {
		if ( function_exists( 'pods' ) ) {
			if ( is_string( $source_id ) ) {
				$source_id = self::remove_source_prefix( $source_id );
			}

			// it also works with user/term/comment, as pod is attached to specific type (post/user/term).
			$pod = pods( $field_source_type, $source_id );

			$is_valid = is_object( $pod ) &&
						method_exists( $pod, 'is_valid' ) &&
						method_exists( $pod, 'field' ) &&
						$pod->is_valid();

			if ( $is_valid ) {
				return $pod->field( $field_name, null, $is_formatted );
			}
		}

		return null;
	}

	protected function get_pod_object_by_group_id( string $group_id ): ?object {
		$pods_api = self::get_pods_api();

		if ( is_object( $pods_api ) && method_exists( $pods_api, 'load_pod' ) ) {
			$pod_id    = explode( '.', $group_id )[0];
			$load_args = array(
				'name' => $pod_id,
			);

			$pod = $pods_api->load_pod( $load_args );

			return is_object( $pod ) ?
				$pod :
				null;
		}

		return null;
	}

	protected function get_group_object_by_group_id( string $group_id ): ?object {
		$pods_api = self::get_pods_api();

		if ( is_object( $pods_api ) && method_exists( $pods_api, 'load_group' ) ) {
			$pod_id         = explode( '.', $group_id )[0];
			$group_short_id = explode( '.', $group_id )[1] ?? '';
			$load_args      = array(
				'pod'  => $pod_id,
				'name' => $group_short_id,
			);

			$group = $pods_api->load_group( $load_args );

			return is_object( $group ) ?
				$group :
				null;
		}

		return null;
	}

	/**
	 * @return array<string,mixed>
	 */
	protected function find_field_info( string $field_id ): array {
		$field_source_type = explode( '.', $field_id )[0];
		$field_name        = explode( '.', $field_id )[1] ?? '';
		$groups_by_pod     = $this->get_groups();
		$data              = array();

		foreach ( $groups_by_pod as $type => $groups ) {
			if ( $field_source_type !== $type ) {
				continue;
			}

			foreach ( $groups as $group ) {
				if ( false === method_exists( $group, 'get_fields' ) ) {
					continue;
				}

				$fields = $group->get_fields();

				foreach ( $fields as $field ) {
					$is_field = is_object( $field ) && method_exists( $field, 'get_args' );

					if ( ! $is_field ) {
						continue;
					}

					$field_info = $field->get_args();
					$field_info = is_array( $field_info ) ?
						$field_info :
						array();

					if ( $this->get_string_arg( 'name', $field_info ) === $field_name ) {
						$data = $field_info;
						break;
					}
				}
			}
		}

		return $data;
	}

	/**
	 * @param string[] $include_only_types
	 *
	 * @return array<string, Field_Meta>
	 */
	protected function get_group_field_choices( object $group, string $type, array $include_only_types ): array {
		$field_choices = array();

		$is_group = method_exists( $group, 'get_args' ) && method_exists( $group, 'get_fields' );

		if ( ! $is_group ) {
			return $field_choices;
		}

		$group_info = $group->get_args();
		$group_info = is_array( $group_info ) ?
			$group_info :
			array();

		$group_name            = $this->get_string_arg( 'name', $group_info );
		$group_id              = self::get_pods_group_id( $type, $group_name );
		$vendor_name           = $this->get_name();
		$supported_field_types = $this->get_supported_field_types();
		$fields                = $group->get_fields();

		foreach ( $fields as $field ) {
			$is_field = is_object( $field ) && method_exists( $field, 'get_args' );

			if ( ! $is_field ) {
				continue;
			}

			$field_info = $field->get_args();
			$field_info = is_array( $field_info ) ?
				$field_info :
				array();
			$field_type = $this->get_string_arg( 'type', $field_info );

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

			$field_name = $this->get_string_arg( 'name', $field_info );
			$field_id   = self::get_pods_field_id( $type, $field_name );

			$field_key = $this->get_field_key( $group_id, $field_id );

			$field_meta = new Field_Meta( $vendor_name, $field_id );
			$this->fill_field_meta( $field_meta, $field_info );

			$field_choices[ $field_key ] = $field_meta;
		}

		return $field_choices;
	}

	/**
	 * @return array<string,string>
	 */
	protected static function get_date_formats(): array {
		return array(
			'datetime' => 'Y-m-d H:i:s',
			'date'     => 'Y-m-d',
			'time'     => 'H:i:s',
		);
	}

	protected static function remove_source_prefix( string $source_id ): string {
		$prefixes = array( 'user_', 'term_', 'comment_' );

		foreach ( $prefixes as $prefix ) {
			$position = strpos( $source_id, $prefix );

			if ( is_int( $position ) ) {
				return str_replace( $prefix, '', $source_id );
			}
		}

		return $source_id;
	}

	protected static function get_pods_api(): ?object {
		$pods_api = function_exists( 'pods_api' ) ?
			pods_api() :
			null;

		return is_object( $pods_api ) ?
			$pods_api :
			null;
	}

	/**
	 * @return array<string|int,mixed>
	 */
	protected static function load_pick_related_objects(): array {
		$pods_pick = 'PodsField_Pick';

		$is_supported = class_exists( $pods_pick ) && property_exists( $pods_pick, 'related_objects' );

		if ( $is_supported ) {
			$pods_pick_instance = new $pods_pick();

			$is_usable = is_object( $pods_pick_instance ) && method_exists( $pods_pick_instance, 'setup_related_objects' );

			if ( $is_usable ) {
				call_user_func( array( $pods_pick_instance, 'setup_related_objects' ) );

				$related_objects = $pods_pick::$related_objects;

				return is_array( $related_objects ) ?
					$related_objects :
					array();
			}
		}

		return array();
	}

	protected static function get_php_date_format( string $js_date_format ): string {
		$date_formats = array(
			'mdy'       => 'm/d/Y',
			'mdy_dash'  => 'm-d-Y',
			'mdy_dot'   => 'm.d.Y',
			'dmy'       => 'd/m/Y',
			'dmy_dash'  => 'd-m-Y',
			'dmy_dot'   => 'd.m.Y',
			'ymd_slash' => 'Y/m/d',
			'ymd_dash'  => 'Y-m-d',
			'ymd_dot'   => 'Y.m.d',
			'dMy'       => 'd/M/Y',
			'dMy_dash'  => 'd-M-Y',
			'fjy'       => 'F j, Y',
			'fjsy'      => 'F jS, Y',
			'y'         => 'Y',
			'c'         => 'c',
		);

		return key_exists( $js_date_format, $date_formats ) ?
			$date_formats[ $js_date_format ] :
			'';
	}

	protected static function get_php_time_format( string $js_time_format ): string {
		$time_formats = array(
			'h_mm_A'     => 'g:i A',
			'h_mm_ss_A'  => 'g:i:s A',
			'hh_mm_A'    => 'h:i A',
			'hh_mm_ss_A' => 'h:i:s A',
			'h_mma'      => 'g:ia',
			'hh_mma'     => 'h:ia',
			'h_mm'       => 'g:i',
			'h_mm_ss'    => 'g:i:s',
			'hh_mm'      => 'h:i',
			'hh_mm_ss'   => 'h:i:s',
		);

		return key_exists( $js_time_format, $time_formats ) ?
			$time_formats[ $js_time_format ] :
			'';
	}
}
