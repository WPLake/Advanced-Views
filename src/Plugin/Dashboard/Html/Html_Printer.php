<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Dashboard\Html;

use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

defined( 'ABSPATH' ) || exit;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
final class Html_Printer {
	public static function link_tags( string $url, string $label ): Html_Tag {
		$link_tag = tag( 'a' )
			->attributes( array(
				'target' => '_blank',
				'href'   => $url,
			) );

		return $link_tag
			->content(
				$label
			);
	}

	/**
	 * @param array<Html_Node|null> $blocks
	 */
	public static function dashboard_form_tags( string $page, string $form_nonce, array $blocks ): Html_Tag {
		$form_tag = tag( 'form' )
			->attributes( array(
				'action' => '',
				'method' => 'post',
				'class'  => 'av-dashboard',
			) );
		$main_tag = tag( 'div' )
			->attributes( array( 'class' => 'av-dashboard__main' ) );

		$page_input  = self::hidden_input_tags( '_av-page', $page );
		$nonce_input = self::hidden_input_tags( '_wpnonce', $form_nonce );

		$main = $main_tag
			->content( ...$blocks );

		return $form_tag
			->content(
				$page_input,
				$nonce_input,
				$main
			);
	}

	public static function hidden_input_tags( string $name, string $value ): Html_Tag {
		return tag( 'input' )
			->attributes( array(
				'type'  => 'hidden',
				'name'  => $name,
				'value' => $value,
			) );
	}

	public static function title_tags( string $title ): Html_Tag {
		$title_tag = tag( 'p' )
			->attributes( array( 'class' => 'av-introduction__title' ) );

		return $title_tag
			->content(
				$title
			);
	}
}
