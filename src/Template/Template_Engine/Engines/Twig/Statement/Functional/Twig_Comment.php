<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Twig\Statement\Functional;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Functional\Comment_Token;

final class Twig_Comment extends Comment_Token {
	public function get_token(): string {
		return sprintf( '{# %s #}', esc_html( $this->content ) );
	}
}
