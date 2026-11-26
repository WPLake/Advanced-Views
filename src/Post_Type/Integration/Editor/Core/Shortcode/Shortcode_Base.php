<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Integration\Editor\Core\Shortcode;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Plugin\Utils\Code_Minifier;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Core\Avf_User;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Pub\Public_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Dashboard\Live_Reloader\Live_Reloader_Component;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\Cpt_Settings_Storage;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use function Org\Wplake\Advanced_Views\Template\Html\raw_html;
use function Org\Wplake\Advanced_Views\Template\Html\tag;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\any;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\arr;

abstract class Shortcode_Base extends Actor_Base implements Shortcode_Renderer, Actor {
	private Settings_Storage $settings;
	private Cpt_Settings_Storage $cpt_settings_storage;
	private Live_Reloader_Component $live_reloader_component;
	/**
	 * @var array<string,true>
	 */
	private array $rendered_ids;
	protected Public_Cpt $public_cpt;
	protected Html_Renderer $html_renderer;

	public function __construct(
		Public_Cpt $public_cpt,
		Settings_Storage $settings,
		Cpt_Settings_Storage $cpt_settings_storage,
		Live_Reloader_Component $live_reloader_component,
		Html_Renderer $html_renderer
	) {
		$this->public_cpt              = $public_cpt;
		$this->rendered_ids            = array();
		$this->settings                = $settings;
		$this->cpt_settings_storage    = $cpt_settings_storage;
		$this->live_reloader_component = $live_reloader_component;
		$this->html_renderer           = $html_renderer;
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		return true;
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		foreach ( $this->public_cpt->shortcodes() as $shortcode ) {
			self::add_shortcode( $shortcode, array( $this, 'do_shortcode' ) );
		}
	}

	/**
	 * @param mixed[] $shortcode_arguments
	 */
	public function maybe_add_quick_link_and_shadow_css(
		string $html,
		string $unique_id,
		array $shortcode_arguments,
		bool $is_gutenberg_block
	): string {
		$this->rendered_ids[ $unique_id ] = true;

		$cpt_data = $this->cpt_settings_storage->get( $unique_id );

		$is_dev_mode        = $this->settings->is_dev_mode();
		$can_manage         = Avf_User::can_manage();
		$is_with_quick_link = $is_dev_mode && $can_manage;

		$reloading_component = $this->live_reloader_component->get_reloading_component(
			$cpt_data,
			$shortcode_arguments,
			$is_gutenberg_block
		);
		$html                = $reloading_component . $html;

		$shadow_css    = $this->get_internal_shadow_css( $cpt_data );
		$embedded_html = self::get_embedded_html( $html, $shadow_css, $cpt_data );

		if ( is_string( $embedded_html ) ) {
			$html       = $embedded_html;
			$shadow_css = '';
		}

		$is_with_web_component = Cpt_Settings::WEB_COMPONENT_NONE !== $cpt_data->web_component;
		$is_markup_added       = $is_with_quick_link || $is_with_web_component;

		if ( $is_markup_added ) {
			return $this->add_quick_link_and_last_tag_markup( $html, $shadow_css, $is_with_quick_link, $cpt_data );
		}

		return $html;
	}

	public function get_rendered_items_count(): int {
		return count( $this->rendered_ids );
	}

	/**
	 * @param array<string,string>|string $args
	 */
	public function do_shortcode( $args ): string {
		$attrs     = array();
		$args_list = arr( $args );

		// positional attributes have int keys, but the renderers work with named ones.
		foreach ( $args_list as $name => $value ) {
			$attrs[ (string) $name ] = $value;
		}

		return $this->render_shortcode( $attrs );
	}

	protected function get_post_type(): string {
		return $this->public_cpt->cpt_name();
	}

	/**
	 * @param string[] $user_roles
	 * @param mixed[] $shortcode_args
	 */
	protected function is_shortcode_available_for_user( array $user_roles, array $shortcode_args ): bool {
		$user_with_roles_input    = any( $shortcode_args, 'user-with-roles' );
		$user_with_roles          = self::get_roles( $user_with_roles_input );
		$user_without_roles_input = any( $shortcode_args, 'user-without-roles' );
		$user_without_roles       = self::get_roles( $user_without_roles_input );

		return self::is_available_for_roles(
			$user_roles,
			$user_with_roles,
			$user_without_roles
		);
	}

