<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Template\Frontend_Library\Core\Script\Pattern;

defined( 'ABSPATH' ) || exit;

/**
 * Variable names.
 */
final class Script_Variables {
	/**
	 * The mount element: the Template_Pattern wrapper, or its first outer when there is no wrapper.
	 */
	public string $element = 'root';
	public string $options = 'options';
}
