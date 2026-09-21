<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Compatibility\Version_Migrations\V_2;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Compatibility\Migration\Core\Version\Version_Migration_Base;
use Org\Wplake\Advanced_Views\Plugin\Base\Logger;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Templates_Environment;

final class Migration_2_3_0 extends Version_Migration_Base {
	private Templates_Environment $templates_environment;

	public function __construct( Logger $logger, Templates_Environment $templates_environment ) {
		parent::__construct( $logger );

		$this->templates_environment = $templates_environment;
	}

	public function introduced_version(): string {
		return '2.3.0';
	}

	public function migrate_previous_version(): void {
		self::add_action(
			'init',
			function (): void {
				$this->templates_environment->create_templates_dir();
			}
		);
	}
}
