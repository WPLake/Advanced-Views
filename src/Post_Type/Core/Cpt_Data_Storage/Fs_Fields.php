<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Core\Cpt_Data_Storage;

use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Integration\Template_Integration;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Core\Integration\Template_Integration_Storage;

defined( 'ABSPATH' ) || exit;

class Fs_Fields {
	const DATA_JSON        = 'data.json';
	const STYLE_CSS        = 'style.css';
	const STYLE_SCSS       = 'style.scss';
	const SCRIPT_JS        = 'script.js';
	const MULTILINGUAL_PHP = 'multilingual.php';
	const LINKS_MD         = 'links.md';

	protected Template_Integration_Storage $template_integration_storage;

	public function __construct( Template_Integration_Storage $template_integration_storage ) {
		$this->template_integration_storage = $template_integration_storage;
	}

	/**
	 * @return array<string,string[]>
	 */
	public function extract_markup_multilingual_strings( Cpt_Settings $cpt_settings ): array {
		$integration   = $this->template_integration_storage->resolve_integration( $cpt_settings->template_engine );
		$custom_markup = trim( $cpt_settings->custom_markup );

		if ( strlen( $custom_markup ) > 0 &&
			$integration instanceof Template_Integration ) {
			return $integration->extract_multilingual_strings( $custom_markup );
		}

		return array();
	}

	/**
	 * @return string[]
	 */
	protected function get_template_fs_field_names_without_json(): array {
		return array(
			'markup',
			'custom_markup',
			'css_code',
			'sass_code',
		);
	}

	// returns the data.json content, without the defaults and template fields.
	protected function get_data_json( Cpt_Settings $cpt_settings ): string {
		$template_fs_field_names = $this->get_template_fs_field_names_without_json();

		$tmp = array();

		foreach ( $template_fs_field_names as $template_fs_field_name ) {
			// @phpstan-ignore-next-line
			$tmp[ $template_fs_field_name ] = $cpt_settings->{$template_fs_field_name};
			// @phpstan-ignore-next-line
			$cpt_settings->{$template_fs_field_name} = '';
		}

		// skip defaults, we don't need to store them.
		$data_json = $cpt_settings->getJson( true );

		foreach ( $template_fs_field_names as $template_fs_field_name ) {
			// @phpstan-ignore-next-line
			$cpt_settings->{$template_fs_field_name} = $tmp[ $template_fs_field_name ];
		}

		return $data_json;
	}

	/**
	 * @return array<string,string[]>
	 */
	protected function extract_multilingual_strings( Cpt_Settings $cpt_settings ): array {
		$labels         = $cpt_settings->get_multilingual_strings();
		$markup_strings = $this->extract_markup_multilingual_strings( $cpt_settings );

		$strings = array_merge_recursive( $labels, $markup_strings );

		foreach ( $strings as $domain => $domain_labels ) {
			$strings[ $domain ] = array_unique( $domain_labels );
		}

		return $strings;
	}

	protected function get_multilingual_strings_file_content( Cpt_Settings $cpt_settings ): string {
		$file_lines = array();

		foreach ( $this->extract_multilingual_strings( $cpt_settings ) as $text_domain => $labels ) {
			foreach ( $labels as $label ) {
				// to avoid breaking the PHP string.
				$label = str_replace( "'", '&#039;', $label );
				$label = str_replace( '"', '&quot;', $label );

				$file_lines[] = sprintf( "__('%s', '%s');", $label, $text_domain );
			}
		}

		return "<?php\n" .
				"// This file was generated automatically and contains instance labels for easy detection by multilingual tools.\n" .
				'// Note: any changes made to this file will be lost in the next update.' .
				"\n\n" .
				join( "\n", $file_lines );
	}

	protected function get_links_md_content( Cpt_Settings $cpt_settings ): string {
		return sprintf(
			'[Edit "%s" in WordPress](%s)',
			$cpt_settings->title,
			$cpt_settings->get_edit_post_link( 'redirect' )
		);
	}

	/**
	 * Files of the old format, used only if the JSON misses the related field.
	 *
	 * @return string[]
	 */
	public function get_fallback_fs_field_file_names(): array {
		$field_names = self::get_fallback_field_names();

		return array_keys( $field_names );
	}

