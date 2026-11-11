<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Selection;

use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Library;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory;

defined( 'ABSPATH' ) || exit;

interface Selection_Library extends Frontend_Library {
	public function maybe_activate_selection( Post_Selection_Settings $post_selection_settings ): void;

	/**
	 * Called only when is_target_selection() is true.
	 *
	 * @return array<string,string>
	 */
	public function generate_selection_css( Post_Selection_Settings $post_selection_settings ): array;

	/**
	 * Called only when is_target_selection() is true.
	 *
	 * @return array<string,string>
	 */
	public function generate_selection_js( Post_Selection_Settings $post_selection_settings ): array;

	/**
	 * Gates the markup hooks and the code generation below.
	 */
	public function is_target_selection( Post_Selection_Settings $post_selection_settings ): bool;

	public function get_selection_items_wrapper_class( Post_Selection_Settings $post_selection_settings ): string;

	/**
	 * @return Html_Tag[]
	 */
	public function get_selection_item_outers( Post_Selection_Settings $post_selection_settings, Token_Factory $token_factory ): array;

	/**
	 * @return array<string,string>
	 */
	public function get_selection_shortcode_attrs( Post_Selection_Settings $post_selection_settings ): array;
}
