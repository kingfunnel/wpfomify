<?php if ( empty( $field['label'] ) ) : ?>
<td class="mbt-field-control" colspan="2">
<?php else : ?>
<th class="mbt-field-label">
	<label for="<?php echo $id; ?>"><?php echo $field['label']; ?></label>
</th>
<td class="mbt-field-control">
<?php endif; ?>
	<?php do_action( 'mbt_field_before_wrapper', $name, $value, $field, $post_id ); ?>

	<div class="mbt-field-control-wrapper">
	<?php
	do_action( 'mbt_field_before', $name, $value, $field, $post_id );

	$field_file = MBT_DIR . 'includes/field-' . $field['type'] . '.php';

	if ( file_exists( $field_file ) ) {
		include $field_file;
	} else {
		do_action( 'mbt_field_' . $field['type'], $name, $value, $field, $post_id );
	}
	?>

	<?php if ( isset( $field['description'] ) ) : ?>
	<span class="mbt-field-description"><?php echo $field['description']; ?></span>
	<?php endif; ?>

	<?php if ( isset( $field['help'] ) ) : ?>
	<p class="description"><?php echo $field['help']; ?></p>
	<?php endif; ?>

	<?php do_action( 'mbt_field_after', $name, $value, $field, $post_id ); ?>
	</div>

	<?php do_action( 'mbt_field_after_wrapper', $name, $value, $field, $post_id ); ?>
</td>
