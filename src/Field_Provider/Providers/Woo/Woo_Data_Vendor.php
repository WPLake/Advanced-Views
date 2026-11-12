<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Woo;

use DateTime;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Base;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Integration;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Image_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Woo\Fields\Woo_Featured_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Woo\Fields\Woo_Fields;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Woo\Fields\Woo_Gallery_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Woo\Fields\Woo_Height_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Woo\Fields\Woo_Length_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Woo\Fields\Woo_Price_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Woo\Fields\Woo_Regular_Price_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Woo\Fields\Woo_Sale_Price_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Woo\Fields\Woo_Sku_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Woo\Fields\Woo_Stock_Quantity_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Woo\Fields\Woo_Stock_Status_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Woo\Fields\Woo_Weight_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Woo\Fields\Woo_Width_Field;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Source;

defined( 'ABSPATH' ) || exit;

final class Woo_Data_Vendor extends Field_Provider_Base {
	// for back compatibility only.
	const NAME = 'woo';

	public function get_name(): string {
		return self::NAME;
	}

	public function is_meta_vendor(): bool {
		return true;
	}

	public function is_available(): bool {
		return class_exists( 'WooCommerce' );
	}

	public function make_integration_instance( Instance_Container $container ): ?Field_Provider_Integration {
		return null;
	}

	/**
	 * @return array<string, string>
	 */
	public function get_group_choices(): array {
		$group_key = $this->get_group_key( Woo_Fields::GROUP_NAME );

		return array(
			$group_key => __( 'Product specific fields (WooCommerce)', 'acf-views' ),
		);
	}

	/**
	 * @param string[] $include_only_types
	 *
	 * @return array<string, Field_Meta>
	 */
	public function get_field_choices( array $include_only_types = array() ): array {
		$field_choices = array();
		$vendor_name   = $this->get_name();
		$fields        = self::get_fields_with_labels();
		$field_ids     = array_keys( $fields );

		foreach ( $field_ids as $field_id ) {
			$is_included = array() === $include_only_types || in_array(
				$field_id,
				$include_only_types,
				true
			);

			if ( $is_included ) {
				$field_key = $this->get_field_key( Woo_Fields::GROUP_NAME, $field_id );

				$field_meta = new Field_Meta( $vendor_name, $field_id );
				$this->fill_field_meta( $field_meta );

				$field_choices[ $field_key ] = $field_meta;
			}
		}

		return $field_choices;
	}

