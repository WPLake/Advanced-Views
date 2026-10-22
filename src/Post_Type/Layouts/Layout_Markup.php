<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Layouts;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Layout_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Item_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Field_Template;
use Org\Wplake\Advanced_Views\Template\Html\Html_Renderer;
use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Composed_Htmlable;
use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Htmlable;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Token_Factory;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Token_Factory_Storage;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Conditional\IF_Branch;

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

	public function __construct( Field_Template $field_markup, Field_Provider_Cluster $provider_cluster, Token_Factory_Storage $token_factory_storage ) {
		$this->field_markup          = $field_markup;
		$this->provider_cluster      = $provider_cluster;
		$this->token_factory_storage = $token_factory_storage;
		$this->markups_safe          = array();
	}

	protected function generate_row_markup(
		Layout_Settings $layout_settings,
		Field_Meta $field_meta,
		Item_Settings $item_settings
	): void {
		if ( ! $field_meta->is_field_exist() ||
			// e.g. tab.
		$field_meta->is_ui_only() ) {
			return;
		}

		$token_factory = $this->token_factory_storage->resolve_token_factory( $layout_settings->template_engine );

		$row_branch = $this->row_branch( $layout_settings, $field_meta, $item_settings );

		$if = $token_factory->if()->set_if_branch( $row_branch );

		$token_factory->formatter()->new_line();
		$token_factory->formatter()->tab();

		$if->display();

		$token_factory->formatter()->new_line( 2 );
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

		$markup = $token_factory->html(
			function () use ( $field_meta, $layout_settings, $item_settings, $field_id, $field_type, $token_factory ) {
				$row_tabs_number = 2;

				$row_type = 'row';

				if ( $this->provider_cluster->is_field_type_with_sub_fields(
					$field_meta->get_vendor_name(),
					$field_meta->get_type()
				) ) {
					$row_type = $field_type;
				}

				$token_factory->formatter()
								->new_line();

				$this->field_markup->print_row_markup(
					$row_type,
					'',
					$layout_settings,
					$item_settings,
					$item_settings->field,
					$field_meta,
					$row_tabs_number,
					$field_id
				);

				$token_factory->formatter()->tab();
			}
		);

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

		$new_line       = fn() => $token_factory->formatter()->new_line();
		$rows           = function () use ( $layout_settings ) {
			foreach ( $layout_settings->items as $item ) {
				$this->generate_row_markup(
					$layout_settings,
					$item->field->get_field_meta(),
					$item
				);
			}
		};
		$is_shadow_root = Layout_Settings::WEB_COMPONENT_SHADOW_DOM_DECLARATIVE === $layout_settings->web_component;
		$root_content   = $is_shadow_root ?
			array(
				$new_line,
				array(
					array(
						'tag'   => 'template',
						'attrs' => array( 'shadowrootmode' => 'open' ),
					),
					array( $new_line, $rows ),
				),
				$new_line,
			) :
			array( $new_line, $rows );

		Html_Renderer::print_tag_group(
			array(
				array(
					array(
						'tag'   => $layout_settings->get_tag_name(),
						'attrs' => array( 'class' => $this->root_class( $token_factory, $layout_settings ) ),
					),
					$root_content,
				),
				$new_line,
			)
		);
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
			return Composed_Htmlable::compose( array() );
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

		return Composed_Htmlable::compose( $parts );
	}
}
