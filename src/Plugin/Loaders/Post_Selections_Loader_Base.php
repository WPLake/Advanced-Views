<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Loaders;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Engines_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Integrations\Tax_Field_Settings_Integration;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Integrations\Post_Selection_Settings_Integration;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Integrations\Meta_Field_Settings_Integration;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Selections_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Route_Detector;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Acf\Group_Integrations\Mount_Point_Settings_Integration;
use Org\Wplake\Advanced_Views\Acf\Acf_Utils;
use Closure;
use Org\Wplake\Advanced_Views\Plugin\Core\Hookable\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Module_Loader;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Assets_Reducer;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Gutenberg_Editor_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Table\Fs_Only_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Core\Cpt_Item_Picker;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Post_Selections_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Selection_Git_Box;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Selection_Git_Tabs;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Selection_Interactive_Fields;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Selection_Layout_Integration;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Selection_Meta_Boxes;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Selection_Save_Actions;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Table\Post_Selections_Bulk_Validation_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Table\Post_Selections_Pre_Built_Tab;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Cpt\Table\Post_Selections_Table;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Integration\Gutenberg\Selection_Gutenberg_Block;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Integration\Post_Selection_Shortcode;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Post_Selection_Factory;

abstract class Post_Selections_Loader_Base extends Module_Loader {
	public Cpt_Assets_Reducer $cpt_assets_reducer;
	public Cpt_Gutenberg_Editor_Settings $cpt_gutenberg_editor_settings;
	public Post_Selections_Table $cpt_table;
	public Post_Selections_Cpt $cpt;
	public Fs_Only_Tab $fs_only_tab;
	public Selection_Meta_Boxes $meta_boxes;
	public Post_Selections_Bulk_Validation_Tab $bulk_validation_tab;
	public Post_Selections_Pre_Built_Tab $pre_built_tab;
	public Selection_Layout_Integration $layout_integration;
	public Post_Selection_Shortcode $shortcode;
	public Cpt_Item_Picker $item_picker;
	public Selection_Gutenberg_Block $block;
	public Selection_Save_Actions $save_actions;
	public Selection_Git_Tabs $git_tabs;
	public Selection_Git_Box $git_box;
	public Post_Selection_Factory $factory;
	public Selection_Interactive_Fields $interactive_fields;

	/**
	 * @var Closure():array<int, Hookable>
	 */
	protected Closure $make_elementor_integration;

	/**
	 * @return Hookable[]
	 */
	public function hookable( Route_Detector $route_detector ): array {
		$this->load_acf_groups( $route_detector );

		$this->add_plugin_extension(
			fn(): bool => did_action( 'elementor/loaded' ) > 0,
			$this->make_elementor_integration
		);

		return array_merge(
			$this->make_acf_integrations(),
			$this->make_hookables()
		);
	}

	protected function load_acf_groups( Route_Detector $route_detector ): void {
		$selection_cpt = $this->resolve( Selections_Cpt::class );

		if ( wp_doing_ajax() || $route_detector->is_cpt_admin_route( $selection_cpt->cpt_name() ) ) {
			Acf_Utils::load_groups(
				array(
					'Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Groups' => $this->resolve( Plugin::class )->get_plugin_path( 'src/Post_Type/Post_Selections/Acf/Groups' ),
				)
			);
		}
	}

	/**
	 * @return Hookable[]
	 */
	protected function make_acf_integrations(): array {
		$selection_cpt    = $this->resolve( Selections_Cpt::class );
		$provider_cluster = $this->resolve( Field_Provider_Cluster::class );
		$plugin           = $this->resolve( Plugin::class );

		return array(
			new Post_Selection_Settings_Integration(
				$selection_cpt->cpt_name(),
				$provider_cluster,
				$this->resolve( Layouts_Cpt::class ),
				$this->resolve( Engines_Storage::class )
			),
			// metaField is a part of the Meta Filter, so we use the selection CPT here.
			new Meta_Field_Settings_Integration( $selection_cpt->cpt_name(), $provider_cluster, $plugin ),
			new Tax_Field_Settings_Integration( $selection_cpt->cpt_name(), $provider_cluster, $plugin ),
			new Mount_Point_Settings_Integration( $selection_cpt->cpt_name() ),
		);
	}

	/**
	 * @return Hookable[]
	 */
	protected function make_hookables(): array {
		return array(
			$this->cpt,
			$this->cpt_table,
			$this->fs_only_tab,
			$this->bulk_validation_tab,
			$this->pre_built_tab,
			$this->cpt_assets_reducer,
			$this->cpt_gutenberg_editor_settings,
			$this->meta_boxes,
			$this->save_actions,
			$this->layout_integration,
			$this->shortcode,
			$this->item_picker,
			$this->block,
			$this->git_tabs,
			$this->git_box,
			$this->interactive_fields,
		);
	}
}
