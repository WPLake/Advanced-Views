<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Rendering;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Htmlable;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\stringExtended;

/**
 * @phpstan-type Object_Stringifier callable(object $value):string
 */
final class Html_Stringifier {
	/**
	 * @var array<class-string, Object_Stringifier>
	 */
	private array $object_handlers;
	/**
	 * @var callable(mixed):string $string_handler
	 */
	private $string_handler;

	private Html_Escaper $escaper;

	/**
	 * @param array<class-string, Object_Stringifier> $object_handlers
	 */
	public function __construct( Html_Escaper $escaper, array $object_handlers = array() ) {
		$this->escaper = $escaper;

		$default_handlers      = self::get_object_handlers( $escaper );
		$this->object_handlers = array_merge( $default_handlers, $object_handlers );

		$this->string_handler = array( $this, 'get_safe_string' );
	}

	/**
	 * @param array<class-string, Object_Stringifier> $object_handlers
	 */
	public function set_object_handlers( array $object_handlers ): self {
		$this->object_handlers = $object_handlers;

		return $this;
	}

	/**
	 * @param array<class-string, Object_Stringifier> $object_handlers
	 */
	public function add_object_handlers( array $object_handlers ): self {
		$this->object_handlers = array_merge( $this->object_handlers, $object_handlers );

		return $this;
	}

	/**
	 * @param callable(mixed):string $string_handler
	 */
	public function set_string_handler( callable $string_handler ): self {
		$this->string_handler = $string_handler;

		return $this;
	}

	public function stringify( $value ): string {
		if ( is_object( $value ) ) {
			return $this->stringify_object( $value );
		}

		return ( $this->string_handler )( $value );
	}

	protected function stringify_object( object $value ): string {
		$stringifier = $this->resolve_stringifier( $value );

		if ( is_callable( $stringifier ) ) {
			return $stringifier( $value );
		}

		return ( $this->string_handler )( $value );
	}

	/**
	 * @return Object_Stringifier|null
	 */
	protected function resolve_stringifier( object $value ): ?callable {
		foreach ( $this->object_handlers as $type => $stringifier ) {
			if ( is_a( $value, $type ) ) {
				return $stringifier;
			}
		}

		return null;
	}

	protected function get_safe_string( $value ): string {
		$text_value = stringExtended( $value );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		return $this->escaper->esc_html( $text_value );
	}

	/**
	 * @return array<class-string, Object_Stringifier>
	 */
	protected static function get_object_handlers( Html_Escaper $escaper ): array {
		return array(
			Htmlable::class => fn( object $value ) => $value instanceof Htmlable ?
				$value->to_html( $escaper ) :
				'',
		);
	}
}
