<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Table;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Utils\Html\Tags_Renderer;

/**
 * @phpstan-import-type Chain from Tags_Renderer
 */
final class Row_Title_Printer {
	/**
	 * @param string $action_class e.g. 'import' or 'sync'
	 */
	public static function print_row_title(
		string $title,
		string $action_class,
		string $action_url,
		string $action_label
	): void {
		$title_tags = array(
			array( 'tag' => 'strong' ),
			array(
				'tag'   => 'span',
				'attrs' => array( 'class' => 'row-title' ),
			),
			fn() => print( esc_html( $title ) ),
		);

		self::print_title_and_action( $title_tags, $action_class, $action_url, $action_label );
	}

	/**
	 * @param Chain $title_tags
	 * @param string $action_class e.g. 'edit'
	 */
	public static function print_title_and_action(
		array $title_tags,
		string $action_class,
		string $action_url,
		string $action_label
	): void {
		$action_tags = array(
			array(
				'tag'   => 'div',
				'attrs' => array( 'class' => 'row-actions' ),
			),
			array(
				'tag'   => 'span',
				'attrs' => array( 'class' => $action_class ),
			),
			array(
				'tag'   => 'a',
				'attrs' => array( 'href' => esc_url( $action_url ) ),
			),
			fn() => print( esc_html( $action_label ) ),
		);

		Tags_Renderer::print_tags( array( array( $title_tags, $action_tags ) ) );
	}
}
