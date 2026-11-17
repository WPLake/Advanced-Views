<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Escaping;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Composite_Attribute;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\stringExtended;

/**
 * @phpstan-type Attr_Renderer callable(object $attr):string
 */
abstract class Html_Escaper_Base implements Html_Escaper {
	/**
	 * @var array<class-string, Attr_Renderer>
	 */
	private array $attr_renderers;

	public function __construct() {
		$this->attr_renderers = array(
			Composite_Attribute::class => fn( object $attribute ) => $attribute instanceof Composite_Attribute ?
				$this->render_attribute( $attribute ) :
				'',
		);
	}

	/**
	 * @param array<class-string, Attr_Renderer> $renderers
	 */
	public function add_attr_renderers( array $renderers ): self {
		$this->attr_renderers = array_merge( $this->attr_renderers, $renderers );

		return $this;
	}

	/**
	 * @param mixed $value
	 */
	public function esc_attr( $value ): string {
		if ( is_object( $value ) ) {
			return $this->render_object( $value );
		}

		return $this->to_safe_string( $value );
	}

	public function to_safe_string( $value ): string {
		$text_value = stringExtended( $value );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		return $this->esc_html( $text_value );
	}

	protected function render_object( object $value ): string {
		$renderer = $this->resolve_renderer( $value );

		if ( is_callable( $renderer ) ) {
			return $renderer( $value );
		}

		return $this->to_safe_string( $value );
	}

	/**
	 * @return Attr_Renderer|null
	 */
	protected function resolve_renderer( object $attribute ): ?callable {
		foreach ( $this->attr_renderers as $type => $renderer ) {
			if ( is_a( $attribute, $type ) ) {
				return $renderer;
			}
		}

		return null;
	}

	protected function render_attribute( Composite_Attribute $attribute ): string {
		$rendered_items = array_map(
			fn( $item ) => $this->esc_attr( $item ),
			$attribute->items
		);

		$safe_delimiter = $this->esc_html( $attribute->delimiter );

		return join( $safe_delimiter, $rendered_items );
	}
}
