<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Web_Component;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Template\Web_Component\JS_Component_Composer;

defined( 'ABSPATH' ) || exit;

final class Cpt_Component_Composer {
	public static function compose_js( Cpt_Settings $cpt_settings ): string {
		if ( $cpt_settings->has_js_code() ) {
			$js_code  = $cpt_settings->get_js_code();
			$tag_name = $cpt_settings->get_tag_name();

			$classic_builder = fn() => JS_Component_Composer::compose_classic_component( $tag_name, $js_code );
			$shadow_builder  = fn() => JS_Component_Composer::compose_shadow_component( $tag_name, $js_code );

			$builders = array(
				Cpt_Settings::WEB_COMPONENT_CLASSIC                => $classic_builder,
				Cpt_Settings::WEB_COMPONENT_SHADOW_DOM_DECLARATIVE => $classic_builder,
				Cpt_Settings::WEB_COMPONENT_SHADOW_DOM             => $shadow_builder,
			);

			$builder = $builders[ $cpt_settings->web_component ] ?? null;

			return is_callable( $builder ) ?
				$builder() :
				$js_code;
		}

		return '';
	}
}
