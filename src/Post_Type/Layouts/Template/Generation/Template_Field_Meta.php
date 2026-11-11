<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation;

use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Field_Context\Field_Context;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Row\Row_Composer;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Layout\Outers_Builder;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Layout\Pattern_Container;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Layout\Layout_Library;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;

defined( 'ABSPATH' ) || exit;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
final class Template_Field_Meta {
	private Field_Context $context;
	/**
	 * @var Layout_Library[]
	 */
	private array $field_assets;
	private Pattern_Container $patterns;
	private bool $is_with_field_wrapper;
	private bool $is_with_row_wrapper;
	private Token_Factory $token_factory;
	private Row_Composer $field_markup;

	public function __construct(
		Field_Context $context,
		Row_Composer $field_markup,
		Token_Factory $token_factory
	) {
		$this->context               = $context;
		$this->field_assets          = array();
		$this->patterns              = new Pattern_Container( array(), $context->get_layout_settings(), $context->get_field_settings() );
		$this->is_with_field_wrapper = false;
		$this->is_with_row_wrapper   = false;
		$this->token_factory         = $token_factory;
		$this->field_markup          = $field_markup;
	}

	public function get_context(): Field_Context {
		return $this->context;
	}

	public function get_field_markup(): Row_Composer {
		return $this->field_markup;
	}

	/**
	 * @return Html_Tag[]
	 */
	public function get_item_outers( string $item_id ): array {
		return $this->patterns->item_outers( $this->token_factory, $item_id );
	}

	/**
	 * @return array<string,string|Variable_Token>
	 */
	public function get_item_attributes( string $item_id ): array {
		return $this->patterns->item_attributes( $this->token_factory, $item_id );
	}

	/**
	 * @param Html_Tag[] $item_outers
	 * @param Html_Node[] $nodes
	 *
	 * @return Html_Node[]
	 */
	public function wrap_with_item_outers( array $item_outers, array $nodes ): array {
		return Outers_Builder::compose_outers( $item_outers, $nodes );
	}

	/**
	 * @return  Layout_Library[]
	 */
	public function get_field_assets(): array {
		return $this->field_assets;
	}

	/**
	 * @param Layout_Library[] $field_assets
	 */
	public function set_field_assets( array $field_assets ): void {
		$this->field_assets = $field_assets;
		$this->patterns     = new Pattern_Container( $field_assets, $this->context->get_layout_settings(), $this->context->get_field_settings() );
	}

	public function is_with_field_wrapper(): bool {
		return $this->is_with_field_wrapper;
	}

	public function set_is_with_field_wrapper( bool $is_with_field_wrapper ): void {
		$this->is_with_field_wrapper = $is_with_field_wrapper;
	}

	public function is_with_row_wrapper(): bool {
		return $this->is_with_row_wrapper;
	}

	public function set_is_with_row_wrapper( bool $is_with_row_wrapper ): void {
		$this->is_with_row_wrapper = $is_with_row_wrapper;
	}

	public function get_token_factory(): Token_Factory {
		return $this->token_factory;
	}
}
