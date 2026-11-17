<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Post_Selection_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Core\Web_Component\Cpt_Component_Factory;
use Org\Wplake\Advanced_Views\Post_Type\Core\Web_Component\Cpt_Component_Renderer;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Layout_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Acf\Groups\Post_Selection_Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Layout\Outers_Builder;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Selection\Selection_Wrapper_Resolver;
use Org\Wplake\Advanced_Views\Template\Html\Composite_Attribute;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Tokens\IF_Branch;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Tokens\Loop_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Functional\Shortcode_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;
use function Org\Wplake\Advanced_Views\Template\Html\compose_attribute;
use function Org\Wplake\Advanced_Views\Template\Html\tag;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
class Post_Selection_Markup {
	private Selection_Wrapper_Resolver $card_composer;
	protected Token_Factory_Storage $token_factory_storage;
	private Layout_Cpt $layout_cpt;
	protected Cpt_Component_Renderer $renderer;

	public function __construct( Selection_Wrapper_Resolver $card_composer, Token_Factory_Storage $token_factory_storage, Layout_Cpt $layout_cpt, Cpt_Component_Renderer $renderer ) {
		$this->card_composer         = $card_composer;
		$this->token_factory_storage = $token_factory_storage;
		$this->layout_cpt            = $layout_cpt;
		$this->renderer              = $renderer;
	}

	public function render_extra_markup( Post_Selection_Settings $post_selection_settings ): string {
		$nodes = $this->extra_nodes( $post_selection_settings );

		return $this->renderer->render_html( $nodes, 1 );
	}

	/**
	 * @return Html_Node[]
	 */
	protected function extra_nodes( Post_Selection_Settings $post_selection_settings ): array {
		$nodes = $this->context_posts_nodes( $post_selection_settings );

		if ( $post_selection_settings->is_with_pagination ) {
			$token_factory     = $this->token_factory_storage->resolve_token_factory( $post_selection_settings->template_engine );
			$pagination_branch = $this->selection_pagination_branch( $token_factory, $post_selection_settings );
			$pagination_if     = $token_factory->if()
				->set_if_branch( $pagination_branch );

			return array( ...$nodes, $pagination_if );
		}

		return $nodes;
	}

	/**
	 * @return Html_Node[]
	 */
	protected function context_posts_nodes( Post_Selection_Settings $post_selection_settings ): array {
		if ( Post_Selection_Settings::ITEMS_SOURCE_CONTEXT_POSTS === $post_selection_settings->items_source ) {
			$token_factory     = $this->token_factory_storage->resolve_token_factory( $post_selection_settings->template_engine );
			$pagination_branch = $this->pagination_branch( $token_factory );
			$pagination_if     = $token_factory->if()
				->set_if_branch( $pagination_branch );

			return array( $pagination_if );
		}

		return array();
	}

	protected function pagination_branch( Token_Factory $token_factory ): IF_Branch {
		$pages_var  = $token_factory->variable( Hard_Post_Selection_Cpt::variable_name() )
									->add_item_path( 'pages_amount' );
		$comparison = $token_factory->comparison()
									->set_left_operand( $pages_var )
									->set_comparison_greater()
									->set_right_operand( $token_factory->literal( 1 ) );

		$paginate_links = $token_factory->functions()
										->paginate_links( array( 'total' => $pages_var ) );

		$pagination = tag( 'div' )
			->content( $paginate_links );

		$body = array( $pagination );

		return $token_factory->if_branch()
			->set_condition( $comparison )
			->set_body( $body );
	}

	protected function selection_pagination_branch( Token_Factory $token_factory, Post_Selection_Settings $post_selection_settings ): IF_Branch {
		$pages_amount_var = $token_factory->variable( Hard_Post_Selection_Cpt::variable_name() )
											->add_item_path( 'pages_amount' );

		$one_literal = $token_factory->literal( 1 );

		$present_pages_comparison = $token_factory->comparison()
			->set_left_operand( $pages_amount_var )
			->set_comparison_greater()
			->set_right_operand( $one_literal );

		$body = $this->compose_pagination( $token_factory, $post_selection_settings, $pages_amount_var );

		return $token_factory->if_branch()
			->set_condition( $present_pages_comparison )
			->set_body( $body );
	}

