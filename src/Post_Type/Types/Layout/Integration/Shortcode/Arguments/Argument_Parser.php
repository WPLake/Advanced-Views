<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Arguments;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Context\Request_Context_Reader;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Items\Multiple\Multiple_Items_Resolver;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Items\Single\Single_Source_Resolver;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Source;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\arrayOrNull;

/**
 * Parses the (normalized) shortcode attrs of a Layout into the Shortcode_Arguments, or an error.
 */
final class Argument_Parser {
	private Single_Source_Resolver $single_source_resolver;
	private Multiple_Items_Resolver $multiple_items_resolver;

	public function __construct(
		Single_Source_Resolver $single_source_resolver,
		Multiple_Items_Resolver $multiple_items_resolver
	) {
		$this->single_source_resolver  = $single_source_resolver;
		$this->multiple_items_resolver = $multiple_items_resolver;
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	public function parse_arguments( Layout_Settings $layout_settings, array $attrs ): Shortcode_Arguments {
		$queried_object_id = Request_Context_Reader::get_queried_object_id() ?? 0;
		$item_type         = $layout_settings->item_type;

		$result = $layout_settings->is_multiple() ?
			$this->multiple_items_resolver->resolve_item_ids( $item_type, $attrs, $queried_object_id ) :
			$this->single_source_resolver->resolve_source( $item_type, $attrs );

		if ( is_string( $result ) ) {
			return ( new Shortcode_Arguments() )->set_error( $result );
		}

		$arguments = $result instanceof Source ?
			( new Shortcode_Arguments() )->set_source( $result ) :
			( new Shortcode_Arguments() )->set_item_ids( $result );

		return $this->fill_common_arguments( $arguments, $attrs, $queried_object_id );
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected function fill_common_arguments( Shortcode_Arguments $arguments, array $attrs, int $queried_object_id ): Shortcode_Arguments {
		$classes          = Attribute_Reader::get_string( $attrs, 'class' );
		$custom_arguments = Attribute_Reader::get_custom_arguments( $attrs );
		// for inner Views.
		$local_data = arrayOrNull( $attrs, 'local-data' );

		return $arguments
			->set_queried_object_id( $queried_object_id )
			->set_classes( $classes )
			->set_custom_arguments( $custom_arguments )
			->set_local_data( $local_data );
	}
}
