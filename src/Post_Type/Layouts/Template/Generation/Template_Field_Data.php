<?php


declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field;
use Org\Wplake\Advanced_Views\Plugin\Utils\Html\Tags_Renderer;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Field_Data;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Template\Html_Wrapper;
use Org\Wplake\Advanced_Views\Template\Library_Pattern\Core\Template\Template_Pattern;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Token_Factory;

defined( 'ABSPATH' ) || exit;

/**
 * @phpstan-import-type Tag_Group from Tags_Renderer
 */
final class Template_Field_Data extends Field_Data {
	/**
	 * @var Template_Pattern[]
	 */
	private array $field_assets;
	private int $tabs_number;
	private bool $is_with_field_wrapper;
	private bool $is_with_row_wrapper;
	private Token_Factory $token_factory;

	public function __construct(
		Layout_Settings $layout_settings,
		?Item_Settings $item_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta,
		Field_Template $field_markup,
		Markup_Field $markup_field,
		Token_Factory $token_factory
	) {
		parent::__construct(
			$layout_settings,
			$item_settings,
			$field_settings,
			$field_meta,
			$field_markup,
			$markup_field
		);

		$this->field_assets          = array();
		$this->tabs_number           = 0;
		$this->is_with_field_wrapper = false;
		$this->is_with_row_wrapper   = false;
		$this->token_factory         = $token_factory;
	}

	/**
	 * Merges the outers of every asset by depth: the n-th outers of all assets form the n-th result item.
	 *
	 * @param Html_Wrapper[][] $assets_outers
	 *
	 * @return Html_Wrapper[]
	 */
	public static function merge_outers( array $assets_outers ): array {
		/**
		 * @var Html_Wrapper[] $merged_outers
		 */
		$merged_outers = array();

		foreach ( $assets_outers as $asset_outers ) {
			$counter = 0;

			foreach ( $asset_outers as $asset_outer ) {
				$merged_outers[ $counter ] = key_exists( $counter, $merged_outers ) ?
					$merged_outers[ $counter ] :
					new Html_Wrapper( '', array() );

				$merged_outers[ $counter ]->merge( $asset_outer );

				++$counter;
			}
		}

		return $merged_outers;
	}

	/**
	 * @return Html_Wrapper[]
	 */
	public function get_item_outers( string $field_id, string $item_id ): array {
		$layout_settings = $this->get_view_data();
		$field_settings  = $this->get_field_data();
		$assets_outers   = array();

		foreach ( $this->field_assets as $field_asset ) {
			$assets_outers[] = $field_asset->get_item_outers( $layout_settings, $field_settings, $field_id, $item_id );
		}

		return self::merge_outers( $assets_outers );
	}

	/**
	 * @param Html_Wrapper[] $item_outers
	 * @param callable():void $print_content
	 *
	 * @return Tag_Group
	 */
	public function wrap_with_item_outers( array $item_outers, callable $print_content, bool $is_without_last_item_tabs = false ): array {
		$last_index = count( $item_outers ) - 1;
		$content    = array( $print_content );

		// the innermost outer goes first.
		foreach ( array_reverse( $item_outers, true ) as $index => $outer ) {
			$is_without_tabs = $index === $last_index && $is_without_last_item_tabs;

			$content = array(
				array(
					$this->item_outer_tag( $outer ),
					array(
						function () use ( $is_without_tabs ) {
							$this->token_factory->formatter()
								->new_line();

							++$this->tabs_number;

							if ( ! $is_without_tabs ) {
								$this->token_factory->formatter()
									->tab( $this->tabs_number );
							}
						},
						array( $content ),
						function () use ( $is_without_tabs ) {
							if ( ! $is_without_tabs ) {
								$this->token_factory->formatter()
									->new_line();
							}

							$this->token_factory->formatter()
								->tab( --$this->tabs_number );
						},
					),
				),
			);
		}

		return $content;
	}

	/**
	 * @return  Template_Pattern[]
	 */
	public function get_field_assets(): array {
		return $this->field_assets;
	}

	/**
	 * @param Template_Pattern[] $field_assets
	 */
	public function set_field_assets( array $field_assets ): void {
		$this->field_assets = $field_assets;
	}

	public function get_tabs_number(): int {
		return $this->tabs_number;
	}

	public function increment_and_get_tabs_number(): int {
		return ++$this->tabs_number;
	}

	public function decrement_and_get_tabs_number(): int {
		return --$this->tabs_number;
	}

	public function set_tabs_number( int $tabs_number ): void {
		$this->tabs_number = $tabs_number;
	}

	public function is_with_field_wrapper(): bool {
		return $this->is_with_field_wrapper;
	}

	public function set_is_with_field_wrapper( bool $is_with_field_wrapper ): void {
		$this->is_with_field_wrapper = $is_with_field_wrapper;
	}

	public function is_with_row_wrapper(): bool {
		return $this->is_with_row_wrapper;
	}

	public function set_is_with_row_wrapper( bool $is_with_row_wrapper ): void {
		$this->is_with_row_wrapper = $is_with_row_wrapper;
	}

	public function print_tabs(): void {
		$this->token_factory->formatter()
							->tab( $this->get_tabs_number() );
	}

	public function increment_and_print_tabs(): void {
		$this->token_factory->formatter()
							->tab( $this->increment_and_get_tabs_number() );
	}

	public function decrement_and_print_tabs(): void {
		$this->token_factory->formatter()
							->tab( $this->decrement_and_get_tabs_number() );
	}

	public function get_token_factory(): Token_Factory {
		return $this->token_factory;
	}

	/**
	 * @return array{tag:string,attrs:array<string,mixed>}
	 */
	protected function item_outer_tag( Html_Wrapper $outer ): array {
		$attr_class = $outer->attrs['class'] ?? '';
		$class      = '' !== $attr_class ?
			$attr_class :
			$this->get_view_data()->get_item_class( 'item', $this->get_field_data() );

		$attrs = array_merge( array( 'class' => $class ), $outer->attrs );

		foreach ( $outer->variable_attrs as $attr => $variable_info ) {
			$attrs[ $attr ] = $this->token_factory->variable( $variable_info['field_id'] )
													->add_item_path( $variable_info['item_key'] );
		}

		return array(
			'tag'   => $outer->tag,
			'attrs' => $attrs,
		);
	}
}
