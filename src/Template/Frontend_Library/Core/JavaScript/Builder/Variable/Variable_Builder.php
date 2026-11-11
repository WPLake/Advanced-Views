<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\JavaScript\Builder\Variable;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Formatting\Formatters\Text_Formatter;

final class Variable_Builder {
	protected Object_Builder $object_builder;
	protected Html_Escaper $escaper;
	protected Text_Formatter $formatter;
	protected string $declaration_keyword;

	public function __construct( Html_Escaper $escaper, Text_Formatter $formatter ) {
		$this->object_builder      = new Object_Builder( $escaper, $formatter );
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
	public function build_variable( string $variable, array $options ): string {
		$tabs     = $this->formatter->tabulate();
		$variable = $this->escaper->esc_html( $variable );
		$object   = $this->object_builder->build_object( $options );

		return sprintf(
			'%s%s %s = %s;',
			$tabs,
			$this->declaration_keyword,
			$variable,
			$object
		);
	}
}
