<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Assets\Runtime\Script;

use Org\Wplake\Advanced_Views\Plugin\Utils\Code_Minifier;
use Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Asset_Collector;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\raw_html;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

defined( 'ABSPATH' ) || exit;

final class Script_Tag_Composer {
	private Cpt_Asset_Collector $assets_collector;

	public function __construct( Cpt_Asset_Collector $assets_collector ) {
		$this->assets_collector = $assets_collector;
	}

	/**
	 * @return Html_Tag[]
	 */
	public function compose_tags(): array {
		$tags     = array();
		$js_codes = $this->assets_collector->get_js_code();

		foreach ( $js_codes as $name => $js_code ) {
			$script_tag = self::compose_script_tag( $name, $js_code );

			if ( $script_tag instanceof Html_Tag ) {
				$tags[] = $script_tag;
			}
		}

		return $tags;
	}

	protected static function compose_script_tag( string $name, string $js_code ): ?Html_Tag {
		$js_code   = Code_Minifier::minify_js( $js_code );
		$is_filled = strlen( $js_code ) > 0;

		if ( $is_filled ) {
			$attributes = array(
				'type'                      => 'module',
				'data-advanced-views-asset' => $name,
			);
			$js         = raw_html( $js_code );

			return tag( 'script' )
				->attributes( $attributes )
				->content( $js );
		}

		return null;
	}
}
