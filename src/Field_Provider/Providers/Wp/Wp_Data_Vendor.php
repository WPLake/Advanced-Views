<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp;

defined( 'ABSPATH' ) || exit;

use DateTime;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Base;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Integration;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Image_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Link_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Comment\{Comment_Date_Field};
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Comment\Comment_Author_Email_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Comment\Comment_Author_Name_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Comment\Comment_Author_Name_Link_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Comment\Comment_Content_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Comment\Comment_Fields;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Comment\Comment_Parent_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Comment\Comment_Status_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Comment\Comment_User_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Comment_Items\{Comment_Item_Fields};
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Comment_Items\Comment_Items_List_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Menu\{Menu_Fields};
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Menu\Menu_Items_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Menu_Item\{Menu_Item_Fields};
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Menu_Item\Menu_Item_Link_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Post\{Post_Attachment_Link};
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Post\Post_Attachment_Video;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Post\Post_Author_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Post\Post_Content_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Post\Post_Date_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Post\Post_Excerpt_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Post\Post_Fields;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Post\Post_Modified_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Post\Post_Thumbnail_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Post\Post_Thumbnail_Link_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Post\Post_Title_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Post\Post_Title_Link_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Taxonomy_Terms\{Taxonomy_Terms_Field};
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Taxonomy_Terms\Taxonomy_Term_Fields;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Term\{Term_Description_Field};
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Term\Term_Fields;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Term\Term_Name_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Term\Term_Name_Link_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\Term\Term_Slug_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\User\{User_Display_Name_Field};
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\User\User_Author_Link_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\User\User_Bio_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\User\User_Email_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\User\User_Fields;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\User\User_First_Name_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\User\User_Last_Name_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Providers\Wp\Fields\User\User_Website_Field;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Source;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;

class Wp_Data_Vendor extends Field_Provider_Base {
	// for back compatibility only.
	const NAME = 'wp';

	public function get_name(): string {
		return static::NAME;
	}

	public function is_meta_vendor(): bool {
		return false;
	}

	public function is_available(): bool {
		return true;
	}

	public function make_integration_instance( Instance_Container $container ): ?Field_Provider_Integration {
		return null;
	}

	/**
	 * @return array<string, string>
	 */
	public function get_group_choices(): array {
		$groups = array(
			Post_Fields::GROUP_NAME          => __( 'Post & Product fields (WordPress, WooCommerce)', 'acf-views' ),
			Taxonomy_Term_Fields::GROUP_NAME => __( 'Taxonomy terms (WordPress)', 'acf-views' ),
			Term_Fields::GROUP_NAME          => __( 'Term (WordPress)', 'acf-views' ),
			User_Fields::GROUP_NAME          => __( 'User (WordPress)', 'acf-views' ),
			Comment_Item_Fields::GROUP_NAME  => __( 'Comments & Reviews (WordPress, WooCommerce)', 'acf-views' ),
			Comment_Fields::GROUP_NAME       => __( 'Comment & Review fields (WordPress, WooCommerce)', 'acf-views' ),
			Menu_Fields::GROUP_NAME          => __( 'Menu (WordPress)', 'acf-views' ),
			Menu_Item_Fields::GROUP_NAME     => __( 'Menu item (WordPress)', 'acf-views' ),
		);

		$group_choices = array();

		foreach ( $groups as $group_name => $group_label ) {
			$group_key = $this->get_group_key( $group_name );

			$group_choices[ $group_key ] = $group_label;
		}

		return $group_choices;
	}

	/**
	 * @param string[] $include_only_types
	 *
	 * @return array<string, Field_Meta>
	 */
	public function get_field_choices( array $include_only_types = array() ): array {
		$field_choices = $this->create_group_field_choices( $include_only_types );

		$is_terms_included = array() === $include_only_types || in_array(
			Taxonomy_Term_Fields::FIELD_TERMS,
			$include_only_types,
			true
		);

		if ( $is_terms_included ) {
			$taxonomy_choices = $this->create_taxonomy_choices();
			$field_choices    = array_replace( $field_choices, $taxonomy_choices );
		}

		return $field_choices;
	}

