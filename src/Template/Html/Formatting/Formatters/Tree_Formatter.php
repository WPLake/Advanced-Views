<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Formatting\Formatters;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Formatting\Node_Formatter;
use Org\Wplake\Advanced_Views\Template\Html\Formatting\Tag_Formatter;

class Tree_Formatter implements Node_Formatter, Tag_Formatter {
	private int $max_attrs_in_line;

	protected Text_Formatter $formatter;

	public function __construct( Text_Formatter $formatter ) {
		$this->formatter         = $formatter;
		$this->max_attrs_in_line = 4;
	}

	public function set_max_attrs_in_line( int $max_attrs_in_line ): self {
		$this->max_attrs_in_line = $max_attrs_in_line;

		return $this;
	}

	public function format_node( $node, string $node_html ): string {
		return $this->formatter->tabulate() .
				$node_html;
	}

	public function format_node_content( $node, Closure $render_content ): string {
		$new_line        = $this->formatter->new_line();
		$shifted_content = $this->formatter->tab_shift( $render_content );
		$indented_end    = $this->formatter->indent_line();

		$content_parts = array(
			$new_line,
			$shifted_content,
			$indented_end,
		);

		return join( '', $content_parts );
	}

	public function format_attributes( int $open_spaces_count, array $attributes ): string {
		$attr_groups = array();

		if ( key_exists( 'class', $attributes ) ) {
			$attr_groups[] = array( 'class' => $attributes['class'] );
			unset( $attributes['class'] );
		}

		$rest_groups    = array_chunk( $attributes, $this->max_attrs_in_line, true );
		$attr_groups    = array_merge( $attr_groups, $rest_groups );
		$has_attributes = count( $attr_groups ) > 0;

		if ( $has_attributes ) {
			return $this->print_attributes( $open_spaces_count, $attr_groups );
		}

		return '';
	}

	public function format_interleave( $previous_node, $next_node ): string {
		return $this->formatter->new_line();
	}

	/**
	 * @param array<int,array<string,string>> $groups
	 */
	protected function print_attributes( int $open_spaces_count, array $groups ): string {
		$lines = array_map( array( self::class, 'format_attribute_group' ), $groups );

		$aligned = str_repeat( ' ', $open_spaces_count + 1 );
		$indent  = $this->formatter->indent_line();

		$delimiter = $indent . $aligned;
		$joined    = join( $delimiter, $lines );

		return sprintf( ' %s', $joined );
	}

	/**
	 * @param array<string,string> $attr_group
	 */
	protected static function format_attribute_group( array $attr_group ): string {
		$pairs = array();

		foreach ( $attr_group as $name => $value ) {
			$pairs[] = sprintf( '%s="%s"', $name, $value );
		}

		return join( ' ', $pairs );
	}
}
