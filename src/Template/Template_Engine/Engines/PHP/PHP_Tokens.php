<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\PHP;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Tokens\Token_Factory_Base;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Tokens\Statement\Functional\Comment_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Tokens\Nested\Tokens\IF_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Tokens\Statement\Functional\Functions_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Tokens\Block\Tokens\Loop_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Tokens\Statement\Functional\Range_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Tokens\Statement\Variable\Assignment_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Tokens\Statement\Variable\Literal_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Tokens\Statement\Variable\Variable_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\PHP\Tokens\Conditional\PHP_IF;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\PHP\Tokens\PHP_Comment;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\PHP\Tokens\PHP_Printer;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\PHP\Tokens\PHP_Functions;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\PHP\Tokens\PHP_Loop;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\PHP\Tokens\PHP_Range;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\PHP\Tokens\Variable\PHP_Assignment;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\PHP\Tokens\Variable\PHP_Literal;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Engines\PHP\Tokens\Variable\PHP_Variable;

final class PHP_Tokens extends Token_Factory_Base {
	public function comment( string $content ): Comment_Token {
		return new PHP_Comment( $this->printer_class, $content );
	}

	public function variable( string $name ): Variable_Token {
		return new PHP_Variable( $this->printer_class, $name );
	}

	public function if(): IF_Token {
		return new PHP_IF( $this->printer_class );
	}

	public function loop(): Loop_Token {
		return new PHP_Loop( $this->printer_class, $this );
	}

	public function assignment(): Assignment_Token {
		return new PHP_Assignment( $this->printer_class );
	}

	public function literal( $value ): Literal_Token {
		return new PHP_Literal( $this->printer_class, $value );
	}

	public function functions(): Functions_Token {
		return new PHP_Functions( $this );
	}

	public function range(): Range_Token {
		return new PHP_Range( $this->printer_class );
	}
}
