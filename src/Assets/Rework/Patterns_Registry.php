<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Rework;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Factory_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Layout\Layout_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Selection\Selection_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Template\Builder\Template_Builder;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Template\Pattern\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;

defined( 'ABSPATH' ) || exit;

class Patterns_Registry extends Factory_Base {
	/**
	 * @var array<string,Frontend_Library>
	 */
	private array $patterns;

	/**
	 * @param class-string<Frontend_Library>[] $pattern_classes Later classes override the earlier ones with the same pattern name
	 */
	public function __construct( Instance_Container $container, array $pattern_classes ) {
		parent::__construct( $container );

		$this->patterns = array();

		$this->register_patterns( $pattern_classes );
	}

	/**
	 * @return array<string,Frontend_Library>
	 */
	public function get_all(): array {
		return $this->patterns;
	}

	/**
	 * @param string[] $names
	 *
	 * @return Layout_Library[]
	 */
	public function resolve_template_patterns( array $names ): array {
		$names_keys       = array_flip( $names );
		$patterns_by_name = array_intersect_key( $this->patterns, $names_keys );

		return array_filter(
			$patterns_by_name,
			fn( $pattern ) => $pattern instanceof Layout_Library
		);
	}

	public function get_card_items_wrapper( Post_Selection_Settings $post_selection_settings ): ?Html_Tag {
		$patterns = $this->resolve_selection_template_patterns( $post_selection_settings );

		return Template_Builder::row_wrapper( $patterns );
	}

	/**
	 * @return Html_Tag[]
	 */
	public function get_card_item_outers( Post_Selection_Settings $post_selection_settings, Token_Factory $token_factory ): array {
		$patterns = $this->resolve_selection_template_patterns( $post_selection_settings );
		$items    = $token_factory->variable( 'items' );
		$outers   = Template_Builder::field_wrappers( $patterns, $items );

		if ( Post_Selection_Settings::CLASS_GENERATION_NONE === $post_selection_settings->classes_generation ) {
			return $outers;
		}

		$bem_name = $post_selection_settings->get_bem_name();

		return Template_Builder::prefix_selection_classes(
			$outers,
			sprintf( '%s__list', $bem_name ),
			sprintf( '%s__track', $bem_name )
		);
	}

	/**
	 * @return array<string,string|Variable_Token>
	 */
	public function get_card_item_attributes( Post_Selection_Settings $post_selection_settings, Variable_Token $item ): array {
		$patterns = $this->resolve_selection_template_patterns( $post_selection_settings );

		return Template_Builder::item_attributes( $patterns, $item );
	}

	/**
	 * @param class-string<Frontend_Library>[] $pattern_classes
	 */
	protected function register_patterns( array $pattern_classes ): void {
		foreach ( $pattern_classes as $pattern_class ) {
			$pattern = $this->resolve( $pattern_class );

			$this->patterns[ $pattern->get_name() ] = $pattern;
		}
	}

	/**
	 * @return class-string<Template_Pattern>[]
	 */
	protected function resolve_selection_template_patterns( Post_Selection_Settings $post_selection_settings ): array {
		$template_patterns = array();

		foreach ( $this->patterns as $pattern ) {
			if ( $pattern instanceof Selection_Library &&
				$pattern->is_target_selection( $post_selection_settings ) ) {
				$template_pattern = $pattern->get_selection_template_pattern( $post_selection_settings );

				if ( is_string( $template_pattern ) ) {
					$template_patterns[] = $template_pattern;
				}
			}
		}

		return $template_patterns;
	}
}
