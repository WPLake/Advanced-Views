<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Cpt\Table;

use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\tag;
use function Org\Wplake\Advanced_Views\Template\Html\print_html;

defined( 'ABSPATH' ) || exit;

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
		$title_tags   = tag( 'strong' )
			->content(
				tag( 'span' )
					->attributes( array( 'class' => 'row-title' ) )
					->content(
						$title
					)
			);

		self::print_title_and_action( $title_tags, $action_class, $action_url, $action_label );
	}

	/**
	 * @param string $action_class e.g. 'edit'
	 */
	public static function print_title_and_action(
		Html_Tag $title_tags,
		string $action_class,
		string $action_url,
		string $action_label
	): void {
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

		print_html( $title_tags, $action_tags );
	}
}
