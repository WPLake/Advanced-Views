<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Argument_Parser;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Arguments\Attribute_Reader;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Arguments\Common_Arguments;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Items\Multi_Item_Resolver;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Items\Single\Single_Item_Resolver;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Source;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\arrayOrNull;

/**
 * Parses the (normalized) shortcode attrs of a Layout into the Common_Arguments, and resolves its items.
 */
final class Argument_Parser {
	private Single_Item_Resolver $single_item_resolver;
	private Multi_Item_Resolver $multi_item_resolver;

	public function __construct( Single_Item_Resolver $single_item_resolver, Multi_Item_Resolver $multi_item_resolver ) {
		$this->single_item_resolver = $single_item_resolver;
		$this->multi_item_resolver  = $multi_item_resolver;
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	public function parse_common_arguments( array $attrs ): Common_Arguments {
		$queried_object_id = Request_Context_Reader::get_queried_object_id() ?? 0;
		$classes           = Attribute_Reader::get_string( $attrs, 'class' );
		$custom_arguments  = Attribute_Reader::get_custom_arguments( $attrs );
		// for inner Views.
		$local_data = arrayOrNull( $attrs, 'local-data' );

		return ( new Common_Arguments() )
			->set_queried_object_id( $queried_object_id )
			->set_classes( $classes )
			->set_custom_arguments( $custom_arguments )
			->set_local_data( $local_data );
	}

	/**
	 * @param array<string,mixed> $attrs
	 *
	 * @throws Argument_Exception If the items can't be resolved.
	 */
	public function resolve_source( Layout_Settings $layout_settings, array $attrs, Common_Arguments $common_arguments ): Source {
		$item_type = $layout_settings->item_type;

		return $layout_settings->is_multiple() ?
			$this->multi_item_resolver->resolve_item_ids( $item_type, $attrs, $common_arguments ) :
			$this->single_item_resolver->resolve_item_id( $item_type, $attrs );
	}
}
