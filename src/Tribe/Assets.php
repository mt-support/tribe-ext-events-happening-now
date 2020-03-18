<?php
/**
 * Handles registering all Assets for the Events Happening Now
 *
 * To remove a Assets:
 * tribe( 'assets' )->remove( 'asset-name' );
 *
 * @since 1.0.0
 *
 * @package Tribe\Extensions\EventsHappeningNow
 */
namespace Tribe\Extensions\EventsHappeningNow;

use Tribe__Events__Main as Plugin;
use Tribe__Events__Templates;

/**
 * Register
 *
 * @since 1.0.0
 *
 * @package Tribe\Extensions\EventsHappeningNow
 */
class Assets extends \tad_DI52_ServiceProvider {

	/**
	 * Key for this group of assets.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public static $group_key = 'events-happening-now';

	/**
	 * Caches the result of the `should_enqueue_frontend` check.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	protected $should_enqueue_frontend;

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		$plugin = Plugin::instance();

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-skeleton',
			'views-skeleton.css',
			[
				'tribe-common-skeleton-style',
				'tribe-events-views-v2-bootstrap-datepicker-styles',
				'tribe-tooltipster-css',
			],
			'wp_enqueue_scripts',
			[
				'priority'     => 10,
				'conditionals' => [ $this, 'should_enqueue_frontend' ],
				'groups'       => [ static::$group_key ],
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-full',
			'views-full.css',
			[
				'tribe-common-full-style',
				'tribe-events-views-v2-skeleton',
			],
			'wp_enqueue_scripts',
			[
				'priority'     => 10,
				'conditionals' => [
					'operator' => 'AND',
					[ $this, 'should_enqueue_frontend' ],
					[ $this, 'should_enqueue_full_styles' ],
				],
				'groups'       => [ static::$group_key ],
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-tooltip',
			'views/tooltip.js',
			[
				'jquery',
				'tribe-common',
				'tribe-tooltipster',
			],
			null,
			[
				'priority' => 10,
			]
		);

		tribe_asset(
			$plugin,
			'tribe-events-views-v2-breakpoints',
			'views/breakpoints.js',
			[
				'jquery',
				'tribe-common',
			],
			'wp_enqueue_scripts',
			[
				'priority'     => 10,
				'conditionals' => [ $this, 'should_enqueue_frontend' ],
				'groups'       => [ static::$group_key ],
				'in_footer'    => false,
			]
		);

		$overrides_stylesheet = Tribe__Events__Templates::locate_stylesheet( 'tribe-events/tribe-events-happening-now.css' );

		if ( ! empty( $overrides_stylesheet ) ) {
			tribe_asset(
				$plugin,
				'tribe-events-happening-now-override-style',
				$overrides_stylesheet,
				[
					'tribe-common-full-style',
					'tribe-events-views-v2-skeleton',
				],
				'wp_enqueue_scripts',
				[
					'priority'     => 10,
					'conditionals' => [ $this, 'should_enqueue_frontend' ],
					'groups'       => [ static::$group_key ],
				]
			);
		}
	}

	/**
	 * Checks if we should enqueue frontend assets for the V2 views.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function should_enqueue_frontend() {
		if ( null !== $this->should_enqueue_frontend ) {
			return $this->should_enqueue_frontend;
		}

		$should_enqueue = tribe( Template_Bootstrap::class )->should_load();

		/**
		 * Allow filtering of where the base Frontend Assets will be loaded.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $should_enqueue
		 */
		$should_enqueue =  apply_filters( 'tribe_events_happening_now_assets_should_enqueue_frontend', $should_enqueue );

		$this->should_enqueue_frontend = $should_enqueue;

		return $should_enqueue;
	}


	/**
	 * Checks if we are using skeleton setting for Style.
	 *
	 * @since  1.0.0
	 *
	 * @return bool
	 */
	public function is_skeleton_style() {
		$style_option = tribe_get_option( 'stylesheetOption', 'tribe' );
		return 'skeleton' === $style_option;
	}

	/**
	 * Verifies if we dont have skeleton active, which will trigger true for the two other possible options.
	 * Options:
	 * - `full` - Deprecated
	 * - `tribe`  - All styles load
	 *
	 * @since  1.0.0
	 *
	 * @return bool
	 */
	public function should_enqueue_full_styles() {
		$should_enqueue = ! $this->is_skeleton_style();

		/**
		 * Allow filtering of where the base Frontend Assets will be loaded.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $is_skeleton_style
		 */
		return apply_filters( 'tribe_events_happening_now_assets_should_enqueue_full_styles', $should_enqueue );
	}
}
