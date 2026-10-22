<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Dashboard\Html;

use Org\Wplake\Advanced_Views\Template\Html\Html;
use Org\Wplake\Advanced_Views\Template\Html\Html_Renderer;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\bool;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

defined( 'ABSPATH' ) || exit;

/**
 * @phpstan-import-type Tag from Html
 * @phpstan-import-type Tag_Stack from Html
 * @phpstan-import-type Tag_Wrap from Html
 */
final class Toolbar_Printer {
	/**
	 * @param array<int,array<string,mixed>> $tabs
	 */
	public static function print_dashboard_header( string $name, string $version, array $tabs ): void {
		$toolbar_tags = self::toolbar_tags( $name, $version, $tabs );

		Html_Renderer::print( $toolbar_tags );
	}

	/**
	 * @param array<int,array<string,mixed>> $tabs
	 *
	 * @return Tag_Wrap
	 */
	protected static function toolbar_tags( string $name, string $version, array $tabs ): array {
		$title_attrs   = array( 'class' => 'av-toolbar__title' );
		$icon_attrs    = array( 'class' => 'av-toolbar__icon dashicons dashicons-layout' );
		$version_attrs = array( 'class' => 'av-toolbar__title-version' );
		$toolbar_attrs = array( 'class' => 'av-toolbar' );
		$version_label = sprintf( 'v.%s', $version );

		$title_tag   = Html::tag(
			'h2',
			$title_attrs
		);
		$icon_tag    = Html::tag(
			'i',
			$icon_attrs
		);
		$version_tag = Html::tag(
			'span',
			$version_attrs
		);

		$title = Html::wrap(
			$title_tag,
			Html::stack(
				$icon_tag,
				Html::wrap(
					Html::tag( 'span' ),
					$name
				),
				Html::wrap( $version_tag, $version_label )
			)
		);

		$left_block  = self::toolbar_block_tags( 'left', 'isLeftBlock', $tabs );
		$right_block = self::toolbar_block_tags( 'right', 'isRightBlock', $tabs );

		return Html::wrap(
			Html::tag(
				'div',
				$toolbar_attrs
			),
			Html::stack(
				$title,
				$left_block,
				$right_block
			)
		);
	}

	/**
	 * @param array<int,array<string,mixed>> $tabs
	 *
	 * @return Tag_Wrap
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

		return Html::wrap(
			Html::tag(
				'div',
				$block_attrs
			),
			Html::stack( ...$tab_tags )
		);
	}

	/**
	 * @param array<string,mixed> $tab
	 *
	 * @return Tag_Wrap
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
			'href'   => $url,
			'target' => bool( $tab, 'isBlank' ) ?
				'_blank' :
				'_self',
		);

		if ( key_exists( 'style', $tab ) ) {
			$attrs['style'] = string( $tab, 'style' );
		}

		$icon_classes = string( $tab, 'iconClasses' );
		$icon_attrs   = array( 'class' => $icon_classes );
		$icon_tag     = Html::tag(
			'i',
			$icon_attrs
		);
		$icon         = key_exists( 'iconClasses', $tab ) ?
			$icon_tag :
			Html::stack();
		$label        = string( $tab, 'label' );
		$link         = Html::tag(
			'a',
			$attrs
		);

		return Html::wrap(
			$link,
			Html::stack(
				Html::wrap(
					Html::tag( 'span' ),
					$label
				),
				$icon
			)
		);
	}
}
