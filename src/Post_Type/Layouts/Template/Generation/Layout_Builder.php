<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Layout_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Core\Web_Component\Web_Component_Factory;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Template\Html\Composite_Attribute;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Tokens\IF_Branch;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Tokens\IF_Token;
use function Org\Wplake\Advanced_Views\Template\Html\compose_attribute;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
class Layout_Builder {
	private Row_Builder $row_builder;
	private Field_Provider_Cluster $provider_cluster;
	private Token_Factory_Storage $token_factory_storage;
	protected Web_Component_Factory $web_component_factory;

	public function __construct( Row_Builder $row_builder, Field_Provider_Cluster $provider_cluster, Token_Factory_Storage $token_factory_storage, Web_Component_Factory $web_component_factory ) {
		$this->row_builder           = $row_builder;
		$this->provider_cluster      = $provider_cluster;
		$this->token_factory_storage = $token_factory_storage;
		$this->web_component_factory = $web_component_factory;
	}

	public function compose_layout( Layout_Settings $layout_settings ): Html_Tag {
		$token_factory = $this->token_factory_storage->resolve_token_factory( $layout_settings->template_engine );

		$root_class = $this->root_class( $token_factory, $layout_settings );
		$rows       = $this->rows( $layout_settings );

		return $this->web_component_factory->create_web_component( $layout_settings, $rows )
			->attributes( array( 'class' => $root_class ) );
	}

	/**
	 * @return Html_Node[]
	 */
	protected function rows( Layout_Settings $layout_settings ): array {
		$rows = array();

		foreach ( $layout_settings->items as $item ) {
			$row = $this->row_if( $layout_settings, $item->field->get_field_meta(), $item );

			if ( null === $row ) {
				continue;
			}

			$rows[] = $row;
		}

		return $rows;
	}

	protected function row_if(
		Layout_Settings $layout_settings,
		Field_Meta $field_meta,
		Item_Settings $item_settings
	): ?IF_Token {
		if ( ! $field_meta->is_field_exist() ||
			// e.g. tab.
		$field_meta->is_ui_only() ) {
			return null;
		}

		$token_factory = $this->token_factory_storage->resolve_token_factory( $layout_settings->template_engine );

		$row_branch = $this->row_branch( $layout_settings, $field_meta, $item_settings );

		return $token_factory->if()->set_if_branch( $row_branch );
	}

	protected function row_branch(
		Layout_Settings $layout_settings,
		Field_Meta $field_meta,
		Item_Settings $item_settings
	): IF_Branch {
		$token_factory = $this->token_factory_storage->resolve_token_factory( $layout_settings->template_engine );

		$field_id   = $item_settings->field->get_template_field_id();
		$field_type = $field_meta->get_type();

		$is_condition_with_true_stub = $item_settings->field->is_visible_when_empty ||
							$this->provider_cluster->is_empty_value_supported_in_markup(
								$this->provider_cluster->get_vendor_name_by_key( $item_settings->field->key ),
								$field_type
							);

		$row_type = $this->provider_cluster->is_field_type_with_sub_fields(
			$field_meta->get_vendor_name(),
			$field_meta->get_type()
		) ?
			$field_meta->get_type() :
			'row';

		$markup = $this->row_builder->compose_row(
			$row_type,
			'',
			$layout_settings,
			$item_settings,
			$item_settings->field,
			$field_meta,
			$field_id
		);

		$value_var = $token_factory->variable( $field_id )
									->add_item_path( 'value' );
		$condition = $is_condition_with_true_stub ?
			$token_factory->literal( true ) :
			$value_var;

		return $token_factory->if_branch()
			->set_condition( $condition )
			->set_body( $markup );
	}

	protected function root_class( Token_Factory $token_factory, Layout_Settings $layout_settings ): Composite_Attribute {
		if ( Layout_Settings::CLASS_GENERATION_NONE === $layout_settings->classes_generation ) {
			return compose_attribute( array() );
		}

		$bem_name = $layout_settings->get_bem_name();
		$parts    = array(
			$token_factory->variable( Hard_Layout_Cpt::variable_name() )
							->add_item_path( 'classes' ),
			$bem_name,
		);

		// not necessary if the bemName is defined.
		if ( ! $layout_settings->has_unique_bem_name() ) {
			$parts[] = sprintf( ' %s--id--', $bem_name );
			$parts[] = $token_factory->variable( Hard_Layout_Cpt::variable_name() )
									->add_item_path( 'id' );
		}

		$parts[] = sprintf( ' %s--object-id--', $bem_name );
		$parts[] = $token_factory->variable( Hard_Layout_Cpt::variable_name() )
								->add_item_path( 'object_id' );

		return compose_attribute( $parts );
	}
}
