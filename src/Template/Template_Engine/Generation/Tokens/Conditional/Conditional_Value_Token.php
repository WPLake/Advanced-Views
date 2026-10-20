<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Conditional;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Template_Token_Base;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Template_Token;

class Conditional_Value_Token extends Template_Token_Base {
	protected ?Comparison_Token $comparison  = null;
	protected ?Template_Token $accept_value  = null;
	protected ?Template_Token $decline_value = null;

	public function set_comparison( Comparison_Token $comparison ): self {
		$this->comparison = $comparison;

		return $this;
	}

	public function set_accept_value( Template_Token $accept_value ): self {
		$this->accept_value = $accept_value;

		return $this;
	}

	public function set_decline_value( Template_Token $decline_value ): self {
		$this->decline_value = $decline_value;

		return $this;
	}

	public function print_token(): void {
		if ( $this->comparison instanceof Template_Token ) {
			$this->comparison->print_token();
		}

		echo ' ? ';

		if ( $this->accept_value instanceof Template_Token ) {
			$this->accept_value->print_token();
		}

		echo ' : ';

		if ( $this->decline_value instanceof Template_Token ) {
			$this->decline_value->print_token();
		}
	}

	public function display(): void {
		$this->printer_class::display( $this );
	}

	public function raw_display(): void {
		$this->printer_class::raw_display( $this );
	}
}
