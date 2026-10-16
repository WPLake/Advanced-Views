<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Field_Value\WordPress\Media;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Field_Value\Core\Media\Image_Value;

final class WP_Image extends Image_Value {
	const ATTACHMENT_ID = 'attachment_id';

	protected int $attachment_id;
	protected string $image_size;

	public function __construct( int $attachment_id, string $image_size = 'full' ) {
		$this->attachment_id = $attachment_id;
		$this->image_size    = $image_size;
	}

	public function get_attachment_id(): int {
		return $this->attachment_id;
	}

	public function get_value(): string {
		return $this->get_image_data()[0];
	}

	public function get_full_size(): string {
		return (string) wp_get_attachment_image_url( $this->attachment_id, 'full' );
	}

	public function get_caption(): string {
		return (string) get_post_field( 'post_excerpt', $this->attachment_id );
	}

	public function get_description(): string {
		return (string) get_post_field( 'post_content', $this->attachment_id );
	}

	public function get_alt(): string {
		$alt = get_post_meta( $this->attachment_id, '_wp_attachment_image_alt', true );

		return is_string( $alt ) || is_numeric( $alt ) ?
			(string) $alt :
			'';
	}

	public function get_width(): string {
		return (string) $this->get_image_data()[1];
	}

	public function get_height(): string {
		return (string) $this->get_image_data()[2];
	}

	public function get_srcset(): string {
		return $this->get_responsive_attribute( 'wp_calculate_image_srcset' );
	}

	public function get_sizes(): string {
		return $this->get_responsive_attribute( 'wp_calculate_image_sizes' );
	}

	public function get_svg_content(): string {
		if ( false === strpos( $this->get_value(), '.svg' ) ) {
			return '';
		}

		$attached_file = (string) get_attached_file( $this->attachment_id );

		return '' !== $attached_file ?
			// @phpcs:ignore
			(string) file_get_contents( $attached_file ) :
			'';
	}

	public function get_properties(): array {
		return array_merge(
			parent::get_properties(),
			array(
				self::ATTACHMENT_ID => fn() => $this->get_attachment_id(),
			)
		);
	}

	/**
	 * @return array{0:string,1:int,2:int}
	 */
	protected function get_image_data(): array {
		$image_data = wp_get_attachment_image_src( $this->attachment_id, $this->image_size );

		return false === $image_data ?
			array( '', 0, 0 ) :
			array( (string) $image_data[0], (int) $image_data[1], (int) $image_data[2] );
	}

	/**
	 * @param callable(array<int,int>,string,array<mixed>,int):mixed $calculator
	 */
	protected function get_responsive_attribute( callable $calculator ): string {
		list( $src, $width, $height ) = $this->get_image_data();

		$image_meta = wp_get_attachment_metadata( $this->attachment_id );

		if ( '' === $src ||
			false === is_array( $image_meta ) ) {
			return '';
		}

		return (string) $calculator( array( $width, $height ), $src, $image_meta, $this->attachment_id );
	}
}
