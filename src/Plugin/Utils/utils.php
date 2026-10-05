<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Utils;

use Org\Wplake\Advanced_Views\Vendors\Psr\Container\ContainerInterface;
use Throwable;

defined( 'ABSPATH' ) || exit;

/**
 * @template ItemType
 *
 * @param array<int|string, ItemType> $items
 * @param callable(ItemType $item, int|string $key):array<int|string, mixed> $mapper
 *
 * @return mixed[]
 */
function flat_map( array $items, callable $mapper ): array {
	$chunks = array();

	foreach ( $items as $key => $item ) {
		$chunk = $mapper( $item, $key );

		$chunks = array_merge( $chunks, $chunk );
	}

	return $chunks;
}

/**
 * Replaces items by their class key (or by the class value, for int keys). Instances are replaced only by the key.
 *
 * @template T of object
 * @template K of array-key
 *
 * @param array<K, class-string<T>|T> $origin
 * @param array<class-string<T>, class-string<T>|T> $replacements old class => new class|instance
 *
 * @return array<K, class-string<T>|T>
 */
function swap_instances( array $origin, array $replacements ): array {
	$swapped = array();

	foreach ( $origin as $key => $item ) {
		$origin_class = is_string( $key ) ?
			$key :
			( is_string( $item ) ? $item : '' );

		$swapped[ $key ] = $replacements[ $origin_class ] ?? $item;
	}

	return $swapped;
}

/**
 * @template T of object
 *
 * @param array<int|class-string<T>, class-string<T>|T> $items
 * @param ContainerInterface $container
 *
 * @return T[]
 */
function resolve_instances( array $items, ContainerInterface $container ): array {
	return array_values(
		array_map(
			fn( $item ) => is_string( $item ) ?
				$container->get( $item ) :
				$item,
			$items
		)
	);
}

// int-safe str_repeat - as native throws an error if $count is negative.
function repeat_str( string $char, int $count ): string {
	return $count > 0 ?
		str_repeat( $char, $count ) :
		'';
}

/**
 * Executes snippets with the supports of top-file declarations - 'declare(strict_types=1)'.
 * Intercepts and ignores all the top-level echo statements.
 *
 * @param array<string,mixed> $__context
 * @param mixed $__error
 *
 * @return mixed
 */
function eval_snippet( string $__code, array $__context, &$__error ) {
	// @phpcs:ignore
	extract( $__context );

	$__code = trim( $__code );

	// eval snippets without the opening tag,
	// to ensure it works with 'declare(strict_types=1)'.
	if ( 0 === strpos( $__code, '<?php' ) ) {
		$__code = substr( $__code, 5 );
	}

	ob_start();

	try {
		// @phpcs:ignore
		$response = @eval( $__code );
	} catch ( Throwable $error ) {
		$response = null;
		$__error  = $error;
	} finally {
		ob_end_clean();
	}

	return $response;
}

/**
 * Executes templates containing PHP tags, with the output being printed.
 *
 * @param array<string,mixed> $__context
 * @param mixed $__error
 */
function eval_template( string $__code, array $__context, &$__error ): void {
	// @phpcs:ignore
	extract( $__context );

	try {
		// @phpcs:ignore
		@eval( '?>' .$__code );
	} catch ( Throwable $error ) {
		$__error = $error;
	}
}
