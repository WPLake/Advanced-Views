<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Attributes\Attribute_Reader;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Source\Source_Resolver;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Source\Item_Ids_Resolver;
use Org\Wplake\Advanced_Views\Post_Query\Core\Context\Query_Context;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Source;

use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\arrayOrNull;

/**
 * Parses the (normalized) shortcode attrs of a Layout into the Shortcode_Args, or an error.
 */
final class Shortcode_Args_Parser {
	private Source_Resolver $source_resolver;
	private Item_Ids_Resolver $item_ids_resolver;

	public function __construct( Source_Resolver $source_resolver, Item_Ids_Resolver $item_ids_resolver ) {
		$this->source_resolver   = $source_resolver;
		$this->item_ids_resolver = $item_ids_resolver;
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	public function parse( Layout_Settings $layout_settings, array $attrs ): Shortcode_Args {
		// equals to 0 on WooCommerce Shop Page, but in this case pageID can't be gotten with built-in WP functions
		// also works in the taxonomy case.
		$current_page_id = get_queried_object_id();
		$is_multiple     = $layout_settings->is_multiple();

		return $is_multiple ?
			$this->parse_multiple( $layout_settings, $attrs, $current_page_id ) :
			$this->parse_single( $layout_settings, $attrs, $current_page_id );
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected function parse_single( Layout_Settings $layout_settings, array $attrs, int $current_page_id ): Shortcode_Args {
		$source = $this->source_resolver->resolve( $layout_settings->item_type, $attrs, $current_page_id );

		if ( $source instanceof Source ) {
			return self::make_args( $source, array(), $current_page_id, $attrs );
		}

		return Shortcode_Args::make_error( __( 'object-id argument contains the wrong value', 'acf-views' ) );
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected function parse_multiple( Layout_Settings $layout_settings, array $attrs, int $current_page_id ): Shortcode_Args {
		$error = $this->item_ids_resolver->get_error( $layout_settings->item_type, $attrs );

		if ( is_string( $error ) ) {
			return Shortcode_Args::make_error( $error );
		}

		$query_context = Query_Context::new_instance()
										->set_post_id( $current_page_id )
										->set_custom_arguments( Attribute_Reader::get_custom_arguments( $attrs ) );
		$item_ids      = $this->item_ids_resolver->resolve( $layout_settings->item_type, $attrs, $query_context );

		return self::make_args( new Source(), $item_ids, $current_page_id, $attrs );
	}

	/**
	 * @param int[] $item_ids
	 * @param array<string,mixed> $attrs
	 */
	protected static function make_args( Source $source, array $item_ids, int $current_page_id, array $attrs ): Shortcode_Args {
		$classes          = Attribute_Reader::get_string( $attrs, 'class' );
		$custom_arguments = Attribute_Reader::get_custom_arguments( $attrs );
		// for inner Views.
		$local_data = arrayOrNull( $attrs, 'local-data' );

		return new Shortcode_Args(
			$source,
			$item_ids,
			$current_page_id,
			$classes,
			$custom_arguments,
			$local_data
		);
	}
}
