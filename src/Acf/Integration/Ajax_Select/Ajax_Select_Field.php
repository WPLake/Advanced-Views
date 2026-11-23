<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Acf\Integration\Ajax_Select;

defined( 'ABSPATH' ) || exit;

use acf_field_select;
use Org\Wplake\Advanced_Views\Plugin\Core\Avf_User;
use Org\Wplake\Advanced_Views\Plugin\Utils\Query_Arguments;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;

use function Org\Wplake\Advanced_Views\Template\Html\tag;
use function Org\Wplake\Advanced_Views\Utils\paginate_array;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\arr;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

if ( class_exists( 'acf_field_select' ) ) {
	/**
	 * Universal select with the ajax search, which takes its items from the source set in the 'source' argument
	 * (see Ajax_Select_Cluster). Supports the 'multiple' argument, values can be any strings.
	 *
	 * @phpstan-import-type Html_Node from Html_Tag
	 */
	class Ajax_Select_Field extends acf_field_select {
		private Ajax_Select_Cluster $cluster;
		private Html_Renderer $html_renderer;

		public function __construct( Ajax_Select_Cluster $cluster, Html_Renderer $html_renderer ) {
			// @phpstan-ignore-next-line
			$this->public        = false;
			$this->cluster       = $cluster;
			$this->html_renderer = $html_renderer;

			// @phpstan-ignore-next-line
			parent::__construct();
		}

		public function initialize(): void {
			// @phpstan-ignore-next-line
			$this->name = 'av_ajax_select';
			// @phpstan-ignore-next-line
			$this->defaults = array(
				'multiple'      => 0,
				'allow_null'    => 0,
				'choices'       => array(),
				'default_value' => '',
				'ui'            => 0,
				'ajax'          => 0,
				'placeholder'   => '',
				'return_format' => 'value',
				'source'        => '',
			);

			// Private-only ajax.
			add_action( 'wp_ajax_acf/fields/av_ajax_select/query', array( $this, 'ajax_query' ) );
		}

		/**
		 * @param array<string,mixed> $field
		 */
		public function render_field( $field ): void {
			$source_id     = string( $field, 'source' );
			$source        = $this->cluster->get( $source_id );
			$is_renderable = function_exists( 'acf_render_field' ) && $source instanceof Ajax_Select_Source;

			if ( $is_renderable ) {
				$field['type']    = 'select';
				$field['ui']      = 1;
				$field['ajax']    = 1;
				$field['choices'] = self::get_selected_choices( $source, $field );

				// render (it'll call the select render_field method).
				acf_render_field( $field );

				$this->print_add_new_link( $source );
			}
		}

		public function ajax_query(): void {
			if ( $this->is_permitted() ) {
				wp_send_json( $this->get_response() );
			}

			wp_die( 'No permissions' );
		}

		protected function is_permitted(): bool {
			// Check for permissions instead of the nonce, as ACF team has changed it 3 times, and it keeps breaking.
			return Avf_User::can_manage();
		}

		/**
		 * @return array<string,mixed>
		 */
		protected function get_response(): array {
			$per_page = 20;
			$source   = $this->cluster->get( $this->get_ajax_source_id() );
			$results  = $source instanceof Ajax_Select_Source ?
				self::get_results( $source, $per_page ) :
				array();

			return array(
				'results' => $results,
				'limit'   => $per_page,
			);
		}

		protected function get_ajax_source_id(): string {
			$field_key     = Query_Arguments::get_string_for_non_action( 'field_key', 'post' );
			$is_resolvable = strlen( $field_key ) > 0 && function_exists( 'acf_get_field' );

			if ( $is_resolvable ) {
				$field = arr( acf_get_field( $field_key ) );

				return string( $field, 'source' );
			}

			return '';
		}

		protected function print_add_new_link( Ajax_Select_Source $source ): void {
			$has_url = strlen( $source->get_add_new_url() ) > 0;

			if ( $has_url ) {
				$nodes = $this->compose_add_new_link( $source );

				$this->html_renderer->print_html( ...$nodes );
			}
		}

		/**
		 * @return Html_Node[]
		 */
		protected function compose_add_new_link( Ajax_Select_Source $source ): array {
			$link_attrs = array(
				'class'  => 'acf-views__add-new',
				'target' => '_blank',
				'href'   => $source->get_add_new_url(),
			);

			$link = tag( 'a' )
				->attributes( $link_attrs )
				->content( $source->get_add_new_label() );

			return array( $link );
		}

		/**
		 * @param array<string,mixed> $field
		 *
		 * @return array<string,string>
		 */
		protected static function get_selected_choices( Ajax_Select_Source $source, array $field ): array {
			$items      = $source->get_items();
			$raw_values = (array) ( $field['value'] ?? array() );
			$strings    = array_map( 'strval', $raw_values );
			$values     = array_filter(
				$strings,
				fn( string $value ) => '' !== $value
			);
			$choices    = array();

			foreach ( $values as $value ) {
				$choices[ $value ] = $items[ $value ] ?? $value;
			}

			return $choices;
		}

		/**
		 * @return array<int,array<string,string|int>>
		 */
		protected static function get_results( Ajax_Select_Source $source, int $per_page ): array {
			$current_page = self::get_current_page();
			$search_term  = Query_Arguments::get_string_for_non_action( 's', 'post' );
			$items        = $source->get_items();

			$filtered_items  = self::filter_items( $items, $search_term );
			$paginated_items = paginate_array(
				$filtered_items,
				$per_page,
				$current_page
			);
			$results         = array();

			foreach ( $paginated_items as $id => $name ) {
				$results[] = array(
					'id'   => $id,
					'text' => $name,
				);
			}

			return $results;
		}

		protected static function get_current_page(): int {
			$current_page = Query_Arguments::get_int_for_non_action( 'paged', 'post' );

			return 0 === $current_page ?
				1 :
				$current_page;
		}

		/**
		 * @param array<string,string> $items
		 *
		 * @return array<string,string>
		 */
		protected static function filter_items( array $items, string $search_term ): array {
			$is_search = strlen( $search_term ) > 0;

			if ( $is_search ) {
				$is_match = fn( string $name ) => is_int( stripos( $name, $search_term ) );

				return array_filter(
					$items,
					$is_match
				);
			}

			return $items;
		}
	}
}
