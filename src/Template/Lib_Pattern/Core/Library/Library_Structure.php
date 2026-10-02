<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Library;

use Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Structure\Structure;
use Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Code\Target;
defined( 'ABSPATH' ) || exit;

interface Library_Structure extends Library {
	public function get_structure( Target $target ): Structure;
}
