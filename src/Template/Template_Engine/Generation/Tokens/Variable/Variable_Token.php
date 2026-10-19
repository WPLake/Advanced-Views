<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Tokens\Variable;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Token_Printer;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core\Template_Token_Base;

abstract class Variable_Token extends Template_Token_Base {
	const ITEM_PATH_SEPARATOR = '.';

	protected string $name;
	/**
	 * @var string[]
	 */
	protected array $item_path = array();
	protected bool $is_object  = false;

	/**
	 * @param class-string<Token_Printer> $printer_class
	 */
	public function __construct( string $printer_class, string $name ) {
		parent::__construct( $printer_class );

		$this->set_name( $name );
	}

	public function set_name( string $name ): self {
		$ids        = explode( self::ITEM_PATH_SEPARATOR, $name );
		$ids_length = count( $ids );

		$this->name = $ids[0];

		for ( $i = 1; $i < $ids_length; $i++ ) {
			$this->add_item_path( $ids[ $i ] );
		}

		return $this;
	}

	public function get_name(): string {
		return $this->name;
	}

	public function add_item_path( string $item_path ): self {
		$this->item_path[] = $item_path;

		return $this;
	}
}
