<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Assets\Front_Assets;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Wrapper\Label_Printer;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Wrapper\Outer_Printer;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Wrapper\Wrapper_Printer;
use Org\Wplake\Advanced_Views\Template\Html\Html;
use Org\Wplake\Advanced_Views\Template\Html\Html_Renderer;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Template\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Token_Factory_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Format_Token;

class Field_Template {
	private Field_Provider_Cluster $provider_cluster;
	private Front_Assets $front_assets;
	private Token_Factory_Storage $token_factory_storage;

	public function __construct( Field_Provider_Cluster $provider_cluster, Front_Assets $front_assets, Token_Factory_Storage $token_factory_storage ) {
		$this->provider_cluster      = $provider_cluster;
		$this->front_assets          = $front_assets;
		$this->token_factory_storage = $token_factory_storage;
	}

	// public, as used in Upgrades.

	/**
	 * @param Template_Pattern[] $field_assets
	 */
	public function print_field_markup(
		array $field_assets,
		Layout_Settings $layout_settings,
		?Item_Settings $item_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta,
		int &$tabs_number,
		string $field_id,
		bool $is_with_outer_wrappers
	): void {
		$field_type = $field_meta->get_type();

		if ( false === $field_meta->is_field_exist() ) {
			return;
		}

		$vendor_name           = $this->provider_cluster->get_vendor_name_by_key( $field_settings->key );
		$markup_field_instance = $this->get_markup_field_instance( $vendor_name, $field_type );

		if ( ! $markup_field_instance instanceof Markup_Field ) {
			return;
		}

		$is_with_wrapper = $this->is_with_field_wrapper( $field_assets, $layout_settings, $field_settings, $field_meta, 'field' );

		if ( $is_with_wrapper &&
			false === $is_with_outer_wrappers ) {
			Format_Token::next_line();
		}

		$token_factory     = $this->token_factory_storage->resolve_token_factory( $layout_settings->template_engine );
		$markup_field_data = new Template_Field_Data(
			$layout_settings,
			$item_settings,
			$field_settings,
			$field_meta,
			$this,
			$markup_field_instance,
			$token_factory
		);

		$is_with_row_wrapper = $this->is_with_row_wrapper( $layout_settings, $field_settings, $field_meta );

		$markup_field_data->set_field_assets( $field_assets );
		$markup_field_data->set_tabs_number( $tabs_number );
		$markup_field_data->set_is_with_field_wrapper( $is_with_wrapper );
		$markup_field_data->set_is_with_row_wrapper( $is_with_row_wrapper );

		Format_Token::tabulation( $tabs_number );

		Html_Renderer::print( $markup_field_instance->compose_html( $field_id, $markup_field_data ) );

		Format_Token::next_line();

		// read back, as it may be changed in getMarkup().
		$tabs_number = $markup_field_data->get_tabs_number();
	}

	/**
	 * $customFieldMarkup is used in RepeaterField
	 *
	 * @return int current tabs number
	 */
	public function print_row_markup(
		string $row_type,
		string $row_suffix,
		Layout_Settings $layout_settings,
		?Item_Settings $item_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta,
		int $tabs_number,
		string $field_id,
		string $custom_field_markup = ''
	): int {
		$vendor_name         = $this->provider_cluster->get_vendor_name_by_key( $field_settings->key );
		$front_assets        = $this->provider_cluster->get_field_front_assets( $vendor_name, $field_settings );
		$field_assets        = $this->front_assets->resolve_template_patterns( $front_assets );
		$is_label_out_of_row = self::is_label_out_of_row( $field_assets );
		$token_factory       = $this->token_factory_storage->resolve_token_factory( $layout_settings->template_engine );

		$row_tag = '';

		foreach ( $field_assets as $field_asset ) {
			$row_tag = $field_asset->get_row_wrapper_tag( $field_settings, $row_type );

			if ( '' !== $row_tag ) {
				break;
			}
		}

		$is_with_row_wrapper   = $this->is_with_row_wrapper( $layout_settings, $field_settings, $field_meta ) ||
								'' !== $row_tag;
		$is_with_field_wrapper = $this->is_with_field_wrapper(
			$field_assets,
			$layout_settings,
			$field_settings,
			$field_meta,
			$row_type
		);
		$bem_name              = $layout_settings->get_bem_name();
		$field_name_class      = sprintf( '%s__%s%s', $bem_name, $field_settings->id, $row_suffix );

		$row_tag   = 0 === strlen( $row_tag ) ?
			'div' :
			$row_tag;
		$field_tag = $is_with_field_wrapper ?
			$this->get_field_wrapper_tag( $field_assets, $field_settings, $row_type ) :
			'';
		$field_tag = 0 === strlen( $field_tag ) ?
			'div' :
			$field_tag;

		$print_content = function ( bool $is_with_outer_wrappers ) use (
			$custom_field_markup,
			$field_assets,
			$layout_settings,
			$item_settings,
			$field_settings,
			$field_meta,
			&$tabs_number,
			$field_id
		): void {
			if ( strlen( $custom_field_markup ) > 0 ) {
				// @phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $custom_field_markup;
			} else {
				$this->print_field_markup(
					$field_assets,
					$layout_settings,
					$item_settings,
					$field_settings,
					$field_meta,
					$tabs_number,
					$field_id,
					$is_with_outer_wrappers
				);
			}
		};

		$print_label = function () use ( $token_factory, $layout_settings, $field_settings, &$tabs_number, $field_id ): void {
			Label_Printer::print( $token_factory, $layout_settings, $field_settings, $tabs_number, $field_id );
		};

		$is_with_label = strlen( $field_settings->label ) > 0;
		$label_outside = $is_with_label && $is_label_out_of_row ?
			$print_label :
			Html::stack();
		$label_inside  = $is_with_label && ! $is_label_out_of_row ?
			$print_label :
			Html::stack();

		$content = Outer_Printer::wrap_with_outers(
			$field_assets,
			$layout_settings,
			$field_settings,
			$field_id,
			$row_type,
			$tabs_number,
			$token_factory,
			$print_content
		);

		$field_layer = $is_with_field_wrapper ?
			Wrapper_Printer::wrap(
				Wrapper_Printer::field_tag(
					$field_assets,
					$field_id,
					$is_with_row_wrapper,
					$layout_settings,
					$field_settings,
					$field_name_class,
					$field_tag
				),
				$tabs_number,
				false,
				false,
				$content
			) :
			$content;

		$row_inner = Html::stack( $label_inside, $field_layer );
		$row_layer = $is_with_row_wrapper ?
			Wrapper_Printer::wrap(
				Wrapper_Printer::row_tag(
					$field_name_class,
					$layout_settings,
					$row_type,
					self::get_row_wrapper_class( $field_assets, $row_type ),
					$row_tag
				),
				$tabs_number,
				false,
				true,
				$row_inner
			) :
			$row_inner;

		$tags = Html::stack( $label_outside, $row_layer );

		Html_Renderer::print( $tags );

		return $tabs_number;
	}

