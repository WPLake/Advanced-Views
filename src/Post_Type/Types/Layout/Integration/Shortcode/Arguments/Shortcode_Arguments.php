<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Arguments;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Source;

/**
 * The parsed shortcode arguments: either everything the Layout rendering needs, or an error message.
 */
final class Shortcode_Arguments {
	public Source $source;
	/**
	 * @var int[]
	 */
	public array $item_ids;
	public int $current_page_id;
	public string $classes;
	/**
	 * @var mixed[]
	 */
	public array $custom_arguments;
	/**
	 * @var mixed[]|null
	 */
	public ?array $local_data;
	public string $error;

	public function __construct() {
		$this->source           = new Source();
		$this->item_ids         = array();
		$this->current_page_id  = 0;
		$this->classes          = '';
		$this->custom_arguments = array();
		$this->local_data       = null;
		$this->error            = '';
	}

	public function has_error(): bool {
		return strlen( $this->error ) > 0;
	}

	public function set_source( Source $source ): self {
		$this->source = $source;

		return $this;
	}

	/**
	 * @param int[] $item_ids
	 */
	public function set_item_ids( array $item_ids ): self {
		$this->item_ids = $item_ids;

		return $this;
	}

	public function set_current_page_id( int $current_page_id ): self {
		$this->current_page_id = $current_page_id;

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

	public function set_error( string $error ): self {
		$this->error = $error;

		return $this;
	}
}
