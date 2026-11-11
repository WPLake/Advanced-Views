<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Provider\Style_Provider;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Provider\Script_Provider;

defined( 'ABSPATH' ) || exit;

interface Frontend_Library {
	public function get_name(): string;

	public function get_auto_discover_name(): string;

	public function get_js_library(): ?Script_Provider;

	public function get_css_library(): ?Style_Provider;
}
