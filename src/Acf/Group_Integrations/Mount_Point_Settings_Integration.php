<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Acf\Group_Integrations;

use Org\Wplake\Advanced_Views\Acf\Groups\Mount_Point_Settings;

defined( 'ABSPATH' ) || exit;

class Mount_Point_Settings_Integration extends Acf_Integration {
	/**
	 * @return array<string,callable(): array<string,string>>
	 */
	protected function get_choices_callbacks(): array {
		return array(
			Mount_Point_Settings::FIELD_POST_TYPES =>
				fn() => $this->get_post_type_choices(),
		);
	}

	protected function set_field_choices(): void {
		$choices_callbacks = $this->get_choices_callbacks();

		self::bind_field_choices( Mount_Point_Settings::class, $choices_callbacks );
	}
}
