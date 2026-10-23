<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Table;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Html;
use Org\Wplake\Advanced_Views\Template\Html\Rendering\Html_Renderer;
use function Org\Wplake\Advanced_Views\Template\Html\stack;
use function Org\Wplake\Advanced_Views\Template\Html\tag;
use function Org\Wplake\Advanced_Views\Template\Html\nest;

/**
 * @phpstan-import-type Html_Nest from Html
 */
final class Row_Title_Printer {
	/**
	 * @param string $action_class e.g. 'import' or 'sync'
	 */
	public static function print_row_title(
		string $title,
		string $action_class,
		string $action_url,
		string $action_label
	): void {
		$title_tags = nest(
			tag( 'strong' ),
			tag(
				'span',
				array( 'class' => 'row-title' )
			),
			$title
		);

		self::print_title_and_action( $title_tags, $action_class, $action_url, $action_label );
	}

	/**
	 * @param Html_Nest $title_tags
	 * @param string $action_class e.g. 'edit'
	 */
	public static function print_title_and_action(
		array $title_tags,
		string $action_class,
		string $action_url,
		string $action_label
	): void {
		$action_tags = nest(
			tag(
				'div',
				array( 'class' => 'row-actions' )
			),
			tag(
				'span',
				array( 'class' => $action_class )
			),
			tag(
				'a',
				array( 'href' => $action_url )
			),
			$action_label
		);

		$tags = stack( $title_tags, $action_tags );

		Html_Renderer::print( $tags );
	}
}
