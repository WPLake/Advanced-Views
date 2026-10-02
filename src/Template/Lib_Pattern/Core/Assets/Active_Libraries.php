<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Assets;

defined( 'ABSPATH' ) || exit;

class Active_Libraries {
	/**
	 * @var array<string,Assets_Handles>
	 */
	protected array $handles;

	public function __construct() {
		$this->handles = array();
	}

	public function activate( string $library_name, Assets_Handles $handles ): void {
		$this->handles[ $library_name ] = key_exists( $library_name, $this->handles ) ?
			$this->handles[ $library_name ]->merge( $handles ) :
			$handles;
	}

	public function is_active( string $library_name ): bool {
		return key_exists( $library_name, $this->handles );
	}

	public function get_handles( string $library_name ): Assets_Handles {
		return $this->handles[ $library_name ] ?? new Assets_Handles();
	}
}
