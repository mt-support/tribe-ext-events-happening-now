<?php
/**
 * View: Happening Now
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/happening-now.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 5.0.2
 *
 * @var array    $events               The array containing the events.
 * @var string[] $container_classes    Classes used for the container of the view.
 * @var array    $container_data       An additional set of container `data` attributes.
 * @var string   $breakpoint_pointer   String we use as pointer to the current view we are setting up with breakpoints.
 */

if ( empty( $events ) ) {
	return;
}

$header_classes = [ 'tribe-events-header' ];
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

		<div class="tribe-events-calendar-list">

			<?php
			// @TODO: Make this semantic and stick it in the right place. This is a placeholder to hint at somethign for designers.
			?>
			<div class="tribe-events-calendar-list__month-separator">
				<?php esc_html_e( 'Events Happening Now', 'tribe-ext-events-happening-now' ); ?>
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
