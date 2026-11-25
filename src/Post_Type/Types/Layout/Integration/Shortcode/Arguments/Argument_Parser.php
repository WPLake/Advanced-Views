<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Arguments;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Query\Core\Context\Query_Context;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Sources\Item_Ids_Resolver;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Sources\Item_Source_Resolver;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Sources\Legacy_Source_Mapper;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Source;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\arrayOrNull;

/**
 * Parses the (normalized) shortcode attrs of a Layout into the Shortcode_Arguments, or an error.
 */
final class Argument_Parser {
	private Item_Source_Resolver $item_source_resolver;
	private Item_Ids_Resolver $item_ids_resolver;
	private Legacy_Source_Mapper $legacy_source_mapper;

	public function __construct(
		Item_Source_Resolver $item_source_resolver,
		Item_Ids_Resolver $item_ids_resolver,
		Legacy_Source_Mapper $legacy_source_mapper
	) {
		$this->item_source_resolver = $item_source_resolver;
		$this->item_ids_resolver    = $item_ids_resolver;
		$this->legacy_source_mapper = $legacy_source_mapper;
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	public function parse( Layout_Settings $layout_settings, array $attrs ): Shortcode_Arguments {
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
	protected function parse_single( Layout_Settings $layout_settings, array $attrs, int $current_page_id ): Shortcode_Arguments {
		$item_type = $layout_settings->item_type;
		$is_custom = Layout_Settings::ITEM_TYPE_CUSTOM === $item_type;
		// the typed flow is the main one, the legacy arguments of a custom-data Layout are mapped into its result.
		$source   = $is_custom ?
			$this->legacy_source_mapper->map( $attrs, $current_page_id ) :
			$this->item_source_resolver->resolve( $item_type, $attrs, $current_page_id );
		$arg_name = Attribute_Reader::ITEM_ARGUMENTS[ $item_type ] ?? 'object-id';

		if ( $source instanceof Source ) {
			return self::make_arguments( $source, array(), $current_page_id, $attrs );
		}

		// translators: %s is an argument name.
		$error = sprintf( __( '%s argument contains the wrong value', 'acf-views' ), $arg_name );

		return ( new Shortcode_Arguments() )->set_error( $error );
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected function parse_multiple( Layout_Settings $layout_settings, array $attrs, int $current_page_id ): Shortcode_Arguments {
		$error = $this->item_ids_resolver->get_error( $layout_settings->item_type, $attrs );

		if ( is_string( $error ) ) {
			return ( new Shortcode_Arguments() )->set_error( $error );
		}

		$query_context = Query_Context::new_instance()
										->set_post_id( $current_page_id )
										->set_custom_arguments( Attribute_Reader::get_custom_arguments( $attrs ) );
		$item_ids      = $this->item_ids_resolver->resolve( $layout_settings->item_type, $attrs, $query_context );

		return self::make_arguments( new Source(), $item_ids, $current_page_id, $attrs );
	}

	/**
	 * @param int[] $item_ids
	 * @param array<string,mixed> $attrs
	 */
	protected static function make_arguments( Source $source, array $item_ids, int $current_page_id, array $attrs ): Shortcode_Arguments {
		$classes          = Attribute_Reader::get_string( $attrs, 'class' );
		$custom_arguments = Attribute_Reader::get_custom_arguments( $attrs );
		// for inner Views.
		$local_data = arrayOrNull( $attrs, 'local-data' );

		return ( new Shortcode_Arguments() )
			->set_source( $source )
			->set_item_ids( $item_ids )
			->set_current_page_id( $current_page_id )
			->set_classes( $classes )
			->set_custom_arguments( $custom_arguments )
			->set_local_data( $local_data );
	}
}
