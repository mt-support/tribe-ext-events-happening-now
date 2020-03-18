<?php
/**
 * A view for rendering events that are happening now
 *
 * @package Tribe\Events\Views
 */

namespace Tribe\Extensions\EventsHappeningNow\Views;

use DateInterval;
use Tribe\Events\Views\V2\View;
use Tribe__Context as Context;
use Tribe__Date_Utils as Dates;

/**
 * Class Three_Day_List_View
 *
 * @package Tribe\Events\Views\V2\Views
 */
class Happening_Now_View extends View {
	/**
	 * A variable indicating whether this View is one site visitors will see or not.
	 *
	 * We have some "service" Views we use for debug and testing that we do not want visitors to see.
	 *
	 * @var bool The publicly visible flag.
	 */
	public static $publicly_visible = true;

	/**
	 * Returns the "pretty" name that will be visible in the View selector.
	 *
	 * @return string The "pretty" name that will be visible in the View selector.
	 */
	public function get_label() {
		return __( 'Events Happening Now', 'tribe-ext-events-happening-now' );
	}

	/**
	 * Returns the slug that will be used to try and find the templates (in our plugin, in themes, in other plugins and
	 * so on).
	 *
	 * @return string The slug that will be used to locate the View templates.
	 */
	public function get_template_slug() {
		return 'happening-now';
	}

	/**
	 * Overrides the base View method to set up template variables the way this view will need them.
	 *
	 * This method should contain all the logic required to provide the front-end templates with information.
	 * This is the point of contact between the site back-end and the site front-end; its data is filterable.
	 * Want a logic-less front-end template? Do it here.
	 * We're not filtering the variables here: the main View class will do that for us.
	 *
	 * @return array An associative array containing any value that will be available to the front-end template
	 *               at any level. The values we set here will be "global" and available to any template component/part
	 *               by using the (extracted) variable by the same name.
	 */
	protected function setup_template_vars() {
		/**
		 * The base View will fill in some common template variables for us like `events`, `today`, `now`
		 * and more.
		 */
		$default_template_vars = parent::setup_template_vars();

		$template_vars = wp_parse_args(
			[
				'events'       => $this->repository->all(),
				'events_count' => $this->repository->count(),
			],
			$default_template_vars
		);

		return $template_vars;
	}

	/**
	 * Sets up the repository, and/or the repository arguments, that should be used to fetch the events for the View.
	 *
	 * We do not need to filter the arguments here as the main View class will do that for us.
	 *
	 * @param Context|null $context The context of the View request. To the View this is the World. Everything the view
	 *                              needs to know about its render context... lives in the context.
	 *
	 * @return array The
	 */
	protected function setup_repository_args( Context $context = null ) {
		if ( null === $context ) {
			// If we're not explicitly provided a context, then let's use the global one.
			$context = tribe_context();
		}

		/*
		 * The main View class will populate the "usual suspects", repository arguments we need to consider in each View
		 * like keywords, page, posts-per-page.
		 */
		$repository_args = parent::setup_repository_args( $context );

		$now = Dates::build_date_object();

		$repository_args['starts_before'] = $now;
		$repository_args['ends_after']    = $now;

		// Finally set an ordering criteria.
		$repository_args['orderby'] = 'event_date';
		$repository_args['order']   = 'ASC';

		return $repository_args;
	}
}
