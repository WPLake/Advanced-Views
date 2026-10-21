<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Generation\Core;

use Org\Wplake\Advanced_Views\Template\Html\Htmlable\Htmlable;

defined( 'ABSPATH' ) || exit;

interface Template_Token extends Htmlable {
	/**
	 * Prints the token as a complete template statement: output (escaped by the engine) for values,
	 * or the statement itself for loops, conditions, assignments and comments.
	 */
	public function display(): void;

	/**
	 * Same as display(), but the output isn't escaped by the engine. Statements print the same as display().
	 */
	public function raw_display(): void;

	/**
	 * Prints the bare token, as it appears inside other tokens (e.g. a variable name without the output tags).
	 */
	public function print_token(): void;
}
