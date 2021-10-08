<?php
$type              = $settings->type;
$hide_on_mobile    = ( $settings->hide_mobile ) ? true : false;
$is_closable_popup = $settings->closable;
$position          = $settings->position;
$fields_data       = array();
$classes           = '';
$default_img       = '';
$use_gravatar      = true;
$img               = '';
$sequence          = 0;
$template          = '';
$exclude_emails    = IBX_WPFomo_Admin::get_settings( 'exclude_emails' );
$display_last      = $settings->display_last;
$display_last_days = $settings->display_last_days;

if ( $exclude_emails && ! empty( $exclude_emails ) ) {
	$exclude_emails = explode( "\r\n", $exclude_emails );
} else {
	$exclude_emails = array();
}

if ( 'conversion' == $type ) {

	if ( 'custom' == $settings->conversions_source && '' != $settings->conversion_group ) {
		$fields_data['fields']   = $settings->conversion_group;
		$fields_data['template'] = $settings->notification_msg;
	}

	$fields_data = apply_filters( 'ibx_wpfomo_conversion_data', $fields_data, $settings );
	$classes     = ' ibx-notification-popup-conversion';
} elseif ( 'reviews' == $type ) {
	if ( 'custom' === $settings->reviews_source ) {

		if ( '' != $settings->reviews_group ) {
			$fields_data['fields']   = $settings->reviews_group;
			$fields_data['template'] = $settings->review_template;
		}
	}

	$fields_data = apply_filters( 'ibx_wpfomo_reviews_data', $fields_data, $settings );
	$classes = ' ibx-notification-popup-review';
} // End if().

$classes .= ' ibx-notification-' . $position;

if ( $hide_on_mobile ) {
	$classes .= ' ibx-notification-hide-mobile';
}
if ( $is_closable_popup ) {
	$classes .= ' ibx-notification-closable';
}

if ( isset( $settings->default_img_url ) && isset( $settings->default_img_url['url'] ) ) {
	$default_img = $settings->default_img_url['url'];
}
if ( isset( $settings->enable_gravatar_img ) && ! $settings->enable_gravatar_img ) {
	$use_gravatar = false;
}

if ( isset( $fields_data['template'] ) && ! empty( $fields_data['template'] ) ) {
	$template = $fields_data['template'];
}

if ( isset( $fields_data['fields'] ) && ! empty( $fields_data['fields'] ) ) {
	foreach ( $fields_data['fields'] as $key => $value ) {
		if ( empty( $template ) && isset( $value['template'] ) && ! empty( $value['template'] ) ) {
			$template = $value['template'];
		}
		if ( isset( $value['time'] ) && empty( $value['time'] ) ) {
			continue;
		}
		?>

	<div class="ibx-notification-popup notification-animate ibx-notification-popup-<?php echo $id; ?><?php echo $classes; ?>" id="ibx-notification-popup-<?php echo $id; ?>" data-popup-id="<?php echo $id; ?>" data-sequence="<?php echo $sequence; ?>">
		<div class="ibx-notification-popup-wrapper">

			<?php if ( $is_closable_popup ) : ?>
				<p class="ibx-notification-popup-close" title="<?php esc_html_e( 'Close', 'ibx-wpfomo' ); ?>">×</p>
			<?php endif; ?>

			<div class="ibx-notification-popup-content">
				<?php
				if ( ! $settings->disable_img ) :
					include IBX_WPFOMO_DIR . 'includes/parts/notification/image.php';
				endif;
				?>

				<div class="ibx-notification-popup-text">
					<?php include IBX_WPFOMO_DIR . 'includes/parts/notification/text.php'; ?>
					<?php include IBX_WPFOMO_DIR . 'includes/parts/notification/branding.php'; ?>
				</div>

				<?php include IBX_WPFOMO_DIR . 'includes/parts/notification/link.php'; ?>
			</div>
		</div>
	</div>
		<?php $sequence++;
	}
}

do_action( 'ibx_wpfomo_after_notification_markup', $settings, $classes, $sequence );
?>
