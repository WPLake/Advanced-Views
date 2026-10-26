<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Post_Selections;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Assets\Front_Assets;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Post_Selection_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Groups\Post_Selection_Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Template\Html\Html_Renderer;
use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Htmlable;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Template\Html_Wrapper;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Token_Factory;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Token_Factory_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Conditional\IF_Branch;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\compose_html;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
class Post_Selection_Markup {
	private Front_Assets $front_assets;
	protected Token_Factory_Storage $token_factory_storage;
	private Layouts_Cpt $layout_cpt;
	protected Html_Renderer $html_renderer;

	public function __construct( Front_Assets $front_assets, Token_Factory_Storage $token_factory_storage, Layouts_Cpt $layout_cpt, Html_Renderer $html_renderer ) {
		$this->html_renderer = $html_renderer;
		$this->front_assets          = $front_assets;
		$this->token_factory_storage = $token_factory_storage;
		$this->layout_cpt            = $layout_cpt;
	}

	public function print_extra_markup( Post_Selection_Settings $post_selection_settings ): void {
		if ( Post_Selection_Settings::ITEMS_SOURCE_CONTEXT_POSTS !== $post_selection_settings->items_source ) {
			return;
		}

		$token_factory = $this->token_factory_storage->resolve_token_factory( $post_selection_settings->template_engine );

		$pagination_branch = $this->pagination_branch( $token_factory );

		$if = $token_factory->if()->set_if_branch( $pagination_branch );

		echo "\r\n\t";

		$if->display();

		$token_factory->formatter()->new_line();
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
					fn() => print( "\r\n\t\t\t" ),
					fn() => $token_factory->functions()
											->paginate_links( array( 'total' => $pages_var ) ),
					fn() => print( "\n\t\t" )
				);

			$tags = array(
				fn() => $token_factory->formatter()
									->new_line()
									->tab( 2 ),
				$pagination,
				fn() => print( "\r\n\t" ),
			);

