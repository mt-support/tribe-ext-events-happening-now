<?php
use \Tribe\Extensions\EventsHappeningNow\Shortcode;
/**
 * View: Happening Now
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/happening-now.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTICLE_LINK_HERE}
 *
 * @version TBD
 *
 * @var array     $events               The array containing the events.
 * @var string[]  $container_classes    Classes used for the container of the view.
 * @var array     $container_data       An additional set of container `data` attributes.
 * @var string    $breakpoint_pointer   String we use as pointer to the current view we are setting up with breakpoints.
 * @var Shortcode $shortcode_object     Instance of the Shortcode that created this view.
 */

if ( empty( $events ) ) {
	return;
}

$happening_now_title = $shortcode_object->get_argument( 'title', __( 'Events Happening Now', 'tribe-ext-events-happening-now' ) );

$header_classes    = [ 'tribe-events-header' ];
$wrapper_classes   = [ 'tribe-events-calendar-list', 'tribe-ext-events-happening-now' ];
$wrapper_classes[] = 1 < count( $events ) ? 'tribe-ext-events-happening-now--multiple' : 'tribe-ext-events-happening-now--single';
?>
<div
	<?php tribe_classes( $container_classes ); ?>
	data-js="tribe-events-view"
	<?php foreach ( $container_data as $key => $value ) : ?>
		data-view-<?php echo esc_attr( $key ) ?>="<?php echo esc_attr( $value ) ?>"
	<?php endforeach; ?>
	<?php if ( ! empty( $breakpoint_pointer ) ) : ?>
		data-view-breakpoint-pointer="<?php echo esc_attr( $breakpoint_pointer ); ?>"
	<?php endif; ?>
>
	<div class="tribe-common-l-container tribe-events-l-container">
		<?php $this->template( 'components/json-ld-data' ); ?>

		<?php $this->template( 'components/data' ); ?>

		<?php $this->template( 'components/before' ); ?>

		<div <?php tribe_classes( $wrapper_classes ); ?>>

			<div class="tribe-ext-events-happening-now__title">
				<h2 class="tribe-common-h6 tribe-common-h5--min-medium"><?php echo esc_html( $happening_now_title ); ?></h2>
			</div>

			<?php foreach ( $events as $event ) : ?>
				<?php $this->setup_postdata( $event ); ?>

				<?php $this->template( 'happening-now/event', [ 'event' => $event ] ); ?>

			<?php endforeach; ?>

		</div>

		<?php $this->template( 'components/after' ); ?>

	</div>
</div>

<?php $this->template( 'components/breakpoints' ); ?>
