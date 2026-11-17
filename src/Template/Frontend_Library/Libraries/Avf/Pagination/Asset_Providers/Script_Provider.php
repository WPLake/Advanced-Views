<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Pagination\Asset_Providers;

use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Post_Selection_Cpt;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Provider\Script_Provider_Base;

defined( 'ABSPATH' ) || exit;

final class Script_Provider extends Script_Provider_Base {
	protected const HANDLE = 'acf-views-pagination';

	/**
	 * @var array<int,array<string,mixed>>
	 */
	private array $pagination_instances;

	public function __construct() {
		$this->pagination_instances = array();
	}

	public function enable(): void {
		$this->enable_handles( array( self::HANDLE ) );
	}

	/**
	 * @param array<int,array<string,mixed>> $pagination_instances
	 */
	public function set_pagination_instances( array $pagination_instances ): void {
		$this->pagination_instances = $pagination_instances;
	}

	public function get_script_variables(): array {
		$ajax_url = get_admin_url( null, 'admin-ajax.php' );
		$cpt_name = Hard_Post_Selection_Cpt::cpt_name();

		return array(
			self::HANDLE => array(
				'acfViewsPagination' => array(
					'ajaxData'  => array(
						'url'  => $ajax_url,
						'name' => $cpt_name,
					),
					'instances' => $this->pagination_instances,
				),
			),
		);
	}
}
