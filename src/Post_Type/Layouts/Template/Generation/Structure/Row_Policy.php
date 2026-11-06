<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Structure;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;

final class Row_Policy {
	private Field_Provider_Cluster $provider_cluster;

	public function __construct( Field_Provider_Cluster $provider_cluster ) {
		$this->provider_cluster = $provider_cluster;
	}

	/**
	 * @return string|null Null means the field has no row (missing, or ui-only like a tab)
	 */
	public function resolve_row_type( Field_Meta $field_meta ): ?string {
		$is_renderable = $field_meta->is_field_exist() && ! $field_meta->is_ui_only();

		if ( $is_renderable ) {
			$field_type     = $field_meta->get_type();
			$vendor_name    = $field_meta->get_vendor_name();
			$has_sub_fields = $this->provider_cluster->is_field_type_with_sub_fields( $vendor_name, $field_type );

			return $has_sub_fields ?
				$field_type :
				'row';
		}

		return null;
	}

	public function is_shown_when_empty( Field_Meta $field_meta, Field_Settings $field_settings ): bool {
		$field_type         = $field_meta->get_type();
		$vendor_name        = $this->provider_cluster->get_vendor_name_by_key( $field_settings->key );
		$is_empty_supported = $this->provider_cluster->is_empty_value_supported_in_markup( $vendor_name, $field_type );

		return $field_settings->is_visible_when_empty || $is_empty_supported;
	}
}
