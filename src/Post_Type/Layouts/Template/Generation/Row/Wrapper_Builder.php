<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Row;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Template\Html_Wrapper;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Factory\Token_Factory;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
final class Wrapper_Builder {
	/**
	 * @param Html_Wrapper[] $outers
	 * @param Html_Node[] $nodes
	 * @param string|null $default_class The class of an outer without its own one (item outers); null means no class
	 *
	 * @return Html_Node[]
	 */
	public static function compose_outers(
		Token_Factory $token_factory,
		array $outers,
		array $nodes,
		?string $default_class = null
	): array {
		// the innermost outer goes first.
		$innermost_first = array_reverse( $outers );

		foreach ( $innermost_first as $outer ) {
			$outer_tag = self::compose_outer(
				$token_factory,
				$outer,
				$default_class
			);

			$nodes = array( $outer_tag->content( ...$nodes ) );
		}

		return $nodes;
	}

	protected static function compose_outer( Token_Factory $token_factory, Html_Wrapper $outer, ?string $default_class ): Html_Tag {
		$attrs = $outer->attrs;

		if ( is_string( $default_class ) ) {
			$attr_class  = $outer->attrs['class'] ?? '';
			$is_filled   = strlen( $attr_class ) > 0;
			$class       = $is_filled ?
				$attr_class :
				$default_class;
			$class_attrs = array( 'class' => $class );
			$attrs       = array_merge( $class_attrs, $outer->attrs );
		}

		foreach ( $outer->variable_attrs as $attr => $variable_info ) {
			$attrs[ $attr ] = $token_factory
				->variable( $variable_info['field_id'] )
				->add_item_path( $variable_info['item_key'] );
		}

		return tag( $outer->tag )
			->attributes( $attrs );
	}
}
