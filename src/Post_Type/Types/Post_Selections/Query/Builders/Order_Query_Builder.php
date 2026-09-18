<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selections\Query\Builders;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selections\Query\Post_Query_Builder;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selections\Query\Query_Utils;

final class Order_Query_Builder implements Post_Query_Builder {
	private Field_Provider_Cluster $provider_cluster;

	public function __construct( Field_Provider_Cluster $provider_cluster ) {
		$this->provider_cluster = $provider_cluster;
	}

	public function build_post_query( Post_Selection_Settings $selection_settings ): array {
		$meta_order_keys = array( 'meta_value', 'meta_value_num' );

		$arguments = array(
			'order'    => array(
				'value' => $selection_settings->order,
			),
			'orderby'  => array(
				'condition' => 'none' !== $selection_settings->order_by,
				'value'     => $selection_settings->order_by,
			),
			// @phpcs:ignore
			'meta_key'     => array(
				'condition' => in_array( $selection_settings->order_by, $meta_order_keys, true ),
				'value'     => fn() => $this->get_order_by_meta_key( $selection_settings ),
			),
		);

		return Query_Utils::filter_arguments( $arguments );
	}

	protected function get_order_by_meta_key( Post_Selection_Settings $selection ): ?string {
		$field_meta = $this->provider_cluster->get_field_meta(
			$selection->get_order_by_meta_field_source(),
			$selection->get_order_by_meta_acf_field_id()
		);

		if ( $field_meta->is_field_exist() ) {
			return $field_meta->get_name();
		}

		return null;
	}
}
