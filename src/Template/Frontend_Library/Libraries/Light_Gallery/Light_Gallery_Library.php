<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery\Asset_Providers\Style_Provider;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery\Asset_Providers\Script_Provider;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Builder\Style_Builder;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Builder\Script_Pattern_Builder;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Pattern\Script_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Pattern\Style_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Template\Pattern\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Layout\Layout_Library_Base;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery\Patterns\Gallery_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery\Patterns\Inline_Gallery_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery\Patterns\Lightbox_Pattern;

final class Light_Gallery_Library extends Layout_Library_Base {
	const NAME = 'light-gallery';

	private Script_Provider $script_provider;
	private Style_Provider $style_provider;

	public function __construct( Field_Provider_Cluster $provider_cluster, Script_Pattern_Builder $script_pattern_builder, Style_Builder $style_builder ) {
		parent::__construct( $provider_cluster, $script_pattern_builder, $style_builder );

		$this->set_auto_discover_name( 'light-gallery' );

		$this->script_provider = new Script_Provider();
		$this->style_provider  = new Style_Provider();
	}

	public function get_js_library(): Script_Provider {
		return $this->script_provider;
	}

	public function get_css_library(): Style_Provider {
		return $this->style_provider;
	}

	public function maybe_activate_layout( Layout_Settings $layout_settings ): void {
		$target_fields = $this->get_target_fields( $layout_settings );
		$is_target     = array() !== $target_fields;
		$is_active     = $this->script_provider->has_active_scripts();

		if ( $is_target && ! $is_active ) {
			$this->script_provider->enable();
			$this->style_provider->enable();
		}
	}

	public function get_template_pattern( Field_Settings $field_settings, Layout_Settings $layout_settings ): string {
		return self::get_pattern( $field_settings );
	}

	protected function resolve_js_pattern( Field_Settings $field_settings, Layout_Settings $layout_settings ): string {
		return self::get_pattern( $field_settings );
	}

	protected function resolve_css_pattern( Field_Settings $field_settings, Layout_Settings $layout_settings ): string {
		return self::get_pattern( $field_settings );
	}

	/**
	 * @return class-string<Script_Pattern&Style_Pattern&Template_Pattern>
	 */
	protected static function get_pattern( Field_Settings $field_settings ): string {
		if ( 'lightgallery_v2' === $field_settings->gallery_type ) {
			return Inline_Gallery_Pattern::class;
		}

		$is_multiple = $field_settings->get_field_meta()
			->is_multiple();

		return $is_multiple ?
			Gallery_Pattern::class :
			Lightbox_Pattern::class;
	}
}
