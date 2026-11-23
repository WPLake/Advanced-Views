<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Acf\Integration\Ajax_Select;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Cpt\Plugin_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\Cpt_Settings_Storage;

final class Cpt_Ajax_Select_Source implements Ajax_Select_Source {
	private Cpt_Settings_Storage $settings_storage;
	private Plugin_Cpt $plugin_cpt;

	public function __construct( Cpt_Settings_Storage $settings_storage, Plugin_Cpt $plugin_cpt ) {
		$this->settings_storage = $settings_storage;
		$this->plugin_cpt       = $plugin_cpt;
	}

	public function get_items(): array {
		return $this->settings_storage->get_unique_id_with_name_items_list();
	}

	public function get_add_new_url(): string {
		$cpt_name = $this->plugin_cpt->cpt_name();

		return sprintf( '/wp-admin/post-new.php?post_type=%s', $cpt_name );
	}

	public function get_add_new_label(): string {
		// translators: %s is the singular name of the CPT.
		$label_template = __( 'Add new %s', 'acf-views' );

		$labels        = $this->plugin_cpt->labels();
		$singular_name = $labels->singular_name();

		return sprintf( $label_template, $singular_name );
	}
}
