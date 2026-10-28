<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Web_Component;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Formatter;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
final class Web_Component_Factory {
	protected Html_Formatter $formatter;

	public function __construct( Html_Formatter $formatter ) {
		$this->formatter = $formatter;
	}

	/**
	 * @param Html_Node[] $content
	 */
	public function create_web_component( Cpt_Settings $settings, array $content ): Html_Tag {
		$tag_name = $settings->get_tag_name();
		$children = $this->get_component_children( $settings, $content );

		return tag( $tag_name )
			->content( ...$children );
	}

	/**
	 * @param Html_Node[] $content
	 *
	 * @return Html_Node[]
	 */
	protected function get_component_children( Cpt_Settings $settings, array $content ): array {
		$is_shadow_root = Cpt_Settings::WEB_COMPONENT_SHADOW_DOM_DECLARATIVE === $settings->web_component;

		if ( $is_shadow_root ) {
			$new_line = $this->formatter->get_break_symbol();
			$template = tag( 'template' )
				->attributes( array( 'shadowrootmode' => 'open' ) )
				->content( ...$content );

			return array( $new_line, $template, $new_line );
		}

		return $content;
	}
}
