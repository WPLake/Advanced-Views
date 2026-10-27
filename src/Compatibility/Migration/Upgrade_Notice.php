<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Compatibility\Migration;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Compatibility\Migration\Core\Version\Version_Migration;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Core\Avf_User;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Plugin\Plugin_Meta;
use Org\Wplake\Advanced_Views\Plugin\Settings\Options_Storage;
use Org\Wplake\Advanced_Views\Plugin\Utils\Query_Arguments;
use Org\Wplake\Advanced_Views\Template\Html\Html_Renderer;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\tag;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
final class Upgrade_Notice extends Actor_Base implements Actor {
	private Plugin_Meta $plugin_meta;
	private string $dismiss_key;
	private string $dismiss_nonce_action;
	protected Html_Renderer $html_renderer;

	public function __construct( Plugin_Meta $plugin_meta, Html_Renderer $html_renderer ) {
		$this->plugin_meta = $plugin_meta;

		$this->dismiss_key          = sprintf( '_%s-upgrade-notice-dismiss', $this->plugin_meta->get_short_slug() );
		$this->dismiss_nonce_action = 'avf-upgrade-notice';
		$this->html_renderer = $html_renderer;
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		return $route_detector->is_admin_route();
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		$upgrade_notice = $this->get_upgrade_notice();

		if ( strlen( $upgrade_notice ) > 0 ) {
			self::add_action(
				'admin_notices',
				function (): void {
					if ( ! $this->hide_notice() ) {
						$this->print_notice();
					}
				}
			);
		}
	}

	/**
	 * @param Version_Migration[] $version_migrations
	 */
	public function setup_upgrade_notice( array $version_migrations ): void {
		Plugin::on_translations_ready(
			function () use ( $version_migrations ): void {
				$upgrade_notices = array();

				foreach ( $version_migrations as $version_migration ) {
					$version_upgrade_notice = $version_migration->get_upgrade_notice_text();

					if ( is_string( $version_upgrade_notice ) ) {
						$upgrade_notices[] = sprintf( '%s (v. %s)', $version_upgrade_notice, $version_migration->introduced_version() );
					}
				}

				if ( count( $upgrade_notices ) > 0 ) {
					$upgrade_notice = implode( "\n", $upgrade_notices );

					Options_Storage::set_transient( Options_Storage::TRANSIENT_UPGRADE_NOTICE, $upgrade_notice, WEEK_IN_SECONDS );
				}
			}
		);
	}

	public function print_notice(): void {
		$upgrade_notice = $this->get_upgrade_notice();

		$dismiss_members = Avf_User::can_manage() ?
			$this->get_dismiss_members() :
			null;

		$notice_lines = $this->get_notice_lines_members( $upgrade_notice );

		$paragraph =
			tag( 'p' )
			->content(
				__( 'Advanced Views plugin has been successfully upgraded!', 'acf-views' ),
				tag( 'br' ),
				...array_merge( $notice_lines, array( $dismiss_members ) )
			);
		$tags      = tag( 'div' )
			->attributes( array( 'class' => 'notice notice-info' ) )
			->content(
				$paragraph
			);

		$this->html_renderer->print_html( $tags );
	}

	protected function hide_notice(): bool {
		$dismiss_value = Query_Arguments::get_string_for_admin_action(
			$this->dismiss_key,
			$this->dismiss_nonce_action
		);

		if ( strlen( $dismiss_value ) > 0 &&
			Avf_User::can_manage() ) {
			Options_Storage::delete_transient( Options_Storage::TRANSIENT_UPGRADE_NOTICE );

			return true;
		}

		return false;
	}

	protected function get_upgrade_notice(): string {
		$upgrade_transient = Options_Storage::get_transient( Options_Storage::TRANSIENT_UPGRADE_NOTICE );

		return string( $upgrade_transient );
	}

	/**
	 * @return Html_Node[]
	 */
	protected function get_notice_lines_members( string $upgrade_notice ): array {
		$members = array();

		foreach ( explode( "\n", $upgrade_notice ) as $index => $line ) {
			if ( $index > 0 ) {
				$members[] = tag( 'br' );
			}

			$members[] = $line;
		}

		return $members;
	}

	protected function get_dismiss_members(): Html_Tag {
		$dismiss_url = add_query_arg(
			array(
				$this->dismiss_key => 1,
				'_wpnonce'         => wp_create_nonce( $this->dismiss_nonce_action ),
			),
			Plugin::get_current_admin_url()
		);

		return tag( 'a' )
			->attributes(
				array(
					'style' => array( 'float' => 'right' ),
					'href'  => $dismiss_url,
				)
			)
			->content(
				__( 'Thanks, hide', 'acf-views' )
			);
	}
}
