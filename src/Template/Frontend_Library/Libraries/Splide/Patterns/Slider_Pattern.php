<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Splide\Patterns;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Pattern\Script_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Pattern\Script_Variables;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Pattern\Style_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Template\Pattern\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

defined( 'ABSPATH' ) || exit;

final class Slider_Pattern implements Template_Pattern, Script_Pattern, Style_Pattern {
	public static function get_wrapper(): Html_Tag {
		return tag( 'div' )
			->attributes( array( 'class' => 'splide' ) );
	}

	public static function get_outers( Variable_Token $field ): array {
		return array(
			tag( 'div' )
				->attributes( array( 'class' => 'splide__track' ) ),
			tag( 'ul' )
				->attributes( array( 'class' => 'splide__list' ) ),
		);
	}

	public static function get_item_wrappers( Variable_Token $item ): array {
		return array(
			tag( 'li' )
				->attributes( array( 'class' => 'splide__slide' ) ),
		);
	}

	public static function get_item_attributes( Variable_Token $item ): array {
		return array();
	}

	public static function get_docs_url(): string {
		return 'https://splidejs.com/guides/options/';
	}

	public static function get_js_options(): array {
		return array(
			'type'    => 'loop',
			'perPage' => 1,
			'perMove' => 1,
		);
	}

	public static function get_js_setup( Script_Variables $variables ): array {
		return array(
			sprintf(
				'new Splide(%s, %s).mount()',
				esc_html( $variables->element ),
				esc_html( $variables->options )
			),
		);
	}

	public static function get_css_rules(): array {
		return array(
			'ul' => array(
				'list-style' => 'none',
			),
		);
	}
}
