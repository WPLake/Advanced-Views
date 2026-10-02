<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Library;

use Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Code\Code_Piece;
use Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Code\Target;
defined( 'ABSPATH' ) || exit;

interface Library_Code extends Library {
	public function generate_code( Target $target ): Code_Piece;
}
