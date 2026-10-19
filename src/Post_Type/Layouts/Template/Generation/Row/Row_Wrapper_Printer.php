<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Row;

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
		string $type,
		string $row_class,
		int &$tab_number,
		string $tag
	): void {
		$classes = Wrapper_Class_Resolver::resolve_row_classes( $layout_settings, $field_name_class, $type, $row_class );

		Format_Token::tabulation( $tab_number );
		self::print_opening_tag( $tag, $classes, array() );
		echo '>';
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
		$attrs      = self::merge_field_wrapper_attrs( $field_assets, $field_settings, $field_id );
		$attr_class = $attrs['class'] ?? '';

		unset( $attrs['class'] );

		$classes = Wrapper_Class_Resolver::resolve_field_classes(
			$layout_settings,
			$field_settings,
			$is_with_row_wrapper,
			$field_name_class,
			$attr_class
		);

		Format_Token::tabulation( $tabs_number );
		self::print_opening_tag( $tag, $classes, $attrs );
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

			self::print_attrs( $outer->attrs );
			self::print_variable_attrs( $outer->variable_attrs, $token_factory );

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
		$classes = Wrapper_Class_Resolver::resolve_label_classes( $layout_settings, $field_settings );

		Format_Token::tabulation( $tabs_number );

		printf( '<p class="%s">', esc_html( $classes ) );

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

	/**
	 * @param Template_Pattern[] $field_assets
	 *
	 * @return array<string,string>
	 */
	protected static function merge_field_wrapper_attrs( array $field_assets, Field_Settings $field_settings, string $field_id ): array {
		$attrs = array();

		foreach ( $field_assets as $field_asset ) {
			$asset_attrs = $field_asset->get_field_wrapper_attrs( $field_settings, $field_id );
			$attrs       = array_merge( $attrs, $asset_attrs );
		}

		return $attrs;
	}

	/**
	 * @param array<string,string> $attrs
	 */
	protected static function print_opening_tag( string $tag, string $classes, array $attrs ): void {
		printf( '<%s class="%s"', esc_html( $tag ), esc_html( $classes ) );

		self::print_attrs( $attrs );
	}

	/**
	 * @param array<string,string> $attrs
	 */
	protected static function print_attrs( array $attrs ): void {
		foreach ( $attrs as $attr => $value ) {
			printf( ' %s="%s"', esc_html( $attr ), esc_html( $value ) );
		}
	}

	/**
	 * @param array<string,array{field_id:string,item_key:string}> $variable_attrs
	 */
	protected static function print_variable_attrs( array $variable_attrs, Token_Factory $token_factory ): void {
		foreach ( $variable_attrs as $attr => $variable_info ) {
			$var = $token_factory->variable( $variable_info['field_id'] )
								->add_item_path( $variable_info['item_key'] );

			$token_factory->format()
							->attribute( $attr, $var );
		}
	}
}
