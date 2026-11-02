<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Variable;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Escaping\Html_Escaper;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Statement_Token_Base;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Rendering\Token_Printer;

abstract class Variable_Token extends Statement_Token_Base {
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
	public function __construct( string $printer_class, Html_Escaper $escaper, string $name ) {
		parent::__construct( $printer_class, $escaper );

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
