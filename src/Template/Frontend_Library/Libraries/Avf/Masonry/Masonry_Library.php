<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Masonry;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Layout\Layout_Library_Base;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Builder\Script_Pattern_Builder;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Provider\Script_Provider as Script_Provider_Interface;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Builder\Style_Builder;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Masonry\Asset_Providers\Script_Provider;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Masonry\Patterns\Masonry_Pattern;

class Masonry_Library extends Layout_Library_Base {
	const NAME = 'acf-views-masonry';

	private Script_Provider $script_provider;

	public function __construct( Field_Provider_Cluster $provider_cluster, Script_Pattern_Builder $script_pattern_builder, Style_Builder $style_builder ) {
		parent::__construct( $provider_cluster, $script_pattern_builder, $style_builder );

		$this->set_auto_discover_name( 'acf-views-masonry' );

		$this->script_provider = new Script_Provider();
	}

	public function get_js_library(): Script_Provider_Interface {
		return $this->script_provider;
	}

	public function get_template_pattern( Field_Settings $field_settings, Layout_Settings $layout_settings ): ?string {
		return Masonry_Pattern::class;
	}

	public function maybe_activate_layout( Layout_Settings $layout_settings ): void {
		$target_fields = $this->get_target_fields( $layout_settings );

		if ( array() !== $target_fields ) {
			$this->script_provider->enable();
		}
	}

	/**
	 * Nullable because Pro overrides it to skip old galleries, which use a global script.
	 */
	protected function resolve_js_pattern( Field_Settings $field_settings, Layout_Settings $layout_settings ): ?string {
		return Masonry_Pattern::class;
	}

	protected function resolve_css_pattern( Field_Settings $field_settings, Layout_Settings $layout_settings ): ?string {
		return Masonry_Pattern::class;
	}
}
