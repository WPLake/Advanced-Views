<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Acf\Fields;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Map_Field_Base;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Generation\Template_Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Raw_Field_Value;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Map\Map_Library;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Tokens\IF_Branch;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Tokens\IF_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Tokens\Loop_Token;
use function Org\Wplake\Advanced_Views\Template\Html\tag;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\arr;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
class Map_Field extends Map_Field_Base {
	const MAP = 'map';

	public function compose_html( string $field_id, Template_Field_Meta $markup_field_data ): array {
		$factory    = $markup_field_data->get_token_factory();
		$field_meta = $markup_field_data->get_context()->get_field_meta();

		if ( 'open_street_map' === $field_meta->get_type() ) {
			$map_var = $factory
				->variable( $field_id )
				->add_item_path( self::MAP )
				->display_as_raw( true );

			return array( $map_var );
		}

		$attributes_map = array(
			'data-zoom'       => self::ZOOM,
			'data-center-lat' => self::CENTER_LAT,
			'data-center-lng' => self::CENTER_LNG,
		);

		$div_attributes = array(
			'class' => $this->get_field_class( 'map', $markup_field_data ),
			'style' => array(
				'width'  => '100%',
				'height' => '400px',
			),
		);

		foreach ( $attributes_map as $attribute_name => $key ) {
			$div_attributes[ $attribute_name ] = $factory
				->variable( $field_id )
				->add_item_path( $key );
		}

		$content_nodes = $this->compose_content( $field_id, $markup_field_data );

		return array(
			tag( 'div' )
				->attributes( $div_attributes )
				->content( ...$content_nodes ),
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$field_type = $field_value->get_context()->get_field_meta()->get_type();

		return 'open_street_map' !== $field_type ?
			self::get_template_args_for_google( $field_value ) :
			self::get_template_args_for_os( $field_value );
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_validation_template_variables( Raw_Field_Value $field_value ): array {
		return self::get_validation_args( $field_value );
	}

	public function is_with_field_wrapper(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta
	): bool {
		return $layout_settings->is_with_unnecessary_wrappers ||
				'open_street_map' === $field_meta->get_type();
	}

	/**
	 * @return string[]
	 */
	public function get_conditional_fields( Field_Meta $field_meta ): array {
		return array_merge(
			parent::get_conditional_fields( $field_meta ),
			array(
				Field_Settings::FIELD_MAP_MARKER_ICON,
				Field_Settings::FIELD_MAP_MARKER_ICON_TITLE,
				Field_Settings::FIELD_MAP_ADDRESS_FORMAT,
				Field_Settings::FIELD_IS_MAP_WITH_ADDRESS,
				Field_Settings::FIELD_IS_MAP_WITHOUT_GOOGLE_MAP,
			)
		);
	}

	public function get_front_assets( Field_Settings $field_settings ): array {
		$front_assets = array();

		if ( false === $field_settings->is_map_without_google_map ) {
			$front_assets[] = Map_Library::NAME;
		}

		return array_merge( parent::get_front_assets( $field_settings ), $front_assets );
	}

	/**
	 * @return array<string,mixed>
	 */
	protected function map_marker_attributes(
		string $field_id,
		string $item_id,
		Template_Field_Meta $markup_field_data
	): array {
		$field_context   = $markup_field_data->get_context();
		$token_factory   = $markup_field_data->get_token_factory();
		$layout_settings = $field_context->get_layout_settings();
		$field_settings  = $field_context->get_field_settings();

		$lat_var = $token_factory
			->variable( $item_id )
			->add_item_path( self::LAT );
		$lng_var = $token_factory
			->variable( $item_id )
			->add_item_path( self::LNG );

		return array(
			'class'    => $this->get_item_class( 'map-marker', $layout_settings, $field_settings ),
			'data-lat' => $lat_var,
			'data-lng' => $lng_var,
		);
	}

	/**
	 * @return Html_Node[]
	 */
	protected function compose_content( string $field_id, Template_Field_Meta $markup_field_data ): array {
		$field_context  = $markup_field_data->get_context();
		$field_meta     = $field_context->get_field_meta();
		$field_settings = $field_context->get_field_settings();

		if ( $field_meta->is_multiple() ) {
			return array( $this->compose_item_loop( $field_id, $markup_field_data ) );
		}

		if ( $field_settings->is_visible_when_empty ) {
			return array( $this->compose_conditional_item( $field_id, $field_id, $markup_field_data ) );
		}

		return $this->compose_inner_item( $field_id, $field_id, $markup_field_data );
	}

	protected function compose_item_loop( string $field_id, Template_Field_Meta $markup_field_data ): Loop_Token {
		$token_factory = $markup_field_data->get_token_factory();

		$source_var = $token_factory
			->variable( $field_id )
			->add_item_path( self::VALUE );
		$item_var   = $token_factory->variable( 'marker' );

		$loop_body = $this->compose_inner_item( $field_id, $item_var->get_name(), $markup_field_data );

		return $token_factory
			->loop()
			->set_source_variable( $source_var )
			->set_item_variable( $item_var )
			->set_body( $loop_body );
	}

	protected function compose_conditional_item( string $field_id, string $item_id, Template_Field_Meta $markup_field_data ): IF_Token {
		$token_factory = $markup_field_data->get_token_factory();

		$item_branch = $this->compose_item_branch( $field_id, $item_id, $markup_field_data );

		return $token_factory
			->if()
			->set_if_branch( $item_branch );
	}

	protected function compose_item_branch( string $field_id, string $item_id, Template_Field_Meta $markup_field_data ): IF_Branch {
		$token_factory = $markup_field_data->get_token_factory();
		$value_var     = $token_factory
			->variable( $field_id )
			->add_item_path( self::VALUE );

		$body = $this->compose_inner_item( $field_id, $item_id, $markup_field_data );

		return $token_factory
			->if_branch()
			->set_condition( $value_var )
			->set_body( $body );
	}

	/**
	 * @return Html_Node[]
	 */
	protected function compose_inner_item( string $field_id, string $item_id, Template_Field_Meta $markup_field_data ): array {
		$marker_attrs = $this->map_marker_attributes( $field_id, $item_id, $markup_field_data );

		$tags = tag( 'div' )
			->attributes( $marker_attrs );

		return array( $tags );
	}

	/**
	 * @return array<string, mixed>
	 */
	protected static function get_template_args_for_google( Raw_Field_Value $field_value ): array {
		$field_meta = $field_value->get_context()->get_field_meta();
		$args       = self::get_google_default_args( $field_meta );
		$value      = arr( $field_value->get_value() );

		if ( count( $value ) > 0 ) {
			return $field_meta->is_multiple() ?
				self::with_markers( $args, $value, $field_meta->get_zoom() ) :
				self::with_marker( $args, $value );
		}

		return $args;
	}

	/**
	 * @return array<string, mixed>
	 */
	protected static function get_template_args_for_os( Raw_Field_Value $field_value ): array {
		$field_context  = $field_value->get_context();
		$field_meta     = $field_context->get_field_meta();
		$field_settings = $field_context->get_field_settings();
		$return_format  = $field_meta->get_return_format();
		$raw_value      = $field_value->get_value();

		$args = array(
			self::VALUE => $field_settings->is_map_with_address ?
				array() :
				false,
			self::MAP   => '',
		);

		if ( in_array( $return_format, array( 'leaflet', 'osm' ), true ) ) {
			// used formatted value, as output already made by the plugin, and we just need to show it.
			// todo it doesn't work if return format is not set to 'leaflet js' (e.g. the default 'Raw data' value).
			$args[ self::MAP ] = $field_value->get_formatted_value();
		}

		// if withAddress, will be filled in the Pro class.
		if ( ! $field_settings->is_map_with_address ) {
			$args[ self::VALUE ] = self::has_markers( $raw_value );
		}

		return $args;
	}

	/**
	 * @return array<string, mixed>
	 */
	protected static function get_validation_args( Raw_Field_Value $field_value ): array {
		$field_context = $field_value->get_context();
		$field_meta    = $field_context->get_field_meta();

		if ( 'open_street_map' === $field_meta->get_type() ) {
			return self::get_os_validation_args( $field_value );
		}

		$args        = self::get_google_default_args( $field_meta );
		$marker_args = array(
			self::LAT => '1',
			self::LNG => '1',
		);

		if ( ! $field_meta->is_multiple() ) {
			$single_args = array(
				self::VALUE => '1',
				self::ZOOM  => '1',
			);

			return array_merge( $args, $marker_args, $single_args );
		}

		$multiple_args = array(
			self::VALUE => array( $marker_args ),
		);

		return array_merge( $args, $multiple_args );
	}

	/**
	 * @return array<string, mixed>
	 */
	protected static function get_os_validation_args( Raw_Field_Value $field_value ): array {
		$field_settings = $field_value->get_context()->get_field_settings();

		return array(
			self::VALUE => $field_settings->is_map_with_address ?
				array() :
				true,
			self::MAP   => '<iframe src="https://www.openstreetmap.org/export/embed.html?bbox=5.390371665521,50.7343356,14.857431134479,56.3593356&amp;marker=53.5500279,10.0136948" height="400" width="425" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>',
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	protected static function get_google_default_args( Field_Meta $field_meta ): array {
		$value_args = $field_meta->is_multiple() ?
			array(
				self::VALUE => array(),
			) :
			array(
				self::VALUE => '',
				self::LAT   => 0,
				self::LNG   => 0,
			);

		// set default values, so if the field has no markers, and showWhenEmpty flag,
		// then it can show the map in right position.
		$map_args = array(
			self::ZOOM       => $field_meta->get_zoom(),
			self::CENTER_LAT => $field_meta->get_center_lat(),
			self::CENTER_LNG => $field_meta->get_center_lng(),
		);

		return array_merge( $value_args, $map_args );
	}

	/**
	 * @param array<string, mixed> $args
	 * @param array<string|int, mixed> $value
	 *
	 * @return array<string, mixed>
	 */
	protected static function with_marker( array $args, array $value ): array {
		$lat = string( $value, 'lat' );

		$args[ self::VALUE ] = strlen( $lat ) > 0;
		$args[ self::ZOOM ]  = string( $value, 'zoom', '16' );
		$args[ self::LAT ]   = $lat;
		$args[ self::LNG ]   = string( $value, 'lng' );

		return $args;
	}

	/**
	 * @param array<string, mixed> $args
	 * @param array<string|int, mixed> $value
	 *
	 * @return array<string, mixed>
	 */
	protected static function with_markers( array $args, array $value, int $default_zoom ): array {
		$markers = array();

		foreach ( $value as $item ) {
			$item = arr( $item );

			$markers[] = array(
				self::LAT => string( $item, 'lat' ),
				self::LNG => string( $item, 'lng' ),
			);
		}

		// the plugin doesn't support zoom, so use the default from the ACF field settings.
		$args[ self::ZOOM ]  = $default_zoom;
		$args[ self::VALUE ] = $markers;

		return $args;
	}

	/**
	 * @param mixed $raw_value
	 */
	protected static function has_markers( $raw_value ): bool {
		$markers = is_array( $raw_value ) ?
			arr( $raw_value, 'markers' ) :
			array();

		return array() !== $markers;
	}
}
