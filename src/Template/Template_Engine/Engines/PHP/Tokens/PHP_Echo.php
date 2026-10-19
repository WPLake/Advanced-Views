<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\PHP\Tokens;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Echo_Token;

final class PHP_Echo extends Echo_Token {
	public function print_token(): void {
		echo '<?= ';

		if ( ! $this->is_raw ) {
			echo 'esc_html(';
		}

		$this->content->print_token();

		if ( ! $this->is_raw ) {
			echo ')';
		}

		echo '; ?>';
	}
}
