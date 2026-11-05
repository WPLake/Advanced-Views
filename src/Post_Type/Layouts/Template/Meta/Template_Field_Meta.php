<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Meta;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Field_Data;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Row_Builder;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Wrappers\Outer_Builder;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Template\Html_Wrapper;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Template\Pattern_Container;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Template\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;

defined( 'ABSPATH' ) || exit;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
final class Template_Field_Meta extends Field_Data {
	/**
	 * @var Template_Pattern[]
	 */
	private array $field_assets;
	private Pattern_Container $patterns;
	private bool $is_with_field_wrapper;
	private bool $is_with_row_wrapper;
	private Token_Factory $token_factory;

	public function __construct(
		Layout_Settings $layout_settings,
		?Item_Settings $item_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta,
		Row_Builder $field_markup,
		Markup_Field $markup_field,
		Token_Factory $token_factory
	) {
		parent::__construct(
			$layout_settings,
			$item_settings,
			$field_settings,
			$field_meta,
			$field_markup,
			$markup_field
		);

		$this->field_assets          = array();
		$this->patterns              = new Pattern_Container( array() );
		$this->is_with_field_wrapper = false;
		$this->is_with_row_wrapper   = false;
		$this->token_factory         = $token_factory;
	}

	/**
	 * @return Html_Wrapper[]
	 */
	public function get_item_outers( string $field_id, string $item_id ): array {
		$layout_settings = $this->get_view_data();
		$field_settings  = $this->get_field_data();

		return $this->patterns->item_outers(
			$layout_settings,
			$field_settings,
			$field_id,
			$item_id
		);
	}

	/**
	 * @param Html_Wrapper[] $item_outers
	 * @param Html_Node[] $nodes
	 *
	 * @return Html_Node[]
	 */
	public function wrap_with_item_outers( array $item_outers, array $nodes ): array {
		$layout_settings = $this->get_view_data();
		$field_settings  = $this->get_field_data();
		$item_class      = $layout_settings->get_item_class( 'item', $field_settings );

		return Outer_Builder::compose_outers(
			$this->token_factory,
			$item_outers,
			$nodes,
			$item_class
		);
	}

	/**
	 * @return  Template_Pattern[]
	 */
	public function get_field_assets(): array {
		return $this->field_assets;
	}

	/**
	 * @param Template_Pattern[] $field_assets
	 */
	public function set_field_assets( array $field_assets ): void {
		$this->field_assets = $field_assets;
		$this->patterns     = new Pattern_Container( $field_assets );
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
