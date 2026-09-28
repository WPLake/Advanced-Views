<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Integration\Core;

use Org\Wplake\Advanced_Views\Plugin\Base\Avf_User;

defined( 'ABSPATH' ) || exit;

abstract class Cpt_Integration_Category {
	const NAME = 'advanced-views';

	public static function get_label(): string {
		return __( 'Advanced Views', 'acf-views' );
	}

	public static function can_user_manage(): bool {
		return Avf_User::can_manage();
	}
}
