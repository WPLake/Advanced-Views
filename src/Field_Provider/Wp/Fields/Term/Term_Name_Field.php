<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Provider\Wp\Fields\Term;

use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Meta;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Custom_Field;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Fields\Markup_Field_Base;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Generation\Template_Field_Data;
use Org\Wplake\Advanced_Views\Post_Type\Layouts\Template\Rendering\Variable_Field_Data;
use function Org\Wplake\Advanced_Views\Template\Html\stack;

defined( 'ABSPATH' ) || exit;

class Term_Name_Field extends Markup_Field_Base {
	use Custom_Field;

	public function compose_html( string $field_id, Template_Field_Data $markup_field_data ): array {
		$token_factory = $markup_field_data->get_token_factory();

		return stack(
			$token_factory
				->variable( $field_id )
			->add_item_path( self::VALUE )
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_template_variables( Variable_Field_Data $variable_field_data ): array {
		$args = array(
			self::VALUE => $variable_field_data->get_field_data()->default_value,
		);

		$term = $this->get_term( $variable_field_data->get_value() );

		if ( null === $term ) {
			return $args;
		}

		$name = $term->name;

		// decode to avoid double encoding in Twig.
		$args[ self::VALUE ] = '' !== $name ?
			html_entity_decode( $name, ENT_QUOTES ) :
			$args[ self::VALUE ];

		return $args;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_validation_template_variables( Variable_Field_Data $variable_field_data ): array {
		return array(
			self::VALUE => 'name',
		);
	}

	public function is_with_field_wrapper(
		Layout_Settings $layout_settings,
		Field_Settings $field_settings,
		Field_Meta $field_meta
	): bool {
		return true;
	}

	public function get_custom_field_wrapper_tag(): string {
		return 'p';
	}
}
