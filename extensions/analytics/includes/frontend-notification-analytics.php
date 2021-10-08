<div class="ibx-notification-popup notification-animate ibx-notification-popup-<?php echo $id; ?><?php echo $classes; ?>" id="ibx-notification-popup-<?php echo $id; ?>" data-popup-id="<?php echo $id; ?>" data-sequence="<?php echo $sequence; ?>">
	<div class="ibx-notification-popup-wrapper">

		<?php if ( $settings->closable ) : ?>
			<p class="ibx-notification-popup-close" title="<?php esc_html_e( 'Close', 'ibx-wpfomo' ); ?>">×</p>
		<?php endif; ?>

		<div class="ibx-notification-popup-content">
			<?php
			if ( ! $settings->disable_img ) :
				if ( ( isset( $analytics_type ) && 'page' === $analytics_type ) && isset( $visitor_count ) ) {
					$img = IBX_WPFOMO_URL . 'assets/img/page-analytics.png';
				}

				if ( ( isset( $analytics_type ) && 'conversion' === $analytics_type ) && isset( $conversion_count ) ) {
					$img = IBX_WPFOMO_URL . 'assets/img/page-analytics.png';
				}

				include IBX_WPFOMO_DIR . 'includes/parts/notification/image.php';
			endif;
			?>
			<div class="ibx-notification-popup-text" style="max-width: 160px;">
				<span class="ibx-notification-row-first">
					<span class="ibx-notification-span-anaytics">
						<span id="ibx-notification-popup-analytics-<?php echo $analytics_type; ?>"><?php echo $analytics_count; ?></span>
						<?php echo $analytics_title; ?>
					</span>
					<?php echo $analytics_text; ?>
				</span>
				<?php require IBX_WPFOMO_DIR . 'includes/parts/notification/branding.php'; ?>
			</div>

		</div>
	</div>
</div>
