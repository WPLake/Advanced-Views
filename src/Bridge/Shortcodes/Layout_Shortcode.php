<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Bridge\Shortcodes;

use Org\Wplake\Advanced_Views\Bridge\Interfaces\Shortcodes\View_Shortcode_Interface;

defined( 'ABSPATH' ) || exit;

final class Layout_Shortcode extends Shortcode_Base implements View_Shortcode_Interface {
	/**
	 * @param int[] $post_ids Post (Page, CPT, Product) IDs
	 *
	 * @return static
	 */
	public function set_posts( array $post_ids ): self {
		$this->args['posts'] = $post_ids;

		return $this;
	}

	/**
	 * @param string[] $selection_ids Post Selection IDs, their posts are displayed in the given order
	 *
	 * @return static
	 */
	public function set_post_selections( array $selection_ids ): self {
		$this->args['post-selections'] = $selection_ids;

		return $this;
	}

	/**
	 * @param int[] $user_ids
	 *
	 * @return static
	 */
	public function set_users( array $user_ids ): self {
		$this->args['users'] = $user_ids;

		return $this;
	}

	/**
	 * @param int[] $term_ids
	 *
	 * @return static
	 */
	public function set_terms( array $term_ids ): self {
		$this->args['terms'] = $term_ids;

		return $this;
	}

	/**
	 * @param int[] $comment_ids
	 *
	 * @return static
	 */
	public function set_comments( array $comment_ids ): self {
		$this->args['comments'] = $comment_ids;

		return $this;
	}

	/**
	 * @param string[] $menu_slugs
	 *
	 * @return static
	 */
	public function set_menus( array $menu_slugs ): self {
		$this->args['menus'] = $menu_slugs;

		return $this;
	}

	/**
	 * @return static
	 */
	public function set_options_page( string $options_page ): self {
		$this->args['options-page'] = $options_page;

		return $this;
	}

	// Legacy methods, still work for the 'Custom' item type Layouts.

	/**
	 * @deprecated Use set_posts() or set_options_page() instead.
	 * @param int|string $object_id Post ID or "options", "term", "comment", "menu" string
	 *
	 * @return static
	 */
	public function set_object_id( $object_id ): self {
		$this->args['object-id'] = $object_id;

		return $this;
	}

	/**
	 * @deprecated Use set_users() instead.
	 * @return static
	 */
	public function set_user_id( int $user_id ): self {
		$this->args['user-id'] = $user_id;

		return $this;
	}

	/**
	 * @deprecated Use set_terms() instead.
	 * @return static
	 */
	public function set_term_id( int $term_id ): self {
		$this->args['term-id'] = $term_id;

		return $this;
	}

	/**
	 * @deprecated Use set_comments() instead.
	 * @return static
	 */
	public function set_comment_id( int $comment_id ): self {
		$this->args['comment-id'] = $comment_id;

		return $this;
	}

	/**
	 * @deprecated Use set_menus() instead.
	 * @return static
	 */
	public function set_menu_slug( string $menu_slug ): self {
		$this->args['menu-slug'] = $menu_slug;

		return $this;
	}

	/**
	 * @deprecated Use set_posts() instead.
	 * @return static
	 */
	public function set_post_slug( string $post_slug ): self {
		$this->args['post-slug'] = $post_slug;

		return $this;
	}

	// Deprecated methods.

	/**
	 * @deprecated Use set_posts() or set_options_page() instead.
	 * @param int|string $object_id Post ID or "options", "term", "comment", "menu" string
	 *
	 * @return static
	 */
	public function setObjectId( $object_id ): self {
		return $this->set_object_id( $object_id );
	}

	/**
	 * @deprecated Use set_users() instead.
	 * @return static
	 */
	public function setUserId( int $user_id ): self {
		return $this->set_user_id( $user_id );
	}

	/**
	 * @deprecated Use set_terms() instead.
	 * @return static
	 */
	public function setTermId( int $term_id ): self {
		return $this->set_term_id( $term_id );
	}

	/**
	 * @deprecated Use set_comments() instead.
	 * @return static
	 */
	public function setCommentId( int $comment_id ): self {
		return $this->set_comment_id( $comment_id );
	}

	/**
	 * @deprecated Use set_menus() instead.
	 * @return static
	 */
	public function setMenuSlug( string $menu_slug ): self {
		return $this->set_menu_slug( $menu_slug );
	}
}
