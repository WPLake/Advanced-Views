<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Dashboard\Html;

use Org\Wplake\Advanced_Views\Template\Html\Tags_Renderer;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\bool;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

defined( 'ABSPATH' ) || exit;

/**
 * @phpstan-import-type Tag from Tags_Renderer
 * @phpstan-import-type Tag_Group from Tags_Renderer
 * @phpstan-import-type Tag_Chain from Tags_Renderer
 */
final class Toolbar_Printer {
	/**
	 * @param array<int,array<string,mixed>> $tabs
	 */
	public static function print_dashboard_header( string $name, string $version, array $tabs ): void {
		$toolbar_tags = self::toolbar_tags( $name, $version, $tabs );

		Tags_Renderer::print_tags( $toolbar_tags );
	}

	/**
	 * @param array<int,array<string,mixed>> $tabs
	 *
	 * @return Tag_Chain
	 */
	protected static function toolbar_tags( string $name, string $version, array $tabs ): array {
		$title_attrs   = array( 'class' => 'av-toolbar__title' );
		$icon_attrs    = array( 'class' => 'av-toolbar__icon dashicons dashicons-layout' );
		$version_attrs = array( 'class' => 'av-toolbar__title-version' );
		$toolbar_attrs = array( 'class' => 'av-toolbar' );
		$version_label = sprintf( 'v.%s', $version );

		$title_tag   = array(
			'tag'   => 'h2',
			'attrs' => $title_attrs,
		);
		$icon_tag    = array(
			'tag'   => 'i',
			'attrs' => $icon_attrs,
		);
		$version_tag = array(
			'tag'   => 'span',
			'attrs' => $version_attrs,
		);

		$title = array(
			$title_tag,
			array(
				array( $icon_tag ),
				array( array( 'tag' => 'span' ), $name ),
				array( $version_tag, $version_label ),
			),
		);

		$left_block  = self::toolbar_block_tags( 'left', 'isLeftBlock', $tabs );
		$right_block = self::toolbar_block_tags( 'right', 'isRightBlock', $tabs );

		return array(
			array(
				'tag'   => 'div',
				'attrs' => $toolbar_attrs,
			),
			array(
				$title,
				$left_block,
				$right_block,
			),
		);
	}

	/**
	 * @param array<int,array<string,mixed>> $tabs
	 *
	 * @return Tag_Chain
	 */
	protected static function toolbar_block_tags( string $type, string $block_key, array $tabs ): array {
		$block_tabs = array_filter(
			$tabs,
			fn( array $tab ) => isset( $tab[ $block_key ] )
		);

		$block_class = sprintf( 'av-toolbar__block av-toolbar__block--type--%s', $type );
		$block_attrs = array( 'class' => $block_class );
		$tabs_list   = array_values( $block_tabs );
		$tab_tags    = array_map(
			fn( array $tab ) => self::toolbar_tab_tags( $tab ),
			$tabs_list
		);

		return array(
			array(
				'tag'   => 'div',
				'attrs' => $block_attrs,
			),
			$tab_tags,
		);
	}

	/**
	 * @param array<string,mixed> $tab
	 *
	 * @return Tag_Chain
	 */
	protected static function toolbar_tab_tags( array $tab ): array {
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
			fn( string $tab_class ) => strlen( $tab_class ) > 0
		);
		$url     = string( $tab, 'url' );
		$attrs   = array(
			'class'  => array_values( $classes ),
			'href'   => esc_url( $url ),
			'target' => bool( $tab, 'isBlank' ) ?
				'_blank' :
				'_self',
		);

		if ( key_exists( 'style', $tab ) ) {
			$attrs['style'] = string( $tab, 'style' );
		}

		$icon_classes = string( $tab, 'iconClasses' );
		$icon_attrs   = array( 'class' => $icon_classes );
		$icon_tag     = array(
			'tag'   => 'i',
			'attrs' => $icon_attrs,
		);
		$icon         = key_exists( 'iconClasses', $tab ) ?
			array( $icon_tag ) :
			array();
		$label        = string( $tab, 'label' );
		$link         = array(
			'tag'   => 'a',
			'attrs' => $attrs,
		);

		return array(
			$link,
			array(
				array( array( 'tag' => 'span' ), $label ),
				$icon,
			),
		);
	}
}
