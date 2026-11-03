<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Web_Component;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Utils\WP_Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Formatting\Formatters\Compact_Tag_Formatter;
use Org\Wplake\Advanced_Views\Template\Html\Formatting\Formatters\Text_Formatter;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Tag_Renderer;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Rendering\Token_Renderer;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Rendering\Token_Tree_Formatter;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
final class Web_Component_Renderer {
	protected Html_Renderer $renderer;
	protected Text_Formatter $text_formatter;

	public function __construct(
		Token_Tree_Formatter $formatter,
		Compact_Tag_Formatter $tag_formatter,
		Text_Formatter $text_formatter
	) {
		$this->text_formatter = $text_formatter;

		$escaper      = self::create_escaper();
		$tag_renderer = self::create_tag_renderer( $escaper, $tag_formatter );

		$this->renderer = self::create_renderer( $tag_renderer, $escaper )
								->set_formatter( $formatter );
	}

	/**
	 * A whole markup, from the root tag, ends with a line break
	 */
	public function render_component( Html_Tag $component ): string {
		$html     = $this->renderer->render_html( $component );
		$new_line = $this->text_formatter->new_line();

		return $html . $new_line;
	}

	/**
	 * For the markup parts rendered on their own, with the depth they would have nested into the markup
	 *
	 * @param Html_Node[] $nodes
	 */
	public function render_html( array $nodes, int $depth = 0 ): string {
		return $this->text_formatter->tab_shift(
			fn() => $this->renderer->render_html( ...$nodes ),
			$depth
		);
	}

	protected static function create_renderer( Tag_Renderer $tag_renderer, Html_Escaper $escaper ): Html_Renderer {
		$element_renderers = Token_Renderer::element_renderers();

		$renderer = new Html_Renderer( $tag_renderer, $escaper );

		return $renderer->add_element_renderers( $element_renderers );
	}

	protected static function create_tag_renderer( Html_Escaper $escaper, Compact_Tag_Formatter $tag_formatter ): Tag_Renderer {
		$tag_renderer = new Tag_Renderer( $escaper );

		return $tag_renderer->set_formatter( $tag_formatter );
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
