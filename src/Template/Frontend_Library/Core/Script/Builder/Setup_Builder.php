<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Builder;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Builder\Variable\Variable_Builder;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Pattern\Script_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Pattern\Script_Variables;
use Org\Wplake\Advanced_Views\Template\Html\Formatting\Formatters\Text_Formatter;

final class Setup_Builder {
	protected Text_Formatter $formatter;
	protected Variable_Builder $variable_builder;

	public function __construct( Text_Formatter $formatter, Variable_Builder $variable_builder ) {
		$this->formatter        = $formatter;
		$this->variable_builder = $variable_builder;
	}

	/**
	 * The docs link comment right above the code: the options, then the setup, separated by an empty line.
	 *
	 * @param class-string<Script_Pattern> $js_pattern
	 */
	public function build_setup( string $js_pattern, string $root ): string {
		$variables          = new Script_Variables();
		$variables->element = $root;

		$docs_code    = $this->compose_docs( $js_pattern );
		$options_code = $this->compose_options( $js_pattern, $variables );
		$setup_code   = $this->compose_setup( $js_pattern, $variables );

		$blocks = array(
			$options_code,
			$setup_code,
		);
		$code   = $this->join_blocks( $blocks );

		return $this->prepend_docs( $docs_code, $code );
	}

	/**
	 * @param class-string<Script_Pattern> $js_pattern
	 */
	protected function compose_docs( string $js_pattern ): string {
		$docs_url  = $js_pattern::get_docs_url();
		$is_filled = strlen( $docs_url ) > 0;

		if ( $is_filled ) {
			$tabs = $this->formatter->tabulate();

			return sprintf( '%s/* %s */', $tabs, $docs_url );
		}

		return '';
	}

	/**
	 * @param class-string<Script_Pattern> $js_pattern
	 */
	protected function compose_options( string $js_pattern, Script_Variables $variables ): string {
		$options   = $js_pattern::get_js_options();
		$is_filled = count( $options ) > 0;

		if ( $is_filled ) {
			return $this->variable_builder->build_variable( $variables->options, $options );
		}

		return '';
	}

	/**
	 * @param class-string<Script_Pattern> $js_pattern
	 */
	protected function compose_setup( string $js_pattern, Script_Variables $variables ): string {
		$setup      = $js_pattern::get_js_setup( $variables );
		$tabs       = $this->formatter->tabulate();
		$statements = array();

		foreach ( $setup as $line ) {
			$statements[] = '' === $line ?
				'' :
				sprintf( '%s%s;', $tabs, $line );
		}

		$new_line = $this->formatter->new_line();

		return join( $new_line, $statements );
	}

	protected function prepend_docs( string $docs_code, string $code ): string {
		$is_filled = strlen( $docs_code ) > 0;

		if ( $is_filled ) {
			$new_line = $this->formatter->new_line();
			$lines    = array(
				$docs_code,
				$code,
			);

			return join( $new_line, $lines );
		}

		return $code;
	}

	/**
	 * @param string[] $blocks
	 */
	protected function join_blocks( array $blocks ): string {
		$filled_blocks = array_filter(
			$blocks,
			fn( string $block ) => strlen( $block ) > 0
		);

		$new_lines = $this->formatter->new_line( 2 );

		return join( $new_lines, $filled_blocks );
	}
}
