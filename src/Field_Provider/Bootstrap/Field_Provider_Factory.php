<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Factory_Base;
use Org\Wplake\Advanced_Views\Plugin\Settings\Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Repeater_Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Cpt\Layout_Save_Actions;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Integration\Layout_Shortcode;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layout_Factory;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;
use Org\Wplake\Advanced_Views\Vendors\LightSource\AcfGroups\Creator;

class Field_Provider_Factory extends Factory_Base {
	public function integrations(): Field_Provider_Integrations {
		return new Field_Provider_Integrations(
			$this->resolve( Field_Provider_Cluster::class ),
			$this->resolve( Item_Settings::class ),
			$this->resolve( Layout_Settings_Storage::class ),
			$this->resolve( Layout_Save_Actions::class ),
			$this->resolve( Layout_Factory::class ),
			$this->resolve( Creator::class )->create( Repeater_Field_Settings::class ),
			$this->resolve( Layout_Shortcode::class ),
			$this->resolve( Settings_Storage::class ),
			$this->resolve( Layouts_Cpt::class )
		);
	}
}
