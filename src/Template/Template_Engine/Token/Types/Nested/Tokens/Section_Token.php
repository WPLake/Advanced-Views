<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Tokens;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Html_Element;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Template_Token;

/**
 * A part of a nested token (a loop, a condition branch): the opening statement with the nodes as the content.
 * It has no closing part, as the next section or the end statement closes it.
 *
 * @phpstan-import-type Html_Node from Html_Element
 */
final class Section_Token implements Template_Token {
	private string $open_statement;
	/**
	 * @var Html_Node[]
	 */
	private array $nodes;

	/**
	 * @param Html_Node[] $nodes
	 */
	public function __construct( string $open_statement, array $nodes ) {
		$this->open_statement = $open_statement;
		$this->nodes          = $nodes;
	}

	public function get_open_statement(): string {
		return $this->open_statement;
	}

	/**
	 * @return Html_Node[]
	 */
	public function get_nodes(): array {
		return $this->nodes;
	}
}
