<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Dashboard\Markup;

use Org\Wplake\Advanced_Views\Plugin\Utils\Html\Tags_Renderer;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\bool;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

defined( 'ABSPATH' ) || exit;

/**
 * @phpstan-import-type Tag from Tags_Renderer
 * @phpstan-import-type Tag_Group from Tags_Renderer
 * @phpstan-import-type Tag_Chain from Tags_Renderer
 */
class Toolbar_Printer {
	private Html_Printer $html;

	public function __construct( Html_Printer $html ) {
		$this->html = $html;
	}

	/**
	 * @param array<int,array<string,mixed>> $tabs
	 */
	public function print_dashboard_header( string $name, string $version, array $tabs ): void {
		Tags_Renderer::print_tags( $this->toolbar_tags( $name, $version, $tabs ) );
	}

	/**
	 * @param array<int,array<string,mixed>> $tabs
	 *
	 * @return Tag_Chain
	 */
	protected function toolbar_tags( string $name, string $version, array $tabs ): array {
		$title = array(
			$this->html->tag( 'h2', array( 'class' => 'av-toolbar__title' ) ),
			array(
				array( $this->html->tag( 'i', array( 'class' => 'av-toolbar__icon dashicons dashicons-layout' ) ) ),
				array( $this->html->tag( 'span' ), $this->html->escaped_text( $name ) ),
				array( $this->html->tag( 'span', array( 'class' => 'av-toolbar__title-version' ) ), $this->html->escaped_text( 'v.' . $version ) ),
			),
		);

		return array(
			$this->html->tag( 'div', array( 'class' => 'av-toolbar' ) ),
			array(
				$title,
				$this->toolbar_block_tags( 'left', 'isLeftBlock', $tabs ),
				$this->toolbar_block_tags( 'right', 'isRightBlock', $tabs ),
			),
		);
	}

	/**
	 * @param array<int,array<string,mixed>> $tabs
	 *
	 * @return Tag_Chain
	 */
	protected function toolbar_block_tags( string $type, string $block_key, array $tabs ): array {
		$block_tabs = array_filter(
			$tabs,
			fn( array $tab ) => isset( $tab[ $block_key ] )
		);

		return array(
			$this->html->tag( 'div', array( 'class' => 'av-toolbar__block av-toolbar__block--type--' . $type ) ),
			array_map(
				fn( array $tab ) => $this->toolbar_tab_tags( $tab ),
				array_values( $block_tabs )
			),
		);
	}

	/**
	 * @param array<string,mixed> $tab
	 *
	 * @return Tag_Chain
	 */
	protected function toolbar_tab_tags( array $tab ): array {
		$classes = array_filter(
			array(
				'av-toolbar__tab',
				bool( $tab, 'isActive' ) ?
					'av-toolbar__tab--active' :
					'',
				bool( $tab, 'isSecondary' ) ?
					'av-toolbar__tab--secondary' :
					'',
				string( $tab, 'class' ),
			),
			fn( string $class ) => '' !== $class
		);
		$attrs   = array(
			'class'  => array_values( $classes ),
			'href'   => esc_url( string( $tab, 'url' ) ),
			'target' => bool( $tab, 'isBlank' ) ?
				'_blank' :
				'_self',
		);

		if ( key_exists( 'style', $tab ) ) {
			$attrs['style'] = string( $tab, 'style' );
		}

		$icon = key_exists( 'iconClasses', $tab ) ?
			array( $this->html->tag( 'i', array( 'class' => string( $tab, 'iconClasses' ) ) ) ) :
			array();

		return array(
			$this->html->tag( 'a', $attrs ),
			array(
				array( $this->html->tag( 'span' ), $this->html->escaped_text( string( $tab, 'label' ) ) ),
				$icon,
			),
		);
	}
}
