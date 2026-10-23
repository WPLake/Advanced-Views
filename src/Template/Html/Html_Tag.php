<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html;

defined( 'ABSPATH' ) || exit;

use Closure;
use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Htmlable;

/**
 * @phpstan-type Html_Node Html_Tag|string|Htmlable|Closure
 */
final class Html_Tag {
	private string $tag;
	/**
	 * @var array<string,mixed>
	 */
	private array $attributes = array();
	/**
	 * @var array<int,Html_Node>
	 */
	private array $content = array();

	public function __construct( string $tag ) {
		$this->tag = $tag;
	}

	/**
	 * @param array<string,mixed> $attributes
	 */
	public function attributes( array $attributes ): self {
		$this->attributes = $attributes;

		return $this;
	}

	/**
	 * @param Html_Node[] $items
	 */
	public function content( array $items ): self {
		$this->content = array_merge( $this->content, $items );

		return $this;
	}

	public function get_tag(): string {
		return $this->tag;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function get_attributes(): array {
		return $this->attributes;
	}

	/**
	 * @return array<int,Html_Node>
	 */
	public function get_content(): array {
		return $this->content;
	}
}
