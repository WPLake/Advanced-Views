<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Acf\Group_Integrations;

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

	protected function set_field_choices(): void {
		// fixme improve this pattern accross all Acf_integration children.
		// add a static helper to the parent, which takes ['field' => callback] array, loops, resolves field name, adds filter & merge callback.
		// return should be merged exactly on the level above, so callbacks don't own "merge_array" complicity. pass $field to the callback, but do not declare it in callbacks until it's used.
		// then move all set_field_choices() to this single call.
		self::add_filter(
			'acf/load_field/name=' . Tax_Field_Settings::getAcfFieldName( Tax_Field_Settings::FIELD_TAXONOMY ),
			function ( array $field ) {
				$field['choices'] = $this->get_taxonomy_choices();

				return $field;
			}
		);

		self::add_filter(
			'acf/load_field/name=' . Tax_Field_Settings::getAcfFieldName( Tax_Field_Settings::FIELD_VALUE_TYPE ),
			function ( array $field ) {
				$dynamic_label = __( 'Dynamic term', 'acf-views' );

				// fixme must turned into a call to the ->get_pro_field_label(label), so this check & append happens there.
				if ( $this->plugin->is_pro_field_locked() ) {
					$dynamic_label .= ' ' . $this->plugin->get_pro_only_label();
				}

				$field['choices'] = array(
					Tax_Field_Settings::VALUE_TYPE_STATIC  => __( 'Static term', 'acf-views' ),
					Tax_Field_Settings::VALUE_TYPE_DYNAMIC => $dynamic_label,
				);

				return $field;
			}
		);

		self::add_filter(
			'acf/load_field/name=' . Tax_Field_Settings::getAcfFieldName( Tax_Field_Settings::FIELD_TERM ),
			function ( array $field ) {
				$field['choices'] = $this->get_term_choices();

				return $field;
			}
		);

		self::add_filter(
			'acf/load_field/name=' . Tax_Field_Settings::getAcfFieldName( Tax_Field_Settings::FIELD_DYNAMIC_TERM ),
			function ( array $field ) {
				$field['choices'] = array(
					'$current$'         => __( 'Current term (archive and category pages)', 'acf-views' ),
					'$meta$'            => __( 'Meta field value', 'acf-views' ),
					'$custom-argument$' => __( 'Custom shortcode argument', 'acf-views' ),
				);

				return $field;
			}
		);

		self::add_filter(
			'acf/load_field/name=' . Tax_Field_Settings::getAcfFieldName( Tax_Field_Settings::FIELD_META_GROUP ),
			function ( array $field ) {
				$field['choices'] = $this->provider_cluster->get_group_choices( true );

				return $field;
			}
		);

		self::add_filter(
			'acf/load_field/name=' . Tax_Field_Settings::getAcfFieldName( Tax_Field_Settings::FIELD_META_FIELD ),
			function ( array $field ) {
				$field['choices'] = $this->provider_cluster->get_field_choices( true );

				return $field;
			}
		);
	}
}