	/**
	 * @param mixed[] $data
	 */
	public function fill_field_meta( Field_Meta $field_meta, array $data = array() ): void {
		$field_id = $field_meta->get_field_id();

		$is_taxonomy_field = 0 === strpos( $field_id, Taxonomy_Term_Fields::PREFIX );

		if ( $is_taxonomy_field ) {
			$term_name = str_replace( Taxonomy_Term_Fields::PREFIX, '', $field_id );

			$taxonomy = get_taxonomy( $term_name );
			// @phpstan-ignore-next-line for some reason label can be bool (e.g. post_multilingual, likely from Polylang).
			$label = is_object( $taxonomy ) ? (string) $taxonomy->label : $term_name;

			$field_class = $this->get_field_class( Taxonomy_Term_Fields::FIELD_TERMS );

			$field_meta->set_type( Taxonomy_Term_Fields::FIELD_TERMS );
			// name is necessary for the identifier and markup generation.
			$field_meta->set_name( $term_name );
			$field_meta->set_label( $label );
			$field_meta->set_identifier( $label );
			$field_meta->set_field_class( $field_class );
			// it's necessary to define, as the custom field will include the Taxonomy field,
			// which waits for this setting.
			$field_meta->set_is_multiple( true );

			$field_meta->set_is_field_exist( true );

			return;
		}

		$supported_types = $this->get_supported_field_types();

		$is_supported = in_array(
			$field_id,
			$supported_types,
			true
		);

		if ( $is_supported ) {
			$field_meta->set_type( $field_id );
			// name is necessary for the identifier and markup generation.
			$field_meta->set_name( $field_id );

			$field_names = self::get_field_names( $field_id );
			$field_class = $this->get_field_class( $field_id );

			$field_meta->set_label( $field_names['label'] );
			$field_meta->set_identifier( $field_names['identifier'] );
			$field_meta->set_field_class( $field_class );

			$field_meta->set_is_field_exist( true );
		}
	}

	/**
	 * @param array<string|int,mixed>|null $local_data
	 *
	 * @return mixed
	 */
	public function get_field_value(
		Field_Settings $field_settings,
		Field_Meta $field_meta,
		Source $source,
		?Item_Settings $item_settings = null,
		bool $is_formatted = false,
		?array $local_data = null
	) {
		$field_id = $field_meta->get_field_id();

		$is_post_group      = 0 === strpos( $field_id, Post_Fields::PREFIX );
		$is_menu_item_group = 0 === strpos( $field_id, Menu_Item_Fields::PREFIX );
		// only if not menuItemGroup, as prefixes have the same root.
		$is_menu_group           = ! $is_menu_item_group &&
									0 === strpos( $field_id, Menu_Fields::PREFIX );
		$is_taxonomy_terms_group = 0 === strpos( $field_id, Taxonomy_Term_Fields::PREFIX );
		$is_user_group           = 0 === strpos( $field_id, User_Fields::PREFIX );
		$is_term_group           = 0 === strpos( $field_id, Term_Fields::PREFIX );
		$is_comment_items_group  = 0 === strpos( $field_id, Comment_Item_Fields::PREFIX );
		// only if not commentItemsGroup, as prefixes have the same root.
		$is_comment_group = ! $is_comment_items_group &&
							0 === strpos( $field_id, Comment_Fields::PREFIX );

		$is_id_group = $is_post_group ||
						$is_taxonomy_terms_group ||
						$is_menu_item_group ||
						$is_comment_items_group;
		$is_id_used  = false === $source->is_options() && $is_id_group;

		if ( $is_id_used ) {
			return $source->get_id();
		}

		if ( $is_user_group ) {
			return $source->get_user_id();
		}

		if ( $is_term_group ||
			$is_menu_group ) {
			return $source->get_term_id();
		}

		if ( $is_comment_group ) {
			return $source->get_comment_id();
		}

		return null;
	}

	public function convert_string_to_date_time( Field_Meta $field_meta, string $value ): ?DateTime {
		return null;
	}

	public function convert_date_to_string_for_db_comparison(
		DateTime $date_time,
		Field_Meta $field_meta
	): string {
		return '';
	}

