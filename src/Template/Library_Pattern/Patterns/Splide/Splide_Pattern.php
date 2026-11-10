<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Patterns\Splide;

use Org\Wplake\Advanced_Views\Assets\Asset_Resolver;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Layout\Layout_Pattern_Base;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Selection\Selection_Code_Builder;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Selection\Selection_Pattern;

defined( 'ABSPATH' ) || exit;

abstract class Splide_Pattern extends Layout_Pattern_Base implements Selection_Pattern {
	const NAME = 'splide';

	private string $card_field_id;

	public function __construct( Asset_Resolver $asset_resolver, Field_Provider_Cluster $provider_cluster ) {
		parent::__construct( $asset_resolver, $provider_cluster );

		$this->card_field_id = '';
	}

	abstract protected function print_common_js_code( string $var_name ): void;

	abstract protected function print_common_css_code( string $field_selector, Cpt_Settings $cpt_settings ): void;

	protected function set_card_field_id( string $card_field_id ): void {
		$this->card_field_id = $card_field_id;
	}

	protected function print_js_code( string $var_name, Field_Settings $field_settings, Layout_Settings $layout_settings ): void {
		$this->print_common_js_code( $var_name );
	}

	protected function print_css_code(
		string $field_selector,
		Field_Settings $field_settings,
		Layout_Settings $layout_settings
	): void {
		$this->print_common_css_code( $field_selector, $layout_settings );
	}

	/**
	 * @return array{css:array<string,string>,js:array<string,string>}
	 */
	public function generate_selection_code( Post_Selection_Settings $post_selection_settings ): array {
		$magic_selector = sprintf( '#%s', Post_Selection_Settings::MAGIC_CSS_SELECTOR );

		ob_start();
		$this->print_common_css_code( $magic_selector, $post_selection_settings );
		$css_code = (string) ob_get_clean();

		ob_start();
		$this->print_common_js_code( $this->card_field_id );
		$js_code = (string) ob_get_clean();

		return Selection_Code_Builder::build(
			$post_selection_settings,
			$this->get_auto_discover_name(),
			$this->card_field_id,
			$css_code,
			$js_code
		);
	}
}
