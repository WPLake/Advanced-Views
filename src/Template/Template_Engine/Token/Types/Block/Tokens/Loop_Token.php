<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Block\Tokens;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Html_Element;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Block\Block_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Token_Base;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Token_Printer;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Statement_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory;

/**
 * @phpstan-import-type Html_Node from Html_Element
 */
abstract class Loop_Token extends Token_Base implements Block_Token {
	protected const LOOP_INDEX_KEY = 'index';

	protected Variable_Token $index_var;

	protected ?Statement_Token $source_var = null;
	protected ?Variable_Token $item_var    = null;

	/**
	 * @var Html_Node[]
	 */
	protected array $body = array();

	/**
	 * @param class-string<Token_Printer> $printer_class
	 */
	public function __construct( string $printer_class, Token_Factory $token_factory ) {
		parent::__construct( $printer_class );

		$this->index_var = $token_factory->variable( self::LOOP_INDEX_KEY );
	}

	// source can be not just a variable, but also a function call.
	public function set_source_variable( Statement_Token $source_var ): self {
		$this->source_var = $source_var;

		return $this;
	}

	public function set_item_variable( Variable_Token $item_var ): self {
		$this->item_var = $item_var;

		return $this;
	}

	public function get_index_variable(): Variable_Token {
		return $this->index_var;
	}

	/**
	 * @param Html_Node[] $body
	 */
	public function set_body( array $body ): self {
		$this->body = $body;

		return $this;
	}

	public function get_nodes(): array {
		return $this->body;
	}

	protected static function bare_token( ?Statement_Token $token ): string {
		return $token instanceof Statement_Token ?
			$token->get_token() :
			'';
	}
}