	/**
	 * @param Template_Pattern[] $field_assets
	 */
	public function is_with_field_wrapper(
		array $field_assets,
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta,
		string $row_type
	): bool {
		if ( $field_meta->is_field_exist() ) {
			$field_type            = $field_meta->get_type();
			$vendor_name           = $this->provider_cluster->get_vendor_name_by_key( $field_settings->key );
			$markup_field_instance = $this->get_markup_field_instance( $vendor_name, $field_type );

			if ( $markup_field_instance instanceof Markup_Field ) {
				return '' !== $this->get_field_wrapper_tag( $field_assets, $field_settings, $row_type ) ||
						$markup_field_instance->is_with_field_wrapper( $layout_settings, $field_settings, $field_meta );
			}

			return true;
		}

		return false;
	}

	public function is_with_row_wrapper(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta
	): bool {
		$vendor_name = $field_meta->get_vendor_name();
		$field_type  = $field_meta->get_type();

		return $layout_settings->is_with_unnecessary_wrappers ||
				'' !== $field_settings->label ||
				$this->provider_cluster->is_field_type_with_sub_fields( $vendor_name, $field_type );
	}

	protected function get_markup_field_instance( string $vendor_name, string $field_type ): ?Markup_Field {
		return $this->provider_cluster->get_markup_field_instance( $vendor_name, $field_type );
	}

	/**
	 * @param Template_Pattern[] $field_assets
	 */
	protected function get_field_wrapper_tag( array $field_assets, Field_Settings $field_settings, string $row_type ): string {
		foreach ( $field_assets as $field_asset ) {
			$tag = $field_asset->get_field_wrapper_tag( $field_settings, $row_type );

			if ( strlen( $tag ) > 0 ) {
				return $tag;
			}
		}

		$vendor_name           = $this->provider_cluster->get_vendor_name_by_key( $field_settings->key );
		$field_meta            = $field_settings->get_field_meta();
		$field_type            = $field_meta->get_type();
		$markup_field_instance = $this->get_markup_field_instance( $vendor_name, $field_type );

		return $markup_field_instance instanceof Markup_Field ?
			$markup_field_instance->get_custom_field_wrapper_tag() :
			'';
	}

	/**
	 * @param Template_Pattern[] $field_assets
	 */
	protected static function get_row_wrapper_class( array $field_assets, string $row_type ): string {
		$classes = array();

		foreach ( $field_assets as $field_asset ) {
			$class = $field_asset->get_row_wrapper_class( $row_type );

			if ( '' !== $class ) {
				$classes[] = $class;
			}
		}

		return implode( ' ', $classes );
	}

	/**
	 * @param Template_Pattern[] $field_assets
	 */
	protected static function is_label_out_of_row( array $field_assets ): bool {
		foreach ( $field_assets as $field_asset ) {
			if ( $field_asset->is_label_out_of_row() ) {
				return true;
			}
		}

		return false;
	}
}
