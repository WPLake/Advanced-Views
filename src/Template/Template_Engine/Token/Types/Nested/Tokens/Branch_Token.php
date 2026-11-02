<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Tokens;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Html_Element;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Block\Block_Token;

/**
 * A branch of a condition (if, elseif, else): the branch tag with its body as the content. It has no closing part,
 * as the next branch tag (or the end tag) closes it.
 *
 * @phpstan-import-type Html_Node from Html_Element
 */
final class Branch_Token implements Block_Token {
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
		$this->nodes     = $nodes;
	}

	public function get_open_statement(): string {
		return $this->open_statement;
	}

	public function get_close_statement(): string {
		return '';
	}

	/**
	 * @return Html_Node[]
	 */
	public function get_nodes(): array {
		return $this->nodes;
	}
}
