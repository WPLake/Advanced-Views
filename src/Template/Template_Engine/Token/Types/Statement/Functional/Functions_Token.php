<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Functional;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Statement_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory;

abstract class Functions_Token {
	protected Token_Factory $token_factory;

	public function __construct( Token_Factory $token_factory ) {
		$this->token_factory = $token_factory;
	}

	public function include_inner_layout_for_flexible( string $field, string $classes ): Statement_Token {
		$views_variable = $this->token_factory
			->variable( $field )
			->add_item_path( 'layout_views' );
		$item_variable  = $this->token_factory->variable( 'item' );
		$class_value    = array( 'class' => $classes );
		$class_literal  = $this->token_factory->literal( $class_value );
		$function_name  = $this->include_inner_layout_for_flexible_name();

		$arguments = array(
			$views_variable,
			$item_variable,
			$class_literal,
		);

		return $this->create_raw_call( $function_name, $arguments );
	}

	public function include_inner_layout( string $field_id, string $data_field_id, string $inner_view_class ): Statement_Token {
		$layout_id_var = $this->token_factory
			->variable( $field_id )
			->add_item_path( 'layout_id' );
		$data_var      = $this->token_factory->variable( $data_field_id );
		$class_value   = array( 'class' => $inner_view_class );
		$class_literal = $this->token_factory->literal( $class_value );
		$function_name = $this->include_inner_layout_name();

		$arguments = array(
			$layout_id_var,
			$data_var,
			$class_literal,
		);

		return $this->create_raw_call( $function_name, $arguments );
	}

	/**
	 * @param mixed[] $settings
	 */
	public function paginate_links( array $settings ): Statement_Token {
		$default_settings = array(
			'prev_text' => '<',
			'next_text' => '>',
		);
		$settings         = array_merge( $default_settings, $settings );
		$settings_literal = $this->token_factory->literal( $settings );

		// it's a built-in WordPress function.
		return $this->create_raw_call( 'paginate_links', array( $settings_literal ) );
	}

	abstract protected function include_inner_layout_for_flexible_name(): string;

	abstract protected function include_inner_layout_name(): string;

	/**
	 * @param Statement_Token[] $arguments
	 */
	protected function create_raw_call( string $function_name, array $arguments ): Statement_Token {
		return $this->token_factory
			->function( $function_name )
			->set_arguments( $arguments )
			->display_as_raw( true );
	}
}
