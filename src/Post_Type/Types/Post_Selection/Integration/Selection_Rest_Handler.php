<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Integration;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Integration\Editor\Core\Shortcode\Rest_Handler_Base;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Selection_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Selection_Factory;

final class Selection_Rest_Handler extends Rest_Handler_Base {
	public function __construct(
		Selection_Cpt $selection_cpt,
		Selection_Settings_Storage $selections_settings_storage,
		Selection_Factory $selection_factory
	) {
		parent::__construct( $selection_cpt, $selections_settings_storage, $selection_factory );
	}

	protected function get_unique_id_prefix(): string {
		return Post_Selection_Settings::UNIQUE_ID_PREFIX;
	}
}
