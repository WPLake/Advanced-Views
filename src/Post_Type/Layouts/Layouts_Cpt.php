<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts;

use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Layout_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Labels\Cpt_Labels_Base;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Pub\Public_Cpt_Base;

defined( 'ABSPATH' ) || exit;

final class Layouts_Cpt extends Public_Cpt_Base {
	public function __construct() {
		$this->cpt_name = Hard_Layout_Cpt::cpt_name();
		// replacement will require changes in ALL the "layout-pointer" fields values, like Post Selection -> Item layout.
		$this->slug_prefix = 'view_';
		$this->folder_name = 'layouts';

		$this->shortcode        = 'avf-layout';
		$this->shortcodes       = array( $this->shortcode, 'avf_view', 'acf_views' );
		$this->rest_route_names = array( 'layout', 'view' );

		$this->labels = new class() extends Cpt_Labels_Base{
			public function singular_name(): string {
				return esc_html__( 'Layout', 'acf-views' );
			}

			public function plural_name(): string {
				return esc_html__( 'Layouts', 'acf-views' );
			}
		};
	}
}
