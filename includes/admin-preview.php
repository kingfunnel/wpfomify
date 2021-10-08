<?php
$id = isset( $settings->post_id ) ? $settings->post_id : '';
$suffix = ! empty( $id ) ? '-' . $id : $id;
?>
<style id="ibx-notification-popup-preview">
.ibx-notification-popup<?php echo $suffix; ?> {
	<?php if ( $settings->background_color ) { ?>
		background: <?php echo $settings->background_color; ?>;
	<?php } ?>
	<?php if ( $settings->text_color ) { ?>
		color: <?php echo $settings->text_color; ?>;
	<?php } ?>
	<?php if ( $settings->round_corners >= 0 ) { ?>
		border-radius: <?php echo $settings->round_corners; ?>px;
	<?php } ?>
	<?php if ( $settings->border >= 0 ) { ?>
		border-width: <?php echo $settings->border; ?>px;
		border-style: solid;
		border-color: <?php echo $settings->border_color; ?>;
	<?php } ?>
	<?php
		$shadow_blur = ( $settings->shadow_blur >= 0 ) ? $settings->shadow_blur . 'px' : '0';
		$shadow_spread = ( $settings->shadow_spread >= 0 ) ? $settings->shadow_spread . 'px' : '0';
		$shadow_opacity = ! empty( $settings->shadow_opacity ) ? ( $settings->shadow_opacity / 100 ) : 1;
		$shadow_color = IBX_WPFomo_Helper::hex2rgba( $settings->shadow_color, $shadow_opacity );
	?>
	<?php echo IBX_WPFomo_Helper::render_box_shadow_css( '0', '0', $shadow_blur, $shadow_spread, $shadow_color ); ?>
}
.ibx-notification-popup<?php echo $suffix; ?> {
	<?php if ( $settings->link_color ) { ?>
		color: <?php echo $settings->link_color; ?>;
	<?php } ?>
}
.ibx-notification-popup<?php echo $suffix; ?> .ibx-notification-popup-rating span {
	<?php if ( $settings->star_color ) { ?>
		color: <?php echo $settings->star_color; ?>;
	<?php } ?>
}
.ibx-notification-popup<?php echo $suffix; ?> .ibx-notification-popup-close {
	<?php if ( $settings->text_color ) { ?>
		color: <?php echo $settings->text_color; ?>;
	<?php } ?>
}
.ibx-notification-popup<?php echo $suffix; ?> .ibx-notification-popup-img {
	<?php if ( isset( $settings->img_size ) && $settings->img_size > 0 ) { ?>
		height: <?php echo $settings->img_size; ?>px;
		width: <?php echo $settings->img_size; ?>px;
	<?php } ?>
}
.ibx-notification-popup<?php echo $suffix; ?> .ibx-notification-popup-img img {
	<?php if ( $settings->img_round_corners >= 0 ) { ?>
		border-radius: <?php echo $settings->img_round_corners; ?>px;
	<?php } ?>
	<?php if ( isset( $settings->img_size ) && $settings->img_size > 0 ) { ?>
		max-height: <?php echo $settings->img_size; ?>px;
	<?php } ?>
}
.ibx-notification-popup<?php echo $suffix; ?> .ibx-notification-popup-img.has-letter {
	<?php if ( $settings->img_round_corners >= 0 ) { ?>
		border-radius: <?php echo $settings->img_round_corners; ?>px;
	<?php } ?>
}
.ibx-notification-popup<?php echo $suffix; ?> .ibx-notification-popup-text {
	<?php if ( isset( $settings->vertical_padding ) && $settings->vertical_padding >= 0 ) { ?>
		margin-top: <?php echo $settings->vertical_padding; ?>px;
		margin-bottom: <?php echo $settings->vertical_padding; ?>px;
	<?php } ?>
	<?php if ( isset( $settings->horizontal_padding ) && $settings->horizontal_padding >= 0 ) { ?>
		margin-left: <?php echo $settings->horizontal_padding; ?>px;
		margin-right: <?php echo $settings->horizontal_padding; ?>px;
	<?php } ?>
}

<?php if ( isset( $settings->first_row_font_size ) && ! empty( $settings->first_row_font_size ) ) { ?>
	.ibx-notification-popup<?php echo $suffix; ?> .ibx-notification-row-first {
		font-size: <?php echo $settings->first_row_font_size; ?>px;
	}
<?php } ?>
<?php if ( isset( $settings->second_row_font_size ) && ! empty( $settings->second_row_font_size ) ) { ?>
	.ibx-notification-popup<?php echo $suffix; ?> .ibx-notification-popup-title.ibx-notification-row-second {
		font-size: <?php echo $settings->second_row_font_size; ?>px;
	}
<?php } ?>
</style>

<div id="ibx-notification-preview-panel-button">
	<p class="ibx-notification-preview-panel-open">
		<span class="dashicons dashicons-visibility"></span>
		<span class="ibx-wpfomo-preview-text"><?php _e( 'Preview', 'ibx-wpfomo' ); ?></span>
	</p>