			$this->html_renderer->print( ...$tags );
		};

		$body       = $token_factory->html( $print_pagination );

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

	/**
	 * @param Html_Node[] $inner
	 *
	 * @return Html_Node[]
	 */
	protected function wrap_with_tag( Html_Tag $tag, int &$tabs_number, Token_Factory $token_factory, array $inner ): array {
		$print_opening_tab = function () use ( $token_factory, &$tabs_number ) {
			$token_factory->formatter()->tab( ++$tabs_number );
		};

		$print_closing_tab = function () use ( $token_factory, &$tabs_number ) {
			$token_factory->formatter()->tab( --$tabs_number );
		};

		$members = array_merge(
			array( fn() => $token_factory->formatter()->new_line() ),
			$inner,
			array( $print_closing_tab )
		);
		$nested  = $tag->content( ...$members );

		return array(
			$print_opening_tab,
			$nested,
			fn() => $token_factory->formatter()->new_line(),
		);
	}

	protected function print_shortcode( Post_Selection_Settings $post_selection_settings ): void {
		$token_factory = $this->token_factory_storage->resolve_token_factory( $post_selection_settings->template_engine );

		$id_var      = $token_factory->variable( Hard_Post_Selection_Cpt::variable_name() )
											->add_item_path( 'layout_id' );
		$post_id_var = $token_factory->variable( 'post_id' );

		printf( '[%s', esc_html( $this->layout_cpt->shortcode() ) );

		$token_factory->formatter()
						->attributes(
							array(
								'id'        => $id_var,
								'object-id' => $post_id_var,
							)
						);

		$asset_attrs = $this->front_assets->get_card_shortcode_attrs( $post_selection_settings );

		foreach ( $asset_attrs as $attr => $value ) {
			printf( ' %s="%s"', esc_html( $attr ), esc_html( $value ) );
		}

		echo ']';
		$token_factory->formatter()->new_line();
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

		$token_factory = $this->token_factory_storage->resolve_token_factory( $post_selection_settings->template_engine );

		$tabs_number = 1;

		ob_start();

		if ( $is_load_more ) {
			$this->print_loop( $token_factory, $post_selection_settings, $tabs_number );
		} else {
			$this->print_full_markup( $token_factory, $post_selection_settings, $tabs_number );
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

	protected function print_full_markup( Token_Factory $token_factory, Post_Selection_Settings $post_selection_settings, int $tabs_number ): void {
		$items_branch = $this->items_branch( $token_factory, $post_selection_settings, $tabs_number );

		$if = $token_factory->if()->set_if_branch( $items_branch );

		if ( strlen( $post_selection_settings->no_posts_found_message ) > 0 ) {
			$not_found_branch = $this->not_found_branch( $token_factory, $post_selection_settings, $tabs_number );

			$if->set_else_branch( $not_found_branch );
		}

		$new_line       = fn() => $token_factory->formatter()->new_line();

		$print_start = function () use ( $token_factory, $tabs_number ) {
			$token_factory->formatter()
							->new_line( 2 )
							->tab( $tabs_number );
		};

		$content        = array(
			$print_start,
			$if,
			$new_line,
			fn() => $this->print_extra_markup( $post_selection_settings ),
			$new_line,
		);
		$is_shadow_root = Post_Selection_Settings::WEB_COMPONENT_SHADOW_DOM_DECLARATIVE === $post_selection_settings->web_component;
		$template_tag   = tag( 'template' )
			->attributes( array( 'shadowrootmode' => 'open' ) )
			->content( ...$content );
		$root_content   = $is_shadow_root ?
			array(
				$new_line,
				$template_tag,
				$new_line,
			) :
			$content;

		$root_class = $this->root_class( $token_factory, $post_selection_settings );
		$tag_name   = $post_selection_settings->get_tag_name();

		$root = tag( $tag_name )
			->attributes( array( 'class' => $root_class ) )
			->content( ...$root_content );

		$this->html_renderer->print( $root, $new_line );
	}

	protected function items_branch( Token_Factory $token_factory, Post_Selection_Settings $post_selection_settings, int &$tabs_number ): IF_Branch {
		$post_ids_var = $token_factory->variable( Hard_Post_Selection_Cpt::variable_name() )
										->add_item_path( 'post_ids' );

		$print_items = function () use ( $post_selection_settings, $token_factory, &$tabs_number ) {
			$tabs_number = $this->print_items_markup( $token_factory, $post_selection_settings, $tabs_number );
		};

		$body         = $token_factory->html( $print_items );

		return $token_factory->if_branch()
			->set_condition( $post_ids_var )
			->set_body( $body );
	}

	protected function not_found_branch( Token_Factory $token_factory, Post_Selection_Settings $post_selection_settings, int &$tabs_number ): IF_Branch {
		$print_not_found = function () use ( $token_factory, $post_selection_settings, &$tabs_number ) {
			$tabs_number = $this->print_not_found_markup( $token_factory, $post_selection_settings, $tabs_number );
		};

		$body = $token_factory->html( $print_not_found );

		return $token_factory->if_branch()
			->set_body( $body );
	}

	protected function print_items_markup(
		Token_Factory $token_factory,
		Post_Selection_Settings $post_selection_settings,
		int $tabs_number
	): int {
		$item_outers = $this->front_assets->get_card_item_outers( $post_selection_settings );

		$print_loop = function () use ( $token_factory, $post_selection_settings, &$tabs_number ) {
			$tabs_number = $this->print_loop( $token_factory, $post_selection_settings, $tabs_number );
		};

		$content = array( $print_loop );

		// the innermost outer goes first.
		foreach ( array_reverse( $item_outers ) as $outer ) {
			$content = $this->wrap_with_tag( $this->item_outer_tag( $outer, $token_factory ), $tabs_number, $token_factory, $content );
		}

		$items_wrapper = $this->wrap_with_tag(
			$this->items_wrapper_tag( $post_selection_settings ),
			$tabs_number,
			$token_factory,
			$content
		);

		$print_branches_end = function () use ( $token_factory, $post_selection_settings, &$tabs_number ) {
			if ( strlen( $post_selection_settings->no_posts_found_message ) > 0 ) {
				$token_factory->formatter()
								->tab( --$tabs_number );
			}

			// endif in any case.
			$token_factory->formatter()
							->tab( --$tabs_number );
		};

		$tags = array(
			fn() => $token_factory->formatter()
								->new_line(),
			...$items_wrapper,
			$print_branches_end,
		);

		$this->html_renderer->print( ...$tags );

		return $tabs_number;
	}

	protected function print_loop(
		Token_Factory $token_factory,
		Post_Selection_Settings $post_selection_settings,
		int $tabs_number
	): int {
		$post_ids_var = $token_factory->variable( Hard_Post_Selection_Cpt::variable_name() )
										->add_item_path( 'post_ids' );

		$post_id_var = $token_factory->variable( 'post_id' );

		$print_shortcode_block = function () use ( $tabs_number, $post_selection_settings, $token_factory ) {
			$tabs_number += 2;

			$token_factory->formatter()
				->new_line()
							->tab( $tabs_number );

			$this->print_shortcode( $post_selection_settings );

			$token_factory->formatter()
							->tab( --$tabs_number );
		};

		$loop_body = $token_factory->html( $print_shortcode_block );

		$token_factory->formatter()
						->tab( ++$tabs_number );

		$token_factory->loop()
						->set_source_variable( $post_ids_var )
						->set_item_variable( $post_id_var )
						->set_body( $loop_body )
						->display();

		$token_factory->formatter()
						->new_line();

		return $tabs_number;
	}

	protected function print_not_found_markup(
		Token_Factory $token_factory,
		Post_Selection_Settings $post_selection_settings,
		int $tabs_number
	): int {
		$tabs_number += 2;

		$token_factory->formatter()
						->new_line()
						->tab( $tabs_number );

		$no_posts_message_class = Post_Selection_Settings::CLASS_GENERATION_NONE !== $post_selection_settings->classes_generation ?
			sprintf( '%s__no-posts-message', $post_selection_settings->get_bem_name() ) :
			'';
		$message_var            = $token_factory
			->variable( Hard_Post_Selection_Cpt::variable_name() )
			->add_item_path( 'no_posts_found_message' );

		$tags      = tag( 'div' )
			->attributes( array( 'class' => $no_posts_message_class ) )
			->content(
				$message_var
			);

		$this->html_renderer->print( $tags );

		$token_factory->formatter()
						->new_line()
			->tab( --$tabs_number );

		return $tabs_number;
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
