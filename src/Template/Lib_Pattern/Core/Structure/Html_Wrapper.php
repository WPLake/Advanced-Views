<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Lib_Pattern\Core\Structure;

defined( 'ABSPATH' ) || exit;

class Html_Wrapper {
	public string $tag;
	/**
	 * @var array<string,string>
	 */
	public array $attrs;
	/**
	 * @var array<string,array{field_id:string,item_key:string}>
	 */
	public array $variable_attrs;

	/**
	 * @param array<string,string> $attrs
	 * @param array<string,array{field_id:string,item_key:string}> $variable_attrs
	 */
	public function __construct( string $tag, array $attrs = array(), array $variable_attrs = array() ) {
		$this->tag            = $tag;
		$this->attrs          = $attrs;
		$this->variable_attrs = $variable_attrs;
	}
}
