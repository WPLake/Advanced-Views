<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery\Patterns;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Pattern\Script_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Pattern\Script_Variables;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Pattern\Style_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Template\Pattern\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

final class Gallery_Pattern implements Script_Pattern, Style_Pattern, Template_Pattern {
	public static function get_docs_url(): string {
		return 'https://www.lightgalleryjs.com/docs/settings/#lightgallery-core';
	}

	public static function get_js_options(): array {
		return array(
			'closeOnTap'        => true,
			'download'          => false,
			'allowMediaOverlap' => false,
			'enableDrag'        => false,
			'counter'           => true,
			'#plugins'          => '[window.lgThumbnail]',
		);
	}

	public static function get_js_setup( Script_Variables $variables ): array {
		return array(
			sprintf(
				'new lightGallery(%s, %s)',
				esc_html( $variables->outer_wrapper ),
				esc_html( $variables->options )
			),
		);
	}

	public static function get_css_rules(): array {
		return array(
			'list-style' => 'none',
			'img:hover'  => array(
				'cursor' => 'zoom-in',
			),
		);
	}

	public static function get_outers( Variable_Token $field ): array {
		return array(
			tag( 'ul' ),
		);
	}

	public static function get_item_wrappers( Variable_Token $item ): array {
		$data_attributes = Lightbox_Pattern::data_attributes( $item );

		return array(
			tag( 'li' )
				->attributes( $data_attributes ),
		);
	}

	public static function get_wrapper(): ?Html_Tag {
		return null;
	}

	/**
	 * @return array<string,string|Variable_Token>
	 */
	public static function get_item_attributes( Variable_Token $item ): array {
		return array();
	}
}
