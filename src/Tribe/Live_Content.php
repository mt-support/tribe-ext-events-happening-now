<?php
namespace Tribe\Extensions\EventsHappeningNow;

use Tribe__Utils__Array as Arr;
use Tribe__Date_Utils as Date_Utils;
use Tribe__Timezones as Timezones;

/**
 * Class Shortcode
 *
 * @since TBD
 *
 * @package Tribe\Extensions\EventsHappeningNow
 */
class Live_Content {
	/**
	 * Slug of the current shortcode.
	 *
	 * @since TBD
	 *
	 * @var   string
	 */
	protected $slug = 'tribe-event-live-content';

	/**
	 * Default arguments to be merged into final arguments of the shortcode.
	 *
	 * @since TBD
	 *
	 * @var   array
	 */
	protected $default_arguments = [
		'id'                => null,
		'content_extended'  => 'no',
		'start_time'        => false,
		'end_time'          => false,
	];

	/**
	 * Array of callbacks for arguments validation
	 *
	 * @since TBD
	 *
	 * @var   array
	 */
	protected $validate_arguments_map = [
		'id'               => [ self::class, 'validate_event' ],
		'content_extended' => [ self::class, 'validate_time_string' ],
		'start_time'       => [ self::class, 'validate_time_string' ],
		'end_time'         => [ self::class, 'validate_time_string' ],
	];

	/**
	 * Arguments of the current shortcode.
	 *
	 * @since TBD
	 *
	 * @var   array
	 */
	protected $arguments;

	/**
	 * Content of the current shortcode.
	 *
	 * @since TBD
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
	 * @since TBD
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

		$args = $this->get_arguments();

		$event = ! empty( $args['id'] ) ? tribe_get_event( $args['id'] ) : false;

		return $this->render_content( $event );
	}

	/**
	 * Render Content for an Event
	 *
	 * @param $event
	 *
	 * @return string
	 */
	public function render_content( $event ) {

		$times = $this->get_times( $event );

		// if no valid time is set.
		if ( empty( $times ) ) {
			return __( 'No Start Time is set for Live Content to appear.', 'tribe-ext-events-happening-now' );
		}

		$args = $this->get_arguments();

		// extend live content time line.
		if ( $args['content_extended'] ) {
			$times['end_time']->modify( $args['content_extended'] );
		}

		// Live Now.
		if ( $times['now'] > $times['start_time'] && $times['now'] < $times['end_time'] ) {
			return do_shortcode( $this->content );
		}

		return '';
	}

	/**
	 * Calculate times depending on the Post
	 *
	 * @param $post \WP_Post
	 *
	 * @return array
	 */
	public function get_times( $post ) {

		$args = $this->get_arguments();

		$times = [];

		// if start time is set then use that time overriding Event time.
		if ( ! empty( $args['start_time'] ) ) {
			//get wp timezone
			if ( function_exists( 'wp_timezone' ) ) {
				$timezone = wp_timezone();
			} else {
				$wp_timezone = Timezones::wp_timezone_string();

				if ( Timezones::is_utc_offset( $wp_timezone ) ) {
					$wp_timezone = Timezones::generate_timezone_string_from_utc_offset( $wp_timezone );
				}

				$timezone = new \DateTimeZone( $wp_timezone );
			}

			//if no end time is set then it should show always
			$end_time = ! empty( $args['end_time'] ) ? $args['end_time'] : '+1 year';

			$times['start_time'] = Date_Utils::build_date_object( $args['start_time'], $timezone );
			$times['end_time']   = Date_Utils::build_date_object( $end_time, $timezone );
			$times['now']        = Date_Utils::build_date_object( 'now', $timezone );

		} else if ( tribe_is_event( $post ) ) {
			$times['start_time'] = $post->dates->start_utc;
			$times['end_time']   = $post->dates->end_utc;
			$times['now']        = Date_Utils::build_date_object( 'now' );
		}

		return apply_filters( "tribe_ext_shortcode_{$this->get_registration_slug()}_calculated_time", $times, $post );
	}

