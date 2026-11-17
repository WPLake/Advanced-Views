<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Twig\Nested;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Tokens\Loop_Token;

final class Twig_Loop extends Loop_Token {
	protected function get_open_statement(): string {
		$index  = $this->index_var->get_token();
		$item   = self::bare_token( $this->item_var );
		$source = self::bare_token( $this->source_var );

		return sprintf( '{%% for %s, %s in %s %%}', $index, $item, $source );
	}

	protected function get_end_statement(): string {
		return '{% endfor %}';
	}
}
