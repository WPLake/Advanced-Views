<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Web_Component;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

final class Web_Component_Factory {
	public static function create_web_component( Cpt_Settings $settings, array $content ): Html_Tag {
		$tag_name = $settings->get_tag_name();
		$children = self::get_component_children( $settings, $content );

		return tag( $tag_name )
			->content( ...$children );
	}

	protected static function get_component_children( Cpt_Settings $settings, array $content ): array {
		$is_shadow_root = Cpt_Settings::WEB_COMPONENT_SHADOW_DOM_DECLARATIVE === $settings->web_component;

		if ( $is_shadow_root ) {
			$template = tag( 'template' )
				->attributes( array( 'shadowrootmode' => 'open' ) )
				->content( ...$content );

			return array( $template );
		}

		return $content;
	}
}
