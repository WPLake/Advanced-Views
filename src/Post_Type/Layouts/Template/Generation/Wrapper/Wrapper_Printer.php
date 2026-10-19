<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Wrapper;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Template\Html_Wrapper;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Generation\Token_Factory;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Generation\Tokens\Format_Token;

final class Wrapper_Printer {
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
	 * @param array<string,string> $attrs
	 */
	public static function print_field_wrapper(
		array $attrs,
		int &$tabs_number,
		bool $is_with_row_wrapper,
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		string $field_name_class,
		string $tag
	): void {
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

	public static function print_label(
		Token_Factory $token_factory,
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

		$var = $token_factory->variable( $field_id )
								->add_item_path( 'label' );

		$token_factory->to_echo( $var )
						->print();

		Format_Token::next_line();
		Format_Token::tabulation( --$tabs_number );

		echo '</p>';

		Format_Token::next_line();
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
