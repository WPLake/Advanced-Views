<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Source;

/**
 * The parsed shortcode args: either everything the Layout rendering needs, or an error message.
 */
final class Shortcode_Args {
	private Source $source;
	/**
	 * @var int[]
	 */
	private array $item_ids;
	private int $current_page_id;
	private string $classes;
	/**
	 * @var mixed[]
	 */
	private array $custom_arguments;
	/**
	 * @var mixed[]|null
	 */
	private ?array $local_data;
	private string $error;

	/**
	 * @param int[] $item_ids
	 * @param mixed[] $custom_arguments
	 * @param mixed[]|null $local_data
	 */
	public function __construct(
		Source $source,
		array $item_ids,
		int $current_page_id,
		string $classes,
		array $custom_arguments,
		?array $local_data,
		string $error = ''
	) {
		$this->source           = $source;
		$this->item_ids         = $item_ids;
		$this->current_page_id  = $current_page_id;
		$this->classes          = $classes;
		$this->custom_arguments = $custom_arguments;
		$this->local_data       = $local_data;
		$this->error            = $error;
	}

	public static function make_error( string $error ): self {
		return new self( new Source(), array(), 0, '', array(), null, $error );
	}

	public function has_error(): bool {
		return strlen( $this->error ) > 0;
	}

	public function get_error(): string {
		return $this->error;
	}

	public function get_source(): Source {
		return $this->source;
	}

	/**
	 * @return int[]
	 */
	public function get_item_ids(): array {
		return $this->item_ids;
	}

	public function get_current_page_id(): int {
		return $this->current_page_id;
	}

	public function get_classes(): string {
		return $this->classes;
	}

	/**
	 * @return mixed[]
	 */
	public function get_custom_arguments(): array {
		return $this->custom_arguments;
	}

	/**
	 * @return mixed[]|null
	 */
	public function get_local_data(): ?array {
		return $this->local_data;
	}
}
