<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Cpt;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Core\Avf_User;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Plugin_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Plugin_Meta;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Plugin\Utils\Query_Arguments;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\Cpt_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Core\Git_Api\Git_Api_Interface;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\tag;
use function Org\Wplake\Advanced_Views\Template\Html\print_html;

abstract class Git_Meta_Box extends Actor_Base implements Actor {

	const NONCE_PUSH = 'av-git-item-push';

	private Settings_Storage $settings;
	private string $cpt_name;
	private Cpt_Settings_Storage $cpt_settings_storage;
	private Git_Api_Interface $git_api;
	/**
	 * Used to avoid potential recursion (if user made the recursion setup)
	 *
	 * @var array<string, bool>
	 */
	private array $pushing_unique_ids;
	private Plugin_Meta $plugin_meta;

	public function __construct(
		Plugin_Cpt $cpt,
		Settings_Storage $settings,
		Cpt_Settings_Storage $cpt_settings_storage,
		Git_Api_Interface $git_api,
		Plugin_Meta $plugin_meta
	) {
		$this->cpt_name             = $cpt->cpt_name();
		$this->settings             = $settings;
		$this->cpt_settings_storage = $cpt_settings_storage;
		$this->git_api              = $git_api;
		$this->pushing_unique_ids   = array();
		$this->plugin_meta          = $plugin_meta;
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		return true;
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		if ( $route_detector->is_cpt_admin_route( $this->cpt_name, Route_Detector::CPT_EDIT ) ) {
			// for feature-promotion, show the meta-box even there are no set repositories.
			self::add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		}

		if ( wp_doing_ajax() ) {
			self::add_action(
				sprintf( 'wp_ajax_acf-views__git_meta_box_%s', $this->cpt_name ),
				array( $this, 'push_item_ajax' )
			);
		}
	}

	protected function print_git_lab_repositories_meta_box(): void {
		$intro        = $this->intro_tags();
		$nonce        = $this->nonce_tags();
		$repositories = $this->repositories_tags();
		$with_fields  = $this->with_fields_tags();
		$reminder     = $this->save_reminder_tags();
		$button       = $this->button_tags();

		$tags                  = tag( 'av-git-meta-box' )
			->attributes( array(
				'class' => 'av-git-meta-box',
				'style' => array( 'transition' => 'all .3s ease' ),
			) )
			->content(
				$intro,
				$nonce,
				$repositories,
				tag( 'br' ),
				tag( 'br' ),
				$with_fields,
				tag( 'br' ),
				$reminder,
				$button
			);

		print_html( $tags );
	}

	/**
	 * @return array<string|int, string>
	 */
	protected function get_export_fs_field_values( Cpt_Settings $cpt_settings, bool $is_with_meta_groups ): array {
		// skip auto-generates files, they shouldn't be included.
		return $this->cpt_settings_storage->get_fs_fields()->get_fs_field_values(
			$cpt_settings,
			false,
			true
		);
	}

	abstract protected function push_related_cpt_data_items(
		Cpt_Settings $cpt_settings,
		string $repository_id,
		string $access_token,
		bool $is_with_meta_groups
	): bool;

	protected function push_cpt_data(
		Cpt_Settings $cpt_settings,
		string $repository_id,
		string $access_token,
		bool $is_with_meta_groups
	): bool {
		// set plugin version to be included in the data.json file.
		$cpt_settings->plugin_version = $this->plugin_meta->get_version();

		$fs_field_values = $this->get_export_fs_field_values( $cpt_settings, $is_with_meta_groups );

		// reset plugin version (we don't need it for instances outside of Git repository).
		$cpt_settings->plugin_version = '';

		$fs_title = $this->cpt_settings_storage->get_file_system()->get_fs_title( $cpt_settings->title );

		return $this->git_api->push(
			$repository_id,
			$access_token,
			$this->cpt_name,
			$cpt_settings->get_unique_id( true ),
			$fs_title,
			$fs_field_values
		);
	}

	protected function push_cpt_data_with_all_related_items(
		Cpt_Settings $cpt_settings,
		string $repository_id,
		string $access_token,
		bool $is_with_meta_groups
	): bool {
		$unique_id = $cpt_settings->get_unique_id();

		// avoid recursion (only if the user made the recursion setup).
		if ( key_exists( $unique_id, $this->pushing_unique_ids ) ) {
			return false;
		}

		if ( false === $this->push_cpt_data( $cpt_settings, $repository_id, $access_token, $is_with_meta_groups ) ) {
			return false;
		}

		$this->pushing_unique_ids[ $unique_id ] = true;

		$is_successful = $this->push_related_cpt_data_items(
			$cpt_settings,
			$repository_id,
			$access_token,
			$is_with_meta_groups
		);

		unset( $this->pushing_unique_ids[ $unique_id ] );

		return $is_successful;
	}

