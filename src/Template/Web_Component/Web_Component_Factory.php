<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Web_Component;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
final class Web_Component_Factory {
	/**
	 * @param Html_Node[] $content
	 */
	public function create_component( string $tag_name, array $content ): Html_Tag {
		return tag( $tag_name )
			->content( ...$content );
	}

	/**
	 * @param Html_Node[] $content
	 */
	public function create_shadow_component( string $tag_name, array $content ): Html_Tag {
		$template = tag( 'template' )
			->attributes( array( 'shadowrootmode' => 'open' ) )
			->content( ...$content );

		return $this->create_component( $tag_name, array( $template ) );
	}
}
