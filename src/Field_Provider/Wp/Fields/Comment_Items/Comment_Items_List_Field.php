<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Wp\Fields\Comment_Items;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Custom_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field_Base;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Layout_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Meta\Template_Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Meta\Raw_Field_Value;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use WP_Comment;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
class Comment_Items_List_Field extends Markup_Field_Base {
	const AUTHOR_NAME = 'author_name';
	const CONTENT     = 'content';
	const COMMENT_ID  = 'comment_id';

	use Custom_Field;

	/**
	 * @return Html_Node[]
	 */
	protected function compose_internal_item_layout( string $item_id, Template_Field_Meta $markup_field_data ): array {
		$token_factory = $markup_field_data->get_token_factory();

		$comment_attributes = array( 'class' => $this->get_field_class( 'comment', $markup_field_data ) );
		$author_attributes  = array( 'class' => $this->get_field_class( 'comment-author-name', $markup_field_data ) );
		$content_attributes = array( 'class' => $this->get_field_class( 'comment-content', $markup_field_data ) );

		$author_name = tag( 'div' )
			->attributes( $author_attributes )
			->content(
				$token_factory->variable( $item_id )
								->add_item_path( self::AUTHOR_NAME )
			);

		// escaped by default, even we query approved comments -
		// reason is that HTML expectations in comments is rather an exception, than a rule.
		$content = tag( 'div' )
			->attributes( $content_attributes )
			->content(
				$token_factory->variable( 'comment_item' )
								->add_item_path( self::CONTENT )
			);

		$tags = tag( 'div' )
			->attributes( $comment_attributes )
			->content( $author_name, $content );

		return array( $tags );
	}

	/**
	 * @return Html_Node[]
	 */
	protected function compose_external_item_layout( string $field_id, string $item_id, Template_Field_Meta $markup_field_data ): array {
		$token_factory = $markup_field_data->get_token_factory();

		$id_var         = $token_factory->variable( $field_id )
											->add_item_path( self::LAYOUT_ID );
		$comment_id_var = $token_factory->variable( $item_id )
											->add_item_path( self::COMMENT_ID );

		$shortcode = $token_factory->shortcode( Hard_Layout_Cpt::cpt_name() )
			->set_attributes(
				array(
								'id'         => $id_var,
								'object-id'  => 'comment',
								'comment-id' => $comment_id_var,
							)
			);

		return array( $shortcode );
	}

	/**
	 * @return array<string,string>
	 */
	protected function get_item_twig_args(
		?WP_Comment $wp_comment,
		Field_Settings $field_settings,
		bool $is_for_validation = false
	): array {
		if ( $field_settings->has_external_layout() ) {
			if ( $is_for_validation ) {
				return array(
					self::COMMENT_ID => '1',
				);
			}

			$comment_id = null !== $wp_comment ?
				$wp_comment->comment_ID :
				0;

			return array(
				self::COMMENT_ID => (string) $comment_id,
			);
		}

		if ( $is_for_validation ||
			null === $wp_comment ) {
			return array(
				self::AUTHOR_NAME => 'Name',
				self::CONTENT     => 'Comment content',
			);
		}

		return array(
			// avoid double encoding in Twig.
			self::AUTHOR_NAME => html_entity_decode( $wp_comment->comment_author, ENT_QUOTES ),
			self::CONTENT     => html_entity_decode( $wp_comment->comment_content, ENT_QUOTES ),
		);
	}

	/**
	 * @param WP_Comment[] $comments
	 *
	 * @return WP_Comment[]
	 */
	protected function group_comments_by_parent( array $comments ): array {
		$grouped_comments = array();

		$get_comment_by_id = function ( $comment_id ) use ( $comments ): ?WP_Comment {
			// search commend in array by id.

			foreach ( $comments as $comment ) {
				if ( $comment->comment_ID !== $comment_id ) {
					continue;
				}

				return $comment;
			}

			return null;
		};

		foreach ( $comments as $comment ) {
			$top_comment = '0' !== $comment->comment_parent ?
				$get_comment_by_id( $comment->comment_parent ) :
				null;

			while ( null !== $top_comment ) {
				if ( '0' === $top_comment->comment_parent ) {
					break;
				}

				$top_comment = $get_comment_by_id( $top_comment->comment_parent );
			}

			$comment_key                        = $top_comment->comment_ID ?? $comment->comment_ID;
			$grouped_comments[ $comment_key ] ??= array();
			$grouped_comments[ $comment_key ][] = $comment;
		}

		$grouped = array();

		foreach ( $grouped_comments as $grouped_thread ) {
			// reverse 'one conversation messages', to reflect the historic order.
			$grouped_thread = array_reverse( $grouped_thread );
			$grouped        = array_merge( $grouped, $grouped_thread );
		}

		return $grouped;
	}

	public function compose_html( string $field_id, Template_Field_Meta $markup_field_data ): array {
		$token_factory = $markup_field_data->get_token_factory();

		$value_var = $token_factory->variable( $field_id )
			->add_item_path( self::VALUE );

		$item_var = $token_factory->variable( 'comment_item' );

		$item_nodes = $this->compose_item( $field_id, $item_var->get_name(), $markup_field_data );

		$loop = $token_factory->loop()
			->set_source_variable( $value_var )
			->set_item_variable( $item_var )
			->set_body( $item_nodes );

		return array( $loop );
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_template_variables( Raw_Field_Value $field_value ): array {
		$field_context = $field_value->get_context();

		$layout_id = $field_context->get_field_settings()->get_short_unique_acf_view_id();

		$args = array(
			self::VALUE     => array(),
			self::VIEW_ID   => $layout_id,
			self::LAYOUT_ID => $layout_id,
		);

		$post = $this->get_post( $field_value->get_value() );

		if ( null === $post ) {
			return $args;
		}

		// get all post comments.
		/**
		 * @var WP_Comment[] $comments
		 */
		$comments = get_comments(
			array(
				'post_id' => $post->ID,
				'status'  => 'approve',
			)
		);

		$comments = $this->group_comments_by_parent( $comments );

		foreach ( $comments as $comment ) {
			$args[ self::VALUE ][] = $this->get_item_twig_args( $comment, $field_context->get_field_settings() );
		}

		return $args;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_validation_template_variables( Raw_Field_Value $field_value ): array {
		$field_context = $field_value->get_context();

		$layout_id = $field_context->get_field_settings()->get_short_unique_acf_view_id();

		return array(
			self::VALUE     => array(
				$this->get_item_twig_args( null, $field_context->get_field_settings(), true ),
			),
			self::VIEW_ID   => $layout_id,
			self::LAYOUT_ID => $layout_id,
		);
	}

	public function is_with_field_wrapper(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta
	): bool {
		return true;
	}

	/**
	 * @return string[]
	 */
	public function get_conditional_fields( Field_Meta $field_meta ): array {
		return array_merge(
			parent::get_conditional_fields( $field_meta ),
			array(
				Field_Settings::FIELD_ACF_VIEW_ID,
				Field_Settings::FIELD_SLIDER_TYPE,
			)
		);
	}
}
