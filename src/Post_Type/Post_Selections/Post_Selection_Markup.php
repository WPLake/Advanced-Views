<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Post_Selections;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Core\Web_Component\Web_Component_Factory;
use Org\Wplake\Advanced_Views\Post_Type\Core\Web_Component\Web_Component_Printer;
use Org\Wplake\Advanced_Views\Assets\Front_Assets;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Post_Selection_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Groups\Post_Selection_Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Htmlable;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Template\Html_Wrapper;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Token_Factory;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Token_Factory_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Conditional\IF_Branch;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Loop_Token;
use function Org\Wplake\Advanced_Views\Template\Html\compose_html;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
class Post_Selection_Markup {
	private Front_Assets $front_assets;
	protected Token_Factory_Storage $token_factory_storage;
	private Layouts_Cpt $layout_cpt;
	protected Web_Component_Printer $printer;
	protected Web_Component_Factory $web_component_factory;

	public function __construct( Front_Assets $front_assets, Token_Factory_Storage $token_factory_storage, Layouts_Cpt $layout_cpt, Web_Component_Printer $printer, Web_Component_Factory $web_component_factory ) {
		$this->front_assets          = $front_assets;
		$this->token_factory_storage = $token_factory_storage;
		$this->layout_cpt            = $layout_cpt;
		$this->printer               = $printer;
		$this->web_component_factory = $web_component_factory;
	}

	// public, as called from Upgrades.php.
	public function print_extra_markup( Post_Selection_Settings $post_selection_settings ): void {
		$nodes = $this->separate_with_breaks( $this->extra_nodes( $post_selection_settings ) );

		$this->printer->start()
						->print_indented( fn() => $this->printer->print_html( ...$nodes ) );
	}

	/**
	 * @return Html_Node[]
	 */
	protected function extra_nodes( Post_Selection_Settings $post_selection_settings ): array {
		if ( Post_Selection_Settings::ITEMS_SOURCE_CONTEXT_POSTS !== $post_selection_settings->items_source ) {
			return array();
		}

		$token_factory     = $this->token_factory_storage->resolve_token_factory( $post_selection_settings->template_engine );
		$pagination_branch = $this->pagination_branch( $token_factory );

		return array( $token_factory->if()->set_if_branch( $pagination_branch ) );
	}

	/**
	 * Separates the non-tag nodes with a blank line
	 *
	 * @param Html_Node[] $nodes
	 *
	 * @return Html_Node[]
	 */
	protected function separate_with_breaks( array $nodes ): array {
		$separated = array();

		foreach ( $nodes as $node ) {
			if ( array() !== $separated ) {
				$separated[] = fn() => $this->printer->new_line()->line_start();
			}

			$separated[] = $node;
		}

		return $separated;
	}

	protected function pagination_branch( Token_Factory $token_factory ): IF_Branch {
		$pages_var  = $token_factory->variable( Hard_Post_Selection_Cpt::variable_name() )
									->add_item_path( 'pages_amount' );
		$comparison = $token_factory->comparison()
									->set_left_operand( $pages_var )
									->set_comparison_greater()
									->set_right_operand( $token_factory->literal( 1 ) );

		$print_pagination = function () use ( $token_factory ) {
			$pages_var = $token_factory->variable( Hard_Post_Selection_Cpt::variable_name() )
										->add_item_path( 'pages_amount' );

			$pagination = tag( 'div' )
				->content(
					fn() => $token_factory->functions()
											->paginate_links( array( 'total' => $pages_var ) )
				);

			$this->printer->print_html( $pagination );
		};

		$body = $token_factory->html( fn() => $this->printer->print_indented( $print_pagination ) );

		return $token_factory->if_branch()
			->set_condition( $comparison )
			->set_body( $body );
	}

	protected function items_wrapper_tag( Post_Selection_Settings $post_selection_settings, string $class_name = '' ): Html_Tag {
		$classes  = '';
		$external = $this->front_assets->get_card_items_wrapper_class( $post_selection_settings );

		if ( Post_Selection_Settings::CLASS_GENERATION_NONE !== $post_selection_settings->classes_generation ) {
			$classes .= $post_selection_settings->get_bem_name() . '__items';
			$classes .= '' !== $class_name ?
				' ' . $class_name :
				'';
		}

		// we never skip the external, e.g. 'splide' as it's a library requirement.
		if ( '' !== $external ) {
			$classes .= '' === $classes ?
				$external :
				' ' . $external;
		}

		return tag( 'div' )
			->attributes( array( 'class' => $classes ) );
	}

	protected function item_outer_tag( Html_Wrapper $outer, Token_Factory $token_factory ): Html_Tag {
		$attrs = $outer->attrs;

		foreach ( $outer->variable_attrs as $attr => $variable_info ) {
			$attrs[ $attr ] = $token_factory->variable( $variable_info['field_id'] )
											->add_item_path( $variable_info['item_key'] );
		}

		return tag( $outer->tag )
			->attributes( $attrs );
	}

