<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Assets\Front_Assets;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Wrapper\Field_Wrapper_Builder;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Wrapper\Label_Builder;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Wrapper\Outer_Builder;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Wrapper\Row_Wrapper_Builder;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Template\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory_Storage;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
class Row_Builder {
	private Field_Provider_Cluster $provider_cluster;
	private Front_Assets $front_assets;
	private Token_Factory_Storage $token_factory_storage;
	private Row_Policy $policy;
	private Field_Builder $field_builder;

	public function __construct( Field_Provider_Cluster $provider_cluster, Front_Assets $front_assets, Token_Factory_Storage $token_factory_storage ) {
		$this->provider_cluster      = $provider_cluster;
		$this->front_assets          = $front_assets;
		$this->token_factory_storage = $token_factory_storage;
		$this->policy                = new Row_Policy( $provider_cluster );
		$this->field_builder         = new Field_Builder( $this->policy );
	}

	/**
	 * @param Template_Pattern[] $field_assets
	 *
	 * @return Html_Node[]
	 */
	public function compose_field_markup(
		array $field_assets,
		Layout_Settings $layout_settings,
		?Item_Settings $item_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta,
		string $field_id
	): array {
		$context = $this->create_context( $layout_settings, $field_settings );

		$context->set_field_meta( $field_meta )
			->set_item_settings( $item_settings )
			->set_field_id( $field_id )
			->set_row_type( 'field' )
			->set_patterns( $field_assets );
		$this->define_markup_field( $context );

		return $this->field_builder->compose_field( $context, $this );
	}

	/**
	 * @param Html_Node[]|null $custom_nodes Used in RepeaterField, replaces the field's own markup
	 *
	 * @return Html_Node[]
	 */
	public function compose_row_markup(
		string $row_type,
		string $row_suffix,
		Layout_Settings $layout_settings,
		?Item_Settings $item_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta,
		string $field_id,
		?array $custom_nodes = null
	): array {
		$field_assets = $this->resolve_field_assets( $field_settings );

		$context = $this->create_context( $layout_settings, $field_settings )
						->set_field_meta( $field_meta )
						->set_item_settings( $item_settings )
						->set_field_id( $field_id )
						->set_row_type( $row_type )
						->set_row_suffix( $row_suffix )
						->set_patterns( $field_assets );
		$this->define_markup_field( $context );

		$content = $this->compose_content( $context, $custom_nodes );

		$has_row_wrapper = $this->policy->has_row_wrapper( $context );
		$field_layer     = $this->compose_field_layer( $context, $has_row_wrapper, $content );

		return $this->compose_row_layer( $context, $has_row_wrapper, $field_layer );
	}

	/**
	 * @return Template_Pattern[]
	 */
	protected function resolve_field_assets( Field_Settings $field_settings ): array {
		$vendor_name  = $this->provider_cluster->get_vendor_name_by_key( $field_settings->key );
		$front_assets = $this->provider_cluster->get_field_front_assets( $vendor_name, $field_settings );

		return $this->front_assets->resolve_template_patterns( $front_assets );
	}

	protected function create_context( Layout_Settings $layout_settings, Field_Settings $field_settings ): Row_Context {
		$token_factory = $this->token_factory_storage->resolve_token_factory( $layout_settings->template_engine );

		return new Row_Context(
			$layout_settings,
			$field_settings,
			$token_factory
		);
	}

	protected function define_markup_field( Row_Context $context ): void {
		$field_settings = $context->get_field_settings();
		$field_meta     = $context->get_field_meta();
		$vendor_name    = $this->provider_cluster->get_vendor_name_by_key( $field_settings->key );
		$field_type     = $field_meta->get_type();
		$markup_field   = $this->provider_cluster->get_markup_field_instance( $vendor_name, $field_type );

		$context->set_markup_field( $markup_field );
	}

	/**
	 * The field's own markup (or the custom one) inside the pattern outers.
	 *
	 * @param Html_Node[]|null $custom_nodes
	 *
	 * @return Html_Node[]
	 */
	protected function compose_content( Row_Context $context, ?array $custom_nodes ): array {
		$field_context = $context
			->clone()
			->set_row_type( 'field' );
		$field_nodes   = is_array( $custom_nodes ) ?
			$custom_nodes :
			$this->field_builder->compose_field( $field_context, $this );

		$layout_settings = $context->get_layout_settings();
		$field_settings  = $context->get_field_settings();
		$field_id        = $context->get_field_id();
		$row_type        = $context->get_row_type();

		$patterns     = $context->get_pattern_container();
		$field_outers = $patterns->field_outers( $layout_settings, $field_settings, $field_id, $row_type );

		$token_factory = $context->get_token_factory();

		return Outer_Builder::compose_outers(
			$token_factory,
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
			$field_wrapper = Field_Wrapper_Builder::compose_wrapper( $context, $has_row_wrapper )
													->content( ...$content );

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
			$row_wrapper = Row_Wrapper_Builder::compose_wrapper( $context )
											->content( ...$row_layer );

			$row_layer = array( $row_wrapper );
		}

		return array( ...$label_outside, ...$row_layer );
	}

	/**
	 * @return Html_Node[]
	 */
	protected function compose_label_nodes( Row_Context $context ): array {
		$has_label = $this->policy->has_label( $context );

		if ( $has_label ) {
			return array( Label_Builder::compose_label( $context ) );
		}

		return array();
	}
}
