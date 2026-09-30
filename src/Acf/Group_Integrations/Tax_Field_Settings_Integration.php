<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Acf\Group_Integrations;

use Org\Wplake\Advanced_Views\Acf\Acf_Utils;
use Org\Wplake\Advanced_Views\Acf\Groups\Tax_Field_Settings;
use Org\Wplake\Advanced_Views\Field_Provider\Core\Field_Provider_Cluster;
use Org\Wplake\Advanced_Views\Plugin\Plugin;

defined( 'ABSPATH' ) || exit;

class Tax_Field_Settings_Integration extends Acf_Integration {
	private Field_Provider_Cluster $provider_cluster;
	private Plugin $plugin;

	public function __construct( string $target_cpt_name, Field_Provider_Cluster $provider_cluster, Plugin $plugin ) {
		parent::__construct( $target_cpt_name );

		$this->provider_cluster = $provider_cluster;
		$this->plugin           = $plugin;
	}

	/**
	 * @return array<string,string>
	 */
	protected function get_taxonomy_choices(): array {
		$tax_choices = array(
			'' => __( 'Select', 'acf-views' ),
		);

		$taxonomies = get_taxonomies( array(), 'objects' );

		foreach ( $taxonomies as $taxonomy ) {
			$tax_choices[ $taxonomy->name ] = $taxonomy->label;
		}

		return $tax_choices;
	}

	/**
	 * @return array<string,string>
	 */
	protected function get_term_choices(): array {
		$term_choices = array(
			'' => __( 'Select', 'acf-views' ),
		);

		/**
		 * @var string[] $taxonomy_names
		 */
		$taxonomy_names = get_taxonomies();

		foreach ( $taxonomy_names as $taxonomy_name ) {
			$terms = get_terms(
				array(
					'taxonomy'   => $taxonomy_name,
					'hide_empty' => false,
				)
			);

			if ( is_array( $terms ) ) {
				foreach ( $terms as $term ) {
					$full_tax_id                  = Tax_Field_Settings::create_key( $taxonomy_name, $term->term_id );
					$term_choices[ $full_tax_id ] = $term->name;
				}
			}
		}

		return $term_choices;
	}

	/**
	 * @return array<string,string>
	 */
	protected function get_value_type_choices(): array {
		$dynamic_label = __( 'Dynamic term', 'acf-views' );
		$dynamic_label = $this->plugin->get_pro_field_label( $dynamic_label );

		return array(
			Tax_Field_Settings::VALUE_TYPE_STATIC  => __( 'Static term', 'acf-views' ),
			Tax_Field_Settings::VALUE_TYPE_DYNAMIC => $dynamic_label,
		);
	}

	/**
	 * @return array<string,string>
	 */
	protected static function get_dynamic_term_choices(): array {
		return array(
			'$current$'         => __( 'Current term (archive and category pages)', 'acf-views' ),
			'$meta$'            => __( 'Meta field value', 'acf-views' ),
			'$custom-argument$' => __( 'Custom shortcode argument', 'acf-views' ),
		);
	}

	/**
	 * @return array<string,callable(): array<string,string>>
	 */
	protected function get_choices_callbacks(): array {
		return array(
			Tax_Field_Settings::FIELD_TAXONOMY     =>
				fn() => $this->get_taxonomy_choices(),
			Tax_Field_Settings::FIELD_VALUE_TYPE   =>
				fn() => $this->get_value_type_choices(),
			Tax_Field_Settings::FIELD_TERM         =>
				fn() => $this->get_term_choices(),
			Tax_Field_Settings::FIELD_DYNAMIC_TERM =>
				fn() => self::get_dynamic_term_choices(),
			Tax_Field_Settings::FIELD_META_GROUP   =>
				fn() => $this->provider_cluster->get_group_choices( true ),
			Tax_Field_Settings::FIELD_META_FIELD   =>
				fn() => $this->provider_cluster->get_field_choices( true ),
		);
	}

	protected function set_field_choices(): void {
		$choices_callbacks = $this->get_choices_callbacks();

		Acf_Utils::bind_field_choices( Tax_Field_Settings::class, $choices_callbacks );
	}
}
