<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Taxonomy_Terms;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Custom_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Lists\Taxonomy_Field;
use WP_Post;

defined( 'ABSPATH' ) || exit;

final class Taxonomy_Terms_Field extends Taxonomy_Field {
	use Custom_Field;

	/**
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	protected function get_value( Field_Meta $field_meta, $value ) {
		// do not call the parent method, as we always have a single item here.

		$post = self::get_post( $value );

		if ( is_object( $post ) ) {
			return self::get_post_term_ids( $post, $field_meta );
		}

		return array();
	}

	/**
	 * @return int[]
	 */
	protected static function get_post_term_ids( WP_Post $post, Field_Meta $field_meta ): array {
		$taxonomy_name = substr( $field_meta->get_field_id(), strlen( Taxonomy_Term_Fields::PREFIX ) );
		$post_terms    = get_the_terms( $post, $taxonomy_name );

		if ( is_array( $post_terms ) ) {
			return array_column( $post_terms, 'term_id' );
		}

		return array();
	}
}
