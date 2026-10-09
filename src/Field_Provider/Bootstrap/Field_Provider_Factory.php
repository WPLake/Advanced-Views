<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Acf\Acf_Data_Vendor;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Field_Provider\Data_Vendors;
use Org\Wplake\Advanced_Views\Field_Provider\Meta_Box\Meta_Box_Data_Vendor;
use Org\Wplake\Advanced_Views\Field_Provider\Pods\Pods_Data_Vendor;
use Org\Wplake\Advanced_Views\Field_Provider\Woo\Woo_Data_Vendor;
use Org\Wplake\Advanced_Views\Field_Provider\Wp\Wp_Data_Vendor;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;
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
	public function provider_cluster(): Field_Provider_Cluster {
		$cluster_class = $this->cluster_class();

		return new $cluster_class(
			$this->resolve( Logger::class ),
			$this->resolve( Instance_Container::class ),
			$this->provider_classes()
		);
	}

	/**
	 * @return class-string<Field_Provider>[]
	 */
	protected function provider_classes(): array {
		return array(
			Wp_Data_Vendor::class,
			Woo_Data_Vendor::class,
			Acf_Data_Vendor::class,
			Meta_Box_Data_Vendor::class,
			Pods_Data_Vendor::class,
		);
	}

	/**
	 * @return class-string<Field_Provider_Cluster>
	 */
	protected function cluster_class(): string {
		return Data_Vendors::class;
	}

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
