<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Rendering;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;

final class Tag_Renderer {
	/**
	 * @var string[]
	 */
	private array $self_closing_tags;

	protected Html_Escaper $escaper;
	protected Attribute_Normalizer $attribute_normalizer;

	public function __construct( Html_Escaper $escaper ) {
		$this->escaper              = $escaper;
		$this->attribute_normalizer = new Attribute_Normalizer( $escaper );
		$this->self_closing_tags    = array(
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

	public function get_attribute_normalizer(): Attribute_Normalizer {
		return $this->attribute_normalizer;
	}

	public function render_tag( Html_Tag $tag, string $content = '' ): string {
		$tag_name        = $tag->get_tag();
		$is_self_closing = in_array( $tag_name, $this->self_closing_tags, true );

		return $is_self_closing ?
			$this->render_single_tag( $tag ) :
			$this->render_pair_tag( $tag, $content );
	}

	protected function render_single_tag( Html_Tag $tag ): string {
		$opening_tag = $this->render_opening_tag( $tag );

		return sprintf( '%s/>', $opening_tag );
	}

	protected function render_pair_tag( Html_Tag $tag, string $content ): string {
		$opening_tag = $this->render_opening_tag( $tag );
		$closing_tag = $this->render_closing_tag( $tag );

		$tag_parts = array(
			$opening_tag,
			'>',
			$content,
			$closing_tag,
		);

		return join( '', $tag_parts );
	}

	protected function render_opening_tag( Html_Tag $tag ): string {
		$tag_name   = $tag->get_tag();
		$attributes = $tag->get_attributes();

		$escaped_tag           = $this->escaper->esc_html( $tag_name );
		$normalized_attributes = $this->attribute_normalizer->normalize_attributes( $attributes );
		// fixme call the formatter.
		$rendered_attributes = $this->render_attributes( $normalized_attributes );

		$tag_parts = array(
			'<',
			$escaped_tag,
			$rendered_attributes,
		);

		return join( '', $tag_parts );
	}

	/**
	 * @param array<string,string> $normalized_attributes escaped name => escaped value
	 */
	protected function render_attributes( array $normalized_attributes ): string {
		$html = '';

		foreach ( $normalized_attributes as $escaped_name => $escaped_value ) {
			$html .= sprintf( ' %s="%s"', $escaped_name, $escaped_value );
		}

		return $html;
	}

	protected function render_closing_tag( Html_Tag $tag ): string {
		$tag_name    = $tag->get_tag();
		$escaped_tag = $this->escaper->esc_html( $tag_name );

		return sprintf( '</%s>', $escaped_tag );
	}
}
