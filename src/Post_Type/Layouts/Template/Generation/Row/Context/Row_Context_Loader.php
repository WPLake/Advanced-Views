<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Row\Context;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Library_Container;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Layout\Pattern_Container;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Layout\Layout_Library;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory_Storage;

final class Row_Context_Loader {
	private Field_Provider_Cluster $provider_cluster;
	private Library_Container $library_container;
	private Token_Factory_Storage $token_factory_storage;

	public function __construct( Field_Provider_Cluster $provider_cluster, Library_Container $library_container, Token_Factory_Storage $token_factory_storage ) {
		$this->provider_cluster      = $provider_cluster;
		$this->library_container     = $library_container;
		$this->token_factory_storage = $token_factory_storage;
	}

	/**
	 * @param Layout_Library[]|null $patterns Null means the field's own patterns
	 */
	public function load(
		Layout_Settings $layout_settings,
		?Item_Settings $item_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta,
		string $field_id,
		string $row_type,
		string $row_suffix = '',
		?array $patterns = null
	): Row_Context {
		$token_factory = $this->token_factory_storage->resolve_token_factory( $layout_settings->template_engine );

		$vendor_name  = $this->provider_cluster->get_vendor_name_by_key( $field_settings->key );
		$field_type   = $field_meta->get_type();
		$markup_field = $this->provider_cluster->get_markup_field_instance( $vendor_name, $field_type );

		$patterns          = $patterns ?? $this->resolve_field_patterns( $field_settings );
		$pattern_container = new Pattern_Container( $patterns, $layout_settings, $field_settings );

		return new Row_Context(
			$layout_settings,
			$item_settings,
			$field_settings,
			$field_meta,
			$markup_field,
			$token_factory,
			$pattern_container,
			$field_id,
			$row_type,
			$row_suffix
		);
	}

	/**
	 * @return Layout_Library[]
	 */
	protected function resolve_field_patterns( Field_Settings $field_settings ): array {
		$vendor_name    = $this->provider_cluster->get_vendor_name_by_key( $field_settings->key );
		$pattern_names = $this->provider_cluster->get_field_front_assets( $vendor_name, $field_settings );

		$libraries = $this->library_container->get_by_names( $pattern_names );

		return array_filter(
			$libraries,
			fn( $library ) => $library instanceof Layout_Library
		);
	}
}
