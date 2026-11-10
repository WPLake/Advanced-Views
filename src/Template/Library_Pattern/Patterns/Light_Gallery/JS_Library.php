<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Library_Pattern\Patterns\Light_Gallery;

use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\JS_Library\JS_Library_Base;

defined( 'ABSPATH' ) || exit;

final class JS_Library extends JS_Library_Base {
	protected const HANDLE = 'lightgallery';
	protected const THUMBNAIL_HANDLE = 'lg-thumbnail';

	public function __construct() {
		parent::__construct(
			array(
				self::HANDLE,
				self::THUMBNAIL_HANDLE,
			)
		);
	}

	public function enable(): void {
		$this->enable_handle( self::HANDLE );

		// this addon is always in use.
		$this->enable_handle( self::THUMBNAIL_HANDLE );
	}
}
