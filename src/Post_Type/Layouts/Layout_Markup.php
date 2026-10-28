<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Core\Web_Component\Web_Component_Factory;
use Org\Wplake\Advanced_Views\Post_Type\Core\Web_Component\Web_Component_Printer;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Layout_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Field_Template;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Htmlable;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Token_Factory;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Token_Factory_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Conditional\IF_Branch;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Conditional\IF_Token;
use function Org\Wplake\Advanced_Views\Template\Html\compose_html;

/**
 * @phpstan-import-type Html_Node from Html_Tag
 */
class Layout_Markup {
	/**
	 * Cache
	 *
	 * @var array<string, string>
	 */
	private array $markups_safe;
	private Field_Template $field_markup;
	private Field_Provider_Cluster $provider_cluster;
	private Token_Factory_Storage $token_factory_storage;
	protected Web_Component_Printer $printer;
	protected Web_Component_Factory $web_component_factory;

	public function __construct( Field_Template $field_markup, Field_Provider_Cluster $provider_cluster, Token_Factory_Storage $token_factory_storage, Web_Component_Printer $printer, Web_Component_Factory $web_component_factory ) {
		$this->field_markup          = $field_markup;
		$this->provider_cluster      = $provider_cluster;
		$this->token_factory_storage = $token_factory_storage;
		$this->markups_safe          = array();
		$this->printer               = $printer;
		$this->web_component_factory = $web_component_factory;
	}

	protected function row_if(
		Layout_Settings $layout_settings,
		Field_Meta $field_meta,
		Item_Settings $item_settings
	): ?IF_Token {
		if ( ! $field_meta->is_field_exist() ||
			// e.g. tab.
		$field_meta->is_ui_only() ) {
			return null;
		}

		$token_factory = $this->token_factory_storage->resolve_token_factory( $layout_settings->template_engine );

		$row_branch = $this->row_branch( $layout_settings, $field_meta, $item_settings );

		return $token_factory->if()->set_if_branch( $row_branch );
	}

	protected function row_branch(
		Layout_Settings $layout_settings,
		Field_Meta $field_meta,
		Item_Settings $item_settings
	): IF_Branch {
		$token_factory = $this->token_factory_storage->resolve_token_factory( $layout_settings->template_engine );

		$field_id   = $item_settings->field->get_template_field_id();
		$field_type = $field_meta->get_type();

		$is_condition_with_true_stub = $item_settings->field->is_visible_when_empty ||
							$this->provider_cluster->is_empty_value_supported_in_markup(
								$this->provider_cluster->get_vendor_name_by_key( $item_settings->field->key ),
								$field_type
							);

		$print_row = function () use ( $field_meta, $layout_settings, $item_settings, $field_id, $field_type ) {
			$row_type = 'row';

			if ( $this->provider_cluster->is_field_type_with_sub_fields(
				$field_meta->get_vendor_name(),
				$field_meta->get_type()
			) ) {
				$row_type = $field_type;
			}

			$this->field_markup->print_row_markup(
				$row_type,
				'',
				$layout_settings,
				$item_settings,
				$item_settings->field,
				$field_meta,
				$field_id
			);
		};

		$markup = $token_factory->html( fn() => $this->printer->print_indented( $print_row ) );

		$value_var = $token_factory->variable( $field_id )
									->add_item_path( 'value' );
		$condition = $is_condition_with_true_stub ?
			$token_factory->literal( true ) :
			$value_var;

		return $token_factory->if_branch()
			->set_condition( $condition )
			->set_body( $markup );
	}

	protected function generate_markup( Layout_Settings $layout_settings ): void {
		$token_factory = $this->token_factory_storage->resolve_token_factory( $layout_settings->template_engine );

		$root_class = $this->root_class( $token_factory, $layout_settings );

		$root = $this->web_component_factory->create_web_component( $layout_settings, $this->rows( $layout_settings ) )
			->attributes( array( 'class' => $root_class ) );

		$this->printer->print_component( $root );
		$this->printer->new_line();
	}

	/**
	 * @return Html_Node[]
	 */
	protected function rows( Layout_Settings $layout_settings ): array {
		$rows = array();

		foreach ( $layout_settings->items as $item ) {
			$row = $this->row_if( $layout_settings, $item->field->get_field_meta(), $item );

			if ( null === $row ) {
				continue;
			}

			// the first row follows the root's own line start
			if ( array() !== $rows ) {
				$rows[] = fn() => $this->printer->new_line( 2 )->line_start();
			}

			$rows[] = $row;
		}

		return $rows;
	}

	protected function print_safe_markup( Layout_Settings $layout_settings, bool $is_cache_disabled ): void {
		$short_unique_id = $layout_settings->get_unique_id( true );

		if ( key_exists( $short_unique_id, $this->markups_safe ) &&
			! $is_cache_disabled ) {
			// @phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $this->markups_safe[ $short_unique_id ];
		} else {
			$this->generate_markup( $layout_settings );
		}
	}

	public function print_markup(
		Layout_Settings $layout_settings,
		int $page_id,
		string $view_markup_safe = '',
		bool $is_skip_cache = false,
		bool $is_ignore_custom_markup = false
	): void {
		$view_markup_safe = ( strlen( $view_markup_safe ) > 0 ||
								$is_ignore_custom_markup ) ?
			$view_markup_safe :
			trim( $layout_settings->custom_markup );

		if ( 0 === strlen( $view_markup_safe ) ) {
			ob_start();
			$this->print_safe_markup( $layout_settings, $is_skip_cache );
			$view_markup_safe = (string) ob_get_clean();

			// remove the empty class attribute if the generation is disabled.
			if ( Layout_Settings::CLASS_GENERATION_NONE === $layout_settings->classes_generation ) {
				$view_markup_safe = str_replace( ' class=""', '', $view_markup_safe );
			}

			// @phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $view_markup_safe;
		} else {
			// @phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $view_markup_safe;
		}

		$layout_id                        = $layout_settings->get_unique_id( true );
		$this->markups_safe[ $layout_id ] = $view_markup_safe;
	}

	protected function root_class( Token_Factory $token_factory, Layout_Settings $layout_settings ): Htmlable {
		if ( Layout_Settings::CLASS_GENERATION_NONE === $layout_settings->classes_generation ) {
			return compose_html( array() );
		}

		$bem_name = $layout_settings->get_bem_name();
		$parts    = array(
			$token_factory->variable( Hard_Layout_Cpt::variable_name() )
							->add_item_path( 'classes' ),
			$bem_name,
		);

		// not necessary if the bemName is defined.
		if ( ! $layout_settings->has_unique_bem_name() ) {
			$parts[] = sprintf( ' %s--id--', $bem_name );
			$parts[] = $token_factory->variable( Hard_Layout_Cpt::variable_name() )
									->add_item_path( 'id' );
		}

		$parts[] = sprintf( ' %s--object-id--', $bem_name );
		$parts[] = $token_factory->variable( Hard_Layout_Cpt::variable_name() )
								->add_item_path( 'object_id' );

		return compose_html( $parts );
	}
}
