<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Query\Entity;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Query\Core\Settings\Query_Settings;
use Org\Wplake\Advanced_Views\Post_Query\Core\Context\Context_Container_Base;
use Org\Wplake\Advanced_Views\Post_Query\Core\Context\Query_Context_Container;
use Org\Wplake\Advanced_Views\Post_Query\Core\Post_Query_Builder;
use Org\Wplake\Advanced_Views\Post_Query\Core\Query_Utils;

final class Pagination_Query_Builder implements Post_Query_Builder, Query_Context_Container {
	use Context_Container_Base;

	const NO_LIMIT_VALUE = - 1;

	public function build_post_query( Query_Settings $selection_settings ): array {
		$is_enabled = $selection_settings->is_with_pagination();

		$arguments = array(
			'posts_per_page' => array(
				'condition' => $is_enabled,
				'value'     => fn() => $this->resolve_posts_per_page( $selection_settings ),
			),
			'offset'         => array(
				'condition' => $is_enabled,
				'value'     => fn() => ( $this->get_context()->get_page_number() - 1 ) *
										$selection_settings->get_pagination_per_page(),
			),
		);

		return Query_Utils::filter_arguments( $arguments );
	}

	protected function resolve_posts_per_page( Query_Settings $selection ): int {
		if ( self::NO_LIMIT_VALUE === $selection->get_limit() ) {
			return $selection->get_pagination_per_page();
		}

		$left_items_count = $this->get_left_items_count( $selection );

		return is_int( $left_items_count ) ?
			$left_items_count :
			$selection->get_pagination_per_page();
	}

	protected function get_left_items_count( Query_Settings $selection ): ?int {
		$over_amount = ( $selection->get_pagination_per_page() * $this->get_context()->get_page_number() ) -
						$selection->get_limit();

		if ( $over_amount > 0 ) {
			$posts_per_page = $selection->get_pagination_per_page() - $over_amount;

			return max( $posts_per_page, 0 );
		}

		return null;
	}
}
