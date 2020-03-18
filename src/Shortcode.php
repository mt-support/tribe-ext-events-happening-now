<?php
namespace Tribe\Extensions\EventsHappeningNow;

use Tribe\Events\Pro\Views\V2\Assets as Pro_Assets;
use Tribe\Events\Pro\Views\V2\Shortcodes\Tribe_Events;
use Tribe\Events\Views\V2\Assets as Event_Assets;
use Tribe\Events\Views\V2\Manager as Views_Manager;
use Tribe\Events\Views\V2\Theme_Compatibility;
use Tribe\Events\Views\V2\View;
use Tribe\Events\Views\V2\View_Interface;
use Tribe\Utils\Element_Classes;
use Tribe__Context as Context;
use Tribe__Utils__Array as Arr;

/**
 * Class Shortcode
 *
 * @since 1.0.0
 *
 * @package Tribe\Extensions\EventsHappeningNow
 */
class Shortcode {
	/**
	 * Slug of the current shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @var   string
	 */
	protected $slug = 'tribe-happening-now';

	/**
	 * Default arguments to be merged into final arguments of the shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @var   array
	 */
	protected $default_arguments = [
		'id'                => null,
		'quantity'          => -1,
		'all_day'           => true,
	];


	/**
	 * Array of callbacks for arguments validation
	 *
	 * @since 1.0.0
	 *
	 * @var   array
	 */
	protected $validate_arguments_map = [
		'all_day' => 'tribe_is_truthy',
	];

	/**
	 * Arguments of the current shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @var   array
	 */
	protected $arguments;

	/**
	 * Content of the current shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @var   string
	 */
	protected $content;

	/**
	 * {@inheritDoc}
	 */
	/**
	 * Returns a shortcode HTML code.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_html() {
		$context = tribe_context();

		/**
		 * On blocks editor shortcodes are being rendered in the screen which for some unknown reason makes the admin
		 * URL soft redirect (browser history only) to the front-end view URL of that shortcode.
		 *
		 * @see TEC-3157
		 */
		if ( is_admin() && ! $context->doing_ajax() ) {
			return '';
		}

		// Before anything happens we set a DB ID and value for this shortcode entry.
		$this->set_database_params();

		// Modifies the Context for the shortcode params.
		$context   = $this->alter_context( $context );

		// Fetches if we have a specific view are building.
		$view_slug = 'happening-now';

		// Make sure to enqueue assets.
		tribe_asset_enqueue_group( Event_Assets::$group_key );

		// Toggle the shortcode required modifications.
		$this->toggle_view_hooks( true );

		// Setup the view instance.
		$view = View::make( $view_slug, $context );

		// Setup wether this view should manage url or not.
		$view->get_template()->set( 'should_manage_url', false );

		$theme_compatiblity = tribe( Theme_Compatibility::class );

		$html = '';

		if ( $theme_compatiblity->is_compatibility_required() ) {
			$classes = $theme_compatiblity->get_body_classes();
			$element_classes = new Element_Classes( $classes );
			$html .= '<div ' . $element_classes->get_attribute() . '>';
		}

		$html .= $view->get_html();

		if ( $theme_compatiblity->is_compatibility_required() ) {
			$html .= '</div>';
		}

		// Toggle the shortcode required modifications.
		$this->toggle_view_hooks( false );

