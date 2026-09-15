<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Cpt_Base\Template_Base\Generation\Tokens;

defined( 'ABSPATH' ) || exit;

interface Template_Token {
	public function print(): void;
}
