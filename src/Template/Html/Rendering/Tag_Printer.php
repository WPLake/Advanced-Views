<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Rendering;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Attribute\Attribute_Printer;

final class Tag_Printer {
	/**
	 * @var string[]
	 */
	private array $self_closing_tags;

	protected Html_Escaper $escaper;
	protected Attribute_Printer $attribute_printer;

	public function __construct(
		Html_Escaper $escaper,
		?Attribute_Printer $attribute_printer = null
	) {
		$this->escaper           = $escaper;
		$this->attribute_printer = $attribute_printer ?? new Attribute_Printer( $escaper );
		$this->self_closing_tags = array(
			'area',
			'base',
			'br',
			'col',
			'embed',
			'hr',
			'img',
			'input',
			'link',
			'meta',
			'source',
			'track',
			'wbr',
		);
	}

	/**
	 * @param string[] $self_closing_tags
	 */
	public function set_self_closing_tags( array $self_closing_tags ): self {
		$this->self_closing_tags = $self_closing_tags;

		return $this;
	}

	/**
	 * @param string[] $self_closing_tags
	 */
	public function add_self_closing_tags( array $self_closing_tags ): self {
		$this->self_closing_tags = array_merge( $this->self_closing_tags, $self_closing_tags );

		return $this;
	}

	public function get_attribute_printer(): Attribute_Printer {
		return $this->attribute_printer;
	}

	public function print_tag( Html_Tag $tag, ?callable $print_content = null ): void {
		$is_self_closing = in_array( $tag->get_tag(), $this->self_closing_tags, true );

		if ( $is_self_closing ) {
			$this->print_single_tag( $tag );
		} else {
			$this->print_pair_tag( $tag, $print_content );
		}
	}

	protected function print_single_tag( Html_Tag $tag ): void {
		$this->open_tag( $tag );
		echo '/>';
	}

	protected function print_pair_tag( Html_Tag $tag, ?callable $print_content = null ): void {
		$this->open_tag( $tag );
		echo '>';

		if ( is_callable( $print_content ) ) {
			$print_content();
		}

		$this->print_closing_tag( $tag );
	}

	protected function open_tag( Html_Tag $tag ): void {
		$attributes = $tag->get_attributes();

		$this->print_opening_tag( $tag );
		$this->attribute_printer->print_attributes( $attributes );
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
