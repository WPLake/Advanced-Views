<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Core\Integration;

use Exception;
use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Theme_Settings;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Plugin\Core\Avf_User;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Layout_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Utils\Query_Arguments;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Settings_Creator;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Cpt\Layout_Save_Actions;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Layout_Shortcode_Handler;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Layout_Factory;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Layout_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Source;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use WP_Post;
use WP_Screen;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\raw_html;
use function Org\Wplake\Advanced_Views\Template\Html\tag;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\arr;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

defined( 'ABSPATH' ) || exit;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
abstract class Settings_Vendor_Integration extends Cpt_Settings_Creator implements Field_Provider_Integration {
	const NONCE_ADD_NEW = 'av-add-new';
	const ARGUMENT_FROM = '_from-group';

	private Item_Settings $item_settings;
	private Layout_Settings_Storage $layouts_settings_storage;
	private Field_Provider_Cluster $provider_cluster;
	private Layout_Save_Actions $layouts_cpt_save_actions;
	private Layout_Factory $layout_factory;
	private Field_Provider $data_vendor;
	private Layout_Shortcode_Handler $layout_shortcode;
	private Layout_Cpt $layouts_cpt;
	protected Html_Renderer $html_renderer;

	public function __construct(
		Item_Settings $item_settings,
		Layout_Settings_Storage $layouts_settings_storage,
		Field_Provider_Cluster $provider_cluster,
		Layout_Save_Actions $layouts_cpt_save_actions,
		Layout_Factory $layout_factory,
		Field_Provider $data_vendor,
		Layout_Shortcode_Handler $layout_shortcode,
		Cpt_Theme_Settings $settings,
		Layout_Cpt $layouts_cpt,
		Html_Renderer $html_renderer
	) {
		parent::__construct( $settings );

		$this->item_settings            = $item_settings;
		$this->layouts_settings_storage = $layouts_settings_storage;
		$this->provider_cluster         = $provider_cluster;
		$this->layouts_cpt_save_actions = $layouts_cpt_save_actions;
		$this->layout_factory           = $layout_factory;
		$this->data_vendor              = $data_vendor;
		$this->layout_shortcode         = $layout_shortcode;
		$this->layouts_cpt              = $layouts_cpt;
		$this->html_renderer            = $html_renderer;
	}

	public function get_views_data_storage(): Layout_Settings_Storage {
		return $this->layouts_settings_storage;
	}

	/**
	 * @param array<string,string> $columns
	 *
	 * @return array<string,string>
	 */
	public function add_related_views_column_to_list( array $columns ): array {
		$column_label = __( 'Assigned to Layout', 'acf-views' );

		return array_merge(
			$columns,
			array( 'relatedAcfViews' => $column_label )
		);
	}

	public function maybe_create_view_for_group(): void {
		$redirect_callback = fn() => $this->redirect_to_new_layout();

		self::add_action( 'current_screen', $redirect_callback );
	}

	public function print_related_views_column( string $column, int $post_id ): void {
		$post = 'relatedAcfViews' === $column ?
			get_post( $post_id ) :
			null;

		if ( $post instanceof WP_Post ) {
			$this->print_related_acf_views( $post, true );
		}
	}

	public function validate_related_views_on_group_change(): void {
		$message_callback = fn( array $messages ) => $this->add_invalid_markup_message( $messages );

		// with '20', to make sure it's after ACF.
		self::add_filter(
			'post_updated_messages',
			$message_callback,
			20
		);
	}

	public function add_column_to_list_table(): void {
		$vendor_post_type = $this->get_vendor_post_type();
		$columns_filter   = sprintf( 'manage_%s_posts_columns', $vendor_post_type );
		$column_action    = sprintf( 'manage_%s_posts_custom_column', $vendor_post_type );

		// higher priority, to run after ACF's listener (they don't use 'merge').
		self::add_filter(
			$columns_filter,
			array( $this, 'add_related_views_column_to_list' ),
			20
		);
		self::add_action(
			$column_action,
			array( $this, 'print_related_views_column' ),
			10,
			2
		);
	}

