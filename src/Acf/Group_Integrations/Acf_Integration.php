<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Acf\Group_Integrations;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Group;
use Org\Wplake\Advanced_Views\Plugin\Base\Hookable;
use Org\Wplake\Advanced_Views\Plugin\Base\Hooks_Interface;
use Org\Wplake\Advanced_Views\Plugin\Utils\Route_Detector;

defined( 'ABSPATH' ) || exit;

class Acf_Integration extends Hookable implements Hooks_Interface {
	private string $target_cpt_name;

	public function __construct( string $target_cpt_name ) {
		$this->target_cpt_name = $target_cpt_name;
	}

	/**
	 * @return string[]
	 */
	protected function get_post_type_choices(): array {
		return get_post_types();
	}


	protected function set_field_choices(): void {
	}

	protected function set_conditional_field_rules(): void {
	}


	public function set_hooks( Route_Detector $route_detector ): void {
		if ( false === $route_detector->is_admin_route() ) {
			return;
		}

		// load only on targetCpt pages
		// (but not only on edit pages, as there are Settings & Tools groups).
		if ( '' !== $this->target_cpt_name &&
			false === $route_detector->is_cpt_admin_route( $this->target_cpt_name ) ) {
			return;
		}

		$this->set_field_choices();

		// Conditional field logic requires fields info to be already available.
		// It means the data vendor must already be loaded.
		// 'wp_loaded' is the first one from which MetaBox fields info become available.
		self::add_action(
			'wp_loaded',
			function (): void {
				$this->set_conditional_field_rules();
			}
		);
	}

	/**
	 * @param callable(array<string,mixed> $field): array<string,mixed> $get_overrides getter of the field args to merge
	 */
	protected static function override_field_settings( string $field_name, callable $get_overrides ): void {
		$hook_name = sprintf( 'acf/load_field/name=%s', $field_name );

		$merge_overrides = function ( array $field ) use ( $get_overrides ): array {
			$overrides = $get_overrides( $field );

			return array_merge( $field, $overrides );
		};

		self::add_filter( $hook_name, $merge_overrides );
	}

	/**
	 * @param class-string<Group> $group_class
	 * @param array<string,callable(array<string,mixed> $field): array<string,string>> $choices_callbacks field constant => choices getter
	 */
	protected static function bind_field_choices( string $group_class, array $choices_callbacks ): void {
		foreach ( $choices_callbacks as $field_constant => $get_choices ) {
			$field_name = $group_class::getAcfFieldName( $field_constant );

			$get_overrides = fn( array $field ) => array( 'choices' => $get_choices( $field ) );

			self::override_field_settings( $field_name, $get_overrides );
		}
	}
}
