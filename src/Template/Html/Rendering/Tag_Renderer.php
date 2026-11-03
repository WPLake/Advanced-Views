<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Rendering;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Formatting\Tag_Formatter;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Attributes\Attributes_Normalizer;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Attributes\Attributes_Renderer;

final class Tag_Renderer {
	/**
	 * @var string[]
	 */
	private array $self_closing_tags;

	protected Html_Escaper $escaper;
	protected Attributes_Renderer $attributes_renderer;

	public function __construct( Html_Escaper $escaper ) {
		$this->escaper             = $escaper;
		$this->attributes_renderer = new Attributes_Renderer( $escaper );
		$this->self_closing_tags   = array(
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

	public function set_formatter( ?Tag_Formatter $formatter ): self {
		$this->attributes_renderer->set_formatter( $formatter );

		return $this;
	}

	public function get_attributes_normalizer(): Attributes_Normalizer {
		return $this->attributes_renderer->get_normalizer();
	}

	public function render_tag( Html_Tag $tag, string $content = '' ): string {
		$tag_name        = $tag->get_name();
		$is_self_closing = in_array( $tag_name, $this->self_closing_tags, true );

		return $is_self_closing ?
			$this->render_single_tag( $tag ) :
			$this->render_pair_tag( $tag, $content );
	}

	protected function render_single_tag( Html_Tag $tag ): string {
		$opening_tag = $this->render_opening_part( $tag );

		return sprintf( '%s/>', $opening_tag );
	}

	protected function render_pair_tag( Html_Tag $tag, string $content ): string {
		$tag_name = $tag->get_name();

		$opening_tag = $this->render_opening_part( $tag );
		$closing_tag = $this->render_closing_tag( $tag_name );

		$tag_parts = array(
			$opening_tag,
			'>',
			$content,
			$closing_tag,
		);

		return join( '', $tag_parts );
	}

	protected function render_opening_part( Html_Tag $tag ): string {
		$tag_name = $tag->get_name();
		$open_tag = $this->render_opening_tag( $tag_name );

		$tag_spaces_count    = strlen( $open_tag );
		$attributes          = $tag->get_attributes();
		$rendered_attributes = $this->attributes_renderer->render( $attributes, $tag_spaces_count );

		return $open_tag . $rendered_attributes;
	}

	protected function render_opening_tag( string $tag_name ): string {
		$escaped_tag = $this->escaper->esc_html( $tag_name );

		return sprintf( '<%s', $escaped_tag );
	}

	protected function render_closing_tag( string $tag_name ): string {
		$escaped_tag = $this->escaper->esc_html( $tag_name );

		return sprintf( '</%s>', $escaped_tag );
	}
}