	/**
	 * @return null|array{title:string,url:string}
	 */
	public function get_group_link_by_group_id( string $group_id ): ?array {
		return null;
	}

	/**
	 * @return mixed[]|null
	 */
	public function get_group_export_data( string $group_id ): ?array {
		// the feature is not supported.
		return null;
	}

	/**
	 * @param mixed[] $group_data
	 * @param mixed[] $meta_data
	 */
	public function import_group( array $group_data, array $meta_data ): ?string {
		// the feature is not supported.
		return null;
	}

	// for back compatibility only.
	protected function is_without_name_in_keys(): bool {
		return true;
	}

	protected function get_field_types(): array {
		$comment = array(
			Comment_Fields::FIELD_AUTHOR_EMAIL     => new Comment_Author_Email_Field(),
			Comment_Fields::FIELD_AUTHOR_NAME      => new Comment_Author_Name_Field(),
			Comment_Fields::FIELD_AUTHOR_NAME_LINK => new Comment_Author_Name_Link_Field( new Link_Field() ),
			Comment_Fields::FIELD_CONTENT          => new Comment_Content_Field(),
			Comment_Fields::FIELD_DATE             => new Comment_Date_Field(),
			Comment_Fields::FIELD_STATUS           => new Comment_Status_Field(),
			Comment_Fields::FIELD_PARENT           => new Comment_Parent_Field(),
			Comment_Fields::FIELD_USER             => new Comment_User_Field(),
		);

		$comment_item = array(
			Comment_Item_Fields::FIELD_LIST => new Comment_Items_List_Field(),
		);

		$menu = array(
			Menu_Fields::FIELD_ITEMS => new Menu_Items_Field( new Link_Field() ),
		);

		$menu_item = array(
			Menu_Item_Fields::FIELD_LINK => new Menu_Item_Link_Field(),
		);

		$post = array(
			Post_Fields::FIELD_ATTACHMENT_LINK  => new Post_Attachment_Link(),
			Post_Fields::FIELD_ATTACHMENT_VIDEO => new Post_Attachment_Video(),
			Post_Fields::FIELD_TITLE            => new Post_Title_Field(),
			Post_Fields::FIELD_TITLE_LINK       => new Post_Title_Link_Field(),
			Post_Fields::FIELD_CONTENT          => new Post_Content_Field(),
			Post_Fields::FIELD_EXCERPT          => new Post_Excerpt_Field(),
			Post_Fields::FIELD_THUMBNAIL        => new Post_Thumbnail_Field(),
			Post_Fields::FIELD_THUMBNAIL_LINK   => new Post_Thumbnail_Link_Field( new Image_Field() ),
			Post_Fields::FIELD_AUTHOR           => new Post_Author_Field( new Link_Field() ),
			Post_Fields::FIELD_DATE             => new Post_Date_Field(),
			Post_Fields::FIELD_MODIFIED         => new Post_Modified_Field(),
		);

		$taxonomy_terms = array(
			Taxonomy_Term_Fields::FIELD_TERMS => new Taxonomy_Terms_Field( new Link_Field() ),
		);

		$term = array(
			Term_Fields::FIELD_NAME        => new Term_Name_Field(),
			Term_Fields::FIELD_SLUG        => new Term_Slug_Field(),
			Term_Fields::FIELD_DESCRIPTION => new Term_Description_Field(),
			Term_Fields::FIELD_NAME_LINK   => new Term_Name_Link_Field(),
		);

		$user = array(
			User_Fields::FIELD_FIRST_NAME   => new User_First_Name_Field(),
			User_Fields::FIELD_LAST_NAME    => new User_Last_Name_Field(),
			User_Fields::FIELD_DISPLAY_NAME => new User_Display_Name_Field(),
			User_Fields::FIELD_BIO          => new User_Bio_Field(),
			User_Fields::FIELD_EMAIL        => new User_Email_Field(),
			User_Fields::FIELD_AUTHOR_LINK  => new User_Author_Link_Field(),
			User_Fields::FIELD_WEBSITE      => new User_Website_Field(),
		);

		return array_merge( $post, $user, $comment_item, $comment, $taxonomy_terms, $term, $menu, $menu_item );
	}

