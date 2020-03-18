<?php
/**
 * Handles hooking all the actions and filters used by the module.
 *
 * To remove a filter:
 * remove_filter( 'some_filter', [ tribe( Tribe\Extensions\EventsHappeningNow\Hooks::class ), 'some_filtering_method' ] );
 * remove_filter( 'some_filter', [ tribe( 'events-happening-now.views.filters' ), 'some_filtering_method' ] );
 *
 * To remove an action:
 * remove_action( 'some_action', [ tribe( Tribe\Extensions\EventsHappeningNow\Hooks::class ), 'some_method' ] );
 * remove_action( 'some_action', [ tribe( 'events-happening-now.views.hooks' ), 'some_method' ] );
 *
 * @since 1.0.0
 *
 * @package Tribe\Extensions\EventsHappeningNow
 */

namespace Tribe\Extensions\EventsHappeningNow;

/**
 * Class Hooks
 *
 * @since 1.0.0
 *
 * @package Tribe\Events\Views\V2
 */
class Hooks extends \tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * Adds the actions required by each Views v2 component.
	 *
	 * @since 1.0.0
	 */
	protected function add_actions() {
	}

	/**
	 * Adds the filters required by each Views v2 component.
	 *
	 * @since 1.0.0
	 */
	protected function add_filters() {
	}
}
