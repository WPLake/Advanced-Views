<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Lightbox\Patterns;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\CSS\Pattern\CSS_Pattern;

final class Lightbox_Pattern implements CSS_Pattern {
	public static function get_css_rules(): array {
		return array(
			'list-style' => 'none',
			':hover'     => array(
				'cursor' => 'zoom-in',
			),
		);
	}
}