	/**
	 * @param string[] $include_only_types
	 *
	 * @return array<string, Field_Meta>
	 */
	protected function create_group_field_choices( array $include_only_types ): array {
		$field_choices = array();
		$vendor_name   = $this->get_name();
		$groups        = self::get_fields_with_labels_in_groups();

		foreach ( $groups as $group_name => $group_choices ) {
			$field_ids = array_keys( $group_choices );

			foreach ( $field_ids as $field_id ) {
				$is_included = array() === $include_only_types || in_array(
					$field_id,
					$include_only_types,
					true
				);

				if ( $is_included ) {
					$field_key = $this->get_field_key( $group_name, $field_id );

					$field_meta = new Field_Meta( $vendor_name, $field_id );
					$this->fill_field_meta( $field_meta );

					$field_choices[ $field_key ] = $field_meta;
				}
			}
		}

		return $field_choices;
	}

	/**
	 * @return array<string, Field_Meta>
	 */
	protected function create_taxonomy_choices(): array {
		$taxonomy_choices = array();
		$vendor_name      = $this->get_name();
		$taxonomies       = get_taxonomies( array(), 'objects' );

		foreach ( $taxonomies as $taxonomy ) {
			$field_id     = Taxonomy_Term_Fields::PREFIX . $taxonomy->name;
			$taxonomy_key = $this->get_field_key( Taxonomy_Term_Fields::GROUP_NAME, $field_id );

			$field_meta = new Field_Meta( $vendor_name, $field_id );
			$this->fill_field_meta( $field_meta );

			$taxonomy_choices[ $taxonomy_key ] = $field_meta;
		}

		return $taxonomy_choices;
	}

	/**
	 * @return array{label:string,identifier:string}
	 */
	protected static function get_field_names( string $field_id ): array {
		$groups = self::get_fields_with_labels_in_groups();

		foreach ( $groups as $group_choices ) {
			if ( key_exists( $field_id, $group_choices ) ) {
				return $group_choices[ $field_id ];
			}
		}

		return array(
			'label'      => '',
			'identifier' => '',
		);
	}

