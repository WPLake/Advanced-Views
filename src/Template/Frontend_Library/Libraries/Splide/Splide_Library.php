<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Splide;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\JavaScript\Builder\JS_Pattern_Builder;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Css\Builder\CSS_Builder;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Css\Pattern\CSS_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\JavaScript\Pattern\JS_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Layout\Layout_Library_Base;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Selection\Selection_Code_Builder;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Selection\Selection_Library;

defined( 'ABSPATH' ) || exit;

abstract class Splide_Library extends Layout_Library_Base implements Selection_Library {
	const NAME = 'splide';

	private string $card_field_id;

	public function __construct( Field_Provider_Cluster $provider_cluster, JS_Pattern_Builder $js_pattern_builder, CSS_Builder $css_builder ) {
		parent::__construct( $provider_cluster, $js_pattern_builder, $css_builder );

		$this->card_field_id = '';
	}

	/**
	 * @return class-string<JS_Pattern&CSS_Pattern>
	 */
	abstract protected function get_pattern(): string;

	protected function set_card_field_id( string $card_field_id ): void {
		$this->card_field_id = $card_field_id;
	}

	protected function resolve_js_pattern( Field_Settings $field_settings, Layout_Settings $layout_settings ): ?string {
		return $this->get_pattern();
	}

	protected function resolve_css_pattern( Field_Settings $field_settings, Layout_Settings $layout_settings ): ?string {
		return $this->get_pattern();
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
		$js_pattern = $this->get_pattern();
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
