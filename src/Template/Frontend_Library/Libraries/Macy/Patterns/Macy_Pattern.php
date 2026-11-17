<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Libraries\Macy\Patterns;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Pattern\Script_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Pattern\Script_Variables;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Style\Pattern\Style_Pattern;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Template\Pattern\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable\Variable_Token;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

final class Macy_Pattern implements Template_Pattern, Script_Pattern, Style_Pattern {
	public static function get_wrapper(): ?Html_Tag {
		return null;
	}

	public static function get_outers( Variable_Token $field ): array {
		return array(
			tag( 'ul' ),
		);
	}

	public static function get_item_wrappers( Variable_Token $item ): array {
		return array(
			tag( 'li' ),
		);
	}

	public static function get_item_attributes( Variable_Token $item ): array {
		return array();
	}

	public static function get_docs_url(): string {
		return 'https://github.com/bigbite/macy.js';
	}

	public static function get_js_options(): array {
		return array(
			'columns' => 4,
			'margin'  => array(
				'x' => 0,
				'y' => 0,
			),
			'breakAt' => array(
				992 => 2,
				400 => 1,
			),
		);
	}

	public static function get_js_setup( Script_Variables $variables ): array {
		return array(
			sprintf(
				'%s = {...%1$s, container: %2$s}',
				esc_html( $variables->options ),
				esc_html( $variables->element )
			),
			'',
			sprintf( 'new Macy(%s)', esc_html( $variables->options ) ),
		);
	}

	public static function get_css_rules(): array {
		return array(
			'list-style' => 'none',
			'img'        => array(
				// block is necessary, as inline elements reserve some line-height spacing (even if line-height is 1).
				'display'   => 'block',
				'max-width' => '100%',
				'height'    => 'auto',
			),
		);
	}
}
