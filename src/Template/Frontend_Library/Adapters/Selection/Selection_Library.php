<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Selection;

use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Template\Pattern\Template_Pattern;

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
	 * Gates the markup and the code generation.
	 */
	public function is_target_selection( Post_Selection_Settings $post_selection_settings ): bool;

	/**
	 * The markup declaration of the selection, or null when the library adds no markup for it.
	 * Called only when is_target_selection() is true.
	 *
	 * @return class-string<Template_Pattern>|null
	 */
	public function get_selection_template_pattern( Post_Selection_Settings $post_selection_settings ): ?string;
}
