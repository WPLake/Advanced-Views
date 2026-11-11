<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Layout;

use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Template\Builder\Template_Builder;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Template\Pattern\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;

defined( 'ABSPATH' ) || exit;

/**
 * The Template_Pattern declarations of a field's libraries, resolved for the Layout generation.
 */
final class Pattern_Container {
	const ROW_TYPE_REPEATER_ITEM = 'repeater-item';

	/**
	 * @var class-string<Template_Pattern>[]
	 */
	private array $patterns;
	/**
	 * @var Layout_Library[]
	 */
	private array $libraries;
	private Layout_Settings $layout_settings;
	private Field_Settings $field_settings;

	/**
	 * @param Layout_Library[] $libraries
	 */
	public function __construct( array $libraries, Layout_Settings $layout_settings, Field_Settings $field_settings ) {
		$this->layout_settings = $layout_settings;
		$this->field_settings  = $field_settings;
		$this->libraries       = $libraries;
		$this->patterns        = array();

		foreach ( $libraries as $library ) {
			$pattern = $library->get_template_pattern( $field_settings, $layout_settings );

			if ( is_string( $pattern ) ) {
				$this->patterns[] = $pattern;
			}
		}
	}

	/**
	 * @return Layout_Library[]
	 */
	public function get_libraries(): array {
		return $this->libraries;
	}

	/**
	 * A repeater item is wrapped with the inner wrapper of the patterns.
	 */
	public function row_wrapper( string $row_type, Token_Factory $token_factory, string $field_id ): ?Html_Tag {
		if ( self::ROW_TYPE_REPEATER_ITEM === $row_type ) {
			$item   = $token_factory->variable( $field_id );
			$inners = Template_Builder::item_wrappers( $this->patterns, $item );

			return $inners[0] ?? null;
		}

		return Template_Builder::row_wrapper( $this->patterns );
	}

	public function field_wrapper( string $row_type, Token_Factory $token_factory, string $field_id ): ?Html_Tag {
		if ( self::ROW_TYPE_REPEATER_ITEM === $row_type ) {
			return null;
		}

		$wrappers = Template_Builder::field_wrappers( $this->patterns, $token_factory->variable( $field_id ) );

		return $wrappers[0] ?? null;
	}

	public function has_root_wrapper(): bool {
		return Template_Builder::row_wrapper( $this->patterns ) instanceof Html_Tag;
	}

	/**
	 * @return Html_Tag[]
	 */
	public function field_outers( string $row_type, Token_Factory $token_factory, string $field_id ): array {
		if ( self::ROW_TYPE_REPEATER_ITEM === $row_type ) {
			return array();
		}

		$wrappers   = Template_Builder::field_wrappers( $this->patterns, $token_factory->variable( $field_id ) );
		$outers     = array_slice( $wrappers, 1 );
		$list_class = $this->layout_settings->get_item_class( 'list', $this->field_settings );

		return Template_Builder::prefix_list_class( $outers, $list_class );
	}

	/**
	 * @return Html_Tag[]
	 */
	public function item_outers( Token_Factory $token_factory, string $item_id ): array {
		$inners     = Template_Builder::item_wrappers( $this->patterns, $token_factory->variable( $item_id ) );
		$item_class = $this->layout_settings->get_item_class( 'item', $this->field_settings );

		return Template_Builder::prefix_item_class( $inners, $item_class );
	}

	/**
	 * @return array<string,string|Variable_Token>
	 */
	public function item_attributes( Token_Factory $token_factory, string $item_id ): array {
		return Template_Builder::item_attributes( $this->patterns, $token_factory->variable( $item_id ) );
	}
}