	/**
	 * @param array<string,mixed> $fs_field_values
	 * @param array<string,mixed> $data_json
	 */
	public function set_fallback_fs_fields( Cpt_Settings $cpt_settings, array $fs_field_values, array $data_json ): void {
		$fallback_field_names = self::get_fallback_field_names();

		foreach ( $fallback_field_names as $field_file => $field_name ) {
			$field_value   = $fs_field_values[ $field_file ] ?? null;
			$json_key      = $cpt_settings::getAcfFieldName( $field_name );
			$is_applicable = is_string( $field_value ) && ! key_exists( $json_key, $data_json );

			if ( $is_applicable ) {
				// @phpstan-ignore-next-line
				$cpt_settings->{$field_name} = $field_value;
			}
		}
	}

	/**
	 * @return string[]
	 */
	public function get_fs_field_file_names( bool $is_without_auto_generated = false ): array {
		$template_integrations = $this->template_integration_storage->get_integrations();
		$file_names            = array(
			self::STYLE_CSS,
			self::STYLE_SCSS,
			self::DATA_JSON,
		);

		foreach ( $template_integrations as $template_integration ) {
			$file_names[] = 'default' . $template_integration->get_file_extension();
			$file_names[] = 'custom' . $template_integration->get_file_extension();
		}

		if ( ! $is_without_auto_generated ) {
			$file_names = array_merge(
				$file_names,
				array(
					self::MULTILINGUAL_PHP,
					self::LINKS_MD,
				)
			);
		}

		return $file_names;
	}

	/**
	 * @return array<string, string>
	 */
	public function get_fs_field_values(
		Cpt_Settings $cpt_settings,
		bool $is_bulk_refresh = false,
		bool $is_skip_auto_generated = false
	): array {
		// only links.md is needed for bulk refresh.
		if ( $is_bulk_refresh ) {
			return array(
				self::LINKS_MD => $this->get_links_md_content( $cpt_settings ),
			);
		}

		$auto_generated = array(
			self::MULTILINGUAL_PHP => $this->get_multilingual_strings_file_content( $cpt_settings ),
			self::LINKS_MD         => $this->get_links_md_content( $cpt_settings ),
		);

		$integration             = $this->template_integration_storage->resolve_integration( $cpt_settings->template_engine );
		$template_file_extension = $integration instanceof Template_Integration ?
			$integration->get_file_extension() :
			'';

		$std_fields = array(
			self::STYLE_CSS                      => $cpt_settings->css_code,
			self::DATA_JSON                      => $this->get_data_json( $cpt_settings ),
			'default' . $template_file_extension => $cpt_settings->markup,
			'custom' . $template_file_extension  => $cpt_settings->custom_markup,
		);

		if ( '' !== $cpt_settings->sass_code ) {
			$std_fields[ self::STYLE_SCSS ] = $cpt_settings->sass_code;
		}

		return false === $is_skip_auto_generated ?
			array_merge( $auto_generated, $std_fields ) :
			$std_fields;
	}

	/**
	 * @param array<string,mixed> $fs_field_values
	 */
	public function set_fs_fields( Cpt_Settings $cpt_settings, array $fs_field_values ): void {
		foreach ( $fs_field_values as $field_file => $field_value ) {
			// ignore complex field types.
			if ( false === is_string( $field_value ) &&
				false === is_numeric( $field_value ) ) {
				continue;
			}

			$this->set_fs_field( $cpt_settings, $field_file, (string) $field_value );
		}
	}

	public function set_fs_field( Cpt_Settings $cpt_settings, string $field_file, string $field_value ): void {
		$integration = $this->template_integration_storage->resolve_integration( $cpt_settings->template_engine );

		$is_template_field = fn( string $test_name ) => $integration instanceof Template_Integration &&
				$test_name . $integration->get_file_extension() === $field_file;

		if ( $is_template_field( 'default' ) ) {
			$cpt_settings->markup = $field_value;
		}

		if ( $is_template_field( 'custom' ) ) {
			$cpt_settings->custom_markup = $field_value;
		}

		switch ( $field_file ) {
			case self::STYLE_CSS:
				$cpt_settings->css_code = $field_value;
				break;
			case self::STYLE_SCSS:
				$cpt_settings->sass_code = $field_value;
				break;
		}
	}

	/**
	 * @return array<string,string> file name => field name
	 */
	public static function get_fallback_field_names(): array {
		return array(
			self::SCRIPT_JS => 'js_code',
		);
	}
}
