<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Asset_Collector;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Library\Library_Asset_Collector;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;

/**
 * Renders a Layout (a single or a multiple one) into HTML, registers its assets, and guards against recursion.
 */
final class Layout_Renderer {
	private Layout_Factory $layout_factory;
	private Cpt_Asset_Collector $assets_collector;
	private Library_Asset_Collector $library_asset_collector;
	/**
	 * Used to avoid recursion with post_object/relationship fields
	 *
	 * @var array<string,bool>
	 */
	private array $displaying_views;

	public function __construct(
		Layout_Factory $layout_factory,
		Cpt_Asset_Collector $assets_collector,
		Library_Asset_Collector $library_asset_collector
	) {
		$this->layout_factory          = $layout_factory;
		$this->assets_collector        = $assets_collector;
		$this->library_asset_collector = $library_asset_collector;

		$this->displaying_views = array();
	}

	/**
	 * @param mixed[] $custom_arguments
	 * @param mixed[]|null $local_data
	 * @param int[] $item_ids Used only by the Multiple format
	 *
	 * @return string|null Null if nothing should be displayed (a recursion).
	 */
	public function render(
		Layout_Settings $layout_settings,
		Source $source,
		int $page_id,
		string $classes = '',
		array $custom_arguments = array(),
		?array $local_data = null,
		array $item_ids = array()
	): ?string {
		$layout = $this->layout_factory->create_layout(
			$source,
			$layout_settings->get_unique_id(),
			$page_id,
			$layout_settings,
			$classes,
			$local_data,
			$item_ids
		);
		$render = fn(): string => $this->render_instance( $layout, $custom_arguments );

		if ( $layout_settings->is_multiple() ) {
			return $render();
		}

		// recursionKey must consist from both. It's allowed to use the same View for a post_object field, but with another id.
		$recursion_key    = sprintf( '%s-%s', $layout_settings->get_unique_id(), $source->get_id() );
		$is_first_display = ! isset( $this->displaying_views[ $recursion_key ] );

		/**
		 * A post_object/relationship recursion (PostA links PostB, PostB links PostA) renders nothing, without an error,
		 * so the user can display PostB inside PostA.
		 */
		if ( $is_first_display ) {
			$this->displaying_views[ $recursion_key ] = true;

			$html = $render();

			unset( $this->displaying_views[ $recursion_key ] );

			return $html;
		}

		return null;
	}

	/**
	 * @param mixed[] $custom_arguments
	 */
	protected function render_instance( Layout_Instance $layout, array $custom_arguments ): string {
		$html = $layout->render( $custom_arguments );

		// mark as rendered, only if is not empty
		if ( is_string( $html ) ) {
			$layout_settings = $layout->get_view_data();

			$this->assets_collector->add_asset( $layout_settings );
			$this->library_asset_collector->activate_libraries( $layout_settings );

			return $html;
		}

		return '';
	}
}
