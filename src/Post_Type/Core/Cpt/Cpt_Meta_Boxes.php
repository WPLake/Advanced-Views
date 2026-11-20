<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Cpt;

use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Html\Postbox_Printer;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;

defined( 'ABSPATH' ) || exit;

abstract class Cpt_Meta_Boxes extends Actor_Base implements Actor {
	private Postbox_Printer $postbox;
	private Plugin $plugin;
	protected Html_Renderer $html_renderer;

	public function __construct( Postbox_Printer $postbox, Plugin $plugin, Html_Renderer $html_renderer ) {
		$this->postbox       = $postbox;
		$this->plugin        = $plugin;
		$this->html_renderer = $html_renderer;
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		return $route_detector->is_admin_route();
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		self::add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
	}

	public function add_meta_boxes(): void {
		$cpt_name      = $this->get_cpt_name();
		$screens       = array( $cpt_name );
		$support_title = __( 'Help & Documentation', 'acf-views' );

		add_meta_box(
			'acf-views_support',
			$support_title,
			fn() => $this->postbox->print_postbox_support(),
			$screens,
			'side',
			'low'
		);

		if ( ! $this->plugin->is_pro_version() ) {
			$upgrade_title = __( 'Unlock with Pro', 'acf-views' );

			add_meta_box(
				'acf-views_upgrade',
				$upgrade_title,
				fn() => $this->postbox->print_postbox_upgrade(),
				$screens,
				'side',
				// above the Git box (default), which is registered separately.
				'core'
			);
		}
	}

	abstract protected function get_cpt_name(): string;

	protected function get_postbox(): Postbox_Printer {
		return $this->postbox;
	}
}
