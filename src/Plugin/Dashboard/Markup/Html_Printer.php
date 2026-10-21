<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Dashboard\Markup;

use Org\Wplake\Advanced_Views\Plugin\Utils\Html\Tags_Renderer;

defined( 'ABSPATH' ) || exit;

/**
 * @phpstan-import-type Tag from Tags_Renderer
 * @phpstan-import-type Tag_Group from Tags_Renderer
 * @phpstan-import-type Tag_Chain from Tags_Renderer
 */
class Html_Printer {
	/**
	 * @return Tag_Chain
	 */
	public function link_tags( string $url, string $label ): array {
		return array(
			$this->tag(
				'a',
				array(
					'target' => '_blank',
					'href'   => esc_url( $url ),
				)
			),
			$label,
		);
	}

	/**
	 * @param Tag_Group $blocks
	 *
	 * @return Tag_Chain
	 */
	public function dashboard_form_tags( string $page, string $form_nonce, array $blocks ): array {
		return array(
			array(
				'tag'   => 'form',
				'attrs' => array(
					'action' => '',
					'method' => 'post',
					'class'  => 'av-dashboard',
				),
			),
			array(
				$this->hidden_input_tags( '_av-page', $page ),
				$this->hidden_input_tags( '_wpnonce', $form_nonce ),
				array(
					$this->tag( 'div', array( 'class' => 'av-dashboard__main' ) ),
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
		return array(
			$this->tag( 'p', array( 'class' => 'av-introduction__title' ) ),
			$title,
		);
	}
}
