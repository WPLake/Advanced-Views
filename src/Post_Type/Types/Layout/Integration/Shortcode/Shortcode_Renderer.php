<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Query\Core\Context\Query_Context;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Layout_Factory;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Source;

use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\arrayOrNull;

/**
 * Renders a Layout (a single or a multiple one) for the shortcode args.
 */
final class Shortcode_Renderer {
	private Layout_Factory $layout_factory;
	private Source_Resolver $source_resolver;
	private Item_Ids_Resolver $item_ids_resolver;
	/**
	 * Used to avoid recursion with post_object/relationship fields
	 *
	 * @var array<string,bool>
	 */
	private array $displaying_views;

	public function __construct(
		Layout_Factory $layout_factory,
		Source_Resolver $source_resolver,
		Item_Ids_Resolver $item_ids_resolver
	) {
		$this->layout_factory    = $layout_factory;
		$this->source_resolver   = $source_resolver;
		$this->item_ids_resolver = $item_ids_resolver;

		$this->displaying_views = array();
	}

	/**
	 * @param array<string,mixed> $attrs
	 *
	 * @return string|null Null if nothing should be displayed (a recursion).
	 * @throws Invalid_Source_Args_Exception
	 */
	public function render( Layout_Settings $layout_settings, array $attrs ): ?string {
		// equals to 0 on WooCommerce Shop Page, but in this case pageID can't be gotten with built-in WP functions
		// also works in the taxonomy case.
		$current_page_id = get_queried_object_id();

		return $layout_settings->is_multiple() ?
			$this->render_multiple( $layout_settings, $attrs, $current_page_id ) :
			$this->render_single( $layout_settings, $attrs, $current_page_id );
	}

	/**
	 * @param array<string,mixed> $attrs
	 *
	 * @throws Invalid_Source_Args_Exception
	 */
	protected function render_single( Layout_Settings $layout_settings, array $attrs, int $current_page_id ): ?string {
		$unique_id = $layout_settings->get_unique_id();
		$source    = $this->source_resolver->resolve( $layout_settings->item_type, $attrs, $current_page_id );

		if ( null === $source ) {
			throw new Invalid_Source_Args_Exception( __( 'object-id argument contains the wrong value', 'acf-views' ) );
		}

		// recursionKey must consist from both. It's allowed to use the same View for a post_object field, but with another id.
		$recursion_key = sprintf( '%s-%s', $unique_id, $source->get_id() );

		/*
		 * In case with post_object and relationship fields can be a recursion
		 * e.g. There is a post_object field. PostA contains link to PostB. PostB contains link to postA. View displays PostA...
		 * In this case just return nothing, without any error message (so user can display PostB in PostA without issues)
		 */
		if ( isset( $this->displaying_views[ $recursion_key ] ) ) {
			return null;
		}

		$this->displaying_views[ $recursion_key ] = true;

		$html = $this->render_html( $source, $unique_id, $attrs, $current_page_id, array() );

		unset( $this->displaying_views[ $recursion_key ] );

		return $html;
	}

	/**
	 * @param array<string,mixed> $attrs
	 *
	 * @throws Invalid_Source_Args_Exception
	 */
	protected function render_multiple( Layout_Settings $layout_settings, array $attrs, int $current_page_id ): string {
		$query_context = Query_Context::new_instance()
										->set_post_id( $current_page_id )
										->set_custom_arguments( Shortcode_Attributes::get_custom_arguments( $attrs ) );
		$item_ids      = $this->item_ids_resolver->resolve( $layout_settings->item_type, $attrs, $query_context );

		return $this->render_html( new Source(), $layout_settings->get_unique_id(), $attrs, $current_page_id, $item_ids );
	}

	/**
	 * @param array<string,mixed> $attrs
	 * @param int[] $item_ids
	 */
	protected function render_html(
		Source $source,
		string $unique_id,
		array $attrs,
		int $current_page_id,
		array $item_ids
	): string {
		ob_start();
		$this->layout_factory->make_and_print_html(
			$source,
			$unique_id,
			$current_page_id,
			Shortcode_Attributes::get_string( $attrs, 'class' ),
			Shortcode_Attributes::get_custom_arguments( $attrs ),
			// for inner Views.
			arrayOrNull( $attrs, 'local-data' ),
			$item_ids
		);

		return (string) ob_get_clean();
	}
}
