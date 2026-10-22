<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Dashboard\Html;

use Org\Wplake\Advanced_Views\Template\Html\Html;

defined( 'ABSPATH' ) || exit;

/**
 * @phpstan-import-type Tag from Html
 * @phpstan-import-type Tag_Stack from Html
 * @phpstan-import-type Tag_Wrap from Html
 */
final class Html_Printer {
	/**
	 * @return Tag_Wrap
	 */
	public static function link_tags( string $url, string $label ): array {
		$link_attrs = array(
			'target' => '_blank',
			'href'   => $url,
		);

		$link_tag = Html::tag(
			'a',
			$link_attrs
		);

		return Html::wrap(
			$link_tag,
			$label
		);
	}

	/**
	 * @param Tag_Stack $blocks
	 *
	 * @return Tag_Wrap
	 */
	public static function dashboard_form_tags( string $page, string $form_nonce, array $blocks ): array {
		$form_attrs = array(
			'action' => '',
			'method' => 'post',
			'class'  => 'av-dashboard',
		);
		$main_attrs = array( 'class' => 'av-dashboard__main' );

		$form_tag = Html::tag(
			'form',
			$form_attrs
		);
		$main_tag = Html::tag(
			'div',
			$main_attrs
		);

		$page_input  = self::hidden_input_tags( '_av-page', $page );
		$nonce_input = self::hidden_input_tags( '_wpnonce', $form_nonce );

		return Html::wrap(
			$form_tag,
			Html::stack(
				$page_input,
				$nonce_input,
				Html::wrap( $main_tag, $blocks )
			)
		);
	}

	/**
	 * @return Tag
	 */
	public static function hidden_input_tags( string $name, string $value ): array {
		return Html::tag(
			'input',
			array(
				'type'  => 'hidden',
				'name'  => $name,
				'value' => $value,
			)
		);
	}

	/**
	 * @return Tag_Wrap
	 */
	public static function title_tags( string $title ): array {
		$title_attrs = array( 'class' => 'av-introduction__title' );

		$title_tag = Html::tag(
			'p',
			$title_attrs
		);

		return Html::wrap(
			$title_tag,
			$title
		);
	}
}
