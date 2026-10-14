<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Post_Selections;

use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Post_Selection_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Labels\Cpt_Labels_Base;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Pub\Public_Cpt_Base;

defined( 'ABSPATH' ) || exit;

final class Selections_Cpt extends Public_Cpt_Base {
	public function __construct() {
		$this->cpt_name    = Hard_Post_Selection_Cpt::cpt_name();
		$this->slug_prefix = 'card_';
		$this->folder_name = 'post-selections';

		$this->shortcode        = 'avf-post-selection';
		$this->shortcodes       = array( $this->shortcode, 'avf_card', 'acf_cards' );
		$this->rest_route_names = array( 'post-selection', 'card' );

		$this->labels = new class() extends Cpt_Labels_Base {
			public function singular_name(): string {
				return esc_html__( 'Post Selection', 'acf-views' );
			}

			public function plural_name(): string {
				return esc_html__( 'Post Selections', 'acf-views' );
			}
		};
	}
}