	/**
	 * Configures the base variables for an instance of shortcode.
	 *
	 * @since TBD
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
	 * @since TBD
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
	 * @since TBD
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
	 * @since TBD
	 *
	 * @return string The shortcode slug.
	 */
	public function get_registration_slug() {
		return $this->slug;
	}

	/**
	 * Returns the array of callbacks for this shortcode's arguments.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public function get_validate_arguments_map() {
		/**
		 * Applies a filter to instance arguments validation callbacks.
		 *
		 * @since TBD
		 *
		 * @param  array  $validate_arguments_map   Current set of callbacks for arguments.
		 * @param  static $instance                 Which instance of shortcode we are dealing with.
		 */
		$validate_arguments_map = apply_filters( 'tribe_ext_shortcode_validate_arguments_map', $this->validate_arguments_map, $this );

		$registration_slug = $this->get_registration_slug();

		/**
		 * Applies a filter to instance arguments validation callbacks based on the registration slug of the shortcode.
		 *
		 * @since TBD
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
	 * @since TBD
	 *
	 * @return array
	 */
	public function     get_arguments() {
		/**
		 * Applies a filter to instance arguments.
		 *
		 * @since TBD
		 *
		 * @param  array  $arguments  Current set of arguments.
		 * @param  static $instance   Which instance of shortcode we are dealing with.
		 */
		$arguments = apply_filters( 'tribe_ext_shortcode_arguments', $this->arguments, $this );

		$registration_slug = $this->get_registration_slug();

		/**
		 * Applies a filter to instance arguments based on the registration slug of the shortcode.
		 *
		 * @since TBD
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
	 * @since TBD
	 *
	 * @param array|string  $index   Which index we indent to fetch from the arguments.
	 * @param array         $default Default value if it doesnt exist.
	 *
	 * @return mixed
	 */
	public function get_argument( $index, $default = null ) {
		$arguments = $this->get_arguments();
		$argument  = Arr::get( $arguments, $index, $default );

		/**
		 * Applies a filter to a specific shortcode argument, catch all for all shortcodes..
		 *
		 * @since TBD
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
		 * @since TBD
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
	 * @since TBD
	 *
	 * @return array
	 */
	public function get_default_arguments() {
		/**
		 * Applies a filter to instance default arguments.
		 *
		 * @since TBD
		 *
		 * @param  array  $default_arguments  Current set of default arguments.
		 * @param  static $instance           Which instance of shortcode we are dealing with.
		 */
		$default_arguments = apply_filters( 'tribe_ext_shortcode_default_arguments', $this->default_arguments, $this );

		$registration_slug = $this->get_registration_slug();

		/**
		 * Applies a filter to instance default arguments based on the registration slug of the shortcode.
		 *
		 * @since TBD
		 *
		 * @param  array  $default_arguments   Current set of default arguments.
		 * @param  static $instance            Which instance of shortcode we are dealing with.
		 */
		$default_arguments = apply_filters( "tribe_ext_shortcode_{$registration_slug}_default_arguments", $this->default_arguments, $this );

		return $default_arguments;
	}

	/**
	 * Validation of Null or Truthy values for Shortcode Attributes.
	 *
	 * @since TBD
	 *
	 * @param mixed $value Which value will be validated.
	 *
	 * @return bool|null   Allows Both Null and truthy values.
	 */
	public static function validate_null_or_truthy( $value = null ) {
		if ( null === $value || 'null' === $value ) {
			return null;
		}

		return tribe_is_truthy( $value );
	}

	/**
	 * Check if Post ID is set or set the current post id
	 *
	 * @param $id
	 *
	 * @since TBD
	 *
	 * @return false|int
	 */
	public static function validate_event( $id ) {
		return ( null === $id || 'null' === $id ) ? get_the_ID() : $id;
	}

	/**
	 * Check if provided extended time string is valid or not
	 *
	 * @param $str
	 *
	 * @return bool|string
	 */
	public static function validate_time_string( $str ) {
		return strtotime( $str ) ? $str : false;
	}
}