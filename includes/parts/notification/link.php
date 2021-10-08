<?php if ( isset( $value['url'] ) && ! empty( $value['url'] ) ) : ?>
	<?php
	$target = ( ! $settings->link_target ) ? '_self' : '_blank';
	$url = $value['url'];

	if ( ! empty( $settings->utm_source ) ) {

		$utm_args = array(
			'utm_source'    => urlencode( $settings->utm_source ),
			'utm_medium'    => urlencode( $settings->utm_medium ),
		);

		if ( isset( $settings->utm_campaign ) && ! empty( $settings->utm_campaign ) ) {
			$utm_args['utm_campaign'] = urlencode( $settings->utm_campaign );
		}

		if ( isset( $settings->utm_term ) && ! empty( $settings->utm_term ) ) {
			$utm_args['utm_term'] = urlencode( $settings->utm_term );
		}

		$url = add_query_arg( $utm_args, $value['url'] );
	}
	?>
	<a href="<?php echo esc_url( $url ); ?>" class="ibx-notification-popup-link" target="<?php echo $target; ?>"<?php echo '_blank' == $target ? ' rel="nofollow"' : ''; ?>></a>
<?php endif; ?>
