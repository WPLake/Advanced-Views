<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Rendering\Attributes;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Formatting\Tag_Formatter;

final class Attributes_Renderer {
	protected Attributes_Normalizer $attributes_normalizer;
	protected ?Tag_Formatter $formatter;

	public function __construct( Html_Escaper $escaper ) {
		$this->attributes_normalizer = new Attributes_Normalizer( $escaper );
		$this->formatter             = null;
	}

	public function set_formatter( ?Tag_Formatter $formatter ): self {
		$this->formatter = $formatter;

		return $this;
	}

	public function get_normalizer(): Attributes_Normalizer {
		return $this->attributes_normalizer;
	}

	/**
	 * @param array<string,mixed> $attributes
	 */
	public function render( array $attributes, int $tag_length ): string {
		$normalized_attributes = $this->attributes_normalizer->normalize_attributes( $attributes );

		return $this->format_attributes( $normalized_attributes, $tag_length );
	}

	/**
	 * @param array<string,string> $normalized_attributes escaped name => escaped value
	 */
	protected function format_attributes( array $normalized_attributes, int $tag_length ): string {
		if ( $this->formatter instanceof Tag_Formatter ) {
			return $this->formatter->format_attributes( $normalized_attributes, $tag_length );
		}

		return $this->render_plain_attributes( $normalized_attributes );
	}

	/**
	 * @param array<string,string> $normalized_attributes escaped name => escaped value
	 */
	protected function render_plain_attributes( array $normalized_attributes ): string {
		$html = '';

		foreach ( $normalized_attributes as $escaped_name => $escaped_value ) {
			$html .= sprintf( ' %s="%s"', $escaped_name, $escaped_value );
		}

		return $html;
	}
}
