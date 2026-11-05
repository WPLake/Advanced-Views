<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Core\Web_Component\Web_Component_Factory;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Policy\Classes_Generator;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Policy\Row_Policy;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Row\Nested_Row_Builder;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Tokens\IF_Token;

final class Layout_Builder {
	private Nested_Row_Builder $row_builder;
	private Row_Policy $row_policy;
	private Token_Factory_Storage $token_factory_storage;
	private Web_Component_Factory $web_component_factory;

	public function __construct( Nested_Row_Builder $row_builder, Row_Policy $row_policy, Token_Factory_Storage $token_factory_storage, Web_Component_Factory $web_component_factory ) {
		$this->row_builder           = $row_builder;
		$this->row_policy            = $row_policy;
		$this->token_factory_storage = $token_factory_storage;
		$this->web_component_factory = $web_component_factory;
	}

	public function compose_layout( Layout_Settings $layout_settings ): Html_Tag {
		$token_factory = $this->token_factory_storage->resolve_token_factory( $layout_settings->template_engine );

		$root_class = Classes_Generator::generate_root_class( $token_factory, $layout_settings );
		$rows       = $this->rows( $layout_settings );

		return $this->web_component_factory
			->create_web_component( $layout_settings, $rows )
			->attributes( array( 'class' => $root_class ) );
	}

	/**
	 * @return IF_Token[]
	 */
	protected function rows( Layout_Settings $layout_settings ): array {
		$rows = array();

		foreach ( $layout_settings->items as $item ) {
			$field_meta = $item->field->get_field_meta();
			$row_type   = $this->row_policy->resolve_row_type( $field_meta );

			if ( is_string( $row_type ) ) {
				$is_shown_when_empty = $this->row_policy->is_shown_when_empty( $field_meta, $item->field );

				$rows[] = $this->row_builder->compose_row_if(
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
}
