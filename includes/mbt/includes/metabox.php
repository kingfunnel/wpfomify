<div class="mbt-metabox-tabs-wrapper mbt-layout-<?php echo $layout; ?> wp-clearfix" data-mbt-id="<?php echo $metabox_id; ?>">
	<div class="<?php echo ( 'horizontal' == $layout ) ? 'mbt-row' : 'mbt-col-left'; ?>">
		<div class="mbt-metabox-tabs-wrap">
			<div class="mbt-metabox-tabs-wrap-inner">
				<ul class="mbt-metabox-tabs" data-container=".mbt-metabox-tabs-content">
					<?php $i = 0; foreach ( $tabs as $id => $tab ) : ?>
						<li class="mbt-metabox-tab<?php echo ( 0 == $i ) ? ' active' : ''; ?>" data-tab="<?php echo $id; ?>">
							<a href="#mbt-metabox-tab-<?php echo $id; ?>" data-tab="<?php echo $id; ?>">
								<?php if ( isset( $tab['icon'] ) && ! empty( $tab['icon'] ) ) : ?>
									<span class="<?php echo $tab['icon']; ?>"></span>
								<?php elseif ( $tabnumber ) : ?>
									<span class="mbt-metabox-tab-number"><?php echo $i + 1; ?></span>
								<?php endif; ?>
								<span class="mbt-metabox-tab-title"><?php echo ( isset( $tab['title'] ) ) ? $tab['title'] : ''; ?></span>
							</a>
						</li>
					<?php
					$i++;
					endforeach; ?>
				</ul>
			</div>
		</div>
	</div>
	<div class="<?php echo ( 'horizontal' == $layout ) ? 'mbt-row' : 'mbt-col-right'; ?>">
		
		<?php do_action( 'mbt_metabox_tabs_before_content_wrap', self::$post_id, self::$args ); ?>
		
		<div class="mbt-metabox-tabs-content">
			<?php $i = 0; foreach ( $tabs as $id => $tab ) : // Tabs ?>
				<div id="mbt-metabox-tab-<?php echo $id; ?>" class="mbt-metabox-tab-content<?php echo ( 0 == $i ) ? ' active' : ''; ?>">

					<?php if ( isset( $tab['description'] ) && ! empty( $tab['description'] ) ) : ?>
						<p class="mbt-metabox-tab-description"><?php echo $tab['description']; ?></p>
					<?php endif; ?>

					<?php foreach ( $tab['sections'] as $id => $section ) : // Sections ?>
						<?php
						$collapsable 	= ( isset( $section['collapsable'] ) && $section['collapsable'] ) ? true : false;
						$toggle_type	= ( isset( $section['toggle_type'] ) && 'field' == $section['toggle_type'] ) ? 'field' : 'icon';
						$labels			= ( isset( $section['togggle_field_label'] ) ) ? (array) $section['togggle_field_label'] : array( __( 'Auto', 'ibx-wpfomo' ), __( 'Custom', 'ibx-wpfomo' ) );
						$expanded		= ( isset( $toggle_sections[ $id ] ) || isset( $section['expanded'] ) && $section['expanded'] ) ? true : false;
						$class 			= ( isset( $section['class'] ) && ! empty( $section['class'] ) ) ? ' ' . $section['class'] : '';
						$attr 			= '';
						if ( $collapsable ) {
							$attr = 'field' == $toggle_type ? ' data-toggle-type="field"' : ' data-toggle-type="icon"';
							$attr .= ( $expanded ) ? ' data-default-state="expanded"' : ' data-default-state="collapsed"';
						}
						?>
						<div id="mbt-metabox-section-<?php echo $id; ?>" class="mbt-metabox-section<?php echo ( $collapsable ) ? ' mbt-metabox-section-collapsable' : ''; ?><?php echo $class; ?>"<?php echo $attr; ?>>
							<?php if ( isset( $section['title'] ) && ! empty( $section['title'] ) ) : ?>
								<?php if ( 'field' == $toggle_type ) : ?>
									<h2 class="mbt-metabox-section-title">
										<span><?php echo $section['title']; ?></span>
										<div class="mbt-field">
											<span class="mbt-field-label"><?php echo isset( $labels[0] ) ? $labels[0] : ''; ?></span>
											<label class="mbt-toggle-field">
												<input type="checkbox" id="mbt-metabox-section-<?php echo $id; ?>-toggle" name="mbt_section_toggle[<?php echo $id; ?>]" value="1"<?php echo ( $expanded ) ? ' checked="checked"' : ''; ?> />
												<span class="mbt-toggle-slider"></span>
											</label>
											<span class="mbt-field-label"><?php echo isset( $labels[1] ) ? $labels[1] : ''; ?></span>
										</div>
									</h2>
								<?php endif; ?>
								<?php if ( 'icon' == $toggle_type ) : ?>
									<h2 class="mbt-metabox-section-title"><span><?php echo $section['title']; ?></span></h2>
								<?php endif; ?>
							<?php endif; ?>

							<div class="mbt-metabox-section-content">
								<?php if ( isset( $section['description'] ) && ! empty( $section['description'] ) ) : ?>
									<p class="mbt-metabox-section-description"><?php echo $section['description']; ?></p>
								<?php endif; ?>
								<table class="mbt-metabox-form-table form-table">
									<?php
									$fields = $section['fields'];
									$sorted_fields = array();
									$last_priority = 10;
									foreach ( $fields as $name => $field ) :

										$field['key'] = $name;

										if ( isset( $field['priority'] ) && ! empty( $field['priority'] ) ) {
											$field['priority'] = $last_priority = self::get_field_priority( $sorted_fields, $field['priority'] );
										} else {
											$field['priority'] = $last_priority = self::get_field_priority( $sorted_fields, $last_priority );
										}

										$sorted_fields[ $field['priority'] ] = $field;

									endforeach;

									ksort( $sorted_fields );

									foreach ( $sorted_fields as $key => $field ) :
										$name = $field['key'];
										MetaBox_Tabs::render_metabox_field( $name, $field );
									endforeach;
									?>
								</table>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php
			$i++;
			endforeach; ?>
		</div>

		<?php do_action( 'mbt_metabox_tabs_after_content_wrap', self::$post_id, self::$args ); ?>

	</div>
</div>
