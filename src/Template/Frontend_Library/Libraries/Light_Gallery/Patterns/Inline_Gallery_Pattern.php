<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery\Patterns;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Pattern\Script_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Pattern\Script_Variables;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Pattern\Style_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Template\Pattern\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery\Patterns\Gallery_Pattern;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;

/**
 * The container class is fixed, as the CSS relies on it.
 */
final class Inline_Gallery_Pattern implements Template_Pattern, Script_Pattern, Style_Pattern {
	const CONTAINER_CLASS = '_lg-inline';

	public static function get_wrapper(): ?Html_Tag {
		return null;
	}

	public static function get_outers( Variable_Token $field ): array {
		return Gallery_Pattern::get_outers( $field );
	}

	public static function get_item_wrappers( Variable_Token $item ): array {
		return Gallery_Pattern::get_item_wrappers( $item );
	}

	public static function get_item_attributes( Variable_Token $item ): array {
		return array();
	}

	public static function get_docs_url(): string {
		return 'https://www.lightgalleryjs.com/docs/settings/#lightgallery-core';
	}

	public static function get_js_options(): array {
		return array(
			'showMaximizeIcon'  => true,
			'download'          => true,
			'enableDrag'        => false,
			'allowMediaOverlap' => false,
			'closable'          => false,
			'#plugins'          => '[window.lgThumbnail]',
		);
	}

	public static function get_js_setup( Script_Variables $variables ): array {
		return array(
			'let container = document.createElement("div")',
			sprintf( 'container.classList.add("%s")', self::CONTAINER_CLASS ),
			'',
			sprintf(
				'%s = {...%1$s, container: container}',
				esc_html( $variables->options )
			),
			sprintf(
				'%s.after(container)',
				esc_html( $variables->element )
			),
			'',
			sprintf(
				'new lightGallery(%s, %s).openGallery()',
				esc_html( $variables->element ),
				esc_html( $variables->options )
			),
		);
	}

	public static function get_css_rules(): array {
		$container_selector = sprintf( '~ .%s', self::CONTAINER_CLASS );

		return array(
			'display'           => 'none',
			$container_selector => array(
				'height' => '500px',
			),
		);
	}
}
