<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Rendering\Escaper;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Htmlable;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\stringExtendedOrNull;

defined( 'ABSPATH' ) || exit;

abstract class Html_Escaper_Base implements Html_Escaper {
	/**
	 * @var string[]
	 */
	protected array $url_attributes;

	/**
	 * @param string[]|null $url_attributes
	 */
	public function __construct( ?array $url_attributes = null ) {
		$this->url_attributes = $url_attributes ?? array(
			'href',
			'src',
			'action',
			'formaction',
		);
	}

	public function esc_attr( string $name, string $value ): string {
		return $this->is_url_attribute( $name ) ?
			$this->esc_url( $value ) :
			$this->esc_html( $value );
	}

	/**
	 * @param mixed $value
	 */
	public function stringify( $value ): string {
		if ( $value instanceof Htmlable ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return $value->to_html( $this );
		}

		$text_value = stringExtendedOrNull( $value );

		if ( is_string( $text_value ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return $this->esc_html( $text_value );
		}

		return '';
	}

	protected function is_url_attribute( string $name ): bool {
		return in_array( $name, $this->url_attributes, true );
	}
}
