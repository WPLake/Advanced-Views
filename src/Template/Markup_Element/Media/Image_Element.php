<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Markup_Element\Media;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Markup_Element\Markup_Element;

abstract class Image_Element implements Markup_Element {
	const FULL_SIZE = 'full_size';
	const CAPTION   = 'caption';
	const WIDTH     = 'width';
	const HEIGHT    = 'height';

	abstract public function get_full_size(): string;

	abstract public function get_caption(): string;

	abstract public function get_width(): string;

	abstract public function get_height(): string;

	public function get_properties(): array {
		return array(
			self::FULL_SIZE => fn() => $this->get_full_size(),
			self::CAPTION   => fn() => $this->get_caption(),
			self::WIDTH     => fn() => $this->get_width(),
			self::HEIGHT    => fn() => $this->get_height(),
		);
	}
}
