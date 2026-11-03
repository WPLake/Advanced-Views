<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Bootstrap;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Plugin\Core\Container\Factory_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Logger\Logger;
use Org\Wplake\Advanced_Views\Plugin\Settings\Options_Storage;
use Org\Wplake\Advanced_Views\Plugin\Utils\WP_Html_Escaper;
use Org\Wplake\Advanced_Views\Post_Type\Core\Git_Api\Git_Lab_Api;
use Org\Wplake\Advanced_Views\Post_Type\Core\Mount_Point\Point_Mounter;
use Org\Wplake\Advanced_Views\Post_Type\Core\Mount_Point\Point_Provider;
use Org\Wplake\Advanced_Views\Post_Type\Core\Web_Component\Web_Component_Renderer;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Data_Storage\Layout_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Layouts_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Data_Storage\Selection_Settings_Storage;
use Org\Wplake\Advanced_Views\Post_Type\Post_Selections\Selections_Cpt;
use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Html\Formatting\Formatters\Compact_Tag_Formatter;
use Org\Wplake\Advanced_Views\Template\Html\Formatting\Formatters\Text_Formatter;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Tag_Renderer;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Rendering\Token_Renderer;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Rendering\Token_Tree_Formatter;

class Post_Type_Factory extends Factory_Base {
	/**
	 * A separate (not the shared one) Html_Renderer, configured for the template tokens
	 */
	public static function component_html_renderer(
		Token_Tree_Formatter $node_formatter,
		Compact_Tag_Formatter $tag_formatter
	): Html_Renderer {
		// Local escaper, as the token attribute renderers must not leak to the shared (auto-wired) one.
		$escaper = new WP_Html_Escaper();
		$escaper = Token_Renderer::configure_escaper( $escaper );

		$tag_renderer  = self::component_tag_renderer( $escaper, $tag_formatter );
		$html_renderer = new Html_Renderer( $tag_renderer, $escaper );

		$token_renderer = Token_Renderer::configure_renderer( $html_renderer );

		return $token_renderer->set_formatter( $node_formatter );
	}

	public function web_component_renderer(): Web_Component_Renderer {
		$html_renderer = self::component_html_renderer(
			$this->resolve( Token_Tree_Formatter::class ),
			$this->resolve( Compact_Tag_Formatter::class )
		);

		return new Web_Component_Renderer( $html_renderer, $this->resolve( Text_Formatter::class ) );
	}

	public function git_lab_api(): Git_Lab_Api {
		return new Git_Lab_Api(
			$this->resolve( Logger::class ),
			$this->resolve( Options_Storage::class ),
			array(
				$this->resolve( Layouts_Cpt::class ),
				$this->resolve( Selections_Cpt::class ),
			)
		);
	}

	public function point_mounter(): Point_Mounter {
		return new Point_Mounter(
			array(
				new Point_Provider(
					$this->resolve( Layout_Settings_Storage::class ),
					$this->resolve( Layouts_Cpt::class )
				),
				new Point_Provider(
					$this->resolve( Selection_Settings_Storage::class ),
					$this->resolve( Selections_Cpt::class )
				),
			)
		);
	}

	protected static function component_tag_renderer( Html_Escaper $escaper, Compact_Tag_Formatter $tag_formatter ): Tag_Renderer {
		$tag_renderer = new Tag_Renderer( $escaper );

		return $tag_renderer->set_formatter( $tag_formatter );
	}
}
