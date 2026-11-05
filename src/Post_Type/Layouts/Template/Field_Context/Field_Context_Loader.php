<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Field_Context;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;

defined( 'ABSPATH' ) || exit;

final class Field_Context_Loader {
	private Field_Provider_Cluster $provider_cluster;

	public function __construct( Field_Provider_Cluster $provider_cluster ) {
		$this->provider_cluster = $provider_cluster;
	}

	/**
	 * @return Field_Context|null Null when the field type has no markup field
	 */
	public function load(
		Layout_Settings $layout_settings,
		?Item_Settings $item_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta
	): ?Field_Context {
		$vendor_name  = $this->provider_cluster->get_vendor_name_by_key( $field_settings->key );
		$field_type   = $field_meta->get_type();
		$markup_field = $this->provider_cluster->get_markup_field_instance( $vendor_name, $field_type );

		if ( $markup_field instanceof Markup_Field ) {
			return new Field_Context(
				$layout_settings,
				$item_settings,
				$field_settings,
				$field_meta,
				$markup_field
			);
		}

		return null;
	}
}
