<?php
$attrs = '';
$class = 'mbt-input-field';

if ( isset( $field['disabled'] ) && $field['disabled'] ) {
	$attrs .= ' disabled="disabled"';
}

if ( isset( $field['class'] ) && ! empty( $field['class'] ) ) {
	$class .= ' ' . $field['class'];
}
// Required.
if ( isset( $field['required'] ) && $field['required'] ) {
	$class .= ' mbt-required-field';
}
?>

<?php if ( isset( $field['multiplerows'] ) && $field['multiplerows'] ) { ?>
	<?php if ( isset( $field['rows'] ) && absint( $field['rows'] ) ) { ?>
		<?php
		if ( ! is_array( $value ) ) {
			$value = (array) $value;
		}
		?>
		<div class="mbt-field-multirows-wrapper">
			<?php for ( $i = 0; $i < $field['rows']; $i++ ) { ?>
				<input type="text" id="<?php echo $id; ?>_<?php echo $i; ?>" name="<?php echo $id; ?>[]" class="<?php echo $class; ?>"<?php echo $attrs; ?> value="<?php echo isset( $value[ $i ] ) ? $value[ $i ] : ''; ?>" autocomplete="false" data-index="<?php echo $i; ?>" />
			<?php } ?>
		</div>
	<?php } ?>
<?php } else { ?>
	<?php
	if ( isset( $field['rows'] ) && ! empty( $field['rows'] ) ) {
		$attrs .= ' rows="' . $field['rows'] . '"';
	}
	if ( isset( $field['placeholder'] ) ) {
		$attrs .= ' placeholder="' . $field['placeholder'] . '"';
	}
	?>
	<textarea id="<?php echo $id; ?>" name="<?php echo $id; ?>" class="<?php echo $class; ?>"<?php echo $attrs; ?>><?php echo $value; ?></textarea>
<?php } ?>
