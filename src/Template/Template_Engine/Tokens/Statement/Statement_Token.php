<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Template_Engine\Tokens\Statement;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Template\Html\Html_Element;

/**
 * A single-line token, usable both as a node and as an attribute value. Has no nested nodes.
 */
interface Statement_Token extends Html_Element {
	/**
	 * The complete statement, as it appears in the template (output tags included for values).
	 */
	public function get_statement(): string;

	/**
	 * The bare form, used only when nested into other tokens (e.g. a variable name without the output tags).
	 */
	public function get_token(): string;

	/**
	 * The complete statement, whose output isn't escaped by the engine.
	 */
	public function get_raw_statement(): string;
}
