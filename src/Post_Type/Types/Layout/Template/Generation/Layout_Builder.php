<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Generation;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Layout_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Core\Web_Component\Web_Component_Factory;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Generation\Structure\Row_Policy;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Generation\Row\Row_Builder;
use Org\Wplake\Advanced_Views\Template\Html\Composite_Attribute;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Tokens\IF_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;
use function Org\Wplake\Advanced_Views\Template\Html\compose_attribute;

final class Layout_Builder {
	private Row_Builder $row_builder;
	private Row_Policy $row_policy;
	private Token_Factory_Storage $token_factory_storage;
	private Web_Component_Factory $web_component_factory;

	public function __construct( Row_Builder $row_builder, Row_Policy $row_policy, Token_Factory_Storage $token_factory_storage, Web_Component_Factory $web_component_factory ) {
		$this->row_builder           = $row_builder;
		$this->row_policy            = $row_policy;
		$this->token_factory_storage = $token_factory_storage;
		$this->web_component_factory = $web_component_factory;
	}

	public function compose_layout( Layout_Settings $layout_settings ): Html_Tag {
		$token_factory = $this->token_factory_storage->resolve_token_factory( $layout_settings->template_engine );

		$root_class = $this->generate_root_class( $token_factory, $layout_settings );
		$rows       = $this->compose_rows( $layout_settings );

		return $this->web_component_factory
			->create_web_component( $layout_settings, $rows )
			->attributes( array( 'class' => $root_class ) );
	}

	/**
	 * @return IF_Token[]
	 */
	protected function compose_rows( Layout_Settings $layout_settings ): array {
		$rows = array();

		foreach ( $layout_settings->items as $item ) {
			$field_meta = $item->field->get_field_meta();
			$row_type   = $this->row_policy->resolve_row_type( $field_meta );

			if ( is_string( $row_type ) ) {
				$is_shown_when_empty = $this->row_policy->is_shown_when_empty( $field_meta, $item->field );

				$rows[] = $this->compose_row_if(
					$row_type,
					$is_shown_when_empty,
					$layout_settings,
					$item,
					$field_meta
				);
			}
		}

		return $rows;
	}

	/**
	 * A top-level row of the layout: shown only if the field has a value (or always, if it's shown when empty).
	 */
	protected function compose_row_if(
		string $row_type,
		bool $is_shown_when_empty,
		Layout_Settings $layout_settings,
		Item_Settings $item_settings,
		Field_Meta $field_meta
	): IF_Token {
		$token_factory = $this->token_factory_storage->resolve_token_factory( $layout_settings->template_engine );
		$field_id      = $item_settings->field->get_template_field_id();

		$body = $this->row_builder->compose_row(
			$row_type,
			'',
			$layout_settings,
			$item_settings,
			$item_settings->field,
			$field_meta,
			$field_id
		);

		$value_var = $token_factory
			->variable( $field_id )
			->add_item_path( 'value' );
		$condition = $is_shown_when_empty ?
			$token_factory->literal( true ) :
			$value_var;

		$branch = $token_factory
			->if_branch()
			->set_condition( $condition )
			->set_body( $body );

		return $token_factory
			->if()
			->set_if_branch( $branch );
	}

	protected function generate_root_class( Token_Factory $token_factory, Layout_Settings $layout_settings ): Composite_Attribute {
		$parts = $layout_settings->is_classes_generation_enabled() ?
			self::resolve_root_parts( $token_factory, $layout_settings ) :
			array();

		return compose_attribute( $parts );
	}

	/**
	 * @return array<int,string|Variable_Token>
	 */
	protected static function resolve_root_parts( Token_Factory $token_factory, Layout_Settings $layout_settings ): array {
		$bem_name      = $layout_settings->get_bem_name();
		$variable_name = Hard_Layout_Cpt::variable_name();
		$parts         = array(
			$token_factory
				->variable( $variable_name )
				->add_item_path( 'classes' ),
			$bem_name,
		);

		// not necessary if the bemName is defined.
		$is_id_required = ! $layout_settings->has_unique_bem_name();

		if ( $is_id_required ) {
			$parts[] = sprintf( ' %s--id--', $bem_name );
			$parts[] = $token_factory
				->variable( $variable_name )
				->add_item_path( 'id' );
		}

		$parts[] = sprintf( ' %s--object-id--', $bem_name );
		$parts[] = $token_factory
			->variable( $variable_name )
			->add_item_path( 'object_id' );

		return $parts;
	}
}
