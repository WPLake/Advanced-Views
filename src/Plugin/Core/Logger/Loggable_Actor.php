<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Core\Logger;

use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor_Base;

defined( 'ABSPATH' ) || exit;

class Loggable_Actor extends Actor_Base {
	protected Logger $logger;

	public function __construct( Logger $logger ) {
		$this->logger = $logger;
	}

	public function get_logger(): Logger {
		return $this->logger;
	}
}
