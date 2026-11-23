<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Acf\Integration\Groups;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Integration\Ajax_Select\Ajax_Select_Cluster;
use Org\Wplake\Advanced_Views\Acf\Integration\Ajax_Select\Ajax_Select_Field;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Layout_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Post_Selection_Cpt;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;

final class Custom_Acf_Field_Types extends Actor_Base implements Actor {
	private Ajax_Select_Cluster $ajax_select_cluster;
	private Html_Renderer $html_renderer;

	public function __construct( Ajax_Select_Cluster $ajax_select_cluster, Html_Renderer $html_renderer ) {
		$this->ajax_select_cluster = $ajax_select_cluster;
		$this->html_renderer       = $html_renderer;
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		$layout_cpt_name    = Hard_Layout_Cpt::cpt_name();
		$selection_cpt_name = Hard_Post_Selection_Cpt::cpt_name();

		$is_admin          = $route_detector->is_admin_route();
		$is_layout_edit    = $route_detector->is_cpt_admin_route( $layout_cpt_name, Route_Detector::CPT_EDIT );
		$is_selection_edit = $route_detector->is_cpt_admin_route( $selection_cpt_name, Route_Detector::CPT_EDIT );
		$is_ajax           = wp_doing_ajax();

		// must be present on both edit screens and during ajax requests.
		return $is_admin &&
			( $is_layout_edit || $is_selection_edit || $is_ajax );
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		self::add_action(
			'acf/include_field_types',
			array( $this, 'register_ajax_select_field' )
		);
	}

	public function register_ajax_select_field(): void {
		if ( function_exists( 'acf_register_field_type' ) ) {
			$field_type = new Ajax_Select_Field( $this->ajax_select_cluster, $this->html_renderer );

			acf_register_field_type( $field_type );
		}
	}
}
