<?php
$name = '';
$excluded = false;

if ( ! isset( $img ) ) {
	$img = '';
}
if ( isset( $value ) && is_array( $value ) ) {
	if ( ! empty( $value['email'] ) && in_array( $value['email'], $exclude_emails ) ) {
		$excluded = true;
	}

	if ( isset( $value['image'] ) && isset( $value['image']['url'] ) && ! empty( $value['image']['url'] ) ) {
		$img = $value['image']['url'];
	} elseif ( $use_gravatar && ! empty( $value['email'] ) && ! $excluded ) {
		$img = get_avatar_url(
			$value['email'],
			array(
				'default' => $default_img,
			)
		);
	} else {
		$img = $default_img;
	}

	if ( isset( $value['name'] ) && ! empty( $value['name'] ) ) {
		$name = ucfirst( $value['name'][0] );
	}
	if ( empty( $name ) ) {
		if ( isset( $value['email'] ) && ! empty( $value['email'] ) ) {
			//$name = ucfirst( $value['email'][0] );
		}
	}
}

$img = apply_filters( 'ibx_wpfomo_notification_image_url', $img );

// if ( empty( $img ) ) {
// 	$img = IBX_WPFOMO_URL . 'assets/img/placeholder-300x300.png';
// }

?>
<div class="ibx-notification-popup-img<?php echo empty( $img ) ? ' has-letter' : ''; ?>">
	<?php if ( ! empty( $img ) ) { ?>
	<img src="<?php echo esc_url( $img ); ?>" alt="" />
	<?php } else { ?>
		<?php
		echo $name;
		?>
	<?php } ?>
</div>
<?php
