<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Items\Single;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Argument_Parser\Argument_Exception;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Argument_Parser\Request_Context_Reader;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Arguments\Attribute_Reader;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Items\Lookup\Existing_Item_Filter;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Items\Lookup\Reference_Resolver;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Source;

/**
 * Resolves the Source of a single-item Layout from its shortcode attrs.
 */
final class Single_Item_Resolver {
	private Request_Context_Reader $context_provider;
	private Legacy_Source_Mapper $legacy_source_mapper;

	public function __construct( Request_Context_Reader $context_provider, Legacy_Source_Mapper $legacy_source_mapper ) {
		$this->context_provider     = $context_provider;
		$this->legacy_source_mapper = $legacy_source_mapper;
	}

	/**
	 * @param array<string,mixed> $attrs
	 *
	 * @throws Argument_Exception If the source can't be resolved.
	 */
	public function resolve_item_id( string $item_type, array $attrs ): Source {
		$is_custom = Layout_Settings::ITEM_TYPE_CUSTOM === $item_type;

		// the typed flow is the main one, the legacy arguments of a custom-data Layout are mapped into its result.
		$source = $is_custom ?
			$this->legacy_source_mapper->map_source( $attrs ) :
			$this->resolve_item_source( $item_type, $attrs );

		if ( null === $source ) {
			throw new Argument_Exception( self::get_wrong_argument_error( $item_type ) );
		}

		return $source;
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected function resolve_item_source( string $item_type, array $attrs ): ?Source {
		$arg_name  = Attribute_Reader::ITEM_ARGUMENTS[ $item_type ] ?? '';
		$first_id  = Attribute_Reader::parse_list( $attrs[ $arg_name ] ?? null )[0] ?? '';
		$first_ids = array( $first_id );

		$from_argument = array(
			Layout_Settings::ITEM_TYPE_POST    => fn() => Existing_Item_Filter::validate_post_ids( $first_ids )[0] ?? null,
			Layout_Settings::ITEM_TYPE_USER    => fn() => Existing_Item_Filter::validate_user_ids( $first_ids )[0] ?? null,
			Layout_Settings::ITEM_TYPE_TERM    => fn() => Existing_Item_Filter::validate_term_ids( $first_ids )[0] ?? null,
			Layout_Settings::ITEM_TYPE_COMMENT => fn() => Existing_Item_Filter::validate_comment_ids( $first_ids )[0] ?? null,
			Layout_Settings::ITEM_TYPE_MENU    => fn() => Reference_Resolver::resolve_menu_term_id( $first_id ),
		);
		$from_context  = array(
			Layout_Settings::ITEM_TYPE_POST    => fn() => $this->context_provider->get_post_id(),
			Layout_Settings::ITEM_TYPE_USER    => fn() => Request_Context_Reader::get_current_user_id(),
			Layout_Settings::ITEM_TYPE_TERM    => fn() => Request_Context_Reader::get_queried_term_id(),
			Layout_Settings::ITEM_TYPE_COMMENT => fn() => null,
			Layout_Settings::ITEM_TYPE_MENU    => fn() => Request_Context_Reader::get_queried_term_id(),
		);

		// an invalid argument is not replaced with the context default, only an absent one is.
		$is_defined = strlen( $first_id ) > 0;
		$resolvers  = $is_defined ?
			$from_argument :
			$from_context;

		if ( key_exists( $item_type, $resolvers ) ) {
			$resolved_id = $resolvers[ $item_type ]();

			return self::create_source( $item_type, $resolved_id );
		}

		return null;
	}

	protected static function create_source( string $item_type, ?int $resolved_id ): ?Source {
		$is_post     = Layout_Settings::ITEM_TYPE_POST === $item_type;
		$is_resolved = ! $is_post || null !== $resolved_id;

		return $is_resolved ?
			Source::make_item( $item_type, $resolved_id ?? 0 ) :
			null;
	}

	protected static function get_wrong_argument_error( string $item_type ): string {
		$arg_name = Attribute_Reader::ITEM_ARGUMENTS[ $item_type ] ?? 'object-id';
		// translators: %s is an argument name.
		$error_template = __( '%s argument contains the wrong value', 'acf-views' );

		return sprintf( $error_template, $arg_name );
	}
}
