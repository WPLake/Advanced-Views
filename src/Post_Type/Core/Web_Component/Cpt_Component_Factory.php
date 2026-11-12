<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Web_Component;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Web_Component\Web_Component_Factory;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
final class Cpt_Component_Factory {
	protected Web_Component_Factory $web_component_factory;

	public function __construct( Web_Component_Factory $web_component_factory ) {
		$this->web_component_factory = $web_component_factory;
	}

	/**
	 * @param Html_Node[] $content
	 */
	public function create_web_component( Cpt_Settings $settings, array $content ): Html_Tag {
		$tag_name = $settings->get_tag_name();

		return Cpt_Settings::WEB_COMPONENT_SHADOW_DOM_DECLARATIVE === $settings->web_component ?
			$this->web_component_factory->create_shadow_component( $tag_name, $content ) :
			$this->web_component_factory->create_component( $tag_name, $content );
	}
}
