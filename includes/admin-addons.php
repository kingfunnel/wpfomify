<style>
.update-nag {
    display: none;
}
.ibx-wpfomo-settings-header {
    background-image: url(<?php echo IBX_WPFOMO_URL . 'assets/img/wpfomo-logo-w.png'; ?>);
}
</style>

<div class="ibx-wpfomo-settings-header">
	 <h2><?php echo 'WPFomo ' . esc_html__( 'Addons', 'ibx-wpfomo' ); ?></h2>
</div>

<div class="wrap">
	<?php if ( 'valid' != get_option( 'ibx_wpfomo_license_status' ) ) : ?>
	<div class="notice notice-error">
		<p><?php esc_html_e( 'Please activate your WPfomify license in order to install and activate add-ons.', 'ibx-wpfomo' ); ?></p>
	</div>
	<?php endif; ?>
	<div class="icon32 icon32-ibx-wpfomo-addons" id="icon-ibx-wpfomo"><br></div>
	<h2 class="nav-tab-wrapper ibx-wpfomo-nav-tab-wrapper hidden"></h2>
	<?php if ( count( $addons ) ) : ?>
		<div class="ibx-wpfomo-addons">
		<?php foreach ( $addons as $addon ) :
			$plugin_basename = self::get_plugin_basename_from_slug( $addon['slug'] );
			?>
			<div class="ibx-wpfomo-addon" data-id="<?php echo $addon['id']; ?>" data-slug="<?php echo $addon['slug']; ?>">
				<div class="ibx-wpfomo-addon-inner">
					<div class="ibx-wpfomo-addon-error"></div>
					<div class="ibx-wpfomo-addon-content">
						<h3 class="ibx-wpfomo-addon-title"><?php echo str_replace( ' Addon', '', $addon['title'] ); ?></h3>
						<p><img src="<?php echo $addon['image']; ?>" class="ibx-wpfomo-addon-image" /></p>
						<p><?php echo trim( $addon['desc'] ); ?></p>
					</div>
					<div class="ibx-wpfomo-addon-footer">
						<?php if ( ! isset( $installed_plugins[ $plugin_basename ] ) ) : // display Install button if the plugin is not installed. ?>
							<a href="#" rel="<?php echo $addon['file']; ?>" class="button button-primary ibx-wpfomo-addon-install"><?php _e( 'Install', 'ibx-wpfomo' ); ?></a>
						<?php elseif ( is_plugin_active( $plugin_basename ) ) : // display Deactivate button if the plugin is active. ?>
							<a href="#" rel="<?php echo esc_attr( $plugin_basename ); ?>" class="button button-secondary ibx-wpfomo-addon-deactivate"><?php _e( 'Deactivate', 'ibx-wpfomo' ); ?></a>
						<?php elseif ( ! is_plugin_active( $plugin_basename ) ) : // display Activate button if the plugin is inactive. ?>
							<a href="#" rel="<?php echo esc_attr( $plugin_basename ); ?>" class="button button-primary ibx-wpfomo-addon-activate"><?php _e( 'Activate', 'ibx-wpfomo' ); ?></a>
						<?php endif; ?>
						<span class="spinner"></span>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<div class="ibx-wpfomo-ftp-popup">
		<div class="ibx-wpfomo-popup-inner">
			<a href="#" class="ibx-wpfomo-popup-close" title="<?php esc_html_e( 'Close', 'ibx-wpfomo' ); ?>">×</a>
			<div class="ibx-wpfomo-popup-content"></div>
		</div>
	</div>
</div>
