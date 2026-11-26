<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Arguments;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Query\Core\Context\Query_Context;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Sources\Context_Provider;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Sources\Item_Resolver;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Sources\Item_Validator;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Sources\Legacy_Source_Mapper;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Source;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\arrayOrNull;

/**
 * Parses the (normalized) shortcode attrs of a Layout into the Shortcode_Arguments, or an error.
 */
final class Argument_Parser {
	private Item_Resolver $item_resolver;
	private Context_Provider $context_provider;
	private Legacy_Source_Mapper $legacy_source_mapper;

	public function __construct(
		Item_Resolver $item_resolver,
		Context_Provider $context_provider,
		Legacy_Source_Mapper $legacy_source_mapper
	) {
		$this->item_resolver        = $item_resolver;
		$this->context_provider     = $context_provider;
		$this->legacy_source_mapper = $legacy_source_mapper;
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	public function parse_arguments( Layout_Settings $layout_settings, array $attrs ): Shortcode_Arguments {
		$queried_object_id = Context_Provider::get_queried_object_id() ?? 0;
		$is_multiple       = $layout_settings->is_multiple();

		if ( $is_multiple ) {
			return $this->parse_multiple(
				$layout_settings,
				$attrs,
				$queried_object_id
			);
		}

		return $this->parse_single(
			$layout_settings,
			$attrs,
			$queried_object_id
		);
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected function parse_single( Layout_Settings $layout_settings, array $attrs, int $queried_object_id ): Shortcode_Arguments {
		$item_type = $layout_settings->item_type;
		$source    = $this->resolve_source( $item_type, $attrs );

		if ( $source instanceof Source ) {
			$classes          = Attribute_Reader::get_string( $attrs, 'class' );
			$custom_arguments = Attribute_Reader::get_custom_arguments( $attrs );
			// for inner Views.
			$local_data = arrayOrNull( $attrs, 'local-data' );

			return ( new Shortcode_Arguments() )
				->set_source( $source )
				->set_queried_object_id( $queried_object_id )
				->set_classes( $classes )
				->set_custom_arguments( $custom_arguments )
				->set_local_data( $local_data );
		}

		$error = self::get_wrong_argument_error( $item_type );

		return ( new Shortcode_Arguments() )->set_error( $error );
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected function parse_multiple( Layout_Settings $layout_settings, array $attrs, int $queried_object_id ): Shortcode_Arguments {
		$error = self::get_conflict_error( $layout_settings->item_type, $attrs );

		if ( ! is_string( $error ) ) {
			$query_context = self::create_query_context( $attrs, $queried_object_id );
			$item_ids      = $this->resolve_item_ids( $layout_settings->item_type, $attrs, $query_context );

			$classes          = Attribute_Reader::get_string( $attrs, 'class' );
			$custom_arguments = Attribute_Reader::get_custom_arguments( $attrs );
			// for inner Views.
			$local_data = arrayOrNull( $attrs, 'local-data' );

			return ( new Shortcode_Arguments() )
				->set_item_ids( $item_ids )
				->set_queried_object_id( $queried_object_id )
				->set_classes( $classes )
				->set_custom_arguments( $custom_arguments )
				->set_local_data( $local_data );
		}

		return ( new Shortcode_Arguments() )->set_error( $error );
	}

	/**
	 * @param array<string,mixed> $attrs
	 *
	 * @return int[]
	 */
	protected function resolve_item_ids( string $item_type, array $attrs, Query_Context $context ): array {
		if ( Layout_Settings::ITEM_TYPE_POST === $item_type ) {
			$selection_ids = Attribute_Reader::parse_list( $attrs['post-selections'] ?? null );
			$posts         = Attribute_Reader::parse_list( $attrs['posts'] ?? null );

			if ( count( $selection_ids ) > 0 ) {
				return $this->item_resolver->resolve_selection_posts( $selection_ids, $context );
			}

			return count( $posts ) > 0 ?
				Item_Validator::validate_post_ids( $posts ) :
				Context_Provider::get_main_query_post_ids();
		}

		if ( Layout_Settings::ITEM_TYPE_MENU === $item_type ) {
			return Item_Resolver::resolve_menu_ids( Attribute_Reader::parse_list( $attrs['menus'] ?? null ) );
		}

		$arg_name = Attribute_Reader::ITEM_ARGUMENTS[ $item_type ] ?? '';
		$ids      = Attribute_Reader::parse_list( $attrs[ $arg_name ] ?? null );

		// users, terms and comments have no context list in this release.
		$validators = array(
			Layout_Settings::ITEM_TYPE_USER    => fn( array $ids ): array => Item_Validator::validate_user_ids( $ids ),
			Layout_Settings::ITEM_TYPE_TERM    => fn( array $ids ): array => Item_Validator::validate_term_ids( $ids ),
			Layout_Settings::ITEM_TYPE_COMMENT => fn( array $ids ): array => Item_Validator::validate_comment_ids( $ids ),
		);

		return key_exists( $item_type, $validators ) ?
			$validators[ $item_type ]( $ids ) :
			Item_Validator::validate_ids( $ids );
	}

	/**
	 * @param array<string,mixed> $attrs
	 *
	 * @return string|null Null if the attrs are valid.
	 */
	protected static function get_conflict_error( string $item_type, array $attrs ): ?string {
		$has_posts      = count( Attribute_Reader::parse_list( $attrs['posts'] ?? null ) ) > 0;
		$has_selections = count( Attribute_Reader::parse_list( $attrs['post-selections'] ?? null ) ) > 0;
		$is_conflict    = Layout_Settings::ITEM_TYPE_POST === $item_type && $has_posts && $has_selections;

		return $is_conflict ?
			__( "'posts' and 'post-selections' arguments can't be used together", 'acf-views' ) :
			null;
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected function resolve_source( string $item_type, array $attrs ): ?Source {
		$is_custom = Layout_Settings::ITEM_TYPE_CUSTOM === $item_type;

		// the typed flow is the main one, the legacy arguments of a custom-data Layout are mapped into its result.
		if ( $is_custom ) {
			return $this->legacy_source_mapper->map_source( $attrs );
		}

		return $this->resolve_item_source( $item_type, $attrs );
	}

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected function resolve_item_source( string $item_type, array $attrs ): ?Source {
		$arg_name  = Attribute_Reader::ITEM_ARGUMENTS[ $item_type ] ?? '';
		$first_id  = Attribute_Reader::parse_list( $attrs[ $arg_name ] ?? null )[0] ?? '';
		$first_ids = array( $first_id );

		$from_argument = array(
			Layout_Settings::ITEM_TYPE_POST    => fn() => Item_Validator::validate_post_ids( $first_ids )[0] ?? null,
			Layout_Settings::ITEM_TYPE_USER    => fn() => Item_Validator::validate_user_ids( $first_ids )[0] ?? null,
			Layout_Settings::ITEM_TYPE_TERM    => fn() => Item_Validator::validate_term_ids( $first_ids )[0] ?? null,
			Layout_Settings::ITEM_TYPE_COMMENT => fn() => Item_Validator::validate_comment_ids( $first_ids )[0] ?? null,
			Layout_Settings::ITEM_TYPE_MENU    => fn() => Item_Resolver::resolve_menu_term_id( $first_id ),
		);
		$from_context  = array(
			Layout_Settings::ITEM_TYPE_POST    => fn() => $this->context_provider->get_post_id(),
			Layout_Settings::ITEM_TYPE_USER    => fn() => Context_Provider::get_current_user_id(),
			Layout_Settings::ITEM_TYPE_TERM    => fn() => Context_Provider::get_queried_term_id(),
			Layout_Settings::ITEM_TYPE_COMMENT => fn() => null,
			Layout_Settings::ITEM_TYPE_MENU    => fn() => Context_Provider::get_queried_term_id(),
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

	/**
	 * @param array<string,mixed> $attrs
	 */
	protected static function create_query_context( array $attrs, int $queried_object_id ): Query_Context {
		$custom_arguments = Attribute_Reader::get_custom_arguments( $attrs );

		return Query_Context::new_instance()
			->set_post_id( $queried_object_id )
			->set_custom_arguments( $custom_arguments );
	}

	protected static function get_wrong_argument_error( string $item_type ): string {
		$arg_name = Attribute_Reader::ITEM_ARGUMENTS[ $item_type ] ?? 'object-id';
		// translators: %s is an argument name.
		$error_template = __( '%s argument contains the wrong value', 'acf-views' );

		return sprintf( $error_template, $arg_name );
	}
}
