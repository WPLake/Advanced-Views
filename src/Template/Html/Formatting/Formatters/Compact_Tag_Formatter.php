<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html\Formatting\Formatters;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Formatting\Tag_Formatter;

class Compact_Tag_Formatter implements Tag_Formatter {
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

	public function format_attributes( array $escaped_attributes, int $tag_length ): string {
		if ( count( $escaped_attributes ) > 0 ) {
			$attr_groups = $this->group_attributes( $escaped_attributes );

			return $this->print_attributes( $attr_groups, $tag_length );
		}

		return '';
	}

	/**
	 * The class goes first on its own, the rest are chunked by the line limit (at least one per line)
	 *
	 * @param array<string,string> $escaped_attributes
	 *
	 * @return array<int,array<string,string>>
	 */
	protected function group_attributes( array $escaped_attributes ): array {
		$attr_groups = array();

		if ( key_exists( 'class', $escaped_attributes ) ) {
			$attr_groups[] = array( 'class' => $escaped_attributes['class'] );
			unset( $escaped_attributes['class'] );
		}

		$chunk_size  = max( 1, $this->max_attrs_in_line );
		$rest_groups = array_chunk( $escaped_attributes, $chunk_size, true );

		return array_merge( $attr_groups, $rest_groups );
	}

	/**
	 * @param array<int,array<string,string>> $groups
	 */
	protected function print_attributes( array $groups, int $tag_length ): string {
		$lines = array_map( array( self::class, 'format_attribute_group' ), $groups );

		$aligned = str_repeat( ' ', $tag_length + 1 );
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
