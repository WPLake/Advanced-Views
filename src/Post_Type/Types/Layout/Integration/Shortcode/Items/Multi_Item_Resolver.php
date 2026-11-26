<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Items;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Query\Core\Context\Query_Context;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Source;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Argument_Parser\Argument_Exception;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Argument_Parser\Request_Context_Reader;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Arguments\Attribute_Reader;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Arguments\Common_Arguments;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Items\Lookup\Existing_Item_Filter;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Items\Lookup\Reference_Resolver;

/**
 * Resolves the item IDs of a multiple-items Layout from its shortcode attrs.
 */
final class Multi_Item_Resolver {
	private Reference_Resolver $item_resolver;

	public function __construct( Reference_Resolver $item_resolver ) {
		$this->item_resolver = $item_resolver;
	}

	/**
	 * @param array<string,mixed> $attrs
	 *
	 * @throws Argument_Exception If the attrs are conflicting.
	 */
	public function resolve_item_ids( string $item_type, array $attrs, Common_Arguments $common_arguments ): Source {
		$error = self::get_conflict_error( $item_type, $attrs );

		if ( null !== $error ) {
			throw new Argument_Exception( $error );
		}

		$query_context = self::create_query_context( $common_arguments );
		$item_ids      = $this->resolve_ids( $item_type, $attrs, $query_context );

		return Source::make_items( $item_type, $item_ids );
	}

	/**
	 * @param array<string,mixed> $attrs
	 *
	 * @return int[]
	 */
	protected function resolve_ids( string $item_type, array $attrs, Query_Context $context ): array {
		if ( Layout_Settings::ITEM_TYPE_POST === $item_type ) {
			$selection_ids = Attribute_Reader::parse_list( $attrs['post-selections'] ?? null );
			$posts         = Attribute_Reader::parse_list( $attrs['posts'] ?? null );

			if ( count( $selection_ids ) > 0 ) {
				return $this->item_resolver->resolve_selection_posts( $selection_ids, $context );
			}

			return count( $posts ) > 0 ?
				Existing_Item_Filter::validate_post_ids( $posts ) :
				Request_Context_Reader::get_main_query_post_ids();
		}

		if ( Layout_Settings::ITEM_TYPE_MENU === $item_type ) {
			return Reference_Resolver::resolve_menu_ids( Attribute_Reader::parse_list( $attrs['menus'] ?? null ) );
		}

		$arg_name = Attribute_Reader::ITEM_ARGUMENTS[ $item_type ] ?? '';
		$ids      = Attribute_Reader::parse_list( $attrs[ $arg_name ] ?? null );

		// users, terms and comments have no context list in this release.
		$validators = array(
			Layout_Settings::ITEM_TYPE_USER    => fn( array $ids ): array => Existing_Item_Filter::validate_user_ids( $ids ),
			Layout_Settings::ITEM_TYPE_TERM    => fn( array $ids ): array => Existing_Item_Filter::validate_term_ids( $ids ),
			Layout_Settings::ITEM_TYPE_COMMENT => fn( array $ids ): array => Existing_Item_Filter::validate_comment_ids( $ids ),
		);

		return key_exists( $item_type, $validators ) ?
			$validators[ $item_type ]( $ids ) :
			Existing_Item_Filter::validate_ids( $ids );
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

	protected static function create_query_context( Common_Arguments $common_arguments ): Query_Context {
		return Query_Context::new_instance()
			->set_post_id( $common_arguments->queried_object_id )
			->set_custom_arguments( $common_arguments->custom_arguments );
	}
}
