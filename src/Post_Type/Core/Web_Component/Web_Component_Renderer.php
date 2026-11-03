<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Web_Component;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Formatting\Formatters\Text_Formatter;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
final class Web_Component_Renderer {
	protected Html_Renderer $renderer;
	protected Text_Formatter $text_formatter;

	public function __construct( Html_Renderer $renderer, Text_Formatter $text_formatter ) {
		$this->renderer       = $renderer;
		$this->text_formatter = $text_formatter;
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
}
