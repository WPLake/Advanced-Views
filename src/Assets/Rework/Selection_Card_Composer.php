<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Rework;

use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Selection\Selection_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Template\Builder\Template_Builder;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Template\Pattern\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;

defined( 'ABSPATH' ) || exit;

class Selection_Card_Composer {
	private Library_Container $library_container;

	public function __construct( Library_Container $library_container ) {
		$this->library_container = $library_container;
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
	 * @return class-string<Template_Pattern>[]
	 */
	protected function resolve_selection_template_patterns( Post_Selection_Settings $post_selection_settings ): array {
		$template_patterns = array();

		foreach ( $this->library_container->get_all() as $pattern ) {
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
