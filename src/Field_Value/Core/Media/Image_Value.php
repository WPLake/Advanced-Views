<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Value\Core\Media;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Value\Core\Field_Value;

abstract class Image_Value implements Field_Value {
	const VALUE       = 'value';
	const FULL_SIZE   = 'full_size';
	const CAPTION     = 'caption';
	const DESCRIPTION = 'description';
	const ALT         = 'alt';
	const WIDTH       = 'width';
	const HEIGHT      = 'height';
	const SRCSET      = 'srcset';
	const SIZES       = 'sizes';
	const SVG_CONTENT = 'svg_content';

	abstract public function get_value(): string;

	abstract public function get_full_size(): string;

	abstract public function get_caption(): string;

	abstract public function get_description(): string;

	abstract public function get_alt(): string;

	abstract public function get_width(): string;

	abstract public function get_height(): string;

	abstract public function get_srcset(): string;

	abstract public function get_sizes(): string;

	abstract public function get_svg_content(): string;

	public function get_properties(): array {
		return array(
			self::VALUE       => fn() => $this->get_value(),
			self::FULL_SIZE   => fn() => $this->get_full_size(),
			self::CAPTION     => fn() => $this->get_caption(),
			self::DESCRIPTION => fn() => $this->get_description(),
			self::ALT         => fn() => $this->get_alt(),
			self::WIDTH       => fn() => $this->get_width(),
			self::HEIGHT      => fn() => $this->get_height(),
			self::SRCSET      => fn() => $this->get_srcset(),
			self::SIZES       => fn() => $this->get_sizes(),
			self::SVG_CONTENT => fn() => $this->get_svg_content(),
		);
	}
}
