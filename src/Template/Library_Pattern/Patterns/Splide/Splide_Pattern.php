<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Patterns\Splide;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\JavaScript\Builder\JS_Pattern_Builder;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\JavaScript\Pattern\JS_Pattern;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Layout\Layout_Pattern_Base;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Selection\Selection_Code_Builder;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Selection\Selection_Pattern;

defined( 'ABSPATH' ) || exit;

abstract class Splide_Pattern extends Layout_Pattern_Base implements Selection_Pattern {
	const NAME = 'splide';

	private string $card_field_id;

	public function __construct( Field_Provider_Cluster $provider_cluster, JS_Pattern_Builder $js_pattern_builder ) {
		parent::__construct( $provider_cluster, $js_pattern_builder );

		$this->card_field_id = '';
	}

	/**
	 * @return class-string<JS_Pattern>
	 */
	abstract protected function get_js_pattern(): string;

	abstract protected function print_common_css_code( string $field_selector, Cpt_Settings $cpt_settings ): void;

	protected function set_card_field_id( string $card_field_id ): void {
		$this->card_field_id = $card_field_id;
	}

	protected function resolve_js_pattern( Field_Settings $field_settings, Layout_Settings $layout_settings ): ?string {
		return $this->get_js_pattern();
	}

	protected function print_css_code(
		string $field_selector,
		Field_Settings $field_settings,
		Layout_Settings $layout_settings
	): void {
		$this->print_common_css_code( $field_selector, $layout_settings );
	}

	/**
	 * @return array<string,string>
	 */
	public function generate_selection_css( Post_Selection_Settings $post_selection_settings ): array {
		$magic_selector = sprintf( '#%s', Post_Selection_Settings::MAGIC_CSS_SELECTOR );

		ob_start();
		$this->print_common_css_code( $magic_selector, $post_selection_settings );
		$css_code = (string) ob_get_clean();

		if ( '' !== $css_code ) {
			return array(
				$this->card_field_id => $css_code,
			);
		}

		return array();
	}

	/**
	 * @return array<string,string>
	 */
	public function generate_selection_js( Post_Selection_Settings $post_selection_settings ): array {
		$selector   = Selection_Code_Builder::get_selector( $post_selection_settings, $this->card_field_id );
		$js_pattern = $this->get_js_pattern();
		$js_snippet = $this->js_pattern_builder->build_single_item(
			$js_pattern,
			$this->card_field_id,
			$selector
		);

		if ( '' !== $js_snippet ) {
			return array(
				$this->card_field_id => $js_snippet,
			);
		}

		return array();
	}
}
