<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Template\Html_Wrapper;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Template\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Generation\Token_Factory;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Generation\Token_Factory_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Generation\Tokens\Format_Token;

final class Row_Wrapper_Printer {
	private Token_Factory_Storage $token_factory_storage;

	public function __construct( Token_Factory_Storage $token_factory_storage ) {
		$this->token_factory_storage = $token_factory_storage;
	}

	public static function print_row_wrapper(
		string $field_name_class,
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		string $type,
		string $row_class,
		int &$tab_number,
		string $tag
	): void {
		$row_classes = '';

		if ( Layout_Settings::CLASS_GENERATION_NONE !== $layout_settings->classes_generation ) {
			$row_classes .= $field_name_class;

			if ( $layout_settings->is_with_common_classes ) {
				$bem_name     = $layout_settings->get_bem_name();
				$row_classes .= sprintf( ' %s__%s', $bem_name, $type );
			}
		}

		// do not consider classes_generation=none, as external classes, e.g. 'splide', are required for js.
		if ( '' !== $row_class ) {
			$row_classes .= '' !== $row_classes ?
				' ' :
				'';
			$row_classes .= $row_class;
		}

		Format_Token::tabulation( $tab_number );
		printf( '<%s class="%s">', esc_html( $tag ), esc_html( $row_classes ) );
		Format_Token::next_line();

		++$tab_number;
	}

	/**
	 * @param Template_Pattern[] $field_assets
	 */
	public static function print_field_wrapper(
		array $field_assets,
		string $field_id,
		int &$tabs_number,
		bool $is_with_row_wrapper,
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		string $field_name_class,
		string $tag
	): void {
		$field_classes = '';

		if ( Layout_Settings::CLASS_GENERATION_NONE !== $layout_settings->classes_generation ) {
			$bem_name = $layout_settings->get_bem_name();

			if ( $is_with_row_wrapper ) {
				$field_classes .= sprintf( '%s__%s-field', $bem_name, $field_settings->id );
				$field_classes .= $layout_settings->is_with_common_classes ?
					sprintf( ' %s__field', $bem_name ) :
					'';
			} else {
				$field_classes .= $field_name_class;

				if ( $layout_settings->is_with_common_classes ) {
					$field_classes .= sprintf( ' %s__field', $bem_name );
				}
			}
		}

		$attrs_data = array();

		foreach ( $field_assets as $field_asset ) {
			$asset_attrs = $field_asset->get_field_wrapper_attrs( $field_settings, $field_id );
			$attrs_data  = array_merge( $attrs_data, $asset_attrs );
		}

		$attr_class = $attrs_data['class'] ?? '';
		unset( $attrs_data['class'] );

		// do not consider classes_generation=none, as external classes, e.g. 'splide', are required for js.
		if ( '' !== $attr_class ) {
			$field_classes .= '' !== $field_classes ?
				' ' :
				'';
			$field_classes .= $attr_class;
		}

		Format_Token::tabulation( $tabs_number );

		printf(
			'<%s class="%s"',
			esc_html( $tag ),
			esc_html( $field_classes ),
		);

		foreach ( $attrs_data as $attr => $value ) {
			printf( ' %s="%s"', esc_html( $attr ), esc_html( $value ) );
		}

		echo '>';

		++$tabs_number;
	}

	/**
	 * @param Html_Wrapper[] $field_outers
	 */
	public static function print_opening_field_outers(
		array $field_outers,
		int &$tabs_number,
		Token_Factory $token_factory
	): void {
		foreach ( $field_outers as $outer ) {
			$tabs = str_repeat( "\t", $tabs_number );

			printf( "\r\n%s", esc_html( $tabs ) );

			printf( '<%s', esc_html( $outer->tag ) );

			foreach ( $outer->attrs as $attr => $value ) {
				printf( ' %s="%s"', esc_html( $attr ), esc_html( $value ) );
			}

			foreach ( $outer->variable_attrs as $attr => $variable_info ) {
				$var = $token_factory->variable( $variable_info['field_id'] )
									->add_item_path( $variable_info['item_key'] );

				$token_factory->format()
				->attribute( $attr, $var );
			}

			echo '>';

			$token_factory->format()
							->new_line();

			++$tabs_number;
		}
	}

	/**
	 * @param Html_Wrapper[] $field_outers
	 */
	public static function print_closing_field_outers( array $field_outers, int &$tabs_number ): void {
		foreach ( $field_outers as $outer ) {
			Format_Token::tabulation( --$tabs_number );
			printf( '</%s>', esc_html( $outer->tag ) );
			Format_Token::next_line();
		}
	}

	public function print_label(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		int &$tabs_number,
		string $field_id
	): void {
		$label_class = '';

		if ( Layout_Settings::CLASS_GENERATION_NONE !== $layout_settings->classes_generation ) {
			$bem_name = $layout_settings->get_bem_name();

			$label_class .= sprintf( '%s__%s-label', $bem_name, $field_settings->id );

			$label_class .= $layout_settings->is_with_common_classes ?
				sprintf( ' %s__label', $bem_name ) :
				'';
		}

		Format_Token::tabulation( $tabs_number );

		printf( '<p class="%s">', esc_html( $label_class ) );

		Format_Token::next_line();
		Format_Token::tabulation( ++$tabs_number );

		$token_generator = $this->token_factory_storage->resolve_token_factory( $layout_settings->template_engine );

		$var = $token_generator->variable( $field_id )
								->add_item_path( 'label' );

		$token_generator->to_echo( $var )
						->print();

		Format_Token::next_line();
		Format_Token::tabulation( --$tabs_number );

		echo '</p>';

		Format_Token::next_line();
	}
}
