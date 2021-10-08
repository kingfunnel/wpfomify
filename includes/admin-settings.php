<style>
.update-nag {
    display: none;
}
.ibx-wpfomo-settings-header {
    background-image: url(<?php echo IBX_WPFOMO_URL . 'assets/img/wpfomo-logo-w.png'; ?>);
}
</style>

<div class="ibx-wpfomo-settings-header">
	<h2><?php echo 'WPFomo ' . esc_html__( 'Settings', 'ibx-wpfomo' ); ?></h2>
</div>

<div class="wrap ibx-wpfomo-settings-wrap">

	<div class="icon32 icon32-ibx-wpfomo-settings" id="icon-ibx-wpfomo"><br /></div>

	<h2 class="nav-tab-wrapper ibx-wpfomo-nav-tab-wrapper hidden">
	</h2>

	<?php self::render_update_message(); ?>

	<form method="post" id="ibx-wpfomo-settings-form" action="<?php echo self::get_form_action(); ?>">

		<?php if ( count( $tabs ) > 1 ) : ?>
		<div class="ibx-wpfomo-settings-tabs">
			<?php foreach ( $tabs as $tab_id => $tab ) :
				if ( ! $tab['show'] ) {
					continue;
				} ?>
				<a href="#ibx-wpfomo-tab-<?php echo $tab_id; ?>" class="ibx-wpfomo-tab<?php echo ( $current_tab == $tab_id ) ? ' active' : ''; ?>"><?php echo $tab['title']; ?></a>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<div class="ibx-wpfomo-settings-tabs-content">

		<?php foreach ( $tabs as $tab_id => $tab ) :
			if ( ! $tab['show'] ) {
					continue;
			} ?>

			<div id="ibx-wpfomo-tab-<?php echo $tab_id; ?>" class="ibx-wpfomo-tab-content<?php echo ( $current_tab == $tab_id ) ? ' active' : ''; ?>">

			<?php foreach ( $tab['sections'] as $section_id => $section ) : ?>

				<?php if ( isset( $section['title'] ) && ! empty( $section['title'] ) ) : ?>
					<h2 class="ibx-wpfomo-section-title"><?php echo $section['title']; ?></h2>
				<?php endif; ?>

				<?php if ( isset( $section['description'] ) && ! empty( $section['description'] ) ) : ?>
					<p class="ibx-wpfomo-section-description"><?php echo $section['description']; ?></p>
				<?php endif; ?>

				<table class="form-table">

					<?php if ( 'general' == $tab_id && 'license' == $section_id ) : ?>

						<?php
						$license = get_option( 'ibx_wpfomo_license_key' );
						$status = get_option( 'ibx_wpfomo_license_status' );
						$credit_link = get_option( 'ibx_wpfomo_credit_link_disable' );
						$cache_duration = get_option( 'ibx_wpfomo_cache_duration' );
						?>

						<tr valign="top">
							<th scope="row" valign="top">
								<label for="ibx_wpfomo_license_key"><?php esc_html_e( 'License Key', 'ibx-wpfomo' ); ?></label>
							</th>
							<td>
								<input id="ibx_wpfomo_license_key" name="ibx_wpfomo_license_key" type="password" class="regular-text" value="<?php echo esc_attr( $license ); ?>" autocomplete="false" />
							</td>
						</tr>
						<?php if ( false !== $license ) { ?>
							<tr valign="top">
								<th scope="row" valign="top">
									<?php _e( 'License Status', 'ibx-wpfomo' ); ?>
								</th>
								<td>
									<?php if ( false !== $status && 'valid' == $status ) { ?>
										<span style="color: #4CAF50; padding: 5px 0; margin-right: 10px; text-shadow: none; border-radius: 3px; display: inline-block; text-transform: uppercase;"><?php esc_html_e( 'License is active', 'ibx-wpfomo' ); ?></span>
										<?php wp_nonce_field( 'ibx_wpfomo_license_deactivate_nonce', 'ibx_wpfomo_license_deactivate_nonce' ); ?>
										<input type="submit" class="button-secondary" name="ibx_wpfomo_license_deactivate" value="<?php _e( 'Deactivate License', 'ibx-wpfomo' ); ?>" />
									<?php } else { ?>
										<?php wp_nonce_field( 'ibx_wpfomo_license_activate_nonce', 'ibx_wpfomo_license_activate_nonce' ); ?>
										<input type="submit" class="button-secondary" name="ibx_wpfomo_license_activate" value="<?php esc_html_e( 'Activate License', 'ibx-wpfomo' ); ?>"/>
										<p class="description"><?php esc_html_e( 'Please click the “Activate License” button to activate your license.', 'ibx-wpfomo' ); ?></p>
									<?php } ?>
								</td>
							</tr>
						<?php } ?>
											
					<?php endif; ?>
								
					<?php
					foreach ( $section['fields'] as $name => $field ) : // Fields
						self::render_settings_field( $name, $field );
					endforeach;
					?>

				</table>

			<?php endforeach; ?>

			</div>

		<?php endforeach; ?>

		</div>
						
		<p class="submit">
			<?php wp_nonce_field( '_ibx_wpfomo_settings_nonce', 'ibx_wpfomo_settings_nonce' ); ?>
			<input type="submit" class="ibx-wpfomo-settings-button" name="submit" id="submit" value="<?php esc_html_e( 'Save Changes', 'ibx-wpfomo' ); ?>" />
		</p>
	</form>
</div>
