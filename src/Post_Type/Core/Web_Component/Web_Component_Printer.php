<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Web_Component;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Tag\Formatted_Tag_Printer;

final class Web_Component_Printer {
	protected Html_Renderer $renderer;

	public function __construct( Formatted_Tag_Printer $tag_printer, Html_Escaper $escaper ) {
		$this->renderer = new Html_Renderer( $escaper, $tag_printer );
	}

	public function print_component( Html_Tag $component ): void {
		$this->renderer->print_html( $component );
	}
}
