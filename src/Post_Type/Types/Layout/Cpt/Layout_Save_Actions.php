<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Cpt;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Layout_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Cpt_Save_Actions;
use Org\Wplake\Advanced_Views\Post_Type\Core\Instance;
use Org\Wplake\Advanced_Views\Post_Type\Integration\Library\Library_Cpt_Integrator;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Layout_Factory;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Source;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Template\Rendering\Markup_Provider;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Integration\Template_Integration_Storage;

class Layout_Save_Actions extends Cpt_Save_Actions {
	private Markup_Provider $markup_provider;
	private Layout_Settings $layout_settings;
	private Layout_Factory $layout_factory;
	private Layout_Settings_Storage $layouts_settings_storage;

	public function __construct(
		Logger $logger,
		Layout_Settings_Storage $layouts_settings_storage,
		Plugin $plugin,
		Layout_Settings $layout_settings,
		Library_Cpt_Integrator $cpt_integrator,
		Markup_Provider $markup_provider,
		Layout_Factory $layout_factory,
		Template_Integration_Storage $template_integration_storage
	) {
		// make a clone before passing to the parent, to make sure that external changes won't appear in this object.
		$layout_settings = $layout_settings->getDeepClone();

		parent::__construct(
			$logger,
			$layouts_settings_storage,
			$plugin,
			$layout_settings,
			$cpt_integrator,
			$template_integration_storage,
			$layout_factory
		);

		$this->layouts_settings_storage = $layouts_settings_storage;
		$this->layout_settings          = $layout_settings;
		$this->markup_provider          = $markup_provider;
		$this->layout_factory           = $layout_factory;
	}

	public function update_markup( Cpt_Settings $cpt_settings ): void {
		if ( ! ( $cpt_settings instanceof Layout_Settings ) ) {
			return;
		}

		ob_start();
		// pageId 0, so without CSS, also skipCache and customMarkup.
		$this->markup_provider->print_markup( $cpt_settings, 0, '', true, true );
		$view_markup = (string) ob_get_clean();

		$cpt_settings->markup = $view_markup;
	}

	// public for tests.
	public function get_unique_field_id( Layout_Settings $layout_settings, Item_Settings $item_settings, string $name ): string {
		$is_unique = true;

		foreach ( $layout_settings->items as $item ) {
			$is_other_item = $item !== $item_settings;
			$is_same_id    = $item->field->id === $name;
			$is_duplicate  = $is_other_item && $is_same_id;

			if ( $is_duplicate ) {
				$is_unique = false;
				break;
			}
		}

		if ( $is_unique ) {
			return $name;
		}

		$next_name = sprintf( '%s2', $name );

		return $this->get_unique_field_id( $layout_settings, $item_settings, $next_name );
	}

	public function perform_save_actions( $post_id, bool $is_skip_save = false ): ?Layout_Settings {
		if ( $this->is_my_post( $post_id ) ) {
			return $this->perform_layout_save_actions( $post_id, $is_skip_save );
		}

		return null;
	}

	protected function get_cpt_name(): string {
		return Hard_Layout_Cpt::cpt_name();
	}

	protected function get_custom_markup_acf_field_name(): string {
		return Layout_Settings::getAcfFieldName( Layout_Settings::FIELD_CUSTOM_MARKUP );
	}

	protected function make_validation_instance(): Instance {
		$post_id        = $this->get_acf_ajax_post_id();
		$view_post      = get_post( $post_id );
		$view_unique_id = $view_post->post_name ?? '';

		return $this->layout_factory->create_layout( new Source(), $view_unique_id, 0, $this->layout_settings );
	}

	protected function update_identifiers( Layout_Settings $layout_settings ): void {
		foreach ( $layout_settings->items as $item ) {
			$item->field->id = $this->resolve_field_id( $layout_settings, $item );
		}
	}

	protected function resolve_field_id( Layout_Settings $layout_settings, Item_Settings $item ): string {
		$field_id        = $item->field->id;
		$is_id_filled    = strlen( $field_id ) > 0;
		$is_match_failed = false === preg_match( '/^[a-zA-Z0-9_\-]+$/', $field_id );
		$is_id_broken    = $is_id_filled && $is_match_failed;

		$field_id = $is_id_broken ?
			'' :
			$field_id;

		$is_id_filled = strlen( $field_id ) > 0;
		$unique_id    = $this->get_unique_field_id( $layout_settings, $item, $field_id );
		$is_id_kept   = $is_id_filled && $field_id === $unique_id;

		if ( $is_id_kept ) {
			return $field_id;
		}

		$field_meta = $item->field->get_field_meta();

		if ( $field_meta->is_field_exist() ) {
			$safe_id = self::get_safe_field_id( $field_meta->get_name() );

			return $this->get_unique_field_id( $layout_settings, $item, $safe_id );
		}

		return $field_id;
	}

	/**
	 * @return Layout_Settings|null Null if the settings can't be loaded.
	 */
	protected function perform_layout_save_actions( $post_id, bool $is_skip_save ): ?Layout_Settings {
		// do not save, it'll be below.
		$view_data = parent::perform_save_actions( $post_id, true );

		// not just check on null, but also on the type, for IDE.
		if ( $view_data instanceof Layout_Settings ) {
			$this->update_identifiers( $view_data );
			$this->update_markup( $view_data );

			if ( ! $is_skip_save ) {
				// it'll also update post fields, like 'comment_count'.
				$this->layouts_settings_storage->save( $view_data );
			}

			return $view_data;
		}

		return null;
	}

	protected static function get_safe_field_id( string $name ): string {
		// $Post$ fields have '_' prefix, remove it, otherwise looks bad in the markup
		$name = ltrim( $name, '_' );

		// lowercase is more readable.
		$name = strtolower( $name );

		// transform '_' and ' ' to '-' to follow the BEM standard (underscore only as a delimiter).
		$name = str_replace( array( '_', ' ' ), '-', $name );

		// remove all other characters.
		$name = preg_replace( '/[^a-z0-9\-]/', '', $name );

		return is_string( $name ) ?
			$name :
			'';
	}
}
