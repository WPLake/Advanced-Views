<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Web_Component;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Template\Web_Component\JS_Component_Composer;

defined( 'ABSPATH' ) || exit;

final class Cpt_Component_Composer {
	public static function compose_js( Cpt_Settings $cpt_settings ): string {
		$js_code = $cpt_settings->get_js_code();

		if ( '' === trim( $js_code ) ) {
			return '';
		}

		$tag_name = $cpt_settings->get_tag_name();

		$builders = array(
			Cpt_Settings::WEB_COMPONENT_CLASSIC    => fn() => JS_Component_Composer::compose_classic_component( $tag_name, $js_code ),
			Cpt_Settings::WEB_COMPONENT_SHADOW_DOM_DECLARATIVE => fn() => JS_Component_Composer::compose_classic_component( $tag_name, $js_code ),
			Cpt_Settings::WEB_COMPONENT_SHADOW_DOM => fn() => JS_Component_Composer::compose_shadow_component( $tag_name, $js_code ),
		);

		$builder = $builders[ $cpt_settings->web_component ] ?? null;

		return null !== $builder ?
			$builder() :
			$js_code;
	}
}
