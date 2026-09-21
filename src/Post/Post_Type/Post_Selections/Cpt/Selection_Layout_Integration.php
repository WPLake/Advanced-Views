<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post\Post_Type\Post_Selections\Cpt;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Base\Avf_User;
use Org\Wplake\Advanced_Views\Plugin\Base\Hooks_Interface;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Layout_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Post_Selection_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Plugin\Utils\Query_Arguments;
use Org\Wplake\Advanced_Views\Plugin\Utils\Route_Detector;
use Org\Wplake\Advanced_Views\Post\Post_Type\Core\Cpt_Settings_Creator;
use Org\Wplake\Advanced_Views\Post\Post_Type\Layouts\Cpt\Layout_Meta_Boxes;
use Org\Wplake\Advanced_Views\Post\Post_Type\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post\Post_Type\Post_Selections\Data_Storage\Selection_Settings_Storage;

class Selection_Layout_Integration extends Cpt_Settings_Creator implements Hooks_Interface {

	private Selection_Settings_Storage $post_selections_settings_storage;
	private Layout_Settings_Storage $layouts_settings_storage;
	private Selection_Save_Actions $post_selections_cpt_save_actions;

	public function __construct(
		Selection_Settings_Storage $post_selections_settings_storage,
		Layout_Settings_Storage $layouts_settings_storage,
		Selection_Save_Actions $post_selections_cpt_save_actions,
		Settings_Storage $settings
	) {
		parent::__construct( $settings );

		$this->post_selections_settings_storage = $post_selections_settings_storage;
		$this->layouts_settings_storage         = $layouts_settings_storage;
		$this->post_selections_cpt_save_actions = $post_selections_cpt_save_actions;
	}

	public function maybe_create_selection_for_layout(): void {
		$screen = get_current_screen();

		if ( null === $screen ) {
			return;
		}

		$from      = Query_Arguments::get_int_for_admin_action(
			Layout_Meta_Boxes::ARGUMENT_FROM_LAYOUT,
			Layout_Meta_Boxes::NONCE_MAKE_NEW
		);
		$from_post = 0 !== $from ?
			get_post( $from ) :
			null;

		$is_add_screen = 'post' === $screen->base &&
						'add' === $screen->action;

		if ( Hard_Post_Selection_Cpt::cpt_name() !== $screen->post_type ||
			false === $is_add_screen ||
			null === $from_post ||
			Hard_Layout_Cpt::cpt_name() !== $from_post->post_type ||
			'publish' !== $from_post->post_status ||
			! Avf_User::can_manage() ) {
			return;
		}

		$layout_settings = $this->layouts_settings_storage->get( $from_post->post_name );

		$selection_settings = $this->post_selections_settings_storage->create_new( 'publish', $layout_settings->title );

		if ( null === $selection_settings ) {
			return;
		}

		$selection_settings->acf_view_id  = $layout_settings->get_unique_id();
		$selection_settings->post_types[] = 'post';

		$this->set_defaults_from_settings( $selection_settings );

		// the data above will be saved in this call (link to cardData is in the storage).
		$this->post_selections_cpt_save_actions->perform_save_actions( $selection_settings->get_post_id() );

		wp_safe_redirect( $selection_settings->get_edit_post_link( 'redirect' ) );
		exit;
	}

	public function set_hooks( Route_Detector $route_detector ): void {
		if ( false === $route_detector->is_admin_route() ) {
			return;
		}

		self::add_action( 'current_screen', array( $this, 'maybe_create_selection_for_layout' ) );
	}
}
