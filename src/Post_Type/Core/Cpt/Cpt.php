<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Cpt;

use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Core\Avf_User;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Plugin_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Environment\External_Links;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\Cpt_Settings_Storage;
use Org\Wplake\Advanced_Views\Template\Html\Html_Renderer;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

defined( 'ABSPATH' ) || exit;

abstract class Cpt extends Actor_Base implements Actor {
	private Cpt_Settings_Storage $cpt_settings_storage;
	protected Plugin_Cpt $plugin_cpt;
	protected Html_Renderer $html_renderer;

	public function __construct( Plugin_Cpt $plugin_cpt, Cpt_Settings_Storage $cpt_settings_storage, Html_Renderer $html_renderer ) {
		$this->html_renderer = $html_renderer;
		$this->plugin_cpt           = $plugin_cpt;
		$this->cpt_settings_storage = $cpt_settings_storage;
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		return true;
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		self::add_action( 'init', array( $this, 'add_cpt' ) );

		if ( $route_detector->is_admin_route() ) {
			self::add_filter( 'admin_footer_text', array( $this, 'print_survey_link' ) );
			self::add_filter( 'post_updated_messages', array( $this, 'replace_post_updated_message' ) );
			self::add_filter( 'enter_title_here', array( $this, 'get_title_placeholder' ) );
		}
	}

	abstract public function add_cpt(): void;

	/**
	 * @param array<string, array<int, string>> $messages
	 *
	 * @return array<string, array<int, string>>
	 */
	abstract public function replace_post_updated_message( array $messages ): array;

	public function get_title_placeholder( string $title ): string {
		$screen = get_current_screen()->post_type ?? '';

		if ( $this->get_cpt_name() !== $screen ) {
			return $title;
		}

		return sprintf(
		// translators: %s - singular name of the CPT.
			__( 'Name your %s here (required)', 'acf-views' ),
			$this->plugin_cpt->labels()->singular_name()
		);
	}

	public function print_survey_link( string $html ): string {
		$current_screen = get_current_screen();

		if ( null === $current_screen ||
			$this->get_cpt_name() !== $current_screen->post_type ) {
			return $html;
		}

		$take_label      = __( 'Take', 'acf-views' );
		$survey_label    = __( '2 minute survey', 'acf-views' );
		$improve_label   = __( 'to improve Advanced Views.', 'acf-views' );
		$thank_label     = __( 'Thank you for creating with', 'acf-views' );
		$wordpress_label = __( 'WordPress', 'acf-views' );
		$and_label       = __( 'and', 'acf-views' );
		$plugin_label    = __( 'Advanced Views', 'acf-views' );
		$take_text       = sprintf( '%s ', $take_label );
		$improve_text    = sprintf( ' %s', $improve_label );
		$thank_text      = sprintf( '%s ', $thank_label );
		$and_text        = sprintf( ' %s ', $and_label );
		$survey_link     = self::link_tags( External_Links::SURVEY_URL, $survey_label );
		$wordpress_link  = self::link_tags( 'https://wordpress.org/', $wordpress_label );
		$plugin_link     = self::link_tags( External_Links::BASIC_VERSION_URL, $plugin_label );

		$survey     = tag( 'span' )
			->content(
				$take_text,
				$survey_link,
				$improve_text
			);

		$tags       = tag( 'span' )
			->attributes( array( 'id' => 'footer-thankyou' ) )
			->content(
				$thank_text,
				$wordpress_link,
				$and_text,
				$plugin_link,
				'. ',
				$survey
			);

		return $this->html_renderer->render( $tags );
	}

	protected function get_cpt_name(): string {
		return $this->plugin_cpt->cpt_name();
	}

