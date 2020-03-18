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

use Tribe\Events\Views\V2\View;
use Tribe\Extensions\EventsHappeningNow\Views\Happening_Now_View;
use Tribe__Template;

/**
 * Class Hooks
 *
 * @since 1.0.0
 *
 * @package Tribe\Extensions\EventsHappeningNow
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
		add_action( 'init', [ $this, 'action_add_shortcodes' ], 20 );
	}

	/**
	 * Adds the filters required by each Views v2 component.
	 *
	 * @since 1.0.0
	 */
	protected function add_filters() {
		add_filter( 'tribe_events_views', [ $this, 'filter_events_views' ] );
		add_filter( 'tribe_template_origin_namespace_map', [ $this, 'filter_add_template_origin_namespace' ], 15, 3 );
		add_filter( 'tribe_template_path_list', [ $this, 'filter_template_path_list' ], 15, 2 );
	}

	/**
	 * Adds the new shortcodes, this normally will trigger on `init@P20` due to how we the
	 * v1 is added on `init@P10` and we remove them on `init@P15`.
	 *
	 * It's important to leave gaps on priority for better injection.
	 *
	 * @since 4.7.5
	 */
	public function action_add_shortcodes() {
		$this->container->make( Shortcode_Manager::class )->add_shortcodes();
	}

	/**
	 * Filters the available Views to add the ones implemented in PRO.
	 *
	 * @since 1.0.0
	 *
	 * @param array $views An array of available Views.
	 *
	 * @return array The array of available views, including the PRO ones.
	 */
	public function filter_events_views( array $views = [] ) {

		$views['happening-now'] = Happening_Now_View::class;

		return $views;
	}

	/**
	 * Includes Pro into the path namespace mapping, allowing for a better namespacing when loading files.
	 *
	 * @since 1.0.0
	 *
	 * @param array            $namespace_map Indexed array containing the namespace as the key and path to `strpos`.
	 * @param string           $path          Path we will do the `strpos` to validate a given namespace.
	 * @param Tribe__Template  $template      Current instance of the template class.
	 *
	 * @return array  Namespace map after adding Pro to the list.
	 */
	public function filter_add_template_origin_namespace( $namespace_map, $path, Tribe__Template $template ) {
		$namespace_map['happening-now'] = Main::PATH;
		return $namespace_map;
	}

	/**
	 * Filters the list of folders TEC will look up to find templates to add the ones defined by PRO.
	 *
	 * @since 1.0.0
	 *
	 * @param array           $folders  The current list of folders that will be searched template files.
	 * @param Tribe__Template $template Which template instance we are dealing with.
	 *
	 * @return array The filtered list of folders that will be searched for the templates.
	 */
	public function filter_template_path_list( array $folders = [], Tribe__Template $template ) {
		$path = (array) rtrim( Main::PATH, '/' );

		// Pick up if the folder needs to be added to the public template path.
		$folder = [ 'src/views' ];

		if ( ! empty( $folder ) ) {
			$path = array_merge( $path, $folder );
		}

		$folders['happening-now'] = [
			'id'        => 'happening-now',
			'namespace' => 'happening-now',
			'priority'  => 10,
			'path'      => implode( DIRECTORY_SEPARATOR, $path ),
		];

		return $folders;
	}
}
