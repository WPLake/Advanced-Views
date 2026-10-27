<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Plugin\Utils;

use Org\Wplake\Advanced_Views\Template\Html\Html_Renderer;
use Org\Wplake\Advanced_Views\Template\Html\Html_Tag;
use function Org\Wplake\Advanced_Views\Template\Html\tag;

defined( 'ABSPATH' ) || exit;

class Profiler {
	const SOURCE_NETWORK = '_network';

	/**
	 * @var array<string,array{time_sec:float,calls:int}>
	 */
	private static array $classes_total_usage = array();
	/**
	 * @var array<string,array<string,array{time_sec:float,calls:int}>>
	 */
	private static array $class_hooks_usage = array();
	/**
	 * @var array{estimated_time_sec:float,calls:int}
	 */
	private static array $total_usage        = array(
		'calls'              => 0,
		'estimated_time_sec' => 0,
	);
	private static float $plugin_loaded_sec  = 0;
	private static ?bool $is_profiler_active = null;

	protected Html_Renderer $html_renderer;

	public function __construct( Html_Renderer $html_renderer ) {
		$this->html_renderer = $html_renderer;
	}

	public static function get_callback( string $source, string $hook_name, callable $callback ): callable {
		return self::is_active() ?
			fn( ...$args ) => self::execute_callback( $source, $hook_name, $callback, $args ) :
			$callback;
	}

	public function loaded( float $start_timestamp ): void {
		if ( self::is_active() ) {
			self::$plugin_loaded_sec = microtime( true ) - $start_timestamp;
			add_action( 'shutdown', array( $this, 'print_report' ) );
		}
	}

	public function print_report(): void {
		$classes_total_usage = self::get_classes_total_usage();
		$total_usage         = self::get_total_usage();
		$classes_amount      = count( $classes_total_usage );
		$classes_prefix      = sprintf( "%d\n", $classes_amount );

		$tags      = tag( 'div' )
			->attributes( array(
				'style' => array(
					'max-width' => '1000px',
					'margin'    => '0 auto',
					'padding'   => '50px',
					'border'    => '2px solid gray',
				),
			) )
			->content(
				self::pre_chain( $total_usage ),
				tag( 'hr' ),
				self::pre_chain( $classes_total_usage, $classes_prefix ),
				tag( 'hr' ),
				self::pre_chain( self::$class_hooks_usage )
			);

		$this->html_renderer->print_html( $tags );
	}

	/**
	 * @return array<string,mixed>
	 */
	private static function get_total_usage(): array {
		return array_merge(
			array(
				'plugin_loaded_sec' => self::$plugin_loaded_sec,
			),
			self::$total_usage,
		);
	}

	/**
	 * @return array<string,mixed>
	 */
	private static function get_classes_total_usage(): array {
		$classes_total_usage = array();

		foreach ( self::$classes_total_usage as $class => $class_total_usage ) {
			$hooks = self::$class_hooks_usage[ $class ] ?? array();

			$classes_total_usage[ $class ] = array_merge(
				$class_total_usage,
				array(
					'hooks' => array_keys( $hooks ),
				)
			);
		}

		uasort(
			$classes_total_usage,
			fn( array $first, array $second ) =>
				$second['time_sec'] <=> $first['time_sec']
		);

		return $classes_total_usage;
	}

	private static function is_active(): bool {
		if ( null === self::$is_profiler_active ) {
			self::$is_profiler_active = defined( 'AVF_PROFILER' ) &&
			                            // @phpcs:ignore
			                            isset( $_GET['_avf_profiler'] );
		}

		return self::$is_profiler_active;
	}

	/**
	 * @param array<int,mixed> $args
	 *
	 * @return mixed
	 */
	private static function execute_callback( string $source, string $hook_name, callable $callback, array $args ) {
		$start_at = microtime( true );

		$result = call_user_func_array( $callback, $args );

		$execution_time_sec = microtime( true ) - $start_at;

		self::record_call( $source, $hook_name, $execution_time_sec );

		return $result;
	}

	private static function record_call( string $source, string $hook_name, float $execution_time_sec ): void {
		self::$classes_total_usage[ $source ]             ??= array(
			'time_sec' => 0,
			'calls'    => 0,
			'hooks'    => array(),
		);
		self::$class_hooks_usage[ $source ]               ??= array();
		self::$class_hooks_usage[ $source ][ $hook_name ] ??= array(
			'time_sec' => 0,
			'calls'    => 0,
		);

		self::$total_usage['calls'] += 1;
		// Estimated, as we can't guarantee that the current execution isn't a child to another one.
		self::$total_usage['estimated_time_sec'] += $execution_time_sec;

		self::$classes_total_usage[ $source ]['calls']    += 1;
		self::$classes_total_usage[ $source ]['time_sec'] += $execution_time_sec;

		self::$class_hooks_usage[ $source ][ $hook_name ]['calls']    += 1;
		self::$class_hooks_usage[ $source ][ $hook_name ]['time_sec'] += $execution_time_sec;
	}

	/**
	 * @param mixed $data
	 */
	protected static function pre_chain( $data, string $prefix = '' ): Html_Tag {

		return tag( 'pre' )
			->content(
				fn() => self::print_data( $data, $prefix )
			);
	}

	/**
	 * @param mixed $data
	 */
	protected static function print_data( $data, string $prefix ): void {
		echo esc_html( $prefix );
		// @phpcs:ignore
		print_r( $data );
	}
}
