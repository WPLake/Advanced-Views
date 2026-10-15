<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Markup_Element\Map;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Markup_Element\List_Element;

abstract class Map_Element extends List_Element {
	const ZOOM       = 'zoom';
	const CENTER_LAT = 'center_lat';
	const CENTER_LNG = 'center_lng';

	/**
	 * @return Map_Marker_Element[]
	 */
	abstract public function get_items(): array;

	abstract public function get_zoom(): string;

	abstract public function get_center_lat(): string;

	abstract public function get_center_lng(): string;

	public function get_properties(): array {
		$own_properties = array(
			self::ZOOM       => fn() => $this->get_zoom(),
			self::CENTER_LAT => fn() => $this->get_center_lat(),
			self::CENTER_LNG => fn() => $this->get_center_lng(),
		);

		return array_merge( parent::get_properties(), $own_properties );
	}
}