	/**
	 * @return array<string,array<string,array{label:string,identifier:string}>>
	 */
	protected static function get_fields_with_labels_in_groups(): array {
		return array(
			Post_Fields::GROUP_NAME         => array(
				Post_Fields::FIELD_TITLE            => array(
					'label'      => __( 'Title', 'acf-views' ),
					'identifier' => 'post_title',
				),
				Post_Fields::FIELD_TITLE_LINK       => array(
					'label'      => __( 'Title with link', 'acf-views' ),
					'identifier' => 'post_title_link',
				),
				Post_Fields::FIELD_CONTENT          => array(
					'label'      => __( 'Post Content / Product Description', 'acf-views' ),
					'identifier' => 'post_content',
				),
				Post_Fields::FIELD_EXCERPT          => array(
					'label'      => __( 'Post Excerpt / Short Product Description', 'acf-views' ),
					'identifier' => 'post_excerpt',
				),
				Post_Fields::FIELD_THUMBNAIL        => array(
					'label'      => __( 'Featured Image', 'acf-views' ),
					'identifier' => 'post_featured_image',
				),
				Post_Fields::FIELD_THUMBNAIL_LINK   => array(
					'label'      => __( 'Featured Image with link', 'acf-views' ),
					'identifier' => 'post_featured_image_link',
				),
				Post_Fields::FIELD_AUTHOR           => array(
					'label'      => __( 'Author', 'acf-views' ),
					'identifier' => 'post_author',
				),
				Post_Fields::FIELD_DATE             => array(
					'label'      => __( 'Published date', 'acf-views' ),
					'identifier' => 'post_date',
				),
				Post_Fields::FIELD_MODIFIED         => array(
					'label'      => __( 'Modified date', 'acf-views' ),
					'identifier' => 'post_modified',
				),
				Post_Fields::FIELD_ATTACHMENT_LINK  => array(
					'label'      => __( 'Attachment link', 'acf-views' ),
					'identifier' => 'post_attachment_link',
				),
				Post_Fields::FIELD_ATTACHMENT_VIDEO => array(
					'label'      => __( 'Attachment video', 'acf-views' ),
					'identifier' => 'post_attachment_video',
				),
			),
			User_Fields::GROUP_NAME         => array(
				User_Fields::FIELD_FIRST_NAME   => array(
					'label'      => __( 'First Name', 'acf-views' ),
					'identifier' => 'user_first_name',
				),
				User_Fields::FIELD_LAST_NAME    => array(
					'label'      => __( 'Last Name', 'acf-views' ),
					'identifier' => 'user_last_name',
				),
				User_Fields::FIELD_DISPLAY_NAME => array(
					'label'      => __( 'Display Name', 'acf-views' ),
					'identifier' => 'user_display_name',
				),
				User_Fields::FIELD_BIO          => array(
					'label'      => __( 'Bio', 'acf-views' ),
					'identifier' => 'user_bio',
				),
				User_Fields::FIELD_EMAIL        => array(
					'label'      => __( 'Email', 'acf-views' ),
					'identifier' => 'user_email',
				),
				User_Fields::FIELD_AUTHOR_LINK  => array(
					'label'      => __( 'Author link', 'acf-views' ),
					'identifier' => 'user_author_link',
				),
				User_Fields::FIELD_WEBSITE      => array(
					'label'      => __( 'Website', 'acf-views' ),
					'identifier' => 'user_website',
				),
			),
			Comment_Item_Fields::GROUP_NAME => array(
				Comment_Item_Fields::FIELD_LIST => array(
					'label'      => __( 'List', 'acf-views' ),
					'identifier' => 'comments_list',
				),
			),
			Comment_Fields::GROUP_NAME      => array(
				Comment_Fields::FIELD_AUTHOR_EMAIL     => array(
					'label'      => __( 'Author Email', 'acf-views' ),
					'identifier' => 'comment_author_email',
				),
				Comment_Fields::FIELD_AUTHOR_NAME      => array(
					'label'      => __( 'Author Name', 'acf-views' ),
					'identifier' => 'comment_author_name',
				),
				Comment_Fields::FIELD_AUTHOR_NAME_LINK => array(
					'label'      => __( 'Author Name link', 'acf-views' ),
					'identifier' => 'comment_author_name_link',
				),
				Comment_Fields::FIELD_CONTENT          => array(
					'label'      => __( 'Content', 'acf-views' ),
					'identifier' => 'comment_content',
				),
				Comment_Fields::FIELD_DATE             => array(
					'label'      => __( 'Date', 'acf-views' ),
					'identifier' => 'comment_date',
				),
				Comment_Fields::FIELD_STATUS           => array(
					'label'      => __( 'Status', 'acf-views' ),
					'identifier' => 'comment_status',
				),
				Comment_Fields::FIELD_PARENT           => array(
					'label'      => __( 'Parent', 'acf-views' ),
					'identifier' => 'comment_parent',
				),
				Comment_Fields::FIELD_USER             => array(
					'label'      => __( 'User', 'acf-views' ),
					'identifier' => 'comment_user',
				),
			),
			Term_Fields::GROUP_NAME         => array(
				Term_Fields::FIELD_NAME        => array(
					'label'      => __( 'Name', 'acf-views' ),
					'identifier' => 'term_name',
				),
				Term_Fields::FIELD_SLUG        => array(
					'label'      => __( 'Slug', 'acf-views' ),
					'identifier' => 'term_slug',
				),
				Term_Fields::FIELD_DESCRIPTION => array(
					'label'      => __( 'Description', 'acf-views' ),
					'identifier' => 'term_description',
				),
				Term_Fields::FIELD_NAME_LINK   => array(
					'label'      => __( 'Name link', 'acf-views' ),
					'identifier' => 'term_name_link',
				),
			),
			Menu_Fields::GROUP_NAME         => array(
				Menu_Fields::FIELD_ITEMS => array(
					'label'      => __( 'Items', 'acf-views' ),
					'identifier' => 'menu_items',
				),
			),
			Menu_Item_Fields::GROUP_NAME    => array(
				Menu_Item_Fields::FIELD_LINK => array(
					'label'      => __( 'Link', 'acf-views' ),
					'identifier' => 'menu_item_link',
				),
			),
		);
	}
}
