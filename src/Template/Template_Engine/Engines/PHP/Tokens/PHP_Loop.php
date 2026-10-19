<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\PHP\Tokens;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Loop_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Template_Token;

final class PHP_Loop extends Loop_Token {
	public function print_token(): void {
		echo '<? foreach (';

		if ( $this->source_var instanceof Template_Token ) {
			$this->source_var->print_token();
		}

		echo ' as ';

		$this->index_var->print_token();
		echo ' => ';

		if ( $this->item_var instanceof Template_Token ) {
			$this->item_var->print_token();
		}

		echo '): ?>';

		if ( $this->body instanceof Template_Token ) {
			$this->body->print_token();
		}

		echo '<? endforeach; ?>';
	}
}