</div>

<div class="ibx-notification-preview-panel" id="ibx-notification-preview-panel">
	<div class="ibx-notification-preview-panel-close" title="<?php esc_html_e( 'Close', 'ibx-wpfomo' ); ?>">×</div>
	<div class="ibx-notification-popup ibx-notification-popup-<?php echo $settings->post_id; ?> ibx-notification-type-<?php echo $settings->type; ?>">
		<span class="ibx-notification-preview-text"><?php _e( 'Preview', 'ibx-wpfomo' ); ?></span>
		<div class="ibx-notification-popup-wrapper">
			<!--<p class="ibx-notification-popup-close" title="<?php esc_html_e( 'Close', 'ibx-wpfomo' ); ?>">×</p>-->
			<div class="ibx-notification-popup-content">
				<div class="ibx-notification-popup-img">
					<img src="<?php echo IBX_WPFOMO_URL . 'assets/img/placeholder-300x300.png'; ?>" alt="">
				</div>
				<div class="ibx-notification-popup-text type-conversion">
					<span
						class="ibx-notification-row-first"><?php _e( 'John D. recently purchased', 'ibx-wpfomo' ); ?></span>
					<span
						class="ibx-notification-popup-title ibx-notification-row-second"><?php _e( 'Example Product', 'ibx-wpfomo' ); ?></span>
					<span class="ibx-notification-row-third"><small>1 hour ago</small></span>
				</div>
				<div class="ibx-notification-popup-text type-reviews">
					<!-- <div class="ibx-notification-popup-rating"><span>&#9734</span><span>&#9734</span><span>&#9734</span><span>&#9734</span><span>&#9734</span></div>
					<span class="ibx-notification-popup-review-text"><?php // _e('Awesome product!', 'ibx-wpfomo'); ?></span>
					<span class="ibx-notification-popup-review-name"><?php // _e('John Doe', 'ibx-wpfomo'); ?></span> -->
					<span class="ibx-notification-row-first">
						<span class="ibx-notification-popup-rating">
							<span>☆</span><span>☆</span><span>☆</span><span>☆</span><span>☆</span>
						</span> by
						<span class="ibx-notification-popup-review-name"> <?php _e( 'John Doe', 'ibx-wpfomo' ); ?></span>
					</span>
					<span class="ibx-notification-popup-title ibx-notification-row-second">Hoodie with Logo </span>
					<span class="ibx-notification-row-third"><small>About 1 month ago</small></span>

				</div>
			</div>
		</div>
	</div>
	<div class="ibx-notification-popup notification-animate ibx-notification-popup-<?php echo $settings->post_id; ?> ibx-notification-popup-analytics ibx-notification-layout-second preview-page-analytics"
		style="bottom: 20px; opacity: 1;bottom: 40px;left: 20px;width: 279px;">
		<div class="ibx-notification-popup-wrapper">
			<!--<p class="ibx-notification-popup-close" title="Close">×</p>-->
			<div class="ibx-notification-popup-content">
				<div class="ibx-notification-popup-img">
					<img src="<?php echo IBX_WPFOMO_URL . 'assets/img/page-analytics.png'; ?>" alt="">
				</div>
				<div class="ibx-notification-popup-text" style='max-width:160px'>
					<span class="ibx-notification-row-first">
						<span class="ibx-notification-span-anaytics" for="ibx_wpfomo_page_analytics_title"> 20
							<?php esc_html_e( 'People', 'ibx-wpfomo' ); ?> </span> <span
							for='ibx_wpfomo_page_analytics_msg'><?php esc_html_e( 'are currently viewing this page.', 'ibx-wpfomo' ); ?>
						</span>
					</span>
				</div>
			</div>
		</div>
	</div>
	<div class="ibx-notification-popup notification-animate ibx-notification-popup-<?php echo $settings->post_id; ?> ibx-notification-popup-analytics ibx-notification-layout-second preview-conversion-analytics"
		style="bottom: 20px; opacity: 1;bottom: 40px;left: 20px;width: 279px;">
		<div class="ibx-notification-popup-wrapper">
			<!--<p class="ibx-notification-popup-close" title="<?php esc_html_e( 'Close', 'ibx-wpfomo' ); ?>">×</p>-->
			<div class="ibx-notification-popup-content">
				<div class="ibx-notification-popup-img">
					<img src="<?php echo IBX_WPFOMO_URL . 'assets/img/conversion-analytics.png'; ?>" alt="">
				</div>
				<div class="ibx-notification-popup-text" style='max-width:160px'>
					<span class="ibx-notification-row-first">
						<span class="ibx-notification-span-anaytics" for="ibx_wpfomo_conversion_analytics_title"> 20
							<?php esc_html_e( 'Conversions', 'ibx-wpfomo' ); ?> </span><span
							for='ibx_wpfomo_conversion_analytics_msg'>
							<?php esc_html_e( 'in last 24 hours.', 'ibx-wpfomo' ); ?> </span>
					</span>
				</div>

			</div>
		</div>
	</div>
</div>
