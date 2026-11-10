<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Template;

use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory;

defined( 'ABSPATH' ) || exit;

final class Pattern_Container {
	/**
	 * @var Template_Pattern[]
	 */
	private array $patterns;

	/**
	 * @param Template_Pattern[] $patterns
	 */
	public function __construct( array $patterns ) {
		$this->patterns = $patterns;
	}

	/**
	 * @return Template_Pattern[]
	 */
	public function get_patterns(): array {
		return $this->patterns;
	}

	public function resolve_row_wrapper_tag( Field_Settings $field_settings, string $row_type ): string {
		return $this->first_non_empty(
			static fn( Template_Pattern $pattern ): string => $pattern->get_row_wrapper_tag( $field_settings, $row_type )
		);
	}

	public function resolve_field_wrapper_tag( Field_Settings $field_settings, string $row_type ): string {
		return $this->first_non_empty(
			static fn( Template_Pattern $pattern ): string => $pattern->get_field_wrapper_tag( $field_settings, $row_type )
		);
	}

	public function row_wrapper_class( string $row_type ): string {
		$classes = array();

		foreach ( $this->patterns as $pattern ) {
			$class     = $pattern->get_row_wrapper_class( $row_type );
			$is_filled = strlen( $class ) > 0;

			if ( $is_filled ) {
				$classes[] = $class;
			}
		}

		return implode( ' ', $classes );
	}

	/**
	 * @return array<string,string>
	 */
	public function field_wrapper_attrs( Field_Settings $field_settings, string $field_id ): array {
		$attrs = array();

		foreach ( $this->patterns as $pattern ) {
			$pattern_attrs = $pattern->get_field_wrapper_attrs( $field_settings, $field_id );
			$attrs         = array_merge( $attrs, $pattern_attrs );
		}

		return $attrs;
	}

	public function is_label_out_of_row(): bool {
		foreach ( $this->patterns as $pattern ) {
			if ( $pattern->is_label_out_of_row() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return Html_Tag[]
	 */
	public function field_outers(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		string $field_id,
		string $row_type,
		Token_Factory $token_factory
	): array {
		$patterns_outers = array();

		foreach ( $this->patterns as $pattern ) {
			$patterns_outers[] = $pattern->get_field_outers(
				$layout_settings,
				$field_settings,
				$field_id,
				$row_type,
				$token_factory
			);
		}

		return Outers_Builder::merge_outers( $patterns_outers );
	}

	/**
	 * @return Html_Tag[]
	 */
	public function item_outers(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		string $field_id,
		string $item_id,
		Token_Factory $token_factory
	): array {
		$patterns_outers = array();

		foreach ( $this->patterns as $pattern ) {
			$patterns_outers[] = $pattern->get_item_outers(
				$layout_settings,
				$field_settings,
				$field_id,
				$item_id,
				$token_factory
			);
		}

		return Outers_Builder::merge_outers( $patterns_outers );
	}

	/**
	 * @param callable(Template_Pattern):string $query
	 */
	protected function first_non_empty( callable $query ): string {
		foreach ( $this->patterns as $pattern ) {
			$value     = $query( $pattern );
			$is_filled = strlen( $value ) > 0;

			if ( $is_filled ) {
				return $value;
			}
		}

		return '';
	}
}
