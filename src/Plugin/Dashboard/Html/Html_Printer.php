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
	public function link_tags( string $url, string $label ): array {
		$link_attrs = array(
			'target' => '_blank',
			'href'   => esc_url( $url ),
		);

		return array(
			$this->tag( 'a', $link_attrs ),
			$label,
		);
	}

	/**
	 * @param Tag_Group $blocks
	 *
	 * @return Tag_Chain
	 */
	public function dashboard_form_tags( string $page, string $form_nonce, array $blocks ): array {
		$form_attrs = array(
			'action' => '',
			'method' => 'post',
			'class'  => 'av-dashboard',
		);
		$main_attrs = array( 'class' => 'av-dashboard__main' );

		return array(
			$this->tag( 'form', $form_attrs ),
			array(
				$this->hidden_input_tags( '_av-page', $page ),
				$this->hidden_input_tags( '_wpnonce', $form_nonce ),
				array(
					$this->tag( 'div', $main_attrs ),
					$blocks,
				),
			),
		);
	}

	/**
	 * @return Tag_Chain
	 */
	public function hidden_input_tags( string $name, string $value ): array {
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
	 * @param array<string,mixed> $attrs
	 *
	 * @return Tag
	 */
	public function tag( string $tag, array $attrs = array() ): array {
		return array(
			'tag'   => $tag,
			'attrs' => $attrs,
		);
	}

	/**
	 * @return Tag_Chain
	 */
	public function title_tags( string $title ): array {
		$title_attrs = array( 'class' => 'av-introduction__title' );

		return array(
			$this->tag( 'p', $title_attrs ),
			$title,
		);
	}
}
