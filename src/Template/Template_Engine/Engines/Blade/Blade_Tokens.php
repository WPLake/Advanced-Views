<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Blade;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Blade\Statement\Functional\Blade_Comment;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Blade\Nested\Blade_Loop;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Blade\Nested\Blade_IF;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Blade\Statement\Variable\Blade_Assignment;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory_Base;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Functional\Comment_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Tokens\IF_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Functional\Functions_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Tokens\Loop_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Functional\Range_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Assignment_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Literal_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\PHP\Statement\Functional\PHP_Functions;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\PHP\Statement\Functional\PHP_Range;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\PHP\Statement\Variable\PHP_Literal;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\PHP\Statement\Variable\PHP_Variable;

final class Blade_Tokens extends Token_Factory_Base {
	public function comment( string $content ): Comment_Token {
		return new Blade_Comment(
			$this->printer_class,
			$this->escaper,
			$content
		);
	}

	public function variable( string $name ): Variable_Token {
		return new PHP_Variable(
			$this->printer_class,
			$this->escaper,
			$name
		);
	}

	public function if(): IF_Token {
		return new Blade_IF( $this->printer_class, $this->escaper );
	}

	public function loop(): Loop_Token {
		return new Blade_Loop(
			$this->printer_class,
			$this->escaper,
			$this
		);
	}

	public function assignment(): Assignment_Token {
		return new Blade_Assignment( $this->printer_class, $this->escaper );
	}

	public function literal( $value ): Literal_Token {
		return new PHP_Literal(
			$this->printer_class,
			$this->escaper,
			$value
		);
	}

	public function functions(): Functions_Token {
		return new PHP_Functions( $this );
	}

	public function range(): Range_Token {
		return new PHP_Range( $this->printer_class, $this->escaper );
	}
}
