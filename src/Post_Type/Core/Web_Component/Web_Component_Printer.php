<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Web_Component;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Formatting\Formatted_Tag_Printer;
use Org\Wplake\Advanced_Views\Template\Html\Formatting\Html_Formatter;
use Org\Wplake\Advanced_Views\Template\Html\Html_Renderer;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;

final class Web_Component_Printer {
	protected Html_Renderer $renderer;
	protected Html_Formatter $formatter;

	public function __construct( Formatted_Tag_Printer $tag_printer, Html_Formatter $formatter ) {
		$this->renderer  = new Html_Renderer( $tag_printer );
		$this->formatter = $formatter;
	}

	public function print_component( Html_Tag $component ): void {
		$this->renderer->print_html( $component );
	}
}
