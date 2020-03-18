<?php
/**
 * View: Happening Now
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/happening-now/event/url.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 1.0.0
 *
 * @var WP_Post $event              The event post object with properties added by the `tribe_get_event` function.
 * @var Shortcode $shortcode_object Instance of the Shortcode that created this view.
 *
 * @see tribe_get_event() For the format of the event object.
 */

$event_url = get_post_meta( $event->ID, '_EventURL', true );
$url_title = $shortcode_object->get_argument( 'url_title', __( 'Event Website', 'tribe-ext-events-happening-now' ) );
$hide_url  = $shortcode_object->get_argument( 'hide_url', false );

if ( empty( $event_url ) || ! empty( $hide_url ) ) {
	return;
}
?>

<div class="tribe-events-c-small-cta tribe-common-b3 tribe-events-calendar-list__event-cost">
	<a
		href="<?php echo esc_url( $event_url ); ?>"
		class="tribe-events-c-small-cta__link tribe-common-cta tribe-common-cta--thin-alt"
	>
		<?php echo esc_html( $url_title ); ?>
	</a>
</div>
