<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Structure;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Library\Library;
use LogicException;

/**
 * Library DOM skeleton as role => wrapper. A role is the identity, never the position,
 * so structures of several libraries (e.g. Splide + Lightbox) merge per role.
 *
 * A host owns the tags of its roles, a guest only contributes attrs (its tag is a fallback).
 */
class Structure {
	const ROLE_CONTAINER = 'container';
	const ROLE_LIST      = 'list';
	const ROLE_ITEM      = 'item';

	/**
	 * Nesting order, outermost first.
	 */
	const ROLES = array(
		self::ROLE_CONTAINER,
		self::ROLE_LIST,
		self::ROLE_ITEM,
	);

	/**
	 * @var array<string,Html_Wrapper>
	 */
	protected array $wrappers;
	/**
	 * @var array<string,bool>
	 */
	protected array $hosted_roles;

	/**
	 * @param array<string,Html_Wrapper> $wrappers
	 * @param array<string,bool> $hosted_roles
	 */
	protected function __construct( array $wrappers, array $hosted_roles ) {
		$this->wrappers     = $wrappers;
		$this->hosted_roles = $hosted_roles;
	}

	/**
	 * @param array<string,Html_Wrapper> $wrappers
	 */
	public static function host( array $wrappers ): Structure {
		return new Structure( $wrappers, array_fill_keys( array_keys( $wrappers ), true ) );
	}

	/**
	 * @param array<string,Html_Wrapper> $wrappers
	 */
	public static function guest( array $wrappers ): Structure {
		return new Structure( $wrappers, array() );
	}

	/**
	 * @return array<string,Html_Wrapper>
	 */
	public function get_wrappers(): array {
		return $this->wrappers;
	}

	public function is_hosted( string $role ): bool {
		return key_exists( $role, $this->hosted_roles );
	}

	/**
	 * @throws LogicException when two sides claim different tags for the same role.
	 */
	public function merge( Structure $structure ): Structure {
		$wrappers = $this->wrappers;

		foreach ( $structure->wrappers as $role => $wrapper ) {
			if ( key_exists( $role, $wrappers ) ) {
				$is_hosted         = $this->is_hosted( $role );
				$is_wrapper_hosted = $structure->is_hosted( $role );

				$wrappers[ $role ] = self::merge_role( $role, $wrappers[ $role ], $is_hosted, $wrapper, $is_wrapper_hosted );

				continue;
			}

			$wrappers[ $role ] = $wrapper;
		}

		$hosted_roles = array_merge( $this->hosted_roles, $structure->hosted_roles );

		return new Structure( $wrappers, $hosted_roles );
	}

	protected static function merge_role(
		string $role,
		Html_Wrapper $first,
		bool $is_first_hosted,
		Html_Wrapper $second,
		bool $is_second_hosted
	): Html_Wrapper {
		$tag            = self::resolve_tag( $role, $first->tag, $is_first_hosted, $second->tag, $is_second_hosted );
		$attrs          = array_merge( $first->attrs, $second->attrs );
		$variable_attrs = array_merge( $first->variable_attrs, $second->variable_attrs );

		return new Html_Wrapper( $tag, $attrs, $variable_attrs );
	}

	protected static function resolve_tag( string $role, string $first, bool $is_first_hosted, string $second, bool $is_second_hosted ): string {
		if ( $first === $second ||
			'' === $second ) {
			return $first;
		}

		if ( '' === $first ) {
			return $second;
		}

		if ( $is_first_hosted !== $is_second_hosted ) {
			return $is_first_hosted ?
				$first :
				$second;
		}

		$message      = sprintf( 'Conflicting tags "%s" and "%s" for the "%s" role', $first, $second, $role );
		$safe_message = esc_html( $message );

		throw new LogicException( $safe_message );
	}
}
