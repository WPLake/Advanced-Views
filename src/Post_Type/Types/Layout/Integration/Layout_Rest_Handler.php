<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Integration\Editor\Core\Shortcode\Rest_Handler_Base;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Layout_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Layout_Factory;

final class Layout_Rest_Handler extends Rest_Handler_Base {
	public function __construct(
		Layout_Cpt $layout_cpt,
		Layout_Settings_Storage $layouts_settings_storage,
		Layout_Factory $layout_factory
	) {
		parent::__construct( $layout_cpt, $layouts_settings_storage, $layout_factory );
	}

	protected function get_unique_id_prefix(): string {
		return Layout_Settings::UNIQUE_ID_PREFIX;
	}
}
