<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Tokens;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Html_Element;
use Org\Wplake\Advanced_Views\Template\Html\Raw_Html;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Nested_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Template_Token_Base;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Rendering\Token_Printer;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Statement_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory;

/**
 * @phpstan-import-type Html_Node from Html_Element
 */
abstract class Loop_Token extends Template_Token_Base implements Nested_Token {
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
	public function __construct( string $printer_class, Html_Escaper $escaper, Token_Factory $token_factory ) {
		parent::__construct( $printer_class, $escaper );

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

	/**
	 * @return Html_Node[]
	 */
	public function get_nodes(): array {
		$open_statement = $this->get_open_statement();
		$end_statement  = $this->get_end_statement();

		return array(
			new Section_Token( $open_statement, $this->body ),
			new Raw_Html( $end_statement ),
		);
	}

	abstract protected function get_open_statement(): string;

	abstract protected function get_end_statement(): string;

	protected static function bare_token( ?Statement_Token $token ): string {
		return $token instanceof Statement_Token ?
			$token->get_token() :
			'';
	}
}
