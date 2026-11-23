<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Acf\Integration\Ajax_Select;

defined( 'ABSPATH' ) || exit;

/**
 * Storage of the sources, which the 'av_ajax_select' ACF field type is bound to by its 'source' argument.
 */
final class Ajax_Select_Cluster {
	const SOURCE_LAYOUTS         = 'layouts';
	const SOURCE_POST_SELECTIONS = 'post-selections';

	/**
	 * @var array<string,Ajax_Select_Source>
	 */
	protected array $sources = array();

	public function register( string $id, Ajax_Select_Source $source ): self {
		$this->sources[ $id ] = $source;

		return $this;
	}

	public function get( string $id ): ?Ajax_Select_Source {
		return $this->sources[ $id ] ?? null;
	}
}
