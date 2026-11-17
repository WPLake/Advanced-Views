<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Git_Api;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Settings\Options_Storage;

use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

final class Git_Lab_Api extends Git_Api {
	public function get_file_content( string $repository_id, string $access_token, string $path ): ?string {
		$response = self::request_file(
			$repository_id,
			$access_token,
			$path
		);

		if ( is_array( $response ) ) {
			return wp_remote_retrieve_body( $response );
		}

		$error_message = $response->get_error_message();
		$error_code    = $response->get_error_code();

		$this->get_logger()->warning(
			'fail to request git file content',
			array(
				'error_message' => $error_message,
				'error_code'    => $error_code,
				'repository_id' => $repository_id,
				'path'          => $path,
			)
		);

		return null;
	}

	/**
	 * @param array<string|int, string> $files path => content
	 */
	public function push(
		string $repository_id,
		string $access_token,
		string $cpt_name,
		string $short_unique_id,
		string $fs_title,
		array $files
	): bool {
		$commit_json = $this->create_commit_json(
			$repository_id,
			$access_token,
			$cpt_name,
			$short_unique_id,
			$fs_title,
			$files
		);

		if ( is_string( $commit_json ) ) {
			return $this->post_commit(
				$commit_json,
				$repository_id,
				$access_token,
				$cpt_name,
				$short_unique_id,
				$fs_title
			);
		}

		return false;
	}

	protected function get_transient_prefix(): string {
		return Options_Storage::PREFIX . 'gitlab_';
	}

	/**
	 * @return Git_Repository_Item[]|null
	 */
	protected function request_all_repository_items(
		string $main_folder,
		string $repository_id,
		string $access_token
	): ?array {
		$items       = array();
		$page_number = 1;

		do {
			$page_items = $this->request_page_items(
				$main_folder,
				$repository_id,
				$access_token,
				$page_number
			);
			$is_filled  = is_array( $page_items ) && array() !== $page_items;

			if ( $is_filled ) {
				$items = array_merge( $items, $page_items );
				++$page_number;
			}
		} while ( $is_filled );

		if ( is_array( $page_items ) ) {
			return $this->create_repository_items(
				$items,
				$repository_id,
				$main_folder,
				$page_number
			);
		}

		return null;
	}

	/**
	 * @return array<mixed>|null null if the request failed, an empty array if there are no more items
	 */
	protected function request_page_items(
		string $main_folder,
		string $repository_id,
		string $access_token,
		int $page_number
	): ?array {
		$response = self::request_page(
			$main_folder,
			$repository_id,
			$access_token,
			$page_number
		);

		if ( is_array( $response ) ) {
			return $this->parse_page_items(
				$response,
				$repository_id,
				$main_folder,
				$page_number
			);
		}

		$error_message = $response->get_error_message();
		$error_code    = $response->get_error_code();

		$this->get_logger()->warning(
			'fail to request git repository items',
			array(
				'error_message' => $error_message,
				'error_code'    => $error_code,
				'repository_id' => $repository_id,
				'main_folder'   => $main_folder,
				'page_number'   => $page_number,
			)
		);

		return null;
	}

	/**
	 * @param array<string,mixed> $response
	 *
	 * @return array<mixed> empty if the response is wrong
	 */
	protected function parse_page_items(
		array $response,
		string $repository_id,
		string $main_folder,
		int $page_number
	): array {
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		// 404 if folder is missing
		$response_code = wp_remote_retrieve_response_code( $response );

		$is_response_correct = is_array( $data ) &&
								404 !== $response_code;

		if ( $is_response_correct ) {
			return $data;
		}

		$this->get_logger()->warning(
			'fail to parse git repository items response',
			array(
				'response_code' => $response_code,
				'repository_id' => $repository_id,
				'main_folder'   => $main_folder,
				'page_number'   => $page_number,
			)
		);

		return array();
	}

	/**
	 * @param array<mixed> $items
	 *
	 * @return Git_Repository_Item[]
	 */
	protected function create_repository_items(
		array $items,
		string $repository_id,
		string $main_folder,
		int $page_number
	): array {
		$repository_items = array();

		foreach ( $items as $item ) {
			$repository_item = $this->create_repository_item(
				$item,
				$repository_id,
				$main_folder,
				$page_number
			);

			if ( $repository_item instanceof Git_Repository_Item ) {
				$repository_items[] = $repository_item;
			}
		}

		return $repository_items;
	}

	/**
	 * @param mixed $item
	 */
	protected function create_repository_item(
		$item,
		string $repository_id,
		string $main_folder,
		int $page_number
	): ?Git_Repository_Item {
		$is_item_correct = is_array( $item ) &&
							key_exists( 'name', $item ) &&
							key_exists( 'path', $item );

		if ( $is_item_correct ) {
			$git_repository_item       = new Git_Repository_Item();
			$git_repository_item->name = string( $item, 'name' );
			$git_repository_item->path = string( $item, 'path' );

			return $git_repository_item;
		}

		$this->get_logger()->warning(
			'wrong git repository item',
			array(
				'item'          => $item,
				'repository_id' => $repository_id,
				'main_folder'   => $main_folder,
				'page_number'   => $page_number,
			)
		);

		return null;
	}

