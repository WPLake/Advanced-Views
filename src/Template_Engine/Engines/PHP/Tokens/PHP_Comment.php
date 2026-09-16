<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template_Engine\Engines\PHP\Tokens;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template_Engine\Core\Generation\Tokens\Comment_Token;

final class PHP_Comment extends Comment_Token {
	public function print(): void {
		printf(
			'/* %s */',
			esc_html( $this->content )
		);
	}
}
