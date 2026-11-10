<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\JavaScript\Pattern\Builder;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Formatting\Formatters\Text_Formatter;

final class JS_Object_Builder {
	protected JS_Property_Builder $property_builder;
	protected Html_Escaper $escaper;
	protected Text_Formatter $formatter;
	protected string $declaration_keyword;

	public function __construct( Html_Escaper $escaper, Text_Formatter $formatter ) {
		$this->property_builder    = new JS_Property_Builder( $escaper );
		$this->escaper             = $escaper;
		$this->formatter           = $formatter;
		$this->declaration_keyword = 'var';
	}

	public function set_declaration_keyword( string $declaration_keyword ): self {
		$this->declaration_keyword = $declaration_keyword;

		return $this;
	}

	/**
	 * @param array<string,mixed> $options
	 */
	public function build( array $options, string $variable ): string {
		$opening = $this->compose_opening( $variable );
		$properties    = $this->compose_indented_properties( $options );
		$closing = $this->compose_closing();

		$lines = array(
			$opening,
			$properties,
			$closing,
		);

		return $this->join_lines( $lines );
	}

	protected function compose_opening( string $variable ): string {
		$tabs = $this->formatter->tabulate();

		return sprintf(
			'%s%s %s = {',
			$tabs,
			$this->declaration_keyword,
			$this->escaper->esc_html( $variable )
		);
	}

	protected function compose_closing(): string {
		$tabs = $this->formatter->tabulate();

		return sprintf( '%s};', $tabs );
	}

	/**
	 * @param array<string,mixed> $options
	 */
	protected function compose_indented_properties( array $options ): string {
		return $this->formatter->tab_shift(
			fn() => $this->compose_properties( $options )
		);
	}

	/**
	 * @param string[] $lines
	 */
	protected function join_lines( array $lines ): string {
		$new_line = $this->formatter->new_line();

		return join( $new_line, $lines );
	}

	/**
	 * @param array<string,mixed> $options
	 */
	protected function compose_properties( array $options ): string {
		$properties = array();

		foreach ( $options as $name => $value ) {
			$properties[] = $this->compose_property( $name, $value );
		}

		return $this->join_lines( $properties );
	}

	/**
	 * @param mixed $value
	 */
	protected function compose_property( string $name, $value ): string {
		$tabs = $this->formatter->tabulate();
		$property  = $this->property_builder->build( $name, $value );

		return sprintf( '%s%s', $tabs, $property );
	}
}
