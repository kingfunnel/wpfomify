<?php
$is_closable = $settings->closable;
$timer_style = isset( $settings->countdown_style ) ? $settings->countdown_style : 'evergreen';
$time = 'evergreen' == $timer_style ? $settings->countdown_time : $settings->fixed_countdown_time;
$countdown = '';

if ( isset( $time['days'] ) && ! empty( $time['days'] ) ) {
	$countdown .= $time['days'] . ',';
} else {
	$countdown .= '00,';
}
if ( isset( $time['month'] ) && ! empty( $time['month'] ) ) {
	$countdown .= $time['month'] . ',';
} else {
	$countdown .= 'fixed' == $timer_style ? '00,' : '';
}
if ( isset( $time['year'] ) && ! empty( $time['year'] ) ) {
	$countdown .= $time['year'] . ',';
} else {
	$countdown .= 'fixed' == $timer_style ? '00,' : '';
}
if ( isset( $time['hours'] ) && ! empty( $time['hours'] ) ) {
	$countdown .= $time['hours'] . ',';
} else {
	$countdown .= '00,';
}
if ( isset( $time['minutes'] ) && ! empty( $time['minutes'] ) ) {
	$countdown .= $time['minutes'] . ',';
} else {
	$countdown .= '00,';
}
if ( isset( $time['seconds'] ) && ! empty( $time['seconds'] ) ) {
	$countdown .= $time['seconds'];
} else {
	$countdown .= 'fixed' == $timer_style ? '59' : '00';
}

$attrs = ' data-fomo-id="' . $id . '"';
$attrs .= ' data-initial-delay="' . $settings->initial_delay . '"';
if ( $settings->auto_hide ) {
	$attrs .= ' data-display-duration="' . $settings->display_time . '"';
}

$classes = array(
	'ibx-fomo',
	'ibx-fomo-' . $id,
	'ibx-fomo-position-' . $settings->position_fomo_bar,
);

if ( $settings->enable_countdown ) {
	$classes[] = 'ibx-fomo-countdown-enabled';
}
if ( $settings->hide_mobile ) {
	$classes[] = 'ibx-fomo-hide-mobile';
}

$classes = implode( ' ', $classes );
?>

<div class="<?php echo $classes; ?>" id="ibx-fomo-<?php echo $id; ?>"<?php echo $attrs; ?>>
	<div class="ibx-fomo-bar-wrapper">

		<div class="ibx-fomo-bar-content">

			<?php if ( $is_closable ) : ?>
			<p class="ibx-fomo-bar-close" title="<?php esc_html_e( 'Close', 'ibx-wpfomo' ); ?>">×</p>
			<?php endif; ?>

			<?php if ( $settings->enable_countdown ) : ?>
				<div class="ibx-fomo-countdown-text">
					<span class="ibx-fomo-start-text"><?php echo $settings->countdown_text; ?></span>
					<?php if ( isset( $settings->expire_text ) ) { ?>
					<span class="ibx-fomo-expired-text"><?php echo $settings->expire_text; ?></span>
					<?php } ?>
				</div>
				<div class="ibx-fomo-countdown-wrapper">
					<div class="ibx-fomo-countdown" data-style="<?php echo $timer_style; ?>" data-fomo-time="<?php echo $countdown; ?>">
						<div id="ibx-fomo-countdown-time">
							<div class="ibx-fomo-countdown-time-col">
								<span class="ibx-fomo-days">00</span>
								<span class="ibx-fomo-countdown-time-text"><?php esc_html_e( 'Days', 'ibx-wpfomo' ); ?></span>
							</div>
							<div class="ibx-fomo-countdown-time-col">
								<span class="ibx-fomo-hours">00</span>
								<span class="ibx-fomo-countdown-time-text"><?php esc_html_e( 'Hrs', 'ibx-wpfomo' ); ?></span>
							</div>
							<div class="ibx-fomo-countdown-time-col">
								<span class="ibx-fomo-minutes">00</span>
								<span class="ibx-fomo-countdown-time-text"><?php esc_html_e( 'Mins', 'ibx-wpfomo' ); ?></span>
							</div>
							<div class="ibx-fomo-countdown-time-col">
								<span class="ibx-fomo-seconds">00</span>
								<span class="ibx-fomo-countdown-time-text"><?php esc_html_e( 'Secs', 'ibx-wpfomo' ); ?></span>
							</div>
						</div>
					</div>
				</div>
			<?php endif; ?>
			<div class="ibx-fomo-bar-text">
				<?php echo html_entity_decode( $settings->fomo_desc ); ?>
				<?php if ( ! empty( $settings->button_text ) && ! empty( $settings->button_url ) ) :
					$button_attrs = '';
					if ( isset( $settings->button_url_target ) && $settings->button_url_target ) {
						$button_attrs = ' target="_blank" rel="nofollow noopener"';
					}
					?>
					<a href="<?php echo $settings->button_url; ?>" class="ibx-fomo-bar-button"<?php echo $button_attrs; ?>><?php echo $settings->button_text; ?></a>
				<?php endif; ?>
			</div>

		</div>

	</div>
