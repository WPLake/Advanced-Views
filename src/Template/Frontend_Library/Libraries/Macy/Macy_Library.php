<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Macy;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Layout\Layout_Library_Base;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Builder\Script_Pattern_Builder;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Builder\Style_Builder;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Macy\Asset_Providers\Script_Provider;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Macy\Patterns\Macy_Pattern;

final class Macy_Library extends Layout_Library_Base {
	const NAME = 'macy';

	private Script_Provider $script_provider;

	public function __construct( Field_Provider_Cluster $provider_cluster, Script_Pattern_Builder $script_pattern_builder, Style_Builder $style_builder ) {
		parent::__construct( $provider_cluster, $script_pattern_builder, $style_builder );

		$this->set_auto_discover_name( 'macy' );

		$this->script_provider = new Script_Provider();
	}

	public function get_js_library(): Script_Provider {
		return $this->script_provider;
	}

	public function get_template_pattern( Field_Settings $field_settings, Layout_Settings $layout_settings ): string {
		return Macy_Pattern::class;
	}

	public function maybe_activate_layout( Layout_Settings $layout_settings ): void {
		$target_fields = $this->get_target_fields( $layout_settings );
		$is_target     = array() !== $target_fields;
		$is_active     = $this->script_provider->has_active_scripts();

		if ( $is_target && ! $is_active ) {
			$this->script_provider->enable();
		}
	}

	protected function resolve_js_pattern( Field_Settings $field_settings, Layout_Settings $layout_settings ): string {
		return Macy_Pattern::class;
	}

	protected function resolve_css_pattern( Field_Settings $field_settings, Layout_Settings $layout_settings ): string {
		return Macy_Pattern::class;
	}
}
