<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Bridge\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Bridge\Advanced_Views;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Layout_Shortcode;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Integration\Post_Selection_Shortcode;
use Org\Wplake\Advanced_Views\Template\Html\Html_Renderer;

final class Shortcode_Renderers implements Actor {
	private Layout_Shortcode $layout_shortcode;
	private Post_Selection_Shortcode $selection_shortcode;
	private Html_Renderer $html_renderer;

	public function __construct(
		Layout_Shortcode $layout_shortcode,
		Post_Selection_Shortcode $selection_shortcode,
		Html_Renderer $html_renderer
	) {
		$this->layout_shortcode    = $layout_shortcode;
		$this->selection_shortcode = $selection_shortcode;
		$this->html_renderer       = $html_renderer;
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		return true;
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		Advanced_Views::$layout_renderer         = $this->layout_shortcode;
		Advanced_Views::$post_selection_renderer = $this->selection_shortcode;
		Advanced_Views::$html_renderer           = $this->html_renderer;
	}
}
