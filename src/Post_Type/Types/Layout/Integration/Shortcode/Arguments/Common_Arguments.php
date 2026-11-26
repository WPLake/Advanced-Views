<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Arguments;

defined( 'ABSPATH' ) || exit;

/**
 * The shortcode arguments common for any Layout, regardless of its items.
 */
final class Common_Arguments {
	public int $queried_object_id;
	public string $classes;
	/**
	 * @var mixed[]
	 */
	public array $custom_arguments;
	/**
	 * @var mixed[]|null
	 */
	public ?array $local_data;

	public function __construct() {
		$this->queried_object_id = 0;
		$this->classes           = '';
		$this->custom_arguments  = array();
		$this->local_data        = null;
	}

	public function set_queried_object_id( int $queried_object_id ): self {
		$this->queried_object_id = $queried_object_id;

		return $this;
	}

	public function set_classes( string $classes ): self {
		$this->classes = $classes;

		return $this;
	}

	/**
	 * @param mixed[] $custom_arguments
	 */
	public function set_custom_arguments( array $custom_arguments ): self {
		$this->custom_arguments = $custom_arguments;

		return $this;
	}

	/**
	 * @param mixed[]|null $local_data
	 */
	public function set_local_data( ?array $local_data ): self {
		$this->local_data = $local_data;

		return $this;
	}
}