	/**
	 * @return array<string,string>
	 */
	protected function get_labels(): array {

		$labels        = $this->plugin_cpt->labels();
		$plural_name   = $labels->plural_name();
		$singular_name = $labels->singular_name();

		return array(
			'name'               => $plural_name,
			'singular_name'      => $singular_name,
			'menu_name'          => __( 'Advanced Views', 'acf-views' ),
			'parent_item_colon'  => sprintf(
			// translators: %s - singular name of the CPT.
				__( 'Parent %s', 'acf-views' ),
				$singular_name
			),
			'all_items'          => $plural_name,
			'view_item'          => sprintf(
			// translators: %s - singular name of the CPT.
				__( 'Browse %s', 'acf-views' ),
				$singular_name
			),
			'add_new_item'       => sprintf(
			// translators: %s - singular name of the CPT.
				__( 'Add New %s', 'acf-views' ),
				$singular_name
			),
			'add_new'            => __( 'Add New', 'acf-views' ),
			'item_updated'       => sprintf(
			// translators: %s - singular name of the CPT.
				__( '%s updated', 'acf-views' ),
				$singular_name
			),
			'edit_item'          => sprintf(
			// translators: %s - singular name of the CPT.
				__( 'Edit %s', 'acf-views' ),
				$singular_name
			),
			'update_item'        => sprintf(
			// translators: %s - singular name of the CPT.
				__( 'Update %s', 'acf-views' ),
				$singular_name
			),
			'search_items'       => sprintf(
			// translators: %s - plural name of the CPT.
				__( 'Search %s', 'acf-views' ),
				$plural_name
			),
			'not_found'          => $this->get_not_found_label(),
			'not_found_in_trash' => __( 'Not Found In Trash', 'acf-views' ),
		);
	}

	protected function get_storage_label(): string {
		$state = $this->cpt_settings_storage->get_file_system()->is_active() ?
			__( 'enabled', 'acf-views' ) :
			__( 'disabled', 'acf-views' );

		$storage_label = __( 'File system storage', 'acf-views' );
		$storage_link  = self::link_tags( 'https://docs.advanced-views.com/features/file-system-storage', $storage_label );

		// translators: %s - the file system storage state, 'enabled' or 'disabled'.
		$state_label = __( 'is %s.', 'acf-views' );
		$state_value = sprintf( $state_label, $state );
		$state_text  = sprintf( ' %s', $state_value );

		$tags       = tag( 'span' )
			->attributes( array(
				'style' => array(
					'display' => 'inline-block',
					'float'   => 'right',
				),
			) )
			->content(
				$storage_link,
				$state_text
			);

		return $this->html_renderer->render( $tags );
	}

	protected function get_not_found_label(): string {
		$singular_name = $this->plugin_cpt->labels()->singular_name();
		$add_new_url   = admin_url( sprintf( 'post-new.php?post_type=%s', $this->get_cpt_name() ) );

		$tags = array(
			sprintf(
			// translators: %s - singular name of the CPT.
				__( 'No %s yet.', 'acf-views' ),
				$singular_name
			) . ' ',
			self::link_tags(
				$add_new_url,
				sprintf(
				// translators: %s - singular name of the CPT.
					__( 'Add New %s', 'acf-views' ),
					$singular_name
				),
				'_self'
			),
		);

		return $this->html_renderer->render( ...$tags );
	}

	protected static function link_tags( string $url, string $label, string $target = '_blank' ): Html_Tag {

		return tag( 'a' )
			->attributes( array(
				'target' => $target,
				'href'   => $url,
			) )
			->content(
				$label
			);
	}

	/**
	 * @return string[]
	 */
	protected function get_cpt_capabilities(): array {
		$manage_capability = Avf_User::get_manage_capability();

		$post_capabilities = array(
			'edit_post',
			'read_post',
			'delete_post',
			'edit_posts',
			'edit_others_posts',
			'delete_posts',
			'publish_posts',
			'read_private_posts',
		);

		return array_fill_keys( $post_capabilities, $manage_capability );
	}

	/**
	 * @param array<string,mixed> $args
	 */
	protected function register_cpt( array $args ): void {
		$default_args = array(
			// shouldn't be presented in the sitemap and other places.
			'public'              => false,
			'show_ui'             => true,
			'show_in_rest'        => true,
			'has_archive'         => false,
			'show_in_nav_menus'   => false,
			'delete_with_user'    => false,
			'exclude_from_search' => true,
			'capabilities'        => $this->get_cpt_capabilities(),
			'hierarchical'        => false,
			'can_export'          => false,
			'rewrite'             => false,
			'query_var'           => false,
			'supports'            => array( 'title', 'editor' ),
			'show_in_graphql'     => false,
			'show_in_menu'        => true,
		);

		$cpt_args = array_merge( $default_args, $args );

		// @phpstan-ignore-next-line
		register_post_type( $this->get_cpt_name(), $cpt_args );

		// since WP 6.6 we can disable it straightly.
		post_type_supports( $this->get_cpt_name(), 'autosave' );
	}
}
