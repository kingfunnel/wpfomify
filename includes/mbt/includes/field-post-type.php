<?php
$attrs = '';
$class = 'mbt-input-field mbt-post-type-field';
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

// Exclude post type(s).
if ( isset( $field['exclude'] ) ) {
	$exclude = (array) $field['exclude'];
}

$post_types = MetaBox_Tabs_Helper::post_types( $exclude );
?>
<select id="<?php echo $id; ?>" name="<?php echo $id; ?>" class="<?php echo $class; ?>">
	<?php foreach ( $post_types as $slug => $type ) : ?>
	<option value="<?php echo $slug; ?>" <?php selected( $value, $slug ); ?>><?php echo $type->labels->name; ?></option>
	<?php endforeach; ?>
</select>
