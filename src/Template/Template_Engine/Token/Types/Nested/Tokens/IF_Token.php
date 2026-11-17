<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Tokens;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Html_Element;
use Org\Wplake\Advanced_Views\Template\Html\Raw_Html;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Template_Token_Base;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Nested\Nested_Token;
use Org\Wplake\Advanced_Views\Template\Template_Engine\Token\Types\Statement\Statement_Token;

/**
 * @phpstan-import-type Html_Node from Html_Element
 */
abstract class IF_Token extends Template_Token_Base implements Nested_Token {
	protected ?IF_Branch $if_branch = null;
	/**
	 * @var IF_Branch[]
	 */
	protected array $elseif_branches  = array();
	protected ?IF_Branch $else_branch = null;

	public function set_if_branch( IF_Branch $branch ): self {
		$this->if_branch = $branch;

		return $this;
	}

	public function set_elseif_branch( IF_Branch $branch ): self {
		$this->elseif_branches[] = $branch;

		return $this;
	}

	public function set_else_branch( IF_Branch $branch ): self {
		$this->else_branch = $branch;

		return $this;
	}

	/**
	 * Every branch is a block with its body as the content, followed by the end tag.
	 *
	 * @return Html_Node[]
	 */
	public function get_nodes(): array {
		$nodes = array();

		foreach ( $this->typed_branches() as $typed_branch ) {
			list( $type, $branch ) = $typed_branch;

			$branch_html = $this->get_branch_html( $type, $branch );

			$nodes[] = new Section_Token( $branch_html, $branch->body );
		}

		$end_html = $this->get_end_html();

		$nodes[] = new Raw_Html( $end_html );

		return $nodes;
	}

	abstract protected function get_branch_html( string $type, IF_Branch $branch ): string;

	abstract protected function get_end_html(): string;

	/**
	 * @return array<int,array{string,IF_Branch}>
	 */
	protected function typed_branches(): array {
		$typed_branches = array();

		if ( $this->if_branch instanceof IF_Branch ) {
			$typed_branches[] = array( 'if', $this->if_branch );
		}

		foreach ( $this->elseif_branches as $branch ) {
			$typed_branches[] = array( 'elseif', $branch );
		}

		if ( $this->else_branch instanceof IF_Branch ) {
			$typed_branches[] = array( 'else', $this->else_branch );
		}

		return $typed_branches;
	}

	protected static function bare_condition( IF_Branch $branch ): string {
		return $branch->condition instanceof Statement_Token ?
			$branch->condition->get_token() :
			'';
	}
}
