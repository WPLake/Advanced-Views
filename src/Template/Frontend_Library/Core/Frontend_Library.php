<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Css\Provider\CSS_Provider;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\JavaScript\Provider\JS_Provider;

defined( 'ABSPATH' ) || exit;

interface Frontend_Library {
	public function get_name(): string;

	public function get_auto_discover_name(): string;

	public function get_js_library(): ?JS_Provider;

	public function get_css_library(): ?CSS_Provider;
}
