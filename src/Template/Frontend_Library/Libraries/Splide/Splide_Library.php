<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Splide;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Builder\Script_Pattern_Builder;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Builder\Style_Builder;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Pattern\Style_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Pattern\Script_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Template\Pattern\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Layout\Layout_Library_Base;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Selection\Selection_Code_Builder;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Selection\Selection_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Splide\Asset_Providers\Script_Provider;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Splide\Asset_Providers\Style_Provider;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Splide\Patterns\Slider_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Splide\Patterns\Slider_Selection_Pattern;

defined( 'ABSPATH' ) || exit;

final class Splide_Library extends Layout_Library_Base implements Selection_Library {
	const NAME = 'splide';

	private string $card_field_id;
	private Script_Provider $script_provider;
	private Style_Provider $style_provider;

	public function __construct( Field_Provider_Cluster $provider_cluster, Script_Pattern_Builder $script_pattern_builder, Style_Builder $style_builder ) {
		parent::__construct( $provider_cluster, $script_pattern_builder, $style_builder );

		$this->set_auto_discover_name( 'splide' );

		$this->card_field_id   = 'items';
		$this->script_provider = new Script_Provider();
		$this->style_provider  = new Style_Provider();
	}

	public function get_js_library(): Script_Provider {
		return $this->script_provider;
	}

	public function get_css_library(): Style_Provider {
		return $this->style_provider;
	}

	/**
	 * @return string[]
	 */
	public static function get_field_front_assets( Field_Settings $field_settings ): array {
		return 'splide_v4' === $field_settings->slider_type ?
			array( self::NAME ) :
			array();
	}

	public function is_target_selection(Post_Selection_Settings $post_selection_settings ): bool {
		return 'splide_v4' === $post_selection_settings->slider_type;
	}

	public function maybe_activate_layout( Layout_Settings $layout_settings ): void {
		$target_fields = $this->get_target_fields( $layout_settings );

		$this->maybe_enable_splide( array() !== $target_fields );
	}

	public function maybe_activate_selection( Post_Selection_Settings $post_selection_settings ): void {
		$this->maybe_enable_splide( $this->is_target_selection( $post_selection_settings ) );
	}

	public function get_template_pattern( Field_Settings $field_settings, Layout_Settings $layout_settings ): string {
		return self::get_pattern();
	}

	public function get_selection_template_pattern( Post_Selection_Settings $post_selection_settings ): string {
		return self::get_selection_pattern();
	}

	/**
	 * @return array<string,string>
	 */
	public function generate_selection_css( Post_Selection_Settings $post_selection_settings ): array {
		// Card doesn't have 'ul'.
		return array();
	}

	/**
	 * @return array<string,string>
	 */
	public function generate_selection_js( Post_Selection_Settings $post_selection_settings ): array {
		$selector   = Selection_Code_Builder::get_selector( $post_selection_settings, $this->card_field_id );
		$js_pattern = self::get_pattern();
		$js_snippet = $this->script_pattern_builder->build_single_item(
			$js_pattern,
			$this->card_field_id,
			$selector
		);

		$is_filled = strlen( $js_snippet ) > 0;

		if ( $is_filled ) {
			return array(
				$this->card_field_id => $js_snippet,
			);
		}

		return array();
	}

	protected function resolve_js_pattern( Field_Settings $field_settings, Layout_Settings $layout_settings ): string {
		return self::get_pattern();
	}

	protected function resolve_css_pattern( Field_Settings $field_settings, Layout_Settings $layout_settings ): string {
		return self::get_pattern();
	}

	protected function maybe_enable_splide( bool $is_target ): void {
		$is_active = $this->script_provider->has_active_scripts();

		if ( $is_target && ! $is_active ) {
			$this->script_provider->enable();
			$this->style_provider->enable();
		}
	}

	/**
	 * @return class-string<Script_Pattern&Style_Pattern&Template_Pattern>
	 */
	protected static function get_pattern(): string {
		return Slider_Pattern::class;
	}

	/**
	 * @return class-string<Template_Pattern>
	 */
	protected static function get_selection_pattern(): string {
		return Slider_Selection_Pattern::class;
	}
}