	protected function print_shortcode( Post_Selection_Settings $post_selection_settings ): void {
		$token_factory = $this->token_factory_storage->resolve_token_factory( $post_selection_settings->template_engine );

		$id_var      = $token_factory->variable( Hard_Post_Selection_Cpt::variable_name() )
											->add_item_path( 'layout_id' );
		$post_id_var = $token_factory->variable( 'post_id' );

		$asset_attrs = $this->front_assets->get_card_shortcode_attrs( $post_selection_settings );

		$token_factory->shortcode( $this->layout_cpt->shortcode() )
			->set_attributes(
				array_merge(
					array(
						'id'        => $id_var,
						'object-id' => $post_id_var,
					),
					$asset_attrs
				)
			)
			->display();
	}

	public function print_markup(
		Post_Selection_Settings $post_selection_settings,
		bool $is_load_more = false,
		bool $is_ignore_custom_markup = false
	): void {
		if ( ! $is_ignore_custom_markup &&
			strlen( $post_selection_settings->custom_markup ) > 0 &&
			! $is_load_more ) {
			$custom_markup = trim( $post_selection_settings->custom_markup );

			if ( strlen( $custom_markup ) > 0 ) {
				// @phpcs:ignore WordPress.Security.EscapeOutput
				echo $custom_markup;

				return;
			}
		}

		ob_start();

		if ( $is_load_more ) {
			$this->print_loop( $post_selection_settings );
		} else {
			$this->print_full_markup( $post_selection_settings );
		}

		$markup = (string) ob_get_clean();

		// remove the empty class attribute if the generation is disabled.
		if ( Post_Selection_Settings::CLASS_GENERATION_NONE === $post_selection_settings->classes_generation ) {
			$markup = str_replace( ' class=""', '', $markup );
		}

		// @phpcs:ignore WordPress.Security.EscapeOutput
		echo $markup;
	}

	public function print_layout_css( Post_Selection_Settings $post_selection_settings ): void {
		if ( false === $post_selection_settings->is_use_layout_css ) {
			return;
		}

		$message = __(
			'Manually edit these rules by disabling Layout Rules, otherwise these rules are updated every time you press the Update button',
			'acf-views'
		);

		echo "/*BEGIN LAYOUT_RULES*/\n";
		printf( "/*%s*/\n", esc_html( $message ) );

		$safe_rules = array();

		foreach ( $post_selection_settings->layout_rules as $layout_rule ) {
			$screen = 0;
			switch ( $layout_rule->screen ) {
				case Post_Selection_Layout_Settings::SCREEN_TABLET:
					$screen = 576;
					break;
				case Post_Selection_Layout_Settings::SCREEN_DESKTOP:
					$screen = 992;
					break;
				case Post_Selection_Layout_Settings::SCREEN_LARGE_DESKTOP:
					$screen = 1400;
					break;
			}

			$safe_rule = array();

			$safe_rule[] = ' display:grid;';

			switch ( $layout_rule->layout ) {
				case Post_Selection_Layout_Settings::LAYOUT_ROW:
					$safe_rule[] = ' grid-auto-flow:column;';
					$safe_rule[] = sprintf( ' grid-column-gap:%s;', esc_html( $layout_rule->horizontal_gap ) );
					break;
				case Post_Selection_Layout_Settings::LAYOUT_COLUMN:
					// the right way is 1fr,
					// but use "1fr" because CodeMirror doesn't recognize it,
					// "1fr" should be replaced with 1fr on the output.
					$safe_rule[] = ' grid-template-columns:"1fr";';
					$safe_rule[] = sprintf( ' grid-row-gap:%s;', esc_html( $layout_rule->vertical_gap ) );
					break;
				case Post_Selection_Layout_Settings::LAYOUT_GRID:
					$safe_rule[] = sprintf( ' grid-template-columns:repeat(%s, "1fr");', esc_html( (string) $layout_rule->amount_of_columns ) );
					$safe_rule[] = sprintf( ' grid-column-gap:%s;', esc_html( $layout_rule->horizontal_gap ) );
					$safe_rule[] = sprintf( ' grid-row-gap:%s;', esc_html( $layout_rule->vertical_gap ) );
					break;
			}

			$safe_rules[ $screen ] = $safe_rule;
		}

		// order is important in media rules.
		ksort( $safe_rules );

		foreach ( $safe_rules as $screen => $safe_rule ) {
			if ( 0 !== $screen ) {
				printf( "\n@media screen and (min-width:%spx) {", esc_html( (string) $screen ) );
			}

			printf( "\n#%s #this__items {\n", esc_html( Post_Selection_Settings::MAGIC_CSS_SELECTOR ) );
			// @phpcs:ignore WordPress.Security.EscapeOutput
			echo join( "\n", $safe_rule );
			echo "\n}\n";

			if ( 0 !== $screen ) {
				echo "}\n";
			}
		}

		echo "\n/*END LAYOUT_RULES*/";
	}

