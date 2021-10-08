<?php
$attrs = '';
$class = 'mbt-input-field mbt-taxonomy-field';
$post_type = '';
$exclude = array();

// Toggle data.
if ( isset( $field['toggle'] ) ) {
	$attrs .= ' data-toggle="' . esc_attr( json_encode( $field['toggle'] ) ) . '"';
}

// Browser's Autocomplete Off.
$attrs .= ' autocomplete="off"';

// Custom field classes.
if ( isset( $field['class'] ) ) {
	$class .= ' ' . $field['class'];
}

// Required.
if ( isset( $field['required'] ) && $field['required'] ) {
	$class .= ' mbt-required-field';
}

// Post type.
if ( isset( $field['post_type'] ) && ! empty( $field['post_type'] ) ) {
	$post_type = $field['post_type'];
}

// Exclude taxonomies.
if ( isset( $field['exclude'] ) ) {
	$exclude = (array) $field['exclude'];
}

// Post type field.
if ( isset( $field['post_type_field'] ) && ! empty( $field['post_type_field'] ) ) {
	$attrs .= ' data-post-type-field="' . $prefix . $field['post_type_field'] . '"';
	$attrs .= ' data-value="' . $value . '"';
	$attrs .= ' data-exclude="' . esc_attr( json_encode( $exclude ) ) . '"';
} else {
	$taxonomies = MetaBox_Tabs_Helper::taxonomies( $post_type, $exclude );
}
?>
<select id="<?php echo $id; ?>" name="<?php echo $id; ?>" class="<?php echo $class; ?>"<?php echo $attrs; ?>>
	<?php if ( empty( $taxonomies ) ) : ?>
		<option value=""></option>
	<?php else : ?>
		<?php foreach ( $taxonomies as $slug => $tax ) : ?>
			<option value="<?php echo $slug; ?>" <?php selected( $value, $slug ); ?>><?php echo $tax->label; ?></option>
		<?php endforeach; ?>
	<?php endif; ?>
</select>
