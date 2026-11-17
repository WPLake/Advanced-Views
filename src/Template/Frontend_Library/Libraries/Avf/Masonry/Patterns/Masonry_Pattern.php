<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Avf\Masonry\Patterns;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Field_Types\Image_Field_Type;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Pattern\Script_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Pattern\Script_Variables;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Pattern\Style_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Template\Pattern\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

final class Masonry_Pattern implements Template_Pattern, Script_Pattern, Style_Pattern {
	public static function get_wrapper(): ?Html_Tag {
		return null;
	}

	public static function get_outers( Variable_Token $field ): array {
		return array(
			tag( 'ul' ),
		);
	}

	public static function get_item_wrappers( Variable_Token $item ): array {
		$attributes = array(
			'data-width'  => $item->clone()
				->add_item_path( Image_Field_Type::WIDTH ),
			'data-height' => $item->clone()
				->add_item_path( Image_Field_Type::HEIGHT ),
		);

		return array(
			tag( 'li' )
				->attributes( $attributes ),
		);
	}

	public static function get_item_attributes( Variable_Token $item ): array {
		return array();
	}

	public static function get_docs_url(): string {
		return 'https://gitlab.com/lightsource/masonry';
	}

	public static function get_js_options(): array {
		return array(
			'ROW_MIN_HEIGHT' => 180,
			'GUTTER'         => 20,
			'MOBILE_GUTTER'  => 10,
			'MOBILE_WIDTH'   => 992,
		);
	}

	public static function get_js_setup( Script_Variables $variables ): array {
		return array(
			sprintf(
				'new AcfViewsMasonry(%s, %s)',
				esc_html( $variables->element ),
				esc_html( $variables->options )
			),
		);
	}

	public static function get_css_rules(): array {
		return array(
			'list-style'           => 'none',
			'transition'           => 'opacity ease .1s',
			'::after'              => array(
				'content' => "''",
				'display' => 'block',
				'clear'   => 'both',
			),
			':not([data-masonry])' => array(
				'opacity' => '0',
			),
			'img'                  => array(
				'display'    => 'block',
				'width'      => '100%',
				'height'     => '100%',
				'object-fit' => 'cover',
			),
		);
	}
}
