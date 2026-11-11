<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Row;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Frontend_Library\Layout\Layout_Library;

/**
 * What the markup fields (e.g. a repeater) need to compose their nested rows.
 *
 * @phpstan-import-type Html_Node from Html_Tag
 */
interface Row_Composer {
	/**
	 * @param Layout_Library[] $field_assets
	 *
	 * @return Html_Node[]
	 */
	public function compose_field_content(
		array $field_assets,
		Layout_Settings $layout_settings,
		?Item_Settings $item_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta,
		string $field_id
	): array;

	/**
	 * @param Html_Node[]|null $custom_nodes Used in RepeaterField, replaces the field's own markup
	 *
	 * @return Html_Node[]
	 */
	public function compose_row(
		string $row_type,
		string $row_suffix,
		Layout_Settings $layout_settings,
		?Item_Settings $item_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta,
		string $field_id,
		?array $custom_nodes = null
	): array;
}
