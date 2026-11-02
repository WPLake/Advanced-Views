<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Twig\Tokens;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Tokens\Block\Tokens\Loop_Token;

final class Twig_Loop extends Loop_Token {
	public function get_open_html(): string {
		$index  = $this->index_var->get_token();
		$item   = self::bare_token( $this->item_var );
		$source = self::bare_token( $this->source_var );

		return sprintf( '{%% for %s, %s in %s %%}', $index, $item, $source );
	}

	public function get_close_html(): string {
		return '{% endfor %}';
	}
}