	public function add_meta_box(): void {
		// for feature-promotion, show the meta-box even there are no set repositories.

		add_meta_box(
			'acf-views_gitlab_repositories',
			__( 'GitLab repositories', 'acf-views' ),
			function ( $post ): void {
				if ( null === $post ||
					'publish' !== $post->post_status ) {
					echo esc_html( __( 'GitLab repository actions are available after publishing.', 'acf-views' ) );

					return;
				}

				$this->print_git_lab_repositories_meta_box();
			},
			array(
				$this->cpt_name,
			),
			'side'
		);
	}

	public function push_item_ajax(): void {
		$post_id             = Query_Arguments::get_int_for_admin_action(
			'_postId',
			self::NONCE_PUSH,
			'post'
		);
		$repository_id       = Query_Arguments::get_string_for_admin_action(
			'_repositoryId',
			self::NONCE_PUSH,
			'post'
		);
		$is_with_meta_groups = '1' === Query_Arguments::get_string_for_admin_action(
			'_isWithMetaGroups',
			self::NONCE_PUSH,
			'post'
		);

		if ( 0 === $post_id ||
			'' === $repository_id ||
			! Avf_User::can_manage() ) {
			echo esc_html( __( 'Error!', 'acf-views' ) );
			exit;
		}

		$post_type       = get_post( $post_id )->post_type ?? '';
		$repository_info = $this->settings->get_git_repository_info_by_id( $repository_id );

		if ( $this->cpt_name !== $post_type ||
			null === $repository_info ) {
			echo esc_html( __( 'Error!', 'acf-views' ) );
			exit;
		}

		$unique_id = get_post( $post_id )->post_name ?? '';

		$cpt_data = $this->cpt_settings_storage->get( $unique_id );

		if ( $this->push_cpt_data_with_all_related_items(
			$cpt_data,
			$repository_info['id'],
			$repository_info['accessToken'],
			$is_with_meta_groups
		) ) {
			echo esc_html( __( 'Pushed!', 'acf-views' ) );
		} else {
			echo esc_html( __( 'Failed!', 'acf-views' ) );
		}

		exit;
	}

	protected function intro_tags(): Html_Tag {
		$save_label    = __( 'Save the item', 'acf-views' );
		$library_label = __( 'to your library', 'acf-views' );
		$library_text  = sprintf( ' %s.', $library_label );

		$docs_link = tag( 'a' )
			->attributes( array(
				'target' => '_blank',
				'href'   => 'https://docs.advanced-views.com/features/workflow/cross-installation-components',
			) )
			->content(
				$save_label
			);

		return tag( 'p' )
			->content(
				$docs_link,
				$library_text
			);
	}

	protected function nonce_tags(): Html_Tag {
		$nonce = wp_create_nonce( self::NONCE_PUSH );

		return tag( 'input' )
			->attributes( array(
				'type'  => 'hidden',
				'name'  => '_av-nonce',
				'value' => $nonce,
			) );
	}

	protected function repositories_tags(): Html_Tag {
		$create_option = static function ( array $git_repository ): Html_Tag {

			return tag( 'option' )
				->attributes( array( 'value' => $git_repository['id'] ) )
				->content(
					$git_repository['name']
				);
		};

		$git_repositories = $this->settings->get_git_repositories();
		$option_chains    = array_map( $create_option, $git_repositories );

		$placeholder_label = __( 'Select repository', 'acf-views' );

		$placeholder_option       = tag( 'option' )
			->attributes( array( 'value' => '' ) )
			->content(
				$placeholder_label
			);

		return tag( 'select' )
			->attributes( array(
				'name'     => '_git-repo',
				'required' => '',
			) )
			->content(
				$placeholder_option,
				...$option_chains
			);
	}

	protected function with_fields_tags(): Html_Tag {
		$label_text = __( 'Include related Meta groups', 'acf-views' );

		$label       = tag( 'label' )
			->attributes( array( 'for' => 'av_git-repo-with-fields' ) )
			->content(
				$label_text
			);

		return tag( 'div' )
			->attributes( array(
				'style' => array(
					'display'     => 'flex',
					'align-items' => 'center',
				),
			) )
			->content(
				tag( 'input' )
					->attributes( array(
						'type' => 'checkbox',
						'name' => '_git-repo-with-fields',
						'id'   => 'av_git-repo-with-fields',
					) ),
				$label
			);
	}

	protected function save_reminder_tags(): Html_Tag {

		return tag( 'p' )
			->content(
				__( 'Note: all changes must be already saved.', 'acf-views' )
			);
	}

	protected function button_tags(): Html_Tag {
		$button_label = __( 'Push to the repository', 'acf-views' );

		return tag( 'button' )
			->attributes( array( 'class' => 'button button-primary button-large' ) )
			->content(
				$button_label
			);
	}
}
