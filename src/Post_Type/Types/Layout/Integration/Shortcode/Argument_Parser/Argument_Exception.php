<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Integration\Shortcode\Argument_Parser;

defined( 'ABSPATH' ) || exit;

use RuntimeException;

/**
 * Thrown when the shortcode arguments can't be resolved, the message is shown to the user.
 */
final class Argument_Exception extends RuntimeException {
}
