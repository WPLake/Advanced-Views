<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Value\Core\Map;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Value\Core\Field_Value;

abstract class Map_Marker_Value implements Field_Value {
	const LAT   = 'lat';
	const LNG   = 'lng';
	const ICON  = 'icon';
	const TITLE = 'title';

	abstract public function get_lat(): string;

	abstract public function get_lng(): string;

	abstract public function get_icon(): string;

	abstract public function get_title(): string;

	public function get_properties(): array {
		return array(
			self::LAT   => fn() => $this->get_lat(),
			self::LNG   => fn() => $this->get_lng(),
			self::ICON  => fn() => $this->get_icon(),
			self::TITLE => fn() => $this->get_title(),
		);
	}
}
