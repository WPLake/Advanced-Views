<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Html;

defined( 'ABSPATH' ) || exit;

/**
 * Text output as is, without escaping: raw CSS/JS, or markup that is already safe (e.g. pre-rendered).
 *
 * @phpstan-import-type Html_Node from Html_Element
 */
final class Raw_Html implements Html_Element {
	private string $html;

	public function __construct( string $html ) {
		$this->html = $html;
	}

	public function get_html(): string {
		return $this->html;
	}

	/**
	 * @return Html_Node[]
	 */
	public function get_nodes(): array {
		return array();
	}
}
