<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Light_Gallery\Patterns;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Pattern\Style_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Pattern\Script_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Pattern\Script_Variables;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Template\Pattern\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Template\Pattern\Template_Pattern_Defaults;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

final class Lightbox_Pattern implements Script_Pattern, Style_Pattern, Template_Pattern {
	use Template_Pattern_Defaults;

	public static function get_docs_url(): string {
		return 'https://www.lightgalleryjs.com/docs/settings/#lightgallery-core';
	}

	public static function get_js_options(): array {
		return array(
			'selector'          => 'this',
			'closeOnTap'        => true,
			'download'          => false,
			'allowMediaOverlap' => false,
			'enableDrag'        => false,
			'counter'           => false,
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
			'img:hover' => array(
				'cursor' => 'zoom-in',
			),
		);
	}

	public static function get_field_wrapper( Variable_Token $field ): Html_Tag {
		$data_attributes = self::data_attributes( $field );

		return tag( 'div' )
			->attributes( $data_attributes );
	}

	/**
	 * @return array<string,Variable_Token>
	 */
	public static function data_attributes( Variable_Token $target ): array {
		return array(
			'data-src'      => $target->clone()
				->add_item_path( 'full_size' ),
			'data-sub-html' => $target->clone()
				->add_item_path( 'caption' ),
		);
	}
}
