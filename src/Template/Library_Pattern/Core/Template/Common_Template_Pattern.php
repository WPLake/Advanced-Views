<?php


declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Template;

use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Assets\Resolver\Asset_Resolver;

defined( 'ABSPATH' ) || exit;

abstract class Common_Template_Pattern extends Template_Pattern_Base {
	private string $card_field_id;

	public function __construct( Asset_Resolver $asset_resolver, Field_Provider_Cluster $provider_cluster ) {
		parent::__construct( $asset_resolver, $provider_cluster );

		$this->card_field_id = '';
	}

	abstract protected function print_common_js_code( string $var_name ): void;

	abstract protected function print_common_css_code( string $field_selector, Cpt_Settings $cpt_settings ): void;

	abstract public function is_target_selection( Post_Selection_Settings $post_selection_settings ): bool;

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

	public function get_selection_items_wrapper_class( Post_Selection_Settings $post_selection_settings ): string {
		return '';
	}

	/**
	 * @return Html_Wrapper[]
	 */
	public function get_selection_item_outers( Post_Selection_Settings $post_selection_settings ): array {
		return array();
	}

	/**
	 * @return array<string,string>
	 */
	public function get_selection_shortcode_attrs( Post_Selection_Settings $post_selection_settings ): array {
		return array();
	}

	/**
	 * @return array{css:array<string,string>,js:array<string,string>}
	 */
	public function generate_code( Cpt_Settings $cpt_settings ): array {
		if ( $cpt_settings instanceof Post_Selection_Settings ) {
			return $this->is_target_selection( $cpt_settings ) ?
				$this->generate_selection_code( $cpt_settings ) :
				array(
					'css' => array(),
					'js'  => array(),
				);
		}

		return parent::generate_code( $cpt_settings );
	}

	/**
	 * @return array{css:array<string,string>,js:array<string,string>}
	 */
	protected function generate_selection_code( Post_Selection_Settings $selection_settings ): array {
		$code = array(
			'css' => array(),
			'js'  => array(),
		);

		$magic_selector = sprintf( '#%s', Post_Selection_Settings::MAGIC_CSS_SELECTOR );

		ob_start();
		$this->print_common_css_code( $magic_selector, $selection_settings );
		$css_code = (string) ob_get_clean();

		ob_start();
		$this->print_common_js_code( $this->card_field_id );
		$js_code = (string) ob_get_clean();

		$bem_name = $selection_settings->get_bem_name();
		$selector = sprintf( '.%s__%s', $bem_name, $this->card_field_id );

		if ( '' !== $css_code ) {
			ob_start();
			$this->print_code_piece( $this->card_field_id, $css_code );
			$code['css'][ $this->card_field_id ] = (string) ob_get_clean();
		}

		if ( '' !== $js_code ) {
			ob_start();
			$this->print_js_code_piece(
				$this->card_field_id,
				$js_code,
				$selector,
				false
			);
			$code['js'][ $this->card_field_id ] = (string) ob_get_clean();
		}

		return $code;
	}
}
