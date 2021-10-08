<?php
$class = 'mbt-input-field';
$show_date = false;

// Custom field classes.
if ( isset( $field['class'] ) ) {
	$class .= ' ' . $field['class'];
}

// Required.
if ( isset( $field['required'] ) && $field['required'] ) {
	$class .= ' mbt-required-field';
}

// Show date.
if ( isset( $field['show_date'] ) && $field['show_date'] ) {
	$show_date = true;
}

if ( is_object( $value ) ) {
	$value = (array) $value;
}

$prepend    = array( '00', '01','02','03','04','05','06','07','08','09' );
$days       = array_merge( $prepend, range( 10, 31 ) );
$hours      = array_merge( $prepend, range( 10, 23 ) );
$minutes    = array_merge( $prepend, range( 10, 59 ) );
$seconds    = array_merge( $prepend, range( 10, 59 ) );

$months 	= array(
	'01'	=> __( 'Jan', 'mbt' ),
	'02'	=> __( 'Feb', 'mbt' ),
	'03'	=> __( 'Mar', 'mbt' ),
	'04'	=> __( 'Apr', 'mbt' ),
	'05'	=> __( 'May', 'mbt' ),
	'06'	=> __( 'Jun', 'mbt' ),
	'07'	=> __( 'Jul', 'mbt' ),
	'08'	=> __( 'Aug', 'mbt' ),
	'09'	=> __( 'Sep', 'mbt' ),
	'10'	=> __( 'Oct', 'mbt' ),
	'11'	=> __( 'Nov', 'mbt' ),
	'12'	=> __( 'Dec', 'mbt' ),
);

$years 	= array();
for ( $i = date('Y'); $i < date('Y') + 6; $i++ ) {
	$years[ $i ] = $i;
}
?>
<div class="mbt-time-field-row">
	<select name="<?php echo $id; ?>[days]" class="mbt-time-field-days <?php echo $class; ?>" title="<?php _e( 'Days', 'mbt' ); ?>" data-type="days">
		<option value=""></option>
	<?php foreach ( $days as $day ) : ?>
		<?php $selected = isset( $value['days'] ) && $value['days'] == $day ? ' selected="selected"' : ''; ?>
		<option value="<?php echo $day ?>"<?php echo $selected ?>><?php echo $day ?></option>
	<?php endforeach; ?>
	</select>
	<?php if ( ! $show_date ) { ?>
		<div class="mbt-time-field-label"><?php _e( 'Days', 'mbt' ); ?></div>
	<?php } else { ?>
		<div class="mbt-time-field-label"><?php _e( 'Day', 'mbt' ); ?></div>
	<?php } ?>
</div>

<?php if ( $show_date ) { ?>
<div class="mbt-time-field-row">
	<select name="<?php echo $id; ?>[month]" class="mbt-time-field-month <?php echo $class; ?>" title="<?php _e( 'Month', 'mbt' ); ?>" data-type="month">
		<option value=""></option>
	<?php foreach ( $months as $month_number => $month ) : ?>
		<?php $selected = isset( $value['month'] ) && $value['month'] == $month_number ? ' selected="selected"' : ''; ?>
		<option value="<?php echo $month_number ?>"<?php echo $selected ?>><?php echo $month ?></option>
	<?php endforeach; ?>
	</select>
	<div class="mbt-time-field-label"><?php _e( 'Month', 'mbt' ); ?></div>
</div>

<div class="mbt-time-field-row">
	<select name="<?php echo $id; ?>[year]" class="mbt-time-field-year <?php echo $class; ?>" title="<?php _e( 'Year', 'mbt' ); ?>" data-type="year">
		<option value=""></option>
	<?php foreach ( $years as $year ) : ?>
		<?php $selected = isset( $value['year'] ) && $value['year'] == $year ? ' selected="selected"' : ''; ?>
		<option value="<?php echo $year ?>"<?php echo $selected ?>><?php echo $year ?></option>
	<?php endforeach; ?>
	</select>
	<div class="mbt-time-field-label"><?php _e( 'Year', 'mbt' ); ?></div>
</div>
<?php } ?>

<div class="mbt-time-field-row">
	<select name="<?php echo $id; ?>[hours]" class="mbt-time-field-hours <?php echo $class; ?>" title="<?php _e( 'Hours', 'mbt' ); ?>" data-type="hours">
		<option value=""></option>
	<?php foreach ( $hours as $hour ) : ?>
		<?php $selected = isset( $value['hours'] ) && $value['hours'] == $hour ? ' selected="selected"' : ''; ?>
		<option value="<?php echo $hour ?>"<?php echo $selected ?>><?php echo $hour ?></option>
	<?php endforeach; ?>
	</select>
	<div class="mbt-time-field-label"><?php _e( 'Hours', 'mbt' ); ?></div>
</div>

<div class="mbt-time-field-row">
	<select name="<?php echo $id; ?>[minutes]" class="mbt-time-field-minutes <?php echo $class; ?>" title="<?php _e( 'Minutes', 'mbt' ); ?>" data-type="minutes">
		<option value=""></option>
	<?php foreach ( $minutes as $minute ) : ?>
		<?php $selected = isset( $value['minutes'] ) && $value['minutes'] == $minute ? ' selected="selected"' : ''; ?>
		<option value="<?php echo $minute ?>"<?php echo $selected ?>><?php echo $minute ?></option>
	<?php endforeach; ?>
	</select>
	<div class="mbt-time-field-label"><?php _e( 'Minutes', 'mbt' ); ?></div>
</div>

<?php if ( ! $show_date ) { ?>
	<div class="mbt-time-field-row">
		<select name="<?php echo $id; ?>[seconds]" class="mbt-time-field-seconds <?php echo $class; ?>" title="<?php _e( 'Seconds', 'mbt' ); ?>">
			<option value=""></option>
		<?php foreach ( $seconds as $second ) : ?>
			<?php $selected = isset( $value['seconds'] ) && $value['seconds'] == $second ? ' selected="selected"' : ''; ?>
			<option value="<?php echo $second ?>"<?php echo $selected ?>><?php echo $second ?></option>
		<?php endforeach; ?>
		</select>
		<div class="mbt-time-field-label"><?php _e( 'Seconds', 'mbt' ); ?></div>
	</div>
<?php } ?>