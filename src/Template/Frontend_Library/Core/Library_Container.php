<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Factory_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Container\Instance_Container;

defined( 'ABSPATH' ) || exit;

final class Library_Container extends Factory_Base {
	/**
	 * @var array<string,Frontend_Library>|null
	 */
	private ?array $libraries;
	/**
	 * @var class-string<Frontend_Library>[]
	 */
	private array $library_classes;

	/**
	 * @param class-string<Frontend_Library>[] $library_classes Later classes override the earlier ones with the same library name
	 */
	public function __construct( Instance_Container $container, array $library_classes ) {
		parent::__construct( $container );

		$this->libraries       = null;
		$this->library_classes = $library_classes;
	}

	/**
	 * @return array<string,Frontend_Library>
	 */
	public function get_all(): array {
		return $this->get_libraries();
	}

	/**
	 * @param string[] $names
	 *
	 * @return array<string,Frontend_Library>
	 */
	public function get_by_names( array $names ): array {
		$libraries  = $this->get_libraries();
		$names_keys = array_flip( $names );

		return array_intersect_key( $libraries, $names_keys );
	}

	/**
	 * @return array<string,Frontend_Library>
	 */
	protected function get_libraries(): array {
		if ( null === $this->libraries ) {
			$this->libraries = $this->register_libraries();
		}

		return $this->libraries;
	}

	/**
	 * @return array<string,Frontend_Library>
	 */
	protected function register_libraries(): array {
		$libraries = array();

		foreach ( $this->library_classes as $library_class ) {
			$library = $this->resolve( $library_class );

			$libraries[ $library->get_name() ] = $library;
		}

		return $libraries;
	}
}
