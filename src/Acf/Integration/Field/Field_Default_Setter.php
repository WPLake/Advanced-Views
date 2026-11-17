<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Acf\Integration\Field;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Theme_Settings;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Actor_Base;
use Org\Wplake\Advanced_Views\Plugin\Core\Actor\Route_Detector;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Layout_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Post_Selection_Cpt;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Acf\Groups\Post_Selection_Settings;
use function Org\Wplake\Advanced_Views\Vendors\WPLake\Typed\string;

defined( 'ABSPATH' ) || exit;

final class Field_Default_Setter extends Actor_Base implements Actor {
	private Cpt_Theme_Settings $settings;

	public function __construct( Cpt_Theme_Settings $settings ) {
		$this->settings = $settings;
	}

	public static function has_route_hooks( Route_Detector $route_detector ): bool {
		$layout_cpt_name    = Hard_Layout_Cpt::cpt_name();
		$selection_cpt_name = Hard_Post_Selection_Cpt::cpt_name();

		$is_layout_add    = $route_detector->is_cpt_admin_route( $layout_cpt_name, Route_Detector::CPT_ADD );
		$is_selection_add = $route_detector->is_cpt_admin_route( $selection_cpt_name, Route_Detector::CPT_ADD );

		return $is_layout_add || $is_selection_add;
	}

	public function set_route_hooks( Route_Detector $route_detector ): void {
		self::add_filter( 'acf/prepare_field', array( $this, 'set_global_defaults_for_field' ) );
	}

	/**
	 * @param array<string,mixed> $field
	 *
	 * @return array<string,mixed>
	 */
	public function set_global_defaults_for_field( array $field ): array {
		$field_name = string( $field, 'key' );
		$setters    = $this->get_default_setters();
		$is_known   = key_exists( $field_name, $setters );

		if ( $is_known ) {
			$field = $setters[ $field_name ]( $field );
		}

		return $field;
	}

	/**
	 * @param callable(): string $getter
	 *
	 * @return callable(array<string,mixed>):array<string,mixed>
	 */
	protected static function value_setter( callable $getter ): callable {
		return function ( array $field ) use ( $getter ): array {
			$field['value'] = $getter();

			return $field;
		};
	}

	/**
	 * @return array<string,callable(array<string,mixed>):array<string,mixed>>
	 */
	protected function get_default_setters(): array {
		$set_template_engine = self::value_setter( fn(): string => $this->settings->get_template_engine() );

		$set_web_component = function ( array $field ): array {
			$web_components_type = $this->settings->get_web_component_type();
			$is_filled           = strlen( $web_components_type ) > 0;

			if ( $is_filled ) {
				$field['value'] = $web_components_type;
			}

			return $field;
		};

		$set_classes_generation = self::value_setter( fn(): string => $this->settings->get_classes_generation() );
		$set_sass_code          = self::value_setter( fn(): string => $this->settings->get_sass_code() );

		$layout_engine    = Layout_Settings::getAcfFieldName( Layout_Settings::FIELD_TEMPLATE_ENGINE );
		$layout_component = Layout_Settings::getAcfFieldName( Layout_Settings::FIELD_WEB_COMPONENT );
		$layout_classes   = Layout_Settings::getAcfFieldName( Layout_Settings::FIELD_CLASSES_GENERATION );
		$layout_sass      = Layout_Settings::getAcfFieldName( Layout_Settings::FIELD_SASS_CODE );

		$selection_engine    = Post_Selection_Settings::getAcfFieldName( Post_Selection_Settings::FIELD_TEMPLATE_ENGINE );
		$selection_component = Post_Selection_Settings::getAcfFieldName( Post_Selection_Settings::FIELD_WEB_COMPONENT );
		$selection_classes   = Post_Selection_Settings::getAcfFieldName( Post_Selection_Settings::FIELD_CLASSES_GENERATION );
		$selection_sass      = Post_Selection_Settings::getAcfFieldName( Post_Selection_Settings::FIELD_SASS_CODE );

		return array(
			$layout_engine       => $set_template_engine,
			$selection_engine    => $set_template_engine,
			$layout_component    => $set_web_component,
			$selection_component => $set_web_component,
			$layout_classes      => $set_classes_generation,
			$selection_classes   => $set_classes_generation,
			$layout_sass         => $set_sass_code,
			$selection_sass      => $set_sass_code,
		);
	}
}
