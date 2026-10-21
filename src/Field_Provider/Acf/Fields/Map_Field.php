<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Acf\Fields;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Conditional\IF_Branch;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Map_Field_Base;
use Org\Wplake\Advanced_Views\Template\Html\Tags_Renderer;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Template_Field_Data;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Rendering\Variable_Field_Data;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Patterns\Map_Pattern;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\arr;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

class Map_Field extends Map_Field_Base {
	const MAP = 'map';

	/**
	 * @return array<string,mixed>
	 */
	protected function map_marker_attributes(
		string $field_id,
		string $item_id,
		Template_Field_Data $markup_field_data
	): array {
		$token_factory = $markup_field_data->get_token_factory();

		return array(
			'class'    => $this->get_item_class(
				'map-marker',
				$markup_field_data->get_view_data(),
				$markup_field_data->get_field_data()
			),
			'data-lat' => $token_factory->variable( $item_id )
										->add_item_path( self::LAT ),
			'data-lng' => $token_factory->variable( $item_id )
										->add_item_path( self::LNG ),
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	protected function get_template_args_for_google( Variable_Field_Data $variable_field_data ): array {
		$args = ! $variable_field_data->get_field_meta()->is_multiple() ?
			array(
				self::VALUE => '',
				self::LAT   => 0,
				self::LNG   => 0,
			) :
			array(
				self::VALUE => array(),
			);

		// common args.
		$args = array_merge(
			$args,
			array(
				// set default values, so if the field has no markers, and showWhenEmpty flag,
				// then it can show the map in right position.
				self::ZOOM       => $variable_field_data->get_field_meta()->get_zoom(),
				self::CENTER_LAT => $variable_field_data->get_field_meta()->get_center_lat(),
				self::CENTER_LNG => $variable_field_data->get_field_meta()->get_center_lng(),
			)
		);

		$value = arr( $variable_field_data->get_value() );

		if ( 0 === count( $value ) ) {
			return $args;
		}

		if ( ! $variable_field_data->get_field_meta()->is_multiple() ) {
			$lat = string( $value, 'lat' );

			$args[ self::VALUE ] = strlen( $lat ) > 0;
			$args[ self::ZOOM ]  = string( $value, 'zoom', '16' );
			$args[ self::LAT ]   = $lat;
			$args[ self::LNG ]   = string( $value, 'lng' );
		} else {
			// the plugin doesn't support zoom, so use the default from the ACF field settings.
			$args[ self::ZOOM ] = $variable_field_data->get_field_meta()->get_zoom();

			$args[ self::VALUE ] = array();

			foreach ( $value as $item ) {
				$item = arr( $item );

				$args[ self::VALUE ][] = array(
					self::LAT => string( $item, 'lat' ),
					self::LNG => string( $item, 'lng' ),
				);
			}
		}

		return $args;
	}

	/**
	 * @return array<string, mixed>
	 */
	protected function get_template_args_for_os( Variable_Field_Data $variable_field_data ): array {
		$args = array(
			self::VALUE => $variable_field_data->get_field_data()->is_map_with_address ?
				array() :
				false,
			self::MAP   => '',
		);

		switch ( $variable_field_data->get_field_meta()->get_return_format() ) {
			case 'leaflet':
			case 'osm':
				// used formatted value, as output already made by the plugin, and we just need to show it.
				$args = array_merge(
					$args,
					array(
						// todo it doesn't work if return format is not set to 'leaflet js' (e.g. the default 'Raw data' value).
						self::MAP => $variable_field_data->get_formatted_value(),
					)
				);
				break;
		}

		// if withAddress, will be filled in the Pro class.
		if ( ! $variable_field_data->get_field_data()->is_map_with_address ) {
			$markers             = is_array( $variable_field_data->get_value() ) &&
							key_exists( 'markers', $variable_field_data->get_value() ) &&
							is_array( $variable_field_data->get_value()['markers'] ) ?
				$variable_field_data->get_value()['markers'] :
				array();
			$args[ self::VALUE ] = array() !== $markers;
		}

		return $args;
	}

	/**
	 * @return array<string, mixed>
	 */
	protected function get_validation_args( Variable_Field_Data $variable_field_data ): array {
		if ( 'open_street_map' !== $variable_field_data->get_field_meta()->get_type() ) {
			$args = ! $variable_field_data->get_field_meta()->is_multiple() ?
				array(
					self::VALUE => '',
					self::LAT   => 0,
					self::LNG   => 0,
				) :
				array( self::VALUE => array() );

			// common args.
			$args = array_merge(
				$args,
				array(// set default values, so if the field has no markers, and showWhenEmpty flag,
					// then it can show the map in right position.
					self::ZOOM       => $variable_field_data->get_field_meta()->get_zoom(),
					self::CENTER_LAT => $variable_field_data->get_field_meta()->get_center_lat(),
					self::CENTER_LNG => $variable_field_data->get_field_meta()->get_center_lng(),
				)
			);

			$validation_args = array(
				self::LAT => '1',
				self::LNG => '1',
			);

			if ( ! $variable_field_data->get_field_meta()->is_multiple() ) {
				$validation_args = array_merge( $args, $validation_args );

				return array_merge(
					$validation_args,
					array(
						self::VALUE => '1',
						self::ZOOM  => '1',
					)
				);
			}

			return array_merge(
				$args,
				array(
					self::VALUE => array( $validation_args ),
				)
			);
		}

		return array(
			self::VALUE => $variable_field_data->get_field_data()->is_map_with_address ?
				array() :
				true,
			self::MAP   => '<iframe src="https://www.openstreetmap.org/export/embed.html?bbox=5.390371665521,50.7343356,14.857431134479,56.3593356&amp;marker=53.5500279,10.0136948" height="400" width="425" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>',
		);
	}

	public function compose_tags( string $field_id, Template_Field_Data $markup_field_data ): array {
		$factory    = $markup_field_data->get_token_factory();
		$field_meta = $markup_field_data->get_field_meta();
		$field_data = $markup_field_data->get_field_data();

		if ( 'open_street_map' === $field_meta->get_type() ) {
			return array(
				fn() => $factory->variable( $field_id )
					->add_item_path( self::MAP )
					->raw_display(),
			);
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
			$div_attributes[ $attribute_name ] = $factory->variable( $field_id )
														->add_item_path( $key );
		}

		return array(
			array(
				'tag'   => 'div',
				'attrs' => $div_attributes,
			),
			function () use ( $factory, $markup_field_data, $field_id, $field_meta, $field_data ) {
				$factory->formatter()
					->new_line();
				$markup_field_data->increment_and_print_tabs();

				if ( $field_meta->is_multiple() ) {
					$this->print_item_loop( $field_id, $markup_field_data );
				} elseif ( $field_data->is_visible_when_empty ) {
					$this->print_conditional_item( $field_id, $field_id, $markup_field_data );
				} else {
					$this->print_inner_item( $field_id, $field_id, $markup_field_data );
				}

				$factory->formatter()
					->new_line();
				$markup_field_data->decrement_and_print_tabs();
			},
		);
	}

	protected function print_item_loop( string $field_id, Template_Field_Data $markup_field_data ): void {
		$token_factory = $markup_field_data->get_token_factory();

		$source_var = $token_factory->variable( $field_id )
										->add_item_path( self::VALUE );
		$item_var   = $token_factory->variable( 'marker' );

		$loop_body = $token_factory->html(
			function () use ( $field_id, $item_var, $markup_field_data, $token_factory ) {
				$token_factory->formatter()
								->new_line();

				$markup_field_data->increment_and_print_tabs();

				$this->print_inner_item( $field_id, $item_var->get_name(), $markup_field_data );

				$token_factory->formatter()
								->new_line();
				$markup_field_data->decrement_and_print_tabs();
			}
		);

		$token_factory->loop()
						->set_source_variable( $source_var )
						->set_item_variable( $item_var )
						->set_body( $loop_body )
						->display();
	}

	protected function print_conditional_item( string $field_id, string $item_id, Template_Field_Data $markup_field_data ): void {
		$token_factory = $markup_field_data->get_token_factory();

		$item_branch = $this->item_branch( $field_id, $item_id, $markup_field_data );

		$if = $token_factory->if()->set_if_branch( $item_branch );

		$if->display();
	}

	protected function item_branch( string $field_id, string $item_id, Template_Field_Data $markup_field_data ): IF_Branch {
		$token_factory = $markup_field_data->get_token_factory();
		$value_var     = $token_factory->variable( $field_id )
										->add_item_path( self::VALUE );

		$body = $token_factory->html(
			function () use ( $field_id, $item_id, $markup_field_data, $token_factory ) {
				$token_factory->formatter()
								->new_line();
				$markup_field_data->increment_and_print_tabs();

				$this->print_inner_item( $field_id, $item_id, $markup_field_data );

				$token_factory->formatter()
								->new_line();
				$markup_field_data->decrement_and_print_tabs();
			}
		);

		return $token_factory->if_branch()
			->set_condition( $value_var )
			->set_body( $body );
	}

	protected function print_inner_item( string $field_id, string $item_id, Template_Field_Data $markup_field_data ): void {
		Tags_Renderer::print_tags(
			array(
				array(
					'tag'   => 'div',
					'attrs' => $this->map_marker_attributes( $field_id, $item_id, $markup_field_data ),
				),
			)
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_template_variables( Variable_Field_Data $variable_field_data ): array {
		return 'open_street_map' !== $variable_field_data->get_field_meta()->get_type() ?
			$this->get_template_args_for_google( $variable_field_data ) :
			$this->get_template_args_for_os( $variable_field_data );
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_validation_template_variables( Variable_Field_Data $variable_field_data ): array {
		return $this->get_validation_args( $variable_field_data );
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
			$front_assets[] = Map_Pattern::NAME;
		}

		return array_merge( parent::get_front_assets( $field_settings ), $front_assets );
	}
}
