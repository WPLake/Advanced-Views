<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Tokens\Statement;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Tokens\Core\Token_Base;

/**
 * By default, a statement is a value wrapped by the engine printer (escaped output, or unescaped for the raw form).
 * Statements without such a wrapper (e.g. comments) override both getters.
 */
abstract class Statement_Token_Base extends Token_Base implements Statement_Token {
	public function get_statement(): string {
		return $this->printer_class::statement( $this );
	}

	public function get_raw_statement(): string {
		return $this->printer_class::raw_statement( $this );
	}

	public function get_nodes(): array {
		return array();
	}
}
