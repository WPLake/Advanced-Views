<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Dashboard\Html;

use Org\Wplake\Advanced_Views\Template\Html\Html_Renderer;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\tag;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\bool;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

defined( 'ABSPATH' ) || exit;

final class Toolbar_Printer {
	protected Html_Renderer $html_renderer;
	public function __construct( Html_Renderer $html_renderer ) {
		$this->html_renderer = $html_renderer;
	}

	/**
	 * @param array<int,array<string,mixed>> $tabs
	 */
	public function print_dashboard_header( string $name, string $version, array $tabs ): void {
		$toolbar_tags = self::toolbar_tags( $name, $version, $tabs );

		$this->html_renderer->print_html( $toolbar_tags );
	}

	/**
	 * @param array<int,array<string,mixed>> $tabs
	 */
	protected static function toolbar_tags( string $name, string $version, array $tabs ): Html_Tag {
		$version_label = sprintf( 'v.%s', $version );

		$icon_tag = tag( 'i' )
			->attributes( array( 'class' => 'av-toolbar__icon dashicons dashicons-layout' ) );

		$name_tag     = tag( 'span' )
			->content(
				$name
			);
		$version_nest = tag( 'span' )
			->attributes( array( 'class' => 'av-toolbar__title-version' ) )
			->content(
				$version_label
			);

		$title = tag( 'h2' )
			->attributes( array( 'class' => 'av-toolbar__title' ) )
			->content(
				$icon_tag,
				$name_tag,
				$version_nest
			);

		$left_block  = self::toolbar_block_tags( 'left', 'isLeftBlock', $tabs );
		$right_block = self::toolbar_block_tags( 'right', 'isRightBlock', $tabs );

		return tag( 'div' )
			->attributes( array( 'class' => 'av-toolbar' ) )
			->content(
				$title,
				$left_block,
				$right_block
			);
	}

	/**
	 * @param array<int,array<string,mixed>> $tabs
	 */
	protected static function toolbar_block_tags( string $type, string $block_key, array $tabs ): Html_Tag {
		$block_tabs = array_filter(
			$tabs,
			fn( array $tab ) => isset( $tab[ $block_key ] )
		);

		$block_class = sprintf( 'av-toolbar__block av-toolbar__block--type--%s', $type );
		$tabs_list   = array_values( $block_tabs );
		$tab_tags    = array_map(
			fn( array $tab ) => self::toolbar_tab_tags( $tab ),
			$tabs_list
		);

		return tag( 'div' )
			->attributes( array( 'class' => $block_class ) )
			->content( ...$tab_tags );
	}

	/**
	 * @param array<string,mixed> $tab
	 */
	protected static function toolbar_tab_tags( array $tab ): Html_Tag {
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
			'href'   => $url,
			'target' => bool( $tab, 'isBlank' ) ?
				'_blank' :
				'_self',
		);

		if ( key_exists( 'style', $tab ) ) {
			$attrs['style'] = string( $tab, 'style' );
		}

		$icon_classes = string( $tab, 'iconClasses' );
		$icon_tag     = tag( 'i' )
			->attributes( array( 'class' => $icon_classes ) );
		$icon         = key_exists( 'iconClasses', $tab ) ?
			$icon_tag :
			null;
		$label        = string( $tab, 'label' );
		$link         = tag( 'a' )
			->attributes( $attrs );

		$label_tag = tag( 'span' )
			->content(
				$label
			);

		return $link
			->content(
				$label_tag,
				$icon
			);
	}
}
