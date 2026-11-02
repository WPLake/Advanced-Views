<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Functional\Comment_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Conditional\Comparison_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Conditional\Conditional_Value_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Tokens\IF_Branch;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Tokens\IF_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Functional\Function_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Functional\Functions_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Tokens\Loop_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Functional\Range_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Functional\Shortcode_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Assignment_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Literal_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;

/**
 * @phpstan-import-type Literal_Value from Literal_Token
 */
interface Token_Factory {
	public function comment( string $content ): Comment_Token;

	public function variable( string $name ): Variable_Token;

	public function if(): IF_Token;

	public function if_branch(): IF_Branch;

	public function loop(): Loop_Token;

	public function assignment(): Assignment_Token;

	/**
	 * @param Literal_Value $value
	 */
	public function literal( $value ): Literal_Token;

	public function conditional_value(): Conditional_Value_Token;

	public function comparison(): Comparison_Token;

	public function function( string $name ): Function_Token;

	public function functions(): Functions_Token;

	public function range(): Range_Token;

	public function shortcode( string $name ): Shortcode_Token;
}
