<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Rendering;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper_Base;
use Org\Wplake\Advanced_Views\Template\Html\Html_Element;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Nested_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Tokens\Section_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Statement_Token;

/**
 * Renderers of the template tokens for an HTML renderer and an escaper. Engine-agnostic: the engine-specific forms are
 * provided by the tokens themselves.
 */
final class Token_Renderer {
	public static function configure_renderer( Html_Renderer $renderer ): Html_Renderer {
		$renderers = self::element_renderers();

		return $renderer->add_element_renderers( $renderers );
	}

	public static function configure_escaper( Html_Escaper_Base $escaper ): Html_Escaper_Base {
		$renderers = self::attr_renderers();

		return $escaper->add_attr_renderers( $renderers );
	}

	/**
	 * @return array<class-string, callable(Html_Element $element, string $content):string>
	 */
	public static function element_renderers(): array {
		return array(
			Statement_Token::class => fn( $element ) => $element instanceof Statement_Token ?
				$element->get_statement() :
				'',
			Section_Token::class   => fn( $element, string $content ) => $element instanceof Section_Token ?
				$element->get_open_statement() . $content :
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
