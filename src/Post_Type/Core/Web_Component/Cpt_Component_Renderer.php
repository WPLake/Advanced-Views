<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Web_Component;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Utils\WP_Html_Escaper;
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
final class Cpt_Component_Renderer {
	protected WP_Html_Escaper $escaper;
	protected Tag_Renderer $tag_renderer;
	protected Html_Renderer $html_renderer;
	protected Text_Formatter $text_formatter;

	public function __construct( Instance_Container $container ) {
		$this->text_formatter = $container->resolve( Text_Formatter::class );
		// custom instances, separate from the container ones.
		$this->escaper       = new WP_Html_Escaper();
		$this->tag_renderer  = new Tag_Renderer( $this->escaper );
		$this->html_renderer = new Html_Renderer( $this->tag_renderer, $this->escaper );

		$this->configure_rendering();
		$this->configure_formatting( $container );
	}

	/**
	 * A whole markup, from the root tag, ends with a line break
	 */
	public function render_component( Html_Tag $component ): string {
		$html     = $this->html_renderer->render_html( $component );
		$new_line = $this->text_formatter->new_line();

		return $html . $new_line;
	}

	/**
	 * For the markup parts rendered on their own, with the depth they would have nested into the markup
	 *
	 * @param Html_Node[] $nodes
	 */
	public function render_html( array $nodes, int $depth = 0 ): string {
		$render_nodes = fn() => $this->html_renderer->render_html( ...$nodes );

		return $depth > 0 ?
			$this->text_formatter->tab_shift( $render_nodes, $depth ) :
			$render_nodes();
	}

	protected function configure_rendering(): void {
		Token_Renderer::configure_attribute_rendering( $this->escaper );
		Token_Renderer::configure_element_renderering( $this->html_renderer );
	}

	protected function configure_formatting( Instance_Container $container ): void {
		$node_formatter = $container->resolve( Token_Tree_Formatter::class );
		$this->html_renderer->set_formatter( $node_formatter );

		$tag_formatter = $container->resolve( Compact_Tag_Formatter::class );
		$this->tag_renderer->set_formatter( $tag_formatter );
	}
}
