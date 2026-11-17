<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Table;

use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

defined( 'ABSPATH' ) || exit;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
final class Row_Title_Printer {
	/**
	 * @param string $action_class e.g. 'import' or 'sync'
	 *
	 * @return Html_Node[]
	 */
	public function row_title_nodes(
		string $title,
		string $action_class,
		string $action_url,
		string $action_label
	): array {
		$title_tags = tag( 'strong' )
			->content(
				tag( 'span' )
					->attributes( array( 'class' => 'row-title' ) )
					->content(
						$title
					)
			);

		return $this->title_and_action_nodes( $title_tags, $action_class, $action_url, $action_label );
	}

	/**
	 * @param string $action_class e.g. 'edit'
	 *
	 * @return Html_Node[]
	 */
	public function title_and_action_nodes(
		Html_Tag $title_tags,
		string $action_class,
		string $action_url,
		string $action_label
	): array {
		$action_tags = tag( 'div' )
			->attributes( array( 'class' => 'row-actions' ) )
			->content(
				tag( 'span' )
					->attributes( array( 'class' => $action_class ) )
					->content(
						tag( 'a' )
							->attributes( array( 'href' => $action_url ) )
							->content(
								$action_label
							)
					)
			);

		return array( $title_tags, $action_tags );
	}
}
