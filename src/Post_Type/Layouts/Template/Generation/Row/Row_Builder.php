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
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Row\Context\Row_Context;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Row\Context\Row_Context_Loader;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Wrappers\Row_Wrapper_Builder;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Template\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory_Storage;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
class Row_Builder implements Row_Composer {
	private Row_Context_Loader $context_loader;
	private Inner_Row_Policy $policy;
	private Field_Builder $field_builder;
	private Row_Wrapper_Builder $row_wrapper_builder;

	public function __construct( Field_Provider_Cluster $provider_cluster, Front_Assets $front_assets, Token_Factory_Storage $token_factory_storage ) {
		$this->context_loader      = new Row_Context_Loader(
			$provider_cluster,
			$front_assets,
			$token_factory_storage
		);
		$this->policy              = new Inner_Row_Policy( $provider_cluster );
		$this->field_builder       = new Field_Builder( $this->policy );
		$this->row_wrapper_builder = new Row_Wrapper_Builder( $this->policy );
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

		return $this->field_builder->compose_content( $context, $this );
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

		$field_layer = $this->field_builder->compose_field(
			$context,
			$this,
			$custom_nodes
		);

		return $this->compose_row_layer( $context, $field_layer );
	}

	/**
	 * The label (inside or outside of the row wrapper) and the row wrapper around the field layer.
	 *
	 * @param Html_Node[] $field_layer
	 *
	 * @return Html_Node[]
	 */
	protected function compose_row_layer( Row_Context $context, array $field_layer ): array {
		$label_nodes = self::compose_label_nodes( $context );

		if ( Inner_Row_Policy::is_label_out_of_row( $context ) ) {
			$wrapped = $this->row_wrapper_builder->wrap( $context, $field_layer );

			return array( ...$label_nodes, ...$wrapped );
		}

		return $this->row_wrapper_builder->wrap( $context, array( ...$label_nodes, ...$field_layer ) );
	}

	/**
	 * @return Html_Node[]
	 */
	protected static function compose_label_nodes( Row_Context $context ): array {
		return Inner_Row_Policy::has_label( $context ) ?
			array( Label_Builder::compose_label( $context ) ) :
			array();
	}
}
