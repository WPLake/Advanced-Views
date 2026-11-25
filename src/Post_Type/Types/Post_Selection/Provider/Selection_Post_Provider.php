<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Provider;

defined( 'ABSPATH' ) || exit;

use Exception;
use Org\Wplake\Advanced_Views\Post_Query\Core\Context\Query_Context;
use Org\Wplake\Advanced_Views\Post_Query\Provider\Post_Provider;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Post_Query;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\arr;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\int;

class Selection_Post_Provider implements Post_Provider {
	private Selection_Settings_Storage $settings_storage;
	private Post_Query $post_query;

	public function __construct( Selection_Settings_Storage $settings_storage, Post_Query $post_query ) {
		$this->settings_storage = $settings_storage;
		$this->post_query       = $post_query;
	}

	public function get_posts( string $source_id, Query_Context $context ): array {
		try {
			$selection_settings = $this->settings_storage->get( $source_id );
		} catch ( Exception $exception ) {
			return array();
		}

		if ( ! $selection_settings->isLoaded() ) {
			return array();
		}

		$result = $this->post_query->query_posts( $selection_settings, $context );

		return array_values(
			array_map(
				fn( $post_id ): int => int( $post_id ),
				arr( $result, 'postIds' )
			)
		);
	}
}
