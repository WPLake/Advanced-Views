<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Patterns\Lightbox;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Css\Pattern\CSS_Pattern;

final class Image_Lightbox_CSS implements CSS_Pattern {
	public static function get_css_rules(): array {
		return array(
			'list-style' => 'none',
			':hover'     => array(
				'cursor' => 'zoom-in',
			),
		);
	}
}
