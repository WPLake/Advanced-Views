<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Template_Token_Base;

/**
 * By default, a statement is a value wrapped by the engine printer (escaped output, or unescaped for the raw form).
 * Statements without such a wrapper (e.g. comments) override both getters.
 */
abstract class Statement_Token_Base extends Template_Token_Base implements Statement_Token {
	protected bool $is_raw = false;

	public function display_as_raw( bool $is_raw ): self {
		$this->is_raw = $is_raw;

		return $this;
	}

	public function get_statement(): string {
		return $this->is_raw ?
			$this->printer_class::raw_statement( $this ) :
			$this->printer_class::statement( $this );
	}

	public function get_nodes(): array {
		return array();
	}
}
