<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Library_Pattern\Patterns;

use Org\Wplake\Advanced_Views\Library_Pattern\Core\Template\Template_Pattern_Base;

defined( 'ABSPATH' ) || exit;

abstract class Masonry_Pattern extends Template_Pattern_Base {
	const NAME = 'acf-views-masonry';
}
