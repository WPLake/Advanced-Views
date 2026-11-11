<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Patterns\Light_Gallery;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Css\Pattern\CSS_Pattern;

final class Lightbox_Single_CSS implements CSS_Pattern {
	public static function get_css_rules(): array {
		return array(
			'img:hover' => array(
				'cursor' => 'zoom-in',
			),
		);
	}
}
