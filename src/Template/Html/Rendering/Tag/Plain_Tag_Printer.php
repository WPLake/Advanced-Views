<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Rendering\Tag;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Attribute\Attribute_Renderer;
use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;

final class Plain_Tag_Printer implements Tag_Printer {
	protected Html_Escaper $escaper;
	protected Attribute_Renderer $attribute_renderer;

	public function __construct(
		Html_Escaper $escaper,
		?Attribute_Renderer $attribute_renderer = null
	) {
		$this->escaper            = $escaper;
		$this->attribute_renderer = $attribute_renderer ?? new Attribute_Renderer( $escaper );
	}

	public function print_pair_tag( Html_Tag $tag, Closure $print_content ): void {
		$this->open_tag( $tag );
		echo '>';
		$print_content();
		$this->print_closing_tag( $tag );
	}

	public function print_single_tag( Html_Tag $tag ): void {
		$this->open_tag( $tag );
		echo '/>';
	}

	protected function open_tag( Html_Tag $tag ): void {
		$attributes = $tag->get_attributes();

		$this->print_opening_tag( $tag );
		$this->attribute_renderer->print_attributes( $attributes );
	}

	protected function print_opening_tag( Html_Tag $tag ): void {
		$tag_name    = $tag->get_tag();
		$escaped_tag = $this->escaper->esc_html( $tag_name );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		printf( '<%s', $escaped_tag );
	}

	protected function print_closing_tag( Html_Tag $tag ): void {
		$tag_name    = $tag->get_tag();
		$escaped_tag = $this->escaper->esc_html( $tag_name );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		printf( '</%s>', $escaped_tag );
	}
}
