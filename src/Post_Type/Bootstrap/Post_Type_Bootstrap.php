<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;
use Org\Wplake\Advanced_Views\Plugin\Core\Module\Bootstrap_Base;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage\File_System_Loader;
use Org\Wplake\Advanced_Views\Post_Type\Core\Git_Api\Git_Api_Interface;
use Org\Wplake\Advanced_Views\Post_Type\Core\Git_Api\Git_Lab_Api;
use Org\Wplake\Advanced_Views\Post_Type\Core\Mount_Point\Point_Mounter;

class Post_Type_Bootstrap extends Bootstrap_Base {
	public static function get_type_definitions(): array {
		return array(
			Git_Api_Interface::class => Git_Lab_Api::class,
		);
	}

	public static function get_instance_factories( Instance_Container $container ): array {
		$factory = $container->resolve( Post_Type_Factory::class );

		return array(
			Git_Lab_Api::class   => fn() => $factory->git_lab_api(),
			Point_Mounter::class => fn() => $factory->point_mounter(),
		);
	}

	public static function get_actor_factories( Instance_Container $container ): array {
		return array(
			File_System_Loader::class => fn() => File_System_Loader::instance(),
			Point_Mounter::class      => fn() => $container->resolve( Point_Mounter::class ),
		);
	}
}
