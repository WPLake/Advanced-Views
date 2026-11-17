<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Twig;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory_Base;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Functional\Comment_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Conditional\Comparison_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Tokens\IF_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Functional\Functions_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Tokens\Loop_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Functional\Range_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Assignment_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Literal_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Twig\Statement\Conditional\Twig_Comparison;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Twig\Nested\Twig_IF;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Twig\Statement\Functional\Twig_Comment;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Twig\Statement\Functional\Twig_Functions;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Twig\Nested\Twig_Loop;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Twig\Statement\Functional\Twig_Range;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Twig\Statement\Variable\Twig_Assignnment;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Twig\Statement\Variable\Twig_Literal;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\Twig\Statement\Variable\Twig_Variable;

final class Twig_Tokens extends Token_Factory_Base {
	public function comment( string $content ): Comment_Token {
		return new Twig_Comment(
			$this->printer_class,
			$this->escaper,
			$content
		);
	}

	public function variable( string $name ): Variable_Token {
		return new Twig_Variable(
			$this->printer_class,
			$this->escaper,
			$name
		);
	}

	public function if(): IF_Token {
		return new Twig_IF( $this->printer_class, $this->escaper );
	}

	public function loop(): Loop_Token {
		return new Twig_Loop(
			$this->printer_class,
			$this->escaper,
			$this
		);
	}

	public function assignment(): Assignment_Token {
		return new Twig_Assignnment( $this->printer_class, $this->escaper );
	}

	public function comparison(): Comparison_Token {
		return new Twig_Comparison( $this->printer_class, $this->escaper );
	}

	public function literal( $value ): Literal_Token {
		return new Twig_Literal(
			$this->printer_class,
			$this->escaper,
			$value
		);
	}

	public function functions(): Functions_Token {
		return new Twig_Functions( $this );
	}

	public function range(): Range_Token {
		return new Twig_Range( $this->printer_class, $this->escaper );
	}
}
