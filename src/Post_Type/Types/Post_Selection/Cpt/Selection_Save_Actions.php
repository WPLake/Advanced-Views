<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Cpt;

defined( 'ABSPATH' ) || exit;

use Exception;
use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Post_Selection_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Post_Query\Core\Post_Query_Builder;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Save_Actions;
use Org\Wplake\Advanced_Views\Post_Type\Core\Instance;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Library\Library_Cpt_Integrator;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Cpt\Template\Template_Code_Updater;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Post_Selection_Factory;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Integration\Template_Integration_Storage;

class Selection_Save_Actions extends Cpt_Save_Actions {

	private Template_Code_Updater $template_updater;
	private Post_Query_Builder $query_builder;
	private Post_Selection_Factory $post_selection_factory;
	/**
	 * @var Post_Selection_Settings
	 */
	private Post_Selection_Settings $post_selection_settings;
	private Selection_Settings_Storage $selection_settings_storage;

	public function __construct(
		Logger $logger,
		Selection_Settings_Storage $post_selections_settings_storage,
		Plugin $plugin,
		Post_Selection_Settings $post_selection_settings,
		Library_Cpt_Integrator $cpt_integrator,
		Template_Code_Updater $template_updater,
		Post_Query_Builder $query_builder,
		Post_Selection_Factory $post_selection_factory,
		Template_Integration_Storage $template_integration_storage
	) {
		// make a clone before passing to the parent, to make sure that external changes won't appear in this object.
		$post_selection_settings = $post_selection_settings->getDeepClone();

		parent::__construct(
			$logger,
			$post_selections_settings_storage,
			$plugin,
			$post_selection_settings,
			$cpt_integrator,
			$template_integration_storage,
			$post_selection_factory
		);

		$this->selection_settings_storage = $post_selections_settings_storage;
		$this->post_selection_settings    = $post_selection_settings;
		$this->template_updater           = $template_updater;
		$this->query_builder              = $query_builder;
		$this->post_selection_factory     = $post_selection_factory;
	}

	/**
	 * @param int|string $post_id
	 *
	 * @throws Exception
	 */
	public function perform_save_actions( $post_id, bool $is_skip_save = false ): ?Post_Selection_Settings {
		if ( $this->is_my_post( $post_id ) ) {
			// skip save, it'll be below.
			$selection_settings = parent::perform_save_actions( $post_id, true );

			// not just on null, but also on the type, for IDE.
			if ( $selection_settings instanceof Post_Selection_Settings ) {
				return $this->save_updated_selection( $selection_settings, $is_skip_save );
			}
		}

		return null;
	}

	protected function get_cpt_name(): string {
		return Hard_Post_Selection_Cpt::cpt_name();
	}

	protected function get_custom_markup_acf_field_name(): string {
		return Post_Selection_Settings::getAcfFieldName( Post_Selection_Settings::FIELD_CUSTOM_MARKUP );
	}

	protected function make_validation_instance(): Instance {
		return $this->post_selection_factory->make( $this->post_selection_settings );
	}

	protected function integrate_template_code( Cpt_Settings $cpt_data ): void {
		if ( ! $this->post_selection_settings->has_template() ) {
			return;
		}

		parent::integrate_template_code( $cpt_data );
	}

	protected function validate_custom_markup(): void {
		if ( ! $this->post_selection_settings->has_template() ) {
			return;
		}

		parent::validate_custom_markup();
	}

	protected function update_markup( Cpt_Settings $cpt_settings ): void {
		if ( $cpt_settings instanceof Post_Selection_Settings ) {
			$this->template_updater->update_markup( $cpt_settings );
		}
	}

	protected function update_query_preview( Post_Selection_Settings $selection_settings ): void {
		// @phpcs:ignore
		$selection_settings->query_preview = print_r(
			$this->query_builder->build_post_query( $selection_settings ),
			true
		);
	}

	protected function save_updated_selection( Post_Selection_Settings $selection_settings, bool $is_skip_save ): Post_Selection_Settings {
		$this->update_selection( $selection_settings );

		if ( ! $is_skip_save ) {
			$this->selection_settings_storage->save( $selection_settings );
		}

		return $selection_settings;
	}

	protected function update_selection( Post_Selection_Settings $selection_settings ): void {
		$this->update_query_preview( $selection_settings );

		if ( $selection_settings->has_template() ) {
			$this->template_updater->update_markup( $selection_settings );
			$this->template_updater->update_layout_rules( $selection_settings );
		}
	}
}
