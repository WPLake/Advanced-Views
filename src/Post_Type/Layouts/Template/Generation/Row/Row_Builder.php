<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Row;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Assets\Front_Assets;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Field\Field_Builder;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Field\Label_Builder;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Field\Nested_Row_Builder;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Naming\Class_Names_Generator;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Policy\Inner_Row_Policy;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Wrappers\Field_Wrapper_Builder;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Wrappers\Outer_Builder;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Wrappers\Row_Wrapper_Builder;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Template\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Tokens\IF_Token;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
class Row_Builder implements Nested_Row_Builder {
	private Token_Factory_Storage $token_factory_storage;
	private Row_Context_Loader $context_loader;
	private Inner_Row_Policy $policy;
	private Field_Builder $field_builder;

	public function __construct( Field_Provider_Cluster $provider_cluster, Front_Assets $front_assets, Token_Factory_Storage $token_factory_storage ) {
		$this->token_factory_storage = $token_factory_storage;
		$this->context_loader        = new Row_Context_Loader( $provider_cluster, $front_assets, $token_factory_storage );
		$this->policy                = new Inner_Row_Policy( $provider_cluster );
		$this->field_builder         = new Field_Builder( $this->policy );
	}

	/**
	 * @param Template_Pattern[] $field_assets
	 *
	 * @return Html_Node[]
	 */
	public function compose_field(
		array $field_assets,
		Layout_Settings $layout_settings,
		?Item_Settings $item_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta,
		string $field_id
	): array {
		$context = $this->context_loader->load(
			$layout_settings,
			$item_settings,
			$field_settings,
			$field_meta,
			$field_id,
			'field',
			'',
			$field_assets
		);

		return $this->field_builder->compose_field( $context, $this );
	}

	/**
	 * @param Html_Node[]|null $custom_nodes Used in RepeaterField, replaces the field's own markup
	 *
	 * @return Html_Node[]
	 */
	public function compose_row(
		string $row_type,
		string $row_suffix,
		Layout_Settings $layout_settings,
		?Item_Settings $item_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta,
		string $field_id,
		?array $custom_nodes = null
	): array {
		$context = $this->context_loader->load(
			$layout_settings,
			$item_settings,
			$field_settings,
			$field_meta,
			$field_id,
			$row_type,
			$row_suffix
		);

		$content = $this->compose_content( $context, $custom_nodes );

		$has_row_wrapper = $this->policy->has_row_wrapper( $context );
		$field_layer     = $this->compose_field_layer( $context, $has_row_wrapper, $content );

		return $this->compose_row_layer( $context, $has_row_wrapper, $field_layer );
	}

	/**
	 * A top-level row of the layout: shown only if the field has a value (or always, if it's shown when empty).
	 */
	public function compose_row_if(
		string $row_type,
		bool $is_shown_when_empty,
		Layout_Settings $layout_settings,
		Item_Settings $item_settings,
		Field_Meta $field_meta
	): IF_Token {
		$token_factory = $this->token_factory_storage->resolve_token_factory( $layout_settings->template_engine );
		$field_id      = $item_settings->field->get_template_field_id();

		$body = $this->compose_row(
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

	/**
	 * The field's own markup (or the custom one) inside the pattern outers.
	 *
	 * @param Html_Node[]|null $custom_nodes
	 *
	 * @return Html_Node[]
	 */
	protected function compose_content( Row_Context $context, ?array $custom_nodes ): array {
		$field_nodes = is_array( $custom_nodes ) ?
			$custom_nodes :
			$this->field_builder->compose_field( $context->with_row_type( 'field' ), $this );

		$layout_settings = $context->get_layout_settings();
		$field_settings  = $context->get_field_settings();
		$field_id        = $context->get_field_id();
		$row_type        = $context->get_row_type();

		$patterns     = $context->get_pattern_container();
		$field_outers = $patterns->field_outers( $layout_settings, $field_settings, $field_id, $row_type );

		return Outer_Builder::compose_outers(
			$context->get_token_factory(),
			$field_outers,
			$field_nodes
		);
	}

	/**
	 * @param Html_Node[] $content
	 *
	 * @return Html_Node[]
	 */
	protected function compose_field_layer( Row_Context $context, bool $has_row_wrapper, array $content ): array {
		$has_field_wrapper = $this->policy->has_field_wrapper( $context );

		if ( $has_field_wrapper ) {
			$field_settings = $context->get_field_settings();

			$attrs         = $context->get_pattern_container()->field_wrapper_attrs( $field_settings, $context->get_field_id() );
			$pattern_class = string( $attrs, 'class' );
			unset( $attrs['class'] );

			$classes = Class_Names_Generator::field_wrapper(
				$context->get_layout_settings(),
				$field_settings,
				$context->get_row_suffix(),
				$has_row_wrapper,
				$pattern_class
			);

			$field_wrapper = Field_Wrapper_Builder::compose_wrapper(
				$this->policy->field_wrapper_tag( $context ),
				$classes,
				$attrs
			)->content( ...$content );

			return array( $field_wrapper );
		}

		return $content;
	}

	/**
	 * The label (inside or outside of the row) and the row wrapper around the field layer.
	 *
	 * @param Html_Node[] $field_layer
	 *
	 * @return Html_Node[]
	 */
	protected function compose_row_layer( Row_Context $context, bool $has_row_wrapper, array $field_layer ): array {
		$label_nodes = $this->compose_label_nodes( $context );

		$is_label_out  = $this->policy->is_label_out_of_row( $context );
		$label_outside = $is_label_out ?
			$label_nodes :
			array();
		$label_inside  = $is_label_out ?
			array() :
			$label_nodes;

		$row_layer = array(
			...$label_inside,
			...$field_layer,
		);

		if ( $has_row_wrapper ) {
			$row_wrapper = $this->compose_row_wrapper( $context )
								->content( ...$row_layer );

			$row_layer = array( $row_wrapper );
		}

		return array( ...$label_outside, ...$row_layer );
	}

	protected function compose_row_wrapper( Row_Context $context ): Html_Tag {
		$row_type       = $context->get_row_type();
		$field_settings = $context->get_field_settings();

		$pattern_class = $context->get_pattern_container()->row_wrapper_class( $row_type );
		$classes       = Class_Names_Generator::row_wrapper(
			$context->get_layout_settings(),
			$field_settings,
			$context->get_row_suffix(),
			$row_type,
			$pattern_class
		);

		return Row_Wrapper_Builder::compose_wrapper(
			$this->policy->row_wrapper_tag( $context ),
			$classes
		);
	}

	/**
	 * @return Html_Node[]
	 */
	protected function compose_label_nodes( Row_Context $context ): array {
		$has_label = $this->policy->has_label( $context );

		if ( $has_label ) {
			$classes = Class_Names_Generator::label( $context->get_layout_settings(), $context->get_field_settings() );

			return array(
				Label_Builder::compose_label(
					$context->get_token_factory(),
					$context->get_field_id(),
					$classes
				),
			);
		}

		return array();
	}
}