	/**
	 * @return Html_Node[]
	 */
	protected function compose_pagination(
		Token_Factory $token_factory,
		Post_Selection_Settings $post_selection_settings,
		Variable_Token $pages_amount_var
	): array {
		$type_var    = $token_factory->variable( Hard_Post_Selection_Cpt::variable_name() )
											->add_item_path( 'pagination_type' );
		$card_id_var = $token_factory->variable( Hard_Post_Selection_Cpt::variable_name() )
											->add_item_path( 'id' );

		// do not consider the 'class generation' = none, as the classes are must have for the js code to work.
		$bem_name      = $post_selection_settings->get_bem_name();
		$class_prefix  = sprintf( '%s__pagination %s__pagination--type--', $bem_name, $bem_name );
		$class         = compose_attribute(
			array(
				$class_prefix,
				$type_var,
			)
		);
		$content_nodes = $this->pagination_content( $token_factory, $post_selection_settings );

		$pagination = tag( 'div' )
			->attributes(
				array(
					'class'             => $class,
					'data-pages-amount' => $pages_amount_var,
					'data-card-id'      => $card_id_var,
				)
			)
			->content( ...$content_nodes );

		return array( $pagination );
	}

	/**
	 * @return Html_Node[]
	 */
	protected function pagination_content( Token_Factory $token_factory, Post_Selection_Settings $post_selection_settings ): array {
		$pagination_type = $post_selection_settings->pagination_type;

		$builders = array(
			Post_Selection_Settings::PAGINATION_TYPE_LOAD_MORE_BUTTON => fn() => array( $this->load_more_button( $token_factory, $post_selection_settings ) ),
			Post_Selection_Settings::PAGINATION_TYPE_PAGE_NUMBERS     => fn() => array( $this->page_links( $token_factory, $post_selection_settings ) ),
		);

		// infinity has no markup.
		return key_exists( $pagination_type, $builders ) ?
			$builders[ $pagination_type ]() :
			array();
	}

	protected function load_more_button( Token_Factory $token_factory, Post_Selection_Settings $post_selection_settings ): Html_Tag {
		$label_var = $token_factory->variable( Hard_Post_Selection_Cpt::variable_name() )
									->add_item_path( 'load_more_label' );

		$bem_name     = $post_selection_settings->get_bem_name();
		$button_attrs = array( 'class' => sprintf( '%s__load-more', $bem_name ) );

		return tag( 'button' )
			->attributes( $button_attrs )
			->content( $label_var );
	}

	protected function page_links(
		Token_Factory $token_factory,
		Post_Selection_Settings $post_selection_settings
	): Loop_Token {
		$pages_amount_var = $token_factory->variable( Hard_Post_Selection_Cpt::variable_name() )
											->add_item_path( 'pages_amount' );

		$first_page      = $token_factory->literal( 1 );
		$pages_range     = $token_factory->range()
										->set_from( $first_page )
										->set_to( $pages_amount_var );
		$page_number_var = $token_factory->variable( 'page_number' );

		$loop_body = $this->compose_page_link( $token_factory, $post_selection_settings );

		return $token_factory->loop()
								->set_source_variable( $pages_range )
								->set_item_variable( $page_number_var )
								->set_body( $loop_body );
	}

	/**
	 * @return Html_Node[]
	 */
	protected function compose_page_link(
		Token_Factory $token_factory,
		Post_Selection_Settings $post_selection_settings
	): array {
		$bem_name         = $post_selection_settings->get_bem_name();
		$active_class_var = $token_factory->variable( 'active_class' );
		$page_number_var  = $token_factory->variable( 'page_number' );

		$first_page_literal = $token_factory->literal( 1 );

		$first_page_comparison = $token_factory->comparison()
			->set_left_operand( $first_page_literal )
			->set_comparison_equal()
			->set_right_operand( $page_number_var );

		$active_html_class = sprintf( ' %s__page--active', $bem_name );
		$active_literal    = $token_factory->literal( $active_html_class );
		$empty_literal     = $token_factory->literal( '' );

		$class_value = $token_factory->conditional_value()
									->set_comparison( $first_page_comparison )
									->set_accept_value( $active_literal )
									->set_decline_value( $empty_literal );

		$active_class_assignment = $token_factory->assignment()
												->set_variable( $active_class_var )
												->set_value( $class_value );

		$page_class = sprintf( '%s__page', $bem_name );
		$class      = compose_attribute(
			array(
				$page_class,
				$active_class_var,
			)
		);

		$tags = tag( 'a' )
			->attributes(
				array(
					'class'            => $class,
					'data-page-number' => $page_number_var,
					'href'             => '#',
				)
			)
			->content( $page_number_var );

		return array( $active_class_assignment, $tags );
	}

	protected function items_wrapper_tag( Post_Selection_Settings $post_selection_settings, string $class_name = '' ): Html_Tag {
		$classes  = '';
		$wrapper  = $this->card_composer->resolve_items_wrapper( $post_selection_settings );
		$external = $wrapper instanceof Html_Tag ?
			string( $wrapper->get_attributes(), 'class' ) :
			'';

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

	protected function shortcode_token( Post_Selection_Settings $post_selection_settings ): Shortcode_Token {
		$token_factory = $this->token_factory_storage->resolve_token_factory( $post_selection_settings->template_engine );

		$id_var      = $token_factory->variable( Hard_Post_Selection_Cpt::variable_name() )
											->add_item_path( 'layout_id' );
		$post_id_var = $token_factory->variable( 'post_id' );

		$asset_attrs = $this->card_composer->resolve_item_attributes( $post_selection_settings, $post_id_var );

		return $token_factory->shortcode( $this->layout_cpt->shortcode() )
			->set_attributes(
				array_merge(
					array(
						'id'        => $id_var,
						'object-id' => $post_id_var,
					),
					$asset_attrs
				)
			);
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

		if ( $is_load_more ) {
			$this->print_loop( $post_selection_settings );
		} else {
			$this->print_full_markup( $post_selection_settings );
		}
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

		$content    = array( $if, ...$this->extra_nodes( $post_selection_settings ) );
		$root_class = $this->root_class( $token_factory, $post_selection_settings );

		$root = Cpt_Component_Factory::create_web_component( $post_selection_settings, $content )
			->attributes( array( 'class' => $root_class ) );

		$html = $this->renderer->render_component( $root );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $html;
	}

	protected function items_branch( Token_Factory $token_factory, Post_Selection_Settings $post_selection_settings ): IF_Branch {
		$post_ids_var = $token_factory->variable( Hard_Post_Selection_Cpt::variable_name() )
										->add_item_path( 'post_ids' );

		$body = array( $this->items_tag( $token_factory, $post_selection_settings ) );

		return $token_factory->if_branch()
			->set_condition( $post_ids_var )
			->set_body( $body );
	}

	protected function not_found_branch( Token_Factory $token_factory, Post_Selection_Settings $post_selection_settings ): IF_Branch {
		$body = array( $this->not_found_tag( $token_factory, $post_selection_settings ) );

		return $token_factory->if_branch()
			->set_body( $body );
	}

	protected function items_tag( Token_Factory $token_factory, Post_Selection_Settings $post_selection_settings ): Html_Tag {
		$item_outers = $this->card_composer->resolve_item_outers( $post_selection_settings, $token_factory );
		$loop        = array( $this->loop_token( $token_factory, $post_selection_settings ) );
		$content     = Outers_Builder::compose_outers( $item_outers, $loop );

		return $this->items_wrapper_tag( $post_selection_settings )
			->content( ...$content );
	}

	protected function print_loop( Post_Selection_Settings $post_selection_settings ): void {
		$token_factory = $this->token_factory_storage->resolve_token_factory( $post_selection_settings->template_engine );

		$loop = $this->loop_token( $token_factory, $post_selection_settings );

		$html = $this->renderer->render_html( array( $loop ), 2 );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $html;
	}

	protected function loop_token( Token_Factory $token_factory, Post_Selection_Settings $post_selection_settings ): Loop_Token {
		$post_ids_var = $token_factory->variable( Hard_Post_Selection_Cpt::variable_name() )
										->add_item_path( 'post_ids' );

		$post_id_var = $token_factory->variable( 'post_id' );

		$loop_body = array( $this->shortcode_token( $post_selection_settings ) );

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

	protected function root_class( Token_Factory $token_factory, Post_Selection_Settings $post_selection_settings ): Composite_Attribute {
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

		return compose_attribute( $parts );
	}
}
