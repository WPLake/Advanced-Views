<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Pagination;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Pagination\Asset_Providers\Script_Provider;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Acf\Groups\Post_Selection_Settings;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Provider\Style_Provider;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Frontend_Library;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Adapters\Selection\Selection_Library;

defined( 'ABSPATH' ) || exit;

final class Pagination_Library implements Frontend_Library, Selection_Library {
	const NAME = 'acf-views-pagination';

	/**
	 * @var array<int,array<string,mixed>>
	 */
	private array $pagination_instances;
	private Script_Provider $script_provider;

	public function __construct() {
		$this->pagination_instances = array();
		$this->script_provider      = new Script_Provider();
	}

	public function get_name(): string {
		return self::NAME;
	}

	public function get_auto_discover_name(): string {
		return '';
	}

	public function get_js_library(): Script_Provider {
		return $this->script_provider;
	}

	public function get_css_library(): ?Style_Provider {
		return null;
	}

	public function maybe_activate_selection( Post_Selection_Settings $post_selection_settings ): void {
		if ( $post_selection_settings->is_with_pagination ) {
			$this->pagination_instances[] = self::create_instance( $post_selection_settings );

			$this->script_provider->enable();
			$this->script_provider->set_pagination_instances( $this->pagination_instances );
		}
	}

	/**
	 * It adds no markup of its own, so the markup hooks and the code generation are never involved.
	 */
	public function is_target_selection( Post_Selection_Settings $post_selection_settings ): bool {
		return false;
	}

	/**
	 * @return array<string,string>
	 */
	public function generate_selection_css( Post_Selection_Settings $post_selection_settings ): array {
		return array();
	}

	/**
	 * @return array<string,string>
	 */
	public function generate_selection_js( Post_Selection_Settings $post_selection_settings ): array {
		return array();
	}

	public function get_selection_template_pattern( Post_Selection_Settings $post_selection_settings ): ?string {
		return null;
	}

	/**
	 * @return array<string,mixed>
	 */
	protected static function create_instance( Post_Selection_Settings $post_selection_settings ): array {
		$bem_name  = $post_selection_settings->get_bem_name();
		$markup_id = $post_selection_settings->get_markup_id();

		$is_custom = strlen( $post_selection_settings->bem_name ) > 0;
		$selector  = $is_custom ?
			// it's a JS selector for tag, so no dots or other symbols.
			$post_selection_settings->bem_name :
			sprintf( '.%s--id--%s', $bem_name, $markup_id );

		return array(
			'selector'     => $selector,
			'elementClass' => $bem_name,
		);
	}
}
