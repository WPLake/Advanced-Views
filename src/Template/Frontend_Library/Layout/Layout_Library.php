<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Layout;

use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Template\Pattern\Template_Pattern;

defined( 'ABSPATH' ) || exit;

interface Layout_Library extends Frontend_Library {
	public function maybe_activate_layout( Layout_Settings $layout_settings ): void;

	/**
	 * @return array<string,string>
	 */
	public function generate_layout_css( Layout_Settings $layout_settings ): array;

	/**
	 * @return array<string,string>
	 */
	public function generate_layout_js( Layout_Settings $layout_settings ): array;

	/**
	 * The markup declaration of the field, or null when the library adds no markup for it.
	 *
	 * @return class-string<Template_Pattern>|null
	 */
	public function get_template_pattern( Field_Settings $field_settings, Layout_Settings $layout_settings ): ?string;
}
