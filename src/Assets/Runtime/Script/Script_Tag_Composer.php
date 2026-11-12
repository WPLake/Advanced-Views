<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Runtime\Script;

use Org\Wplake\Advanced_Views\Plugin\Utils\Code_Minifier;
use Org\Wplake\Advanced_Views\Post_Type\Core\Asset\Cpt_Assets_Collector;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\raw_html;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

defined( 'ABSPATH' ) || exit;

class Script_Tag_Composer {
	private Cpt_Assets_Collector $assets_collector;

	public function __construct( Cpt_Assets_Collector $assets_collector ) {
		$this->assets_collector = $assets_collector;
	}

	/**
	 * @return Html_Tag[]
	 */
	public function compose_tags(): array {
		$tags = array();

		foreach ( $this->assets_collector->get_js_code() as $name => $js_code ) {
			$js_code = Code_Minifier::minify_js( $js_code );

			if ( 0 === strlen( $js_code ) ) {
				continue;
			}

			$attributes = array(
				'type'                      => 'module',
				'data-advanced-views-asset' => $name,
			);

			$tags[] = tag( 'script' )
				->attributes( $attributes )
				->content(
					raw_html( $js_code )
				);
		}

		return $tags;
	}
}
