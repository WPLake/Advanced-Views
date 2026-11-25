<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Layout_Settings;

class Source {
	/**
	 * @var int|string Post id or 'options', 'user_x', 'term_x'
	 */
	private $id;
	private bool $is_block;
	private int $user_id;
	private int $term_id;
	private int $comment_id;

	public function __construct() {
		$this->id         = '';
		$this->is_block   = false;
		$this->user_id    = 0;
		$this->term_id    = 0;
		$this->comment_id = 0;
	}

	public static function make_item( string $item_type, int $item_id ): self {
		$source = new self();

		$id_formats = array(
			Layout_Settings::ITEM_TYPE_USER    => 'user_%d',
			Layout_Settings::ITEM_TYPE_COMMENT => 'comment_%d',
			Layout_Settings::ITEM_TYPE_TERM    => 'term_%d',
			Layout_Settings::ITEM_TYPE_MENU    => 'term_%d',
		);
		$id_format  = $id_formats[ $item_type ] ?? '%d';
		$source_id  = sprintf( $id_format, $item_id );

		$source->set_id( $source_id );

		$id_setters = array(
			Layout_Settings::ITEM_TYPE_USER    => fn() => $source->set_user_id( $item_id ),
			Layout_Settings::ITEM_TYPE_COMMENT => fn() => $source->set_comment_id( $item_id ),
			Layout_Settings::ITEM_TYPE_TERM    => fn() => $source->set_term_id( $item_id ),
			Layout_Settings::ITEM_TYPE_MENU    => fn() => $source->set_term_id( $item_id ),
		);

		if ( key_exists( $item_type, $id_setters ) ) {
			$id_setters[ $item_type ]();
		}

		return $source;
	}

	public function is_options(): bool {
		return 'options' === $this->id;
	}

	/**
	 * @return int|string
	 */
	public function get_id() {
		return $this->id;
	}

	public function is_block(): bool {
		return $this->is_block;
	}

	public function get_user_id(): int {
		return $this->user_id;
	}

	public function get_comment_id(): int {
		return $this->comment_id;
	}

	public function get_term_id(): int {
		return $this->term_id;
	}

	// setters.

	/**
	 * @param int|string $id
	 */
	public function set_id( $id ): void {
		$this->id = $id;
	}

	public function set_term_id( int $term_id ): void {
		$this->term_id = $term_id;
	}

	public function set_is_block( bool $is_block ): void {
		$this->is_block = $is_block;
	}

	public function set_user_id( int $user_id ): void {
		$this->user_id = $user_id;
	}

	public function set_comment_id( int $comment_id ): void {
		$this->comment_id = $comment_id;
	}
}
