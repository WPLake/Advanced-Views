<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Dashboard\Html;

use Org\Wplake\Advanced_Views\Template\Html\Tags_Renderer;

defined( 'ABSPATH' ) || exit;

/**
 * @phpstan-import-type Tag from Tags_Renderer
 * @phpstan-import-type Tag_Group from Tags_Renderer
 * @phpstan-import-type Tag_Chain from Tags_Renderer
 */
final class Html_Printer {
	/**
	 * @return Tag_Chain
	 */
	public static function link_tags( string $url, string $label ): array {
		$link_attrs = array(
			'target' => '_blank',
			'href'   => $url,
		);

		$link_tag = array(
			'tag'   => 'a',
			'attrs' => $link_attrs,
		);

		return array(
			$link_tag,
			$label,
		);
	}

	/**
	 * @param Tag_Group $blocks
	 *
	 * @return Tag_Chain
	 */
	public static function dashboard_form_tags( string $page, string $form_nonce, array $blocks ): array {
		$form_attrs = array(
			'action' => '',
			'method' => 'post',
			'class'  => 'av-dashboard',
		);
		$main_attrs = array( 'class' => 'av-dashboard__main' );

		$form_tag = array(
			'tag'   => 'form',
			'attrs' => $form_attrs,
		);
		$main_tag = array(
			'tag'   => 'div',
			'attrs' => $main_attrs,
		);

		$page_input  = self::hidden_input_tags( '_av-page', $page );
		$nonce_input = self::hidden_input_tags( '_wpnonce', $form_nonce );

		return array(
			$form_tag,
			array(
				$page_input,
				$nonce_input,
				array( $main_tag, $blocks ),
			),
		);
	}

	/**
	 * @return Tag_Chain
	 */
	public static function hidden_input_tags( string $name, string $value ): array {
		return array(
			array(
				'tag'   => 'input',
				'attrs' => array(
					'type'  => 'hidden',
					'name'  => $name,
					'value' => $value,
				),
			),
		);
	}

	/**
	 * @return Tag_Chain
	 */
	public static function title_tags( string $title ): array {
		$title_attrs = array( 'class' => 'av-introduction__title' );

		$title_tag = array(
			'tag'   => 'p',
			'attrs' => $title_attrs,
		);

		return array(
			$title_tag,
			$title,
		);
	}
}