	/**
	 * @param mixed[] $data
	 */
	public function fill_field_meta( Field_Meta $field_meta, array $data = array() ): void {
		$field_id        = $field_meta->get_field_id();
		$supported_types = $this->get_supported_field_types();

		$is_supported = in_array(
			$field_id,
			$supported_types,
			true
		);

		if ( $is_supported ) {
			// it's necessary to have real meta names for WP_Query.
			$real_field_name = self::get_real_field_name( $field_id );

			$field_names = self::get_fields_with_labels()[ $field_id ] ?? array();
			$field_meta
				->set_type( $field_id )
				->set_name( $real_field_name )
				->set_label( $field_names['label'] ?? '' )
				->set_identifier( $field_names['identifier'] ?? '' )
				->set_is_field_exist( true );

			if ( Woo_Fields::FIELD_GALLERY === $field_id ) {
				$field_meta->set_is_multiple( true );
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
		if ( $source->is_options() ) {
			return null;
		}

		return $source->get_id();
	}

	public function convert_string_to_date_time( Field_Meta $field_meta, string $value ): ?DateTime {
		return null;
	}

	public function convert_date_to_string_for_db_comparison(
		DateTime $date_time,
		Field_Meta $field_meta
	): string {
		return '';
	}

	/**
	 * @return null|array{title:string,url:string}
	 */
	public function get_group_link_by_group_id( string $group_id ): ?array {
		return null;
	}

	/**
	 * @return mixed[]|null
	 */
	public function get_group_export_data( string $group_id ): ?array {
		// the feature is not supported.
		return null;
	}

	/**
	 * @param mixed[] $group_data
	 * @param mixed[] $meta_data
	 */
	public function import_group( array $group_data, array $meta_data ): ?string {
		// the feature is not supported.
		return null;
	}

	// for back compatibility only.
	protected function is_without_name_in_keys(): bool {
		return true;
	}

	/**
	 * @return array<string,Markup_Field>
	 */
	protected function get_field_types(): array {
		return array(
			Woo_Fields::FIELD_GALLERY        => new Woo_Gallery_Field( new Image_Field() ),
			Woo_Fields::FIELD_PRICE          => new Woo_Price_Field(),
			Woo_Fields::FIELD_REGULAR_PRICE  => new Woo_Regular_Price_Field(),
			Woo_Fields::FIELD_SALE_PRICE     => new Woo_Sale_Price_Field(),
			Woo_Fields::FIELD_SKU            => new Woo_Sku_Field(),
			Woo_Fields::FIELD_STOCK_STATUS   => new Woo_Stock_Status_Field(),
			Woo_Fields::FIELD_STOCK_QUANTITY => new Woo_Stock_Quantity_Field(),
			Woo_Fields::FIELD_WEIGHT         => new Woo_Weight_Field(),
			Woo_Fields::FIELD_LENGTH         => new Woo_Length_Field(),
			Woo_Fields::FIELD_WIDTH          => new Woo_Width_Field(),
			Woo_Fields::FIELD_HEIGHT         => new Woo_Height_Field(),
			Woo_Fields::FIELD_FEATURED       => new Woo_Featured_Field(),
		);
	}

	/**
	 * @return array<string,array{label:string,identifier:string}>
	 */
	protected static function get_fields_with_labels(): array {
		return array(
			Woo_Fields::FIELD_GALLERY        => array(
				'label'      => __( 'Gallery', 'acf-views' ),
				'identifier' => 'gallery',
			),
			Woo_Fields::FIELD_PRICE          => array(
				'label'      => __( 'Price', 'acf-views' ),
				'identifier' => 'price',
			),
			Woo_Fields::FIELD_REGULAR_PRICE  => array(
				'label'      => __( 'Regular price', 'acf-views' ),
				'identifier' => 'regular_price',
			),
			Woo_Fields::FIELD_SALE_PRICE     => array(
				'label'      => __( 'Sale price', 'acf-views' ),
				'identifier' => 'sale_price',
			),
			Woo_Fields::FIELD_SKU            => array(
				'label'      => __( 'SKU', 'acf-views' ),
				'identifier' => 'sku',
			),
			Woo_Fields::FIELD_STOCK_STATUS   => array(
				'label'      => __( 'Stock status', 'acf-views' ),
				'identifier' => 'stock_status',
			),
			Woo_Fields::FIELD_STOCK_QUANTITY => array(
				'label'      => __( 'Stock quantity', 'acf-views' ),
				'identifier' => 'stock_quantity',
			),
			Woo_Fields::FIELD_WEIGHT         => array(
				'label'      => __( 'Weight', 'acf-views' ),
				'identifier' => 'weight',
			),
			Woo_Fields::FIELD_LENGTH         => array(
				'label'      => __( 'Length', 'acf-views' ),
				'identifier' => 'length',
			),
			Woo_Fields::FIELD_WIDTH          => array(
				'label'      => __( 'Width', 'acf-views' ),
				'identifier' => 'width',
			),
			Woo_Fields::FIELD_HEIGHT         => array(
				'label'      => __( 'Height', 'acf-views' ),
				'identifier' => 'height',
			),
			Woo_Fields::FIELD_FEATURED       => array(
				'label'      => __( 'Featured', 'acf-views' ),
				'identifier' => 'featured',
			),
		);
	}

	protected static function get_real_field_name( string $field_id ): string {
		$real_names = self::get_real_field_names();

		return $real_names[ $field_id ] ?? '';
	}

	/**
	 * FEATURED has no real name, as it's actually a taxonomy.
	 *
	 * @return array<string,string>
	 */
	protected static function get_real_field_names(): array {
		return array(
			Woo_Fields::FIELD_GALLERY        => '_product_image_gallery',
			Woo_Fields::FIELD_PRICE          => '_price',
			Woo_Fields::FIELD_REGULAR_PRICE  => '_regular_price',
			Woo_Fields::FIELD_SALE_PRICE     => '_sale_price',
			Woo_Fields::FIELD_SKU            => '_sku',
			Woo_Fields::FIELD_STOCK_STATUS   => '_stock_status',
			Woo_Fields::FIELD_STOCK_QUANTITY => '_stock',
			Woo_Fields::FIELD_WEIGHT         => '_weight',
			Woo_Fields::FIELD_LENGTH         => '_length',
			Woo_Fields::FIELD_WIDTH          => '_width',
			Woo_Fields::FIELD_HEIGHT         => '_height',
		);
	}
}
