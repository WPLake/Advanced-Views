<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Web_Component;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Utils\WP_Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Formatting\Text_Formatter;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Tag_Renderer;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Rendering\Token_Renderer;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Rendering\Token_Tree_Formatter;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
final class Web_Component_Printer {
	protected Html_Renderer $renderer;
	protected Text_Formatter $text_formatter;

	public function __construct( Token_Tree_Formatter $formatter, Text_Formatter $text_formatter ) {
		$this->text_formatter = $text_formatter;
		$this->renderer       = self::create_renderer()
									->set_formatter( $formatter );
	}

	/**
	 * A whole markup, from the root tag, ends with a line break
	 */
	public function print_component( Html_Tag $component ): void {
		$html     = $this->renderer->render_html( $component );
		$new_line = $this->text_formatter->new_line();

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $html . $new_line;
	}

	/**
	 * For the markup parts printed on their own, indented as if they were nested into the markup
	 *
	 * @param Html_Node[] $nodes
	 */
	public function print_html_at( int $depth, array $nodes ): void {
		$html = $this->text_formatter->tab_shift(
			fn() => $this->renderer->render_html( ...$nodes ),
			$depth
		);

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $html;
	}

	protected static function create_renderer(): Html_Renderer {
		$escaper      = self::create_escaper();
		$tag_renderer = new Tag_Renderer( $escaper );
		$renderer     = new Html_Renderer( $tag_renderer, $escaper );

		$element_renderers = Token_Renderer::element_renderers();

		return $renderer->add_element_renderers( $element_renderers );
	}

	/**
	 * Local, as the token attribute renderers must not leak to the shared (auto-wired) escaper.
	 */
	protected static function create_escaper(): Html_Escaper {
		$escaper        = new WP_Html_Escaper();
		$attr_renderers = Token_Renderer::attr_renderers();

		$escaper->add_attr_renderers( $attr_renderers );

		return $escaper;
	}
}
