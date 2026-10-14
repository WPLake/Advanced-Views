<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Cpt\Pub;

use Org\Wplake\Advanced_Views\Plugin\Cpt\Plugin_Cpt_Base;

defined( 'ABSPATH' ) || exit;

class Public_Cpt_Base extends Plugin_Cpt_Base implements Public_Cpt {
	/**
	 * @var string[]
	 */
	protected array $shortcodes = array();
	protected string $shortcode = '';
	/**
	 * @var string[]
	 */
	protected array $rest_route_names = array();

	public function shortcodes(): array {
		return $this->shortcodes;
	}

	public function shortcode(): string {
		return $this->shortcode;
	}

	public function rest_route_names(): array {
		return $this->rest_route_names;
	}
}