	protected function create_error_markup( string $error_text ): string {
		$tags = tag( 'p' )
			->attributes( array( 'style' => array( 'color' => 'red' ) ) )
			->content( $error_text );

		return $this->html_renderer->render_html( $tags );
	}

	/**
	 * @param mixed[] $args
	 */
	protected function get_error_markup( string $shortcode, array $args, string $error ): string {
		if ( Avf_User::can_see_errors() ) {
			$error_text = self::get_error_text( $shortcode, $args, $error );

			return $this->create_error_markup( $error_text );
		}

		return '';
	}

	protected function get_shortcode_name(): string {
		return $this->public_cpt->shortcode();
	}

	protected function get_live_reloader_component(): Live_Reloader_Component {
		return $this->live_reloader_component;
	}

	protected function get_internal_shadow_css( Cpt_Settings $cpt_data ): string {
		$is_css_internal = $cpt_data->is_css_internal();

		return $is_css_internal ?
			$this->create_shadow_css( $cpt_data ) :
			'';
	}

	protected function create_shadow_css( Cpt_Settings $cpt_data ): string {
		$css_code   = $cpt_data->get_css_code( Cpt_Settings::CODE_MODE_DISPLAY );
		$shadow_css = Code_Minifier::minify_css( $css_code );
		$host_css   = sprintf( ':host{all: initial!important;}%s', $shadow_css );

		$tags = tag( 'style' )
			->content( raw_html( $host_css ) );

		return $this->html_renderer->render_html( $tags );
	}

	protected function add_quick_link_and_last_tag_markup(
		string $html,
		string $shadow_css,
		bool $is_with_quick_link,
		Cpt_Settings $cpt_data
	): string {
		$html           = trim( $html );
		$last_tag_regex = Cpt_Settings::WEB_COMPONENT_SHADOW_DOM_DECLARATIVE !== $cpt_data->web_component ?
			'/<\/[a-z0-9\-_]+>$/' :
			'/<\/template>/';

		$last_tag_match = self::find_last_tag_match( $html, $last_tag_regex );

		if ( is_array( $last_tag_match ) ) {
			return $this->insert_markup_before_last_tag(
				$html,
				$last_tag_match,
				$shadow_css,
				$is_with_quick_link,
				$cpt_data
			);
		}

		return $html;
	}

	/**
	 * @param array{0:string,1:int} $last_tag_match
	 */
	protected function insert_markup_before_last_tag(
		string $html,
		array $last_tag_match,
		string $shadow_css,
		bool $is_with_quick_link,
		Cpt_Settings $cpt_data
	): string {
		$quick_link_html = $is_with_quick_link ? $this->create_quick_link_html( $cpt_data ) : '';

		$closing_div          = $last_tag_match[0];
		$closing_div_position = $last_tag_match[1];
		$closing_div_length   = strlen( $closing_div );

		return substr_replace(
			$html,
			$shadow_css . $quick_link_html . $closing_div,
			$closing_div_position,
			$closing_div_length
		);
	}

	protected function create_quick_link_html( Cpt_Settings $cpt_data ): string {
		$label       = __( 'Edit', 'acf-views' );
		$label      .= sprintf( ' "%s"', $cpt_data->title );
		$link_target = self::get_quick_link_target();

		$attrs = array(
			'href'        => $cpt_data->get_edit_post_link(),
			'target'      => $link_target,
			'class'       => 'acf-views__quick-link',
			'style'       => 'display:block;color:#008BB7;transition: all .3s ease;text-decoration: none;font-size: 12px;white-space: nowrap;opacity:.5;padding:3px 0;',
			'onMouseOver' => "this.style.opacity='1';this.style.textDecoration='underline'",
			'onMouseOut'  => "this.style.opacity='.5';this.style.textDecoration='none'",
		);

		$tags = tag( 'a' )
			->attributes( $attrs )
			->content( $label );

		return $this->html_renderer->render_html( $tags );
	}