</div>

<style id="ibx-fomo-<?php echo $id; ?>-style">
	<?php echo '#ibx-fomo-' . $id; ?> .ibx-fomo-bar-wrapper {
		background: <?php echo ! empty( $settings->background_color ) ? $settings->background_color : '#fff'; ?>;
		color: <?php echo ! empty( $settings->text_color ) ? $settings->text_color : '#000'; ?>;
		position: <?php echo 1 == $settings->sticky ? 'fixed' : 'absolute' ?>;
	}
	<?php echo '#ibx-fomo-' . $id; ?> .ibx-fomo-bar-wrapper .ibx-fomo-bar-clickable .ibx-fomo-bar-text {
		<?php if ( ! empty( $settings->text_color ) ) { ?>
			color: <?php echo $settings->text_color; ?>;
		<?php } ?>;
	}
	<?php echo '#ibx-fomo-' . $id; ?> .ibx-fomo-bar-wrapper .ibx-fomo-countdown-wrapper {
		color: <?php echo ! empty( $settings->countdown_text_color ) ? $settings->countdown_text_color : '#fff'; ?>;
	}
	<?php echo '#ibx-fomo-' . $id; ?> .ibx-fomo-bar-wrapper .ibx-fomo-countdown-wrapper #ibx-fomo-countdown-time .ibx-fomo-countdown-time-col {
		background: <?php echo ! empty( $settings->countdown_background_color ) ? $settings->countdown_background_color : '#ff0000'; ?>;
	}
	<?php echo '#ibx-fomo-' . $id; ?> .ibx-fomo-bar-wrapper {
		border-bottom: <?php echo '' != $settings->border ? $settings->border . 'px' : '0px'; ?> solid <?php echo ! empty( $settings->border_color ) ? $settings->border_color : '#555'; ?>;

		<?php
			$shadow_blur = ( $settings->shadow_blur >= 0 ) ? $settings->shadow_blur . 'px' : '0';
			$shadow_spread = ( $settings->shadow_spread >= 0 ) ? $settings->shadow_spread . 'px' : '0';
			$shadow_opacity = ! empty( $settings->shadow_opacity ) ? ( $settings->shadow_opacity / 100 ) : 1;
			$shadow_color = IBX_WPFomo_Helper::hex2rgba( $settings->shadow_color, $shadow_opacity );
		?>
		<?php echo IBX_WPFomo_Helper::render_box_shadow_css( '0', '0', $shadow_blur, $shadow_spread, $shadow_color ); ?>
	}
	<?php echo '#ibx-fomo-' . $id; ?> .ibx-fomo-bar-wrapper .ibx-fomo-bar-button {
		<?php if ( ! empty( $settings->button_bg_color ) ) { ?>
			background: <?php echo $settings->button_bg_color; ?>;
			background-color: <?php echo $settings->button_bg_color; ?>;
		<?php } ?>
		<?php if ( ! empty( $settings->button_text_color ) ) { ?>
			color: <?php echo $settings->button_text_color; ?>;
		<?php } ?>
		<?php if ( $settings->button_border >= 0 ) { ?>
			border-width: <?php echo $settings->button_border; ?>px;
			border-style: solid;
		<?php } ?>
		<?php if ( ! empty( $settings->button_border_color ) ) { ?>
			border-color: <?php echo $settings->button_border_color; ?>;
		<?php } ?>
		<?php if ( $settings->button_border_radius >= 0 ) { ?>
			border-radius: <?php echo $settings->button_border_radius; ?>px;
		<?php } ?>
	}
	<?php echo '#ibx-fomo-' . $id; ?> .ibx-fomo-bar-wrapper .ibx-fomo-bar-button:hover {
		<?php if ( ! empty( $settings->button_bg_hover_color ) ) { ?>
			background: <?php echo $settings->button_bg_hover_color; ?>;
			background-color: <?php echo $settings->button_bg_hover_color; ?>;
		<?php } ?>
		<?php if ( ! empty( $settings->button_text_hover_color ) ) { ?>
			color: <?php echo $settings->button_text_hover_color; ?>;
		<?php } ?>
		<?php if ( ! empty( $settings->button_border_hover_color ) ) { ?>
			border-color: <?php echo $settings->button_border_hover_color; ?>;
		<?php } ?>
	}
</style>
