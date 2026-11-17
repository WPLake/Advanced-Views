<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\PHP\Statement\Functional;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Functional\Comment_Token;

final class PHP_Comment extends Comment_Token {
	public function get_token(): string {
		$escaped_content = $this->escaper->esc_html( $this->content );

		return sprintf( '/* %s */', $escaped_content );
	}
}
