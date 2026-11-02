<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Web_Component;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Utils\WP_Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Formatting\Text_Formatter;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Tag_Renderer;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Token_Renderer;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Token_Tree_Formatter;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
final class Web_Component_Printer {
	protected Html_Renderer $renderer;
	protected Text_Formatter $text_formatter;

	public function __construct(
		Token_Tree_Formatter $formatter,
		Text_Formatter $text_formatter,
		Tag_Renderer $tag_renderer,
		WP_Html_Escaper $escaper
	) {
		$this->text_formatter = $text_formatter;
		$this->renderer       = new Html_Renderer( $tag_renderer, $escaper );

		$this->renderer->set_formatter( $formatter );

		$element_renderers = Token_Renderer::element_renderers();
		$attr_renderers    = Token_Renderer::attr_renderers();

		$this->renderer->add_element_renderers( $element_renderers );
		$escaper->add_attr_renderers( $attr_renderers );
	}

	/**
	 * Starts a new markup, with the indentation from scratch
	 */
	public function print_component( Html_Tag $component ): void {
		$this->start();

		$this->renderer->print_html( $component );
	}

	/**
	 * For the markup parts printed on their own, without a root tag
	 */
	public function start( int $depth = 0 ): self {
		$this->text_formatter->set_tab_depth( $depth );

		return $this;
	}

	/**
	 * @param Html_Node ...$nodes
	 */
	public function print_html( ...$nodes ): void {
		$this->renderer->print_html( ...$nodes );
	}

	public function new_line( int $count = 1 ): self {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->text_formatter->new_line( $count );

		return $this;
	}
}