	protected function print_full_markup( Post_Selection_Settings $post_selection_settings ): void {
		$token_factory = $this->token_factory_storage->resolve_token_factory( $post_selection_settings->template_engine );

		$items_branch = $this->items_branch( $token_factory, $post_selection_settings );

		$if = $token_factory->if()->set_if_branch( $items_branch );

		if ( strlen( $post_selection_settings->no_posts_found_message ) > 0 ) {
			$not_found_branch = $this->not_found_branch( $token_factory, $post_selection_settings );

			$if->set_else_branch( $not_found_branch );
		}

		$content    = $this->separate_with_breaks( array( $if, ...$this->extra_nodes( $post_selection_settings ) ) );
		$root_class = $this->root_class( $token_factory, $post_selection_settings );

		$root = $this->web_component_factory->create_web_component( $post_selection_settings, $content )
			->attributes( array( 'class' => $root_class ) );

		$this->printer->print_component( $root );
		$this->printer->new_line();
	}

	protected function items_branch( Token_Factory $token_factory, Post_Selection_Settings $post_selection_settings ): IF_Branch {
		$post_ids_var = $token_factory->variable( Hard_Post_Selection_Cpt::variable_name() )
										->add_item_path( 'post_ids' );

		$print_items = fn() => $this->printer->print_html( $this->items_tag( $token_factory, $post_selection_settings ) );

		$body = $token_factory->html( fn() => $this->printer->print_indented( $print_items ) );

		return $token_factory->if_branch()
			->set_condition( $post_ids_var )
			->set_body( $body );
	}

	protected function not_found_branch( Token_Factory $token_factory, Post_Selection_Settings $post_selection_settings ): IF_Branch {
		$print_not_found = fn() => $this->printer->print_html( $this->not_found_tag( $token_factory, $post_selection_settings ) );

		$body = $token_factory->html( fn() => $this->printer->print_indented( $print_not_found ) );

		return $token_factory->if_branch()
			->set_body( $body );
	}

	protected function items_tag( Token_Factory $token_factory, Post_Selection_Settings $post_selection_settings ): Html_Tag {
		$item_outers = $this->front_assets->get_card_item_outers( $post_selection_settings );
		$content     = array( $this->loop_token( $token_factory, $post_selection_settings ) );

		// the innermost outer goes first.
		foreach ( array_reverse( $item_outers ) as $outer ) {
			$content = array(
				$this->item_outer_tag( $outer, $token_factory )
					->content( ...$content ),
			);
		}

		return $this->items_wrapper_tag( $post_selection_settings )
			->content( ...$content );
	}

	protected function print_loop( Post_Selection_Settings $post_selection_settings ): void {
		$token_factory = $this->token_factory_storage->resolve_token_factory( $post_selection_settings->template_engine );

		$this->printer->start( 2 )
						->tabs();

		$this->loop_token( $token_factory, $post_selection_settings )->display();

		$this->printer->new_line();
	}

	protected function loop_token( Token_Factory $token_factory, Post_Selection_Settings $post_selection_settings ): Loop_Token {
		$post_ids_var = $token_factory->variable( Hard_Post_Selection_Cpt::variable_name() )
										->add_item_path( 'post_ids' );

		$post_id_var = $token_factory->variable( 'post_id' );

		$print_shortcode = fn() => $this->print_shortcode( $post_selection_settings );

		$loop_body = $token_factory->html( fn() => $this->printer->print_indented( $print_shortcode ) );

		return $token_factory->loop()
						->set_source_variable( $post_ids_var )
						->set_item_variable( $post_id_var )
						->set_body( $loop_body );
	}

	protected function not_found_tag( Token_Factory $token_factory, Post_Selection_Settings $post_selection_settings ): Html_Tag {
		$no_posts_message_class = Post_Selection_Settings::CLASS_GENERATION_NONE !== $post_selection_settings->classes_generation ?
			sprintf( '%s__no-posts-message', $post_selection_settings->get_bem_name() ) :
			'';
		$message_var            = $token_factory
			->variable( Hard_Post_Selection_Cpt::variable_name() )
			->add_item_path( 'no_posts_found_message' );

		return tag( 'div' )
			->attributes( array( 'class' => $no_posts_message_class ) )
			->content(
				$message_var
			);
	}

	protected function root_class( Token_Factory $token_factory, Post_Selection_Settings $post_selection_settings ): Htmlable {
		$bem_name = $post_selection_settings->get_bem_name();
		$parts    = array(
			$token_factory->variable( Hard_Post_Selection_Cpt::variable_name() )
							->add_item_path( 'classes' ),
			$bem_name,
		);

		if ( ! $post_selection_settings->has_unique_bem_name() ) {
			$parts[] = sprintf( ' %s--id--', $bem_name );
			$parts[] = $token_factory->variable( Hard_Post_Selection_Cpt::variable_name() )
									->add_item_path( 'id' );
		}

		return compose_html( $parts );
	}
}
