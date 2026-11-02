<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Rendering;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Html_Element;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Block\Block_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Nested_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Statement_Token;

/**
 * Renderers of the template tokens for an HTML renderer and an escaper. Engine-agnostic: the engine-specific forms are
 * provided by the tokens themselves.
 */
final class Token_Renderer {
	/**
	 * @return array<class-string, callable(Html_Element $element, string $content):string>
	 */
	public static function element_renderers(): array {
		return array(
			Statement_Token::class => fn( $element ) => $element instanceof Statement_Token ?
				$element->get_statement() :
				'',
			Block_Token::class     => fn( $element, string $content ) => $element instanceof Block_Token ?
				$element->get_open_html() . $content . $element->get_close_html() :
				'',
			Nested_Token::class    => fn( $element, string $content ) => $content,
		);
	}

	/**
	 * @return array<class-string, callable(object):string>
	 */
	public static function attr_renderers(): array {
		return array(
			Statement_Token::class => fn( object $token ) => $token instanceof Statement_Token ?
				$token->get_statement() :
				'',
		);
	}
}
