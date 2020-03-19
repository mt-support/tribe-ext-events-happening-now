<?php
/**
 * View: Happening Now
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/happening-now/event.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTICLE_LINK_HERE}
 *
 * @version 1.0.0
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */

$container_classes = [
	'tribe-common-g-row',
	'tribe-events-calendar-list__event-row',
	'tribe-ext-events-happening-now__event-row',
];

$container_classes['tribe-events-calendar-list__event-row--featured'] = $event->featured;
$container_classes['tribe-events-calendar-list__event-row--no-image'] = ! $event->thumbnail->exists;

$event_classes = tribe_get_post_class( [ 'tribe-events-calendar-list__event', 'tribe-common-g-row' ], $event->ID );
?>
<div <?php tribe_classes( $container_classes ); ?>>

	<div class="tribe-ext-events-happening-now__event-featured-image tribe-common-g-col">
		<?php $this->template( 'list/event/featured-image', [ 'event' => $event ] ); ?>
	</div>

	<?php $this->template( 'list/event/date-tag', [ 'event' => $event ] ); ?>

	<div class="tribe-events-calendar-list__event-wrapper tribe-common-g-col">
		<article <?php tribe_classes( $event_classes ) ?>>
			<div class="tribe-events-calendar-list__event-details tribe-common-g-col">

				<header class="tribe-events-calendar-list__event-header">
					<?php $this->template( 'list/event/date', [ 'event' => $event ] ); ?>
					<?php $this->template( 'list/event/title', [ 'event' => $event ] ); ?>
					<?php $this->template( 'list/event/venue', [ 'event' => $event ] ); ?>
				</header>

				<?php $this->template( 'list/event/description', [ 'event' => $event ] ); ?>
				<?php $this->template( 'happening-now/event/url', [ 'event' => $event ] ); ?>

			</div>
		</article>
	</div>

</div>