	/**
	 * @param Layout_Settings[] $view_data_items
	 *
	 * @return void
	 */
	public function signup_gutenberg_blocks( array $view_data_items ): void {
		// it's a stub. Will be overridden by specific vendors in the Pro version.
	}

	abstract protected function get_vendor_post_type(): string;

	/**
	 * @return mixed[]
	 */
	abstract protected function get_group_fields( WP_Post $wp_post ): array;

	/**
	 * @param array<string,mixed> $field
	 */
	abstract protected function fill_field_id_and_type( array $field, string &$field_id, string &$field_type ): void;

	protected function get_provider_cluster(): Field_Provider_Cluster {
		return $this->provider_cluster;
	}

	protected static function get_block_description( Layout_Settings $layout_settings ): string {
		$label     = __( 'Advanced View', 'acf-views' );
		$unique_id = $layout_settings->get_unique_id( true );

		return sprintf(
			'%s (%s, id = %s).',
			$layout_settings->description,
			$label,
			$unique_id
		);
	}

	protected static function get_block_id( Layout_Settings $layout_settings ): string {
		if ( $layout_settings->is_gutenberg_block_with_digital_id ) {
			$source = $layout_settings->getSource();

			return sprintf( 'acf-views-block-%s', $source );
		}

		$unique_id = $layout_settings->get_unique_id( true );

		return sprintf( 'acf-view-%s', $unique_id );
	}

	protected static function get_block_category(): string {
		return __( 'Advanced Views', 'acf-views' );
	}

	/**
	 * @param array<string,mixed>|null $local_data
	 */
	protected function render_view(
		Layout_Settings $layout_settings,
		int $post_id,
		?array $local_data = null
	): void {
		$source    = new Source();
		$unique_id = $layout_settings->get_unique_id();

		$source->set_id( $post_id );
		$source->set_is_block( true );
		$source->set_user_id( get_current_user_id() );

		ob_start();
		$this->layout_factory->make_and_print_html(
			$source,
			$unique_id,
			$post_id,
			'',
			array(),
			$local_data
		);
		$html = (string) ob_get_clean();

		$shadow_html = $this->layout_shortcode->maybe_add_quick_link_and_shadow_css(
			$html,
			$unique_id,
			array(),
			true
		);

		// @phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $shadow_html;
	}

	protected static function link_with_js_hover_tag( string $url, string $title ): Html_Tag {
		return tag( 'a' )
			->attributes(
				array(
					'href'        => $url,
					'target'      => '_blank',
					'style'       => array( 'transition' => 'all .3s ease' ),
					'onMouseOver' => "this.style.filter='brightness(30%)'",
					'onMouseOut'  => "this.style.filter='brightness(100%)'",
				)
			)
			->content(
				$title
			);
	}

	/**
	 * @param mixed[] $field
	 * @param string[] $supported_field_types
	 */
	protected function add_item_to_view(
		string $group_id,
		array $field,
		Layout_Settings $layout_settings,
		array $supported_field_types
	): ?Item_Settings {
		$field_id   = '';
		$field_type = '';

		$this->fill_field_id_and_type( $field, $field_id, $field_type );

		$is_supported = in_array(
			$field_type,
			$supported_field_types,
			true
		);

		if ( $is_supported ) {
			$item = $this->item_settings->getDeepClone();

			// we could use the group field on the level, but it less clear for beginners,
			// as they may wonder how to add fields from other groups, like WP.
			$item->group      = $group_id;
			$item->field->key = $item->field::create_field_key( $group_id, $field_id );

			$layout_settings->items[] = $item;

			return $item;
		}

		return null;
	}

	/**
	 * @param array<string,string> $link_attrs
	 */
	protected static function add_new_link_tag( int $from, string $label_suffix = '', array $link_attrs = array() ): Html_Tag {
		$default_attrs = array(
			'target'      => '_blank',
			'class'       => 'button',
			'style'       => 'min-height: 0;line-height: 1.2;padding: 3px 7px;font-size:11px;height:auto;transition:all .3s ease;',
			'onmouseover' => "this.style.color='#044767'",
			'onmouseout'  => "this.style.color='#0783BE'",
			'icon_class'  => '',
			'icon_style'  => '',
		);
		$link_attrs    = array_merge( $default_attrs, $link_attrs );

		$label = __( 'Add new', 'acf-views' ) . $label_suffix;

		$cpt_name   = Hard_Layout_Cpt::cpt_name();
		$nonce      = wp_create_nonce( self::NONCE_ADD_NEW );
		$admin_url  = admin_url( '/post-new.php' );
		$query_args = array(
			'post_type'         => $cpt_name,
			self::ARGUMENT_FROM => $from,
			'_wpnonce'          => $nonce,
		);

		$url = add_query_arg( $query_args, $admin_url );

		$icon_class = $link_attrs['icon_class'];
		$icon_style = $link_attrs['icon_style'];
		unset( $link_attrs['icon_class'], $link_attrs['icon_style'] );

		$link_attrs['href'] = $url;

		$icon_tag = '' !== $icon_class ?
			tag( 'i' )
				->attributes(
					array(
						'class' => $icon_class,
						'style' => $icon_style,
					)
				) :
			null;

		return tag( 'a' )
			->attributes( $link_attrs )
			->content(
				$icon_tag,
				$label
			);
	}

	/**
	 * @param Layout_Settings[] $related_views
	 * @param array<string,string> $add_new_link_args
	 */
	protected function print_related_acf_views(
		?WP_Post $wp_post,
		bool $is_list_look = false,
		array $related_views = array(),
		array $add_new_link_args = array()
	): void {
		$nodes = $this->related_acf_views_nodes( $wp_post, $is_list_look, $related_views, $add_new_link_args );

		$this->html_renderer->print_html( ...$nodes );
	}

	/**
	 * @param Layout_Settings[] $related_views
	 * @param array<string,string> $add_new_link_args
	 *
	 * @return Html_Node[]
	 */
	protected function related_acf_views_nodes(
		?WP_Post $wp_post,
		bool $is_list_look = false,
		array $related_views = array(),
		array $add_new_link_args = array()
	): array {
		$related_views = $wp_post instanceof WP_Post ?
			$this->get_group_related_views( $wp_post ) :
			$related_views;

		$label = array() !== $related_views ?
			// translators: %s is the plural name of the CPT.
			__( 'Assigned to %s:', 'acf-views' ) . ' ' :
			// translators: %s is the plural name of the CPT.
			__( 'Not assigned to any %s.', 'acf-views' );
		$cpt_labels = $this->layouts_cpt->labels();
		$label      = sprintf( $label, $cpt_labels->plural_name() );

		$nodes = $is_list_look ?
			array() :
			array( $label );

		$last_index = count( $related_views ) - 1;
		$counter    = 0;

		foreach ( $related_views as $related_view ) {
			$edit_link = $related_view->get_edit_post_link();
			$nodes[]   = self::link_with_js_hover_tag( $edit_link, $related_view->title );

			if ( $counter !== $last_index ) {
				$nodes[] = ', ';
			}

			++$counter;
		}

		// ignore on the creation page +
		// if post is missing (Pods).
		if ( $wp_post instanceof WP_Post && 'publish' === $wp_post->post_status ) {
			$nodes[] = tag( 'br' );
			$nodes[] = tag( 'br' );
			$nodes[] = self::add_new_link_tag( $wp_post->ID, '', $add_new_link_args );
		}

		return $nodes;
	}

	/**
	 * @param Layout_Settings[] $related_views
	 *
	 * @throws Exception
	 */
	protected function update_markup_preview( array $related_views ): void {
		foreach ( $related_views as $related_view_data ) {
			// update the markup preview in all the cases (even if View has custom, Preview must be fresh for copy/paste).
			$this->layouts_cpt_save_actions->update_markup( $related_view_data );
			$this->layouts_settings_storage->save( $related_view_data );
		}
	}

	/**
	 * @param Layout_Settings[] $related_views
	 *
	 * @return string[]
	 * @throws Exception
	 */
	protected function get_related_view_links_with_invalid_custom_markup( array $related_views ): array {
		$views_with_invalid_custom_markup = array();

		foreach ( $related_views as $related_view_data ) {
			// update the markup preview in all the cases (even if View has custom, Preview must be fresh for copy/paste)
			// also, it's necessary to update the markupPreview before the validation
			// as the validation uses the markupPreview as 'canonical' for the 'array' type validation.
			$this->layouts_cpt_save_actions->update_markup( $related_view_data );
			$this->layouts_settings_storage->save( $related_view_data );

			$invalid_link = $this->create_invalid_markup_link( $related_view_data );

			if ( is_string( $invalid_link ) ) {
				$views_with_invalid_custom_markup[] = $invalid_link;
			}
		}

		return $views_with_invalid_custom_markup;
	}

	protected function get_tab_label(): string {
		return __( 'Advanced Views', 'acf-views' );
	}

	/**
	 * @param mixed[] $field
	 */
	protected function get_group_key_by_from_post( WP_Post $wp_post, array $field ): string {
		return $this->data_vendor->get_group_key( $wp_post->post_name );
	}

	protected function redirect_to_new_layout(): void {
		$screen          = get_current_screen();
		$from_post       = $screen instanceof WP_Screen ?
			$this->get_group_post_to_convert( $screen ) :
			null;
		$layout_settings = $from_post instanceof WP_Post ?
			$this->layouts_settings_storage->create_new( 'publish', $from_post->post_title ) :
			null;

		if ( $from_post instanceof WP_Post && $layout_settings instanceof Layout_Settings ) {
			$this->fill_layout_from_group( $layout_settings, $from_post );

			// it'll save the data above too.
			$this->layouts_cpt_save_actions->perform_save_actions( $layout_settings->get_post_id() );

			wp_safe_redirect( $layout_settings->get_edit_post_link( 'redirect' ) );
			exit;
		}
	}

	protected function get_group_post_to_convert( WP_Screen $screen ): ?WP_Post {
		$from      = Query_Arguments::get_int_for_admin_action( self::ARGUMENT_FROM, self::NONCE_ADD_NEW );
		$from_post = 0 !== $from ?
			get_post( $from ) :
			null;

		$layout_cpt_name = Hard_Layout_Cpt::cpt_name();
		$is_add_layout   = 'post' === $screen->base &&
							'add' === $screen->action &&
							$layout_cpt_name === $screen->post_type;

		if ( $is_add_layout &&
			$from_post instanceof WP_Post &&
			$this->is_group_post_published( $from_post ) &&
			Avf_User::can_manage() ) {
			return $from_post;
		}

		return null;
	}

	protected function fill_layout_from_group( Layout_Settings $layout_settings, WP_Post $from_post ): void {
		$vendor_name           = $this->get_vendor_name();
		$supported_field_types = $this->provider_cluster->get_supported_field_types( $vendor_name );
		$group_fields          = $this->get_group_fields( $from_post );
		$group_key             = $this->get_first_field_group_key( $from_post, $group_fields );

		$layout_settings->title = $from_post->post_title;
		$this->set_defaults_from_settings( $layout_settings );

		foreach ( $group_fields as $field ) {
			$field = arr( $field );

			$this->add_item_to_view(
				$group_key,
				$field,
				$layout_settings,
				$supported_field_types
			);
		}
	}

	/**
	 * Get group field key if at least one field is present
	 * (e.g. Pods requires a field data to get the '_pod_name' part).
	 * The global group filter stays empty, so beginners can easily mix fields from different groups.
	 *
	 * @param mixed[] $group_fields
	 */
	protected function get_first_field_group_key( WP_Post $from_post, array $group_fields ): string {
		$is_filled = count( $group_fields ) > 0;

		if ( $is_filled ) {
			return $this->get_group_key_by_from_post( $from_post, arr( $group_fields[0] ) );
		}

		return '';
	}

	protected function is_group_post_published( WP_Post $post ): bool {
		$vendor_post_type = $this->get_vendor_post_type();

		return $vendor_post_type === $post->post_type &&
				'publish' === $post->post_status;
	}

	/**
	 * @return Layout_Settings[]
	 */
	protected function get_group_related_views( WP_Post $group_post ): array {
		$group_key = $this->data_vendor->get_group_key( $group_post->post_name );

		return $this->layouts_settings_storage->get_all_with_meta_group_in_use( $group_key );
	}

	/**
	 * @param array<string,mixed> $messages
	 *
	 * @return array<string,mixed>
	 */
	protected function add_invalid_markup_message( array $messages ): array {
		global $post;

		$vendor_post_type = $this->get_vendor_post_type();
		$message          = key_exists( $vendor_post_type, $messages ) && $post instanceof WP_Post ?
			$this->get_invalid_markup_message( $post ) :
			'';
		$is_filled        = strlen( $message ) > 0;

		if ( $is_filled ) {
			$type_messages                = arr( $messages, $vendor_post_type );
			$type_messages[1]             = string( $type_messages, 1 ) . $message;
			$messages[ $vendor_post_type ] = $type_messages;
		}

		return $messages;
	}

	protected function get_invalid_markup_message( WP_Post $group_post ): string {
		if ( $this->is_group_post_published( $group_post ) ) {
			$related_views = $this->get_group_related_views( $group_post );

			$this->update_markup_preview( $related_views );

			$invalid_links = $this->get_related_view_links_with_invalid_custom_markup( $related_views );

			if ( array() !== $invalid_links ) {
				return $this->render_invalid_markup_message( $invalid_links );
			}
		}

		return '';
	}

	/**
	 * @param string[] $invalid_links
	 */
	protected function render_invalid_markup_message( array $invalid_links ): string {
		$links = array();

		foreach ( $invalid_links as $link ) {
			// the 'br' goes between the links.
			if ( array() !== $links ) {
				$links[] = tag( 'br' );
			}

			$links[] = raw_html( $link );
		}

		$links_amount = count( $invalid_links );
		$title_label  = __( 'Views associated with this group contain invalid Custom Markup', 'acf-views' );
		$title_text   = sprintf( '%s %s:', $links_amount, $title_label );

		$title = tag( 'span' )
			->attributes( array( 'style' => array( 'color' => '#dc3232' ) ) )
			->content(
				$title_text
			);

		$tags = array(
			tag( 'br' ),
			tag( 'br' ),
			$title,
			tag( 'br' ),
			tag( 'br' ),
			...$links,
		);

		return $this->html_renderer->render_html( ...$tags );
	}

	protected function create_invalid_markup_link( Layout_Settings $layout_settings ): ?string {
		$custom_markup = trim( $layout_settings->custom_markup );
		$is_filled     = strlen( $custom_markup ) > 0;

		if ( $is_filled ) {
			$unique_id     = $layout_settings->get_unique_id();
			$view          = $this->layout_factory->make( new Source(), $unique_id, 0 );
			$error         = $view->get_markup_validation_error();
			$is_with_error = strlen( $error ) > 0;

			if ( $is_with_error ) {
				$edit_link = $layout_settings->get_edit_post_link();

				$link_tag = tag( 'a' )
					->attributes(
						array(
							'target' => '_blank',
							'href'   => $edit_link,
						)
					)
					->content(
						$layout_settings->title
					);

				return $this->html_renderer->render_html( $link_tag );
			}
		}

		return null;
	}
}
