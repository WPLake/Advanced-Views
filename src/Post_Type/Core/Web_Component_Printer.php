<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Template\Html\Formatting\Formatted_Tag_Printer;
use Org\Wplake\Advanced_Views\Template\Html\Formatting\Html_Formatter;
use Org\Wplake\Advanced_Views\Template\Html\Html_Renderer;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

final class Web_Component_Printer {
	protected Html_Renderer $renderer;
	protected Html_Formatter $formatter;

	public function __construct( Formatted_Tag_Printer $tag_printer, Html_Formatter $formatter ) {
		$this->renderer  = new Html_Renderer( $tag_printer );
		$this->formatter = $formatter;
	}

	public function create_web_component( Cpt_Settings $settings, array $content ): Html_Tag {
		$tag_name = $settings->get_tag_name();
		$root     = self::get_component_root( $settings, $content );

		return tag( $tag_name )
			->content( ...$root );
	}

	public function print_web_component( Html_Tag $component ): void {
		$this->renderer->print( $component );
	}

	protected static function get_component_root( Cpt_Settings $settings, array $content ): array {
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