	/**
	 * @param array<string|int, string> $files path => content
	 */
	protected function create_commit_json(
		string $repository_id,
		string $access_token,
		string $cpt_name,
		string $short_unique_id,
		string $fs_title,
		array $files
	): ?string {
		// skipCache, to make sure we have the latest data (e.g. updated by other websites or git console)
		// it's essential for choosing the right actionType (add/move/update).
		$all_items = $this->get_all_items(
			$cpt_name,
			$repository_id,
			$access_token,
			true
		);

		$file_path_prefix = $this->create_file_path_prefix(
			$cpt_name,
			$fs_title,
			$short_unique_id
		);

		$actions = $this->create_commit_actions(
			$files,
			$file_path_prefix,
			$short_unique_id,
			$all_items
		);

		$commit_data = array(
			'branch'         => 'main',
			'commit_message' => $fs_title,
			'actions'        => $actions,
		);

		$commit_json = wp_json_encode( $commit_data );

		if ( is_string( $commit_json ) ) {
			return $commit_json;
		}

		$this->get_logger()->warning(
			'fail to encode commit data',
			array(
				'cpt_name'        => $cpt_name,
				'repository_id'   => $repository_id,
				'short_unique_id' => $short_unique_id,
				'fs_title'        => $fs_title,
			)
		);

		return null;
	}

	protected function create_file_path_prefix( string $cpt_name, string $fs_title, string $short_unique_id ): string {
		$main_folder = $this->get_main_folder( $cpt_name );

		$folder_parts = array(
			$fs_title,
			$short_unique_id,
		);
		$folder_name  = join( '_', $folder_parts );

		$prefix_parts = array(
			$main_folder,
			$folder_name,
		);

		return join( '/', $prefix_parts );
	}

	/**
	 * @param array<string|int, string> $files path => content
	 * @param Git_Repository_Item[]     $all_items
	 *
	 * @return array<int, array<string,string>>
	 */
	protected function create_commit_actions(
		array $files,
		string $file_path_prefix,
		string $short_unique_id,
		array $all_items
	): array {
		$actions = array();

		foreach ( $files as $file_name => $file_content ) {
			$path_parts    = array(
				$file_path_prefix,
				$file_name,
			);
			$file_path     = join( '/', $path_parts );
			$previous_path = $this->get_file_path(
				$short_unique_id,
				(string) $file_name,
				$all_items
			);

			$actions[] = self::create_commit_action(
				$file_path,
				$file_content,
				$previous_path
			);
		}

		return $actions;
	}

	protected function post_commit(
		string $commit_json,
		string $repository_id,
		string $access_token,
		string $cpt_name,
		string $short_unique_id,
		string $fs_title
	): bool {
		$response = self::request_commit(
			$commit_json,
			$repository_id,
			$access_token
		);

		if ( is_array( $response ) ) {
			return $this->process_commit_response(
				$response,
				$cpt_name,
				$repository_id
			);
		}

		$error_message = $response->get_error_message();
		$error_code    = $response->get_error_code();

		$this->get_logger()->warning(
			'fail to push git commit',
			array(
				'error_message'   => $error_message,
				'error_code'      => $error_code,
				'cpt_name'        => $cpt_name,
				'repository_id'   => $repository_id,
				'short_unique_id' => $short_unique_id,
				'fs_title'        => $fs_title,
			)
		);

		return false;
	}

	/**
	 * @param array<string,mixed> $response
	 */
	protected function process_commit_response( array $response, string $cpt_name, string $repository_id ): bool {
		$response_code = wp_remote_retrieve_response_code( $response );
		$is_successful = 201 === $response_code;

		// clear cache if successful, as the data has changed.
		if ( $is_successful ) {
			$this->clear_cache( $cpt_name, $repository_id );
		}

		return $is_successful;
	}

	/**
	 * @return array<string,mixed>|\WP_Error
	 */
	protected static function request_file( string $repository_id, string $access_token, string $path ) {
		$encoded_path = rawurlencode( $path );
		$url          = sprintf(
			'https://gitlab.com/api/v4/projects/%s/repository/files/%s/raw?ref=main',
			$repository_id,
			$encoded_path
		);

		$authorization = sprintf( 'Bearer %s', $access_token );

		$args = array(
			'headers' => array(
				'Authorization' => $authorization,
			),
		);

		return wp_remote_get( $url, $args );
	}

	/**
	 * @return array<string,mixed>|\WP_Error
	 */
	protected static function request_page(
		string $main_folder,
		string $repository_id,
		string $access_token,
		int $page_number
	) {
		$url = sprintf(
			'https://gitlab.com/api/v4/projects/%s/repository/tree?path=%s&per_page=100&page=%s&recursive=true',
			$repository_id,
			$main_folder,
			$page_number
		);

		$authorization = sprintf( 'Bearer %s', $access_token );

		$args = array(
			'headers' => array(
				'Authorization' => $authorization,
			),
		);

		return wp_remote_get( $url, $args );
	}

	/**
	 * @return array<string,mixed>|\WP_Error
	 */
	protected static function request_commit( string $commit_json, string $repository_id, string $access_token ) {
		$url = sprintf( 'https://gitlab.com/api/v4/projects/%s/repository/commits', $repository_id );

		$request_data = array(
			'headers' => array(
				'Content-Type'  => 'application/json',
				'PRIVATE-TOKEN' => $access_token,
			),
			'body'    => $commit_json,
		);

		return wp_remote_post( $url, $request_data );
	}

	/**
	 * @return array<string,string>
	 */
	protected static function create_commit_action( string $file_path, string $content, ?string $previous_path ): array {
		$action_type = self::resolve_action_type( $file_path, $previous_path );

		$action_data = array(
			'file_path' => $file_path,
			'content'   => $content,
			'action'    => $action_type,
		);

		if ( 'move' === $action_type && null !== $previous_path ) {
			$action_data['previous_path'] = $previous_path;
		}

		return $action_data;
	}

	protected static function resolve_action_type( string $file_path, ?string $previous_path ): string {
		// the order is the priority.
		$matches = array(
			'create' => null === $previous_path,
			'update' => $file_path === $previous_path,
			'move'   => true,
		);

		return array_search( true, $matches, true );
	}
}
