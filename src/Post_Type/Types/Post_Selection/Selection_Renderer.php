<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Query\Core\Context\Query_Context;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Asset_Collector;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Library\Library_Asset_Collector;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Acf\Groups\Post_Selection_Settings;

/**
 * Renders a Post Selection into HTML and registers its assets.
 */
final class Selection_Renderer {
	private Selection_Factory $post_selection_factory;
	private Cpt_Asset_Collector $assets_collector;
	private Library_Asset_Collector $library_asset_collector;

	public function __construct(
		Selection_Factory $post_selection_factory,
		Cpt_Asset_Collector $assets_collector,
		Library_Asset_Collector $library_asset_collector
	) {
		$this->post_selection_factory  = $post_selection_factory;
		$this->assets_collector        = $assets_collector;
		$this->library_asset_collector = $library_asset_collector;
	}

	public function render(
		Post_Selection_Settings $post_selection_settings,
		Query_Context $query_context,
		bool $is_load_more = false,
		string $classes = ''
	): string {
		$selection = $this->post_selection_factory->create_selection( $post_selection_settings, $classes );
		$html      = $selection->render( $query_context, $is_load_more );

		$this->assets_collector->add_asset( $post_selection_settings );
		$this->library_asset_collector->activate_libraries( $post_selection_settings );

		return $html;
	}
}
