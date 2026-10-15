<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Markup_Element\Map;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Markup_Element\Markup_Element;

abstract class Map_Marker_Element implements Markup_Element {
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
