<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\JavaScript\Builder\Variable;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Formatting\Formatters\Text_Formatter;

final class Object_Builder {
	protected Property_Builder $property_builder;
	protected Text_Formatter $formatter;

	public function __construct( Html_Escaper $escaper, Text_Formatter $formatter ) {
		$this->property_builder = new Property_Builder( $escaper, $formatter );
		$this->formatter        = $formatter;
	}

	/**
	 * The object literal at the current depth, with its properties one level deeper.
	 * The opening brace has no tabs, as it follows a declaration or an assignment.
	 *
	 * @param array<string,mixed> $options
	 */
	public function build_object( array $options ): string {
		$properties = $this->formatter->tab_shift(
			fn() => $this->compose_properties( $options )
		);
		$tabs       = $this->formatter->tabulate();

		$lines = array(
			'{',
			$properties,
			sprintf( '%s}', $tabs ),
		);

		return $this->join_lines( $lines );
	}

	/**
	 * @param array<string,mixed> $options
	 */
	protected function compose_properties( array $options ): string {
		$properties = array();

		foreach ( $options as $name => $value ) {
			$properties[] = $this->property_builder->build_property( $name, $value );
		}

		return $this->join_lines( $properties );
	}

	/**
	 * @param string[] $lines
	 */
	protected function join_lines( array $lines ): string {
		$new_line = $this->formatter->new_line();

		return join( $new_line, $lines );
	}
}