		return $html;
	}

	/**
	 * Alters the shortcode context with its arguments.
	 *
	 * @since  1.0.0
	 *
	 * @param Context $context Context we will use to build the view.
	 *
	 * @return Context Context after shortcodes changes.
	 */
	public function alter_context( Context $context, array $arguments = [] ) {
		$shortcode_id = $context->get( 'id' );
		if ( empty( $arguments ) ) {
			$arguments = $this->get_arguments();
			$shortcode_id = $this->get_id();
		}

		$alter_context = $this->args_to_context( $arguments, $context );

		// The View will consume this information on initial state.
		$alter_context['shortcode'] = $shortcode_id;
		$alter_context['id']        = $shortcode_id;

		$context = $context->alter( $alter_context );

		return $context;
	}

	/**
	 * Based on the either a argument "id" of the shortcode definition
	 * or the 8 first characters of the hashed version of a string serialization
	 * of the params sent to the shortcode we will create/get an ID for this
	 * instance of the tribe_events shortcode
	 *
	 * @since  1.0.0
	 *
	 * @return string The shortcode unique(ish) identifier.
	 */
	public function get_id() {
		$arguments = $this->get_arguments();

		// In case we have the ID argument we just return that.
		if ( ! empty( $arguments['id'] ) ) {
			return $arguments['id'];
		}

		ksort( $arguments );

		/*
		 * Generate a string id based on the arguments used to setup the shortcode.
		 * Note that arguments are sorted to catch substantially same shortcode w. diff. order argument.
		 */
		$hash = substr( md5( maybe_serialize( $arguments ) ), 0, 8 );

		return $hash;
	}

	/**
	 * Filters the View repository args to add the ones required by shortcodes to work.
	 *
	 * @since  1.0.0
	 *
	 *
	 * @param array           $repository_args An array of repository arguments that will be set for all Views.
	 * @param Context $context         The current render context object.
	 *
	 * @return array          Repository arguments after shortcode args added.
	 */
	public function filter_view_repository_args( $repository_args, $context ) {
		if ( ! $context instanceof Context ) {
			return $repository_args;
		}

		$shortcode_id = $context->get( 'shortcode' ,false );

		if ( false === $shortcode_id || $context->doing_php_initial_state() ) {
			return $repository_args;
		}

		$shortcode_args = $this->get_database_arguments( $shortcode_id );

		$repository_args = $this->args_to_repository( (array) $repository_args, (array) $shortcode_args, $context );

		return $repository_args;
	}

	/**
	 * Translates shortcode arguments to their Context argument counterpart.
	 *
	 * @since  1.0.0
	 *
	 * @param array   $arguments The shortcode arguments to translate.
	 * @param Context $context The request context.
	 *
	 * @return array The translated shortcode arguments.
	 */
	protected function args_to_context( array $arguments, Context $context ) {
		$context_args = [];

		$category_input = Arr::get_first_set( $arguments, [ 'cat', 'category' ], false );

		if ( ! empty( $category_input ) ) {
			$context_args['event_category'] = Arr::list_to_array( $category_input );
		}

		if ( isset( $arguments['all_day'] ) ) {
			$context_args['all_day'] = tribe_is_truthy( $arguments['all_day'] );
		}

		if ( isset( $arguments['quantity'] ) ) {
			$context_args['posts_per_page'] = (int) $arguments['quantity'];
		}

		return $context_args;
	}

	/**
	 * Translates shortcode arguments to their Repository argument counterpart.
	 *
	 * @since  1.0.0
	 *
	 * @param array    $repository_args  The current repository arguments.
	 * @param array    $arguments        The shortcode arguments to translate.
	 * @param Context  $context          The shortcode arguments to translate.
	 *
	 * @return array The translated shortcode arguments.
	 */
	public function args_to_repository( array $repository_args, array $arguments, $context ) {
		$category_input = Arr::get_first_set( $arguments, [ 'cat', 'category' ], false );

		if ( ! empty( $category_input ) ) {
			$repository_args['event_category'] = Arr::list_to_array( $category_input );
		}

		if ( isset( $arguments['all_day'] ) ) {
			$repository_args['all_day'] = tribe_is_truthy( $arguments['all_day'] );
		}

		if ( isset( $arguments['quantity'] ) ) {
			$repository_args['posts_per_page'] = (int) $arguments['quantity'];
		}

		return $repository_args;
	}

	/**
	 * Filters the View HTML classes to add some related to PRO features.
	 *
	 * @since 5.0.0
	 *
	 * @param array<string>  $html_classes The current View HTML classes.
	 * @param string         $slug         The View registered slug.
	 * @param View_Interface $view         The View currently rendering.
	 *
	 * @return array<string> The filtered HTML classes.
	 */
	public function filter_view_html_classes( $html_classes, $slug, $view ) {
		$context = $view->get_context();

		if ( ! $context instanceof Context ) {
			return $html_classes;
		}

		if ( $shortcode = $context->get( 'shortcode', false ) ) {
			$html_classes[] = 'tribe-events-view--shortcode';
			$html_classes[] = 'tribe-events-view--shortcode-' . $shortcode;
		}

		return $html_classes;
	}

	/**
	 * Filters the View data attributes to add some related to PRO features.
	 *
	 * @since  1.0.0
	 *
	 * @param array<string,string> $data The current View data attributes classes.
	 * @param string               $slug The View registered slug.
	 * @param View_Interface       $view The View currently rendering.
	 *
	 * @return array<string,string> The filtered data attributes.
	 */
	public function filter_view_data( $data, $slug, $view ) {
		$context = $view->get_context();

		if ( ! $context instanceof Context ) {
			return $data;
		}

		if ( $shortcode = $context->get( 'shortcode', false ) ) {
			$data['shortcode'] = $shortcode;
		}

		return $data;
	}

	/**
	 * Configures the base variables for an instance of shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $arguments Set of arguments passed to the Shortcode at hand.
	 * @param string $content   Contents passed to the shortcode, inside of the open and close brackets.
	 *
	 * @return void
	 */
	public function setup( $arguments, $content ) {
		$this->arguments = $this->parse_arguments( $arguments );
		$this->content   = $content;
	}

	/**
	 * Returns the arguments for the shortcode parsed correctly with defaults applied.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $arguments Set of arguments passed to the Shortcode at hand.
	 *
	 * @return array
	 */
	public function parse_arguments( $arguments ) {
		$arguments = shortcode_atts( $this->get_default_arguments(), $arguments, $this->slug );
		return $this->validate_arguments( $arguments );
	}

	/**
	 * Returns the array of arguments for this shortcode after applying the validation callbacks.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $arguments Set of arguments passed to the Shortcode at hand.
	 *
	 * @return array
	 */
	public function validate_arguments( $arguments ) {
		$validate_arguments_map = $this->get_validate_arguments_map();
		foreach ( $validate_arguments_map as $key => $callback ) {
			$arguments[ $key ] = $callback( isset( $arguments[ $key ] ) ? $arguments[ $key ] : null );
		}

		return $arguments;
	}

	/**
	 * Returns the shortcode slug.
	 *
	 * The slug should be the one that will allow the shortcode to be built by the shortcode class by slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string The shortcode slug.
	 */
	public function get_registration_slug() {
		return $this->slug;
	}

	/**
	 * Returns the array of callbacks for this shortcode's arguments.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_validate_arguments_map() {
		/**
		 * Applies a filter to instance arguments validation callbacks.
		 *
		 * @since 1.0.0
		 *
		 * @param  array  $validate_arguments_map   Current set of callbacks for arguments.
		 * @param  static $instance                 Which instance of shortcode we are dealing with.
		 */
		$validate_arguments_map = apply_filters( 'tribe_ext_shortcode_validate_arguments_map', $this->validate_arguments_map, $this );

		$registration_slug = $this->get_registration_slug();

		/**
		 * Applies a filter to instance arguments validation callbacks based on the registration slug of the shortcode.
		 *
		 * @since 1.0.0
		 *
		 * @param  array  $validate_arguments_map   Current set of callbacks for arguments.
		 * @param  static $instance                 Which instance of shortcode we are dealing with.
		 */
		$validate_arguments_map = apply_filters( "tribe_ext_shortcode_{$registration_slug}_validate_arguments_map", $validate_arguments_map, $this );

		return $validate_arguments_map;
	}

	/**
	 * Returns a shortcode arguments after been parsed.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_arguments() {
		/**
		 * Applies a filter to instance arguments.
		 *
		 * @since 1.0.0
		 *
		 * @param  array  $arguments  Current set of arguments.
		 * @param  static $instance   Which instance of shortcode we are dealing with.
		 */
		$arguments = apply_filters( 'tribe_ext_shortcode_arguments', $this->arguments, $this );

		$registration_slug = $this->get_registration_slug();

		/**
		 * Applies a filter to instance arguments based on the registration slug of the shortcode.
		 *
		 * @since 1.0.0
		 *
		 * @param  array  $arguments   Current set of arguments.
		 * @param  static $instance    Which instance of shortcode we are dealing with.
		 */
		$arguments = apply_filters( "tribe_ext_shortcode_{$registration_slug}_arguments", $arguments, $this );

		return $arguments;
	}

	/**
	 * Returns a shortcode argument after been parsed.
	 *
	 * @uses  Arr::get For index fetching and Default.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $index   Which index we indent to fetch from the arguments.
	 * @param array  $default Default value if it doesnt exist.
	 *
	 * @return array
	 */
	public function get_argument( $index, $default = null ) {
		$arguments = $this->get_arguments();
		$argument  = Arr::get( $arguments, $index, $default );

		/**
		 * Applies a filter to a specific shortcode argument, catch all for all shortcodes..
		 *
		 * @since 1.0.0
		 *
		 * @param  mixed  $argument   The argument.
		 * @param  array  $index      Which index we indent to fetch from the arguments.
		 * @param  array  $default    Default value if it doesnt exist.
		 * @param  static $instance   Which instance of shortcode we are dealing with.
		 */
		$argument = apply_filters( 'tribe_ext_shortcode_argument', $argument, $index, $default, $this );

		$registration_slug = $this->get_registration_slug();

		/**
		 * Applies a filter to a specific shortcode argument, to a particular registration slug.
		 *
		 * @since 1.0.0
		 *
		 * @param  mixed  $argument   The argument value.
		 * @param  array  $index      Which index we indent to fetch from the arguments.
		 * @param  array  $default    Default value if it doesnt exist.
		 * @param  static $instance   Which instance of shortcode we are dealing with.
		 */
		$argument = apply_filters( "tribe_ext_shortcode_{$registration_slug}_argument", $argument, $index, $default, $this );

		return $argument;
	}

	/**
	 * Returns a shortcode default arguments.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_default_arguments() {
		/**
		 * Applies a filter to instance default arguments.
		 *
		 * @since 1.0.0
		 *
		 * @param  array  $default_arguments  Current set of default arguments.
		 * @param  static $instance           Which instance of shortcode we are dealing with.
		 */
		$default_arguments = apply_filters( 'tribe_ext_shortcode_default_arguments', $this->default_arguments, $this );

		$registration_slug = $this->get_registration_slug();

		/**
		 * Applies a filter to instance default arguments based on the registration slug of the shortcode.
		 *
		 * @since 1.0.0
		 *
		 * @param  array  $default_arguments   Current set of default arguments.
		 * @param  static $instance            Which instance of shortcode we are dealing with.
		 */
		$default_arguments = apply_filters( "tribe_ext_shortcode_{$registration_slug}_default_arguments", $this->default_arguments, $this );

		return $default_arguments;
	}
}