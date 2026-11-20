<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Cpt;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Query\Core\Context\Query_Context;
use Org\Wplake\Advanced_Views\Post_Query\Core\WP_Selection_Query;
use Org\Wplake\Advanced_Views\Post_Query\Selection\Selection_Query_Builder;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
final class Selection_Results_Preview {
	const MAX_ITEMS = 20;

	private Selection_Query_Builder $query_builder;
	private Html_Renderer $html_renderer;

	public function __construct( Selection_Query_Builder $query_builder, Html_Renderer $html_renderer ) {
		$this->query_builder = $query_builder;
		$this->html_renderer = $html_renderer;
	}

	public function render_results( Post_Selection_Settings $selection_settings ): string {
		return $this->html_renderer->render_html( ...$this->compose_results( $selection_settings ) );
	}

	/**
	 * @return Html_Node[]
	 */
	protected function compose_results( Post_Selection_Settings $selection_settings ): array {
		$selection_query = $this->query_posts( $selection_settings );
		$post_ids        = $selection_query->post_ids;
		$total_count     = $selection_query->wp_query->found_posts;

		if ( count( $post_ids ) > 0 ) {
			$list  = self::compose_list( $post_ids );
			$total = self::compose_total( $total_count );

			return array( $list, $total );
		}

		return array( self::compose_empty_message() );
	}

	protected function query_posts( Post_Selection_Settings $selection_settings ): WP_Selection_Query {
		$query_context = Query_Context::new_instance();
		$query_context->set_post_id( $selection_settings->preview_context_post );

		$this->query_builder->set_query_context( $query_context );

		$query_args   = $this->query_builder->build_post_query( $selection_settings );
		$preview_args = array(
			'posts_per_page' => self::MAX_ITEMS,
			'paged'          => 1,
		);
		$query_args   = array_merge( $query_args, $preview_args );

		return new WP_Selection_Query( $query_args );
	}

	/**
	 * @param int[] $post_ids
	 */
	protected static function compose_list( array $post_ids ): Html_Tag {
		$items = array_map(
			fn( int $post_id ) => self::compose_item( $post_id ),
			$post_ids
		);

		$styles     = array(
			'max-height'   => '300px',
			'overflow-y'   => 'auto',
			'padding-left' => '2em',
		);
		$attributes = array( 'style' => $styles );

		return tag( 'ol' )
			->attributes( $attributes )
			->content( ...$items );
	}

	protected static function compose_item( int $post_id ): Html_Tag {
		$post_title = get_the_title( $post_id );
		$is_filled  = strlen( $post_title ) > 0;
		$title      = $is_filled ? $post_title : (string) $post_id;

		$attributes = array(
			'href'   => (string) get_edit_post_link( $post_id, 'raw' ),
			'target' => '_blank',
		);

		$link = tag( 'a' )
			->attributes( $attributes )
			->content( $title );

		return tag( 'li' )->content( $link );
	}

	protected static function compose_total( int $total_count ): Html_Tag {
		// translators: %d: total found items amount.
		$template = __( 'Total found items: %d', 'acf-views' );
		$text     = sprintf( $template, $total_count );

		return tag( 'p' )->content( $text );
	}

	protected static function compose_empty_message(): Html_Tag {
		$message = __(
			'No matching items found on the site. Try to set the context or adjust the filters.',
			'acf-views'
		);

		return tag( 'p' )->content( $message );
	}
}
