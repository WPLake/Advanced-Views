<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Functional;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Token_Printer;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Statement_Token_Base;

abstract class Comment_Token extends Statement_Token_Base {
	protected string $content;

	/**
	 * @param class-string<Token_Printer> $printer_class
	 */
	public function __construct( string $printer_class, string $content ) {
		parent::__construct( $printer_class );

		$this->content = $content;
	}

	public function get_statement(): string {
		return $this->get_token();
	}

	public function get_raw_statement(): string {
		return $this->get_token();
	}
}