	/**
	 * @return string|null Null if the shadow CSS can't be embedded (not a declarative shadow DOM).
	 */
	protected static function get_embedded_html( string $html, string $shadow_css, Cpt_Settings $cpt_data ): ?string {
		$is_declarative_shadow_dom = Cpt_Settings::WEB_COMPONENT_SHADOW_DOM_DECLARATIVE === $cpt_data->web_component;

		return $is_declarative_shadow_dom ?
			self::embed_shadow_css( $html, $shadow_css ) :
			null;
	}

	/**
	 * @param mixed $argument_input
	 *
	 * @return string[]
	 */
	protected static function get_roles( $argument_input ): array {
		if ( is_string( $argument_input ) ) {
			return self::split_roles( $argument_input );
		}

		// can be an array, if called from Bridge.
		if ( is_array( $argument_input ) ) {
			/**
			 * @var string[] $argument_input
			 */
			return $argument_input;
		}

		return array();
	}

	/**
	 * @return string[]
	 */
	protected static function split_roles( string $roles_input ): array {
		$roles_input = trim( $roles_input );
		$is_filled   = strlen( $roles_input ) > 0;

		return $is_filled ?
			explode( ',', $roles_input ) :
			array();
	}

	/**
	 * @param string[] $user_roles
	 * @param string[] $allowed_roles Empty for no limits.
	 * @param string[] $denied_roles Empty for no limits.
	 */
	protected static function is_available_for_roles( array $user_roles, array $allowed_roles, array $denied_roles ): bool {
		$matched_allowed_roles = array_intersect( $allowed_roles, $user_roles );
		$matched_denied_roles  = array_intersect( $denied_roles, $user_roles );

		$is_allowed_unlimited = 0 === count( $allowed_roles );
		$is_allowed_matched   = count( $matched_allowed_roles ) > 0;
		$is_denied_matched    = count( $matched_denied_roles ) > 0;

		$is_allowed = $is_allowed_unlimited || $is_allowed_matched;

		return $is_allowed && ! $is_denied_matched;
	}

	/**
	 * @param mixed[] $args
	 */
	protected static function get_error_text( string $shortcode, array $args, string $error ): string {
		$joined_attrs    = self::join_string_attrs( $args );
		$shortcode_debug = sprintf( '(%s %s)', $shortcode, $joined_attrs );

		$error_label = __( 'AVF shortcode render error:', 'acf-views' );

		return sprintf( '%s %s %s', $error_label, $error, $shortcode_debug );
	}

	/**
	 * @param mixed[] $args
	 */
	protected static function join_string_attrs( array $args ): string {
		$attrs = array();

		foreach ( $args as $name => $value ) {
			// skip complex types (that may be passed from Bridge).
			if ( is_string( $value ) ) {
				$attrs[] = sprintf( '%s="%s"', $name, $value );
			}
		}

		return implode( ' ', $attrs );
	}

	/**
	 * @return string|null Null if the html has no declarative shadow template.
	 */
	protected static function embed_shadow_css( string $html, string $shadow_css ): ?string {
		$template_opening_tag = '<template shadowrootmode="open">';

		// use strpos instead of str_replace, as we need to replace the first occurrence only,
		// e.g. for Card + View inside, only for Card, as for View we already processed.
		$pos = strpos( $html, $template_opening_tag );

		if ( is_int( $pos ) ) {
			$template_opening_tag_length = strlen( $template_opening_tag );
			$replacement                 = sprintf( "%s\r\n%s", $template_opening_tag, $shadow_css );

			return substr_replace(
				$html,
				$replacement,
				$pos,
				$template_opening_tag_length
			);
		}

		return null;
	}

	/**
	 * @return array{0:string,1:int}|null The last match (the tag and its offset), null if there is none.
	 */
	protected static function find_last_tag_match( string $html, string $last_tag_regex ): ?array {
		preg_match_all( $last_tag_regex, $html, $matches, PREG_OFFSET_CAPTURE );

		$matches_count = count( $matches[0] );

		if ( $matches_count > 0 ) {
			// we need the last match only, e.g.
			// e.g. for Card + View inside, only for Card, as for View we already processed.
			return $matches[0][ $matches_count - 1 ];
		}

		return null;
	}

	protected static function get_quick_link_target(): string {
		$site_url         = get_site_url();
		$playground_pos   = strpos( $site_url, 'playground.wordpress.net' );
		$is_wp_playground = is_int( $playground_pos );

		return $is_wp_playground ?
			'_self' :
			'_blank';
	}
}
