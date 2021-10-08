<?php
function ibx_wpfomo_metabox_args() {
	return array(
		'id'            => 'ibx-wpfomo',
		'title'         => __( 'WPfomify', 'ibx-wpfomo' ),
		'object_types'  => array( 'ibx_wpfomo' ),
		'context'       => 'normal',
		'priority'      => 'high',
		'show_header'   => false,
		'fields_prefix' => 'ibx_wpfomo_',
		'tabnumber'     => true,
		'layout'        => 'horizontal',
		'tabs'          => apply_filters(
			'ibx_wpfomo_metabox_fields',
			array(
				'configuration' => array(
					'title'    => __( 'Source', 'ibx-wpfomo' ),
					'sections' => array(
						'config'               => array(
							'title'  => __( 'Select Source', 'ibx-wpfomo' ),
							'fields' => array(
								'type'               => apply_filters(
									'ibx_wpfomo_field_fomo_type',
									array(
										'type'     => 'select',
										'label'    => __( 'I would like to display', 'ibx-wpfomo' ),
										'default'  => 'conversion',
										'options'  => IBX_WPFomo_Helper::get_notification_types(),
										'toggle'   => array(
											'fomo_bar'   => ibx_wpfomo_fomo_bar_toggle_data(),
											'conversion' => ibx_wpfomo_conversion_toggle_data(),
											'reviews'    => ibx_wpfomo_reviews_toggle_data(),
										),
										'hide'     => array(
											'fomo_bar'   => array(
												'sections' => array( 'csv_fields_section', 'import_csv', 'custom_form_url' ),
												'fields'   => array( 'reviews_source', 'notification_layout', 'notification_msg', 'conversion_group', 'notification_custom_form_msg', 'custom_form_entries', 'csv_form_entries', 'custom_form_target_url' ),
											),
											'reviews'    => array(
												'sections' => array( 'csv_fields_section', 'import_csv', 'custom_form_url' ),
												'fields'   => array( 'notification_msg', 'conversion_group', 'notification_custom_form_msg', 'custom_form_target_url', 'custom_form_entries', 'csv_form_entries' ),
											),
											'conversion' => array(
												'sections' => array( 'csv_fields_section' ),
												'fields'   => array( 'reviews_source', 'custom_form_entries', 'csv_form_entries' ),
											),

										),
										'priority' => 50,
									)
								),
								'conversions_source' => apply_filters(
									'ibx_wpfomo_field_conversions_source',
									array(
										'type'     => 'select',
										'label'    => __( 'From', 'ibx-wpfomo' ),
										'default'  => IBX_WPFomo_Helper::get_default_conversion_source(),
										'options'  => array(
											'custom'     => __( 'Custom (Manual entry)', 'ibx-wpfomo' ),
										),
										'toggle'   => array(
											'custom'     => array(
												'fields' => array( 'notification_msg', 'conversion_group' ),
											),
										),
										'hide'     => array(
											'sections' => array( 'csv_fields_section' ),
										),
										'priority' => 100,
									)
								),
								'reviews_source'     => apply_filters(
									'ibx_wpfomo_field_reviews_source',
									array(
										'type'     => 'select',
										'label'    => __( 'From', 'ibx-wpfomo' ),
										'default'  => IBX_WPFomo_Helper::get_default_reviews_source(),
										'options'  => array(
											'custom' => __( 'Custom', 'ibx-wpfomo' ),
										),
										'toggle'   => array(
											'custom' => array(
												'fields' => array( 'reviews_group', 'review_template' ),
											),
										),
										'hide'     => array(
											'custom' => array(
												'fields' => array( 'notification_msg', 'notification_custom_form_msg' ),
											),
										),
										'priority' => 101,
									)
								),
							),
						),
					),
				),
				'content'       => array(
					'title'    => __( 'Content', 'ibx-wpfomo' ),
					'sections' => array(
						'content_section' => array(
							'title'  => __( 'Content', 'ibx-wpfomo' ),
							'fields' => array(
								// 'notification_layout'    => array(
								// 	'type'    => 'select',
								// 	'label'   => __( 'Notification Style', 'ibx-wpfomo' ),
								// 	'default' => 'second',
								// 	'options' => array(
								// 		'first'  => __( 'Highlight First Row', 'ibx-wpfomo' ),
								// 		'second' => __( 'Highlight Second Row', 'ibx-wpfomo' ),
								// 	),
								// ),
								'notification_msg'       => array(
									'type'      => 'template',
									'label'     => __( 'Notification Template', 'ibx-wpfomo' ),
									'default'   => array(
										'0' => __( '{{name}} from {{city}} signed up for', 'ibx-wpfomo' ),
										'1' => '{{title}}',
										'2' => '',
									),
									'variables' => array( '{{title}}', '{{name}}', '{{city}}', '{{state}}', '{{country}}', '{{time}}' ),
									'sanitize'  => false,
									'priority'  => 50,
								),
								'review_template'        => array(
									'type'      => 'template',
									'label'     => __( 'Review Template', 'ibx-wpfomo' ),
									'default'   => array(
										'0' => '{{rating}}',
										'1' => '{{title}}',
										'2' => '{{name}}',
									),
									'variables' => array( '{{rating}}', '{{title}}', '{{name}}' ),
									'sanitize'  => false,
									'priority'  => 100,
								),
								'fomo_desc'              => array(
									'type'          => 'editor',
									'label'         => '',
									'placeholder'   => __( 'Content you want to display in fomo bar.', 'ibx-wpfomo' ),
									'rows'          => 3,
									'sanitize'      => false,
									'media_buttons' => false,
									'priority'      => 150,
								),
								'button_text'            => array(
									'type'     => 'text',
									'label'    => __( 'Button Text', 'ibx-wpfomo' ),
									'default'  => '',
									'priority' => 250,
								),
								'button_url'             => array(
									'type'     => 'text',
									'label'    => __( 'Button Link', 'ibx-wpfomo' ),
									'default'  => '',
									'priority' => 300,
								),
								'button_url_target' => array(
									'type'        => 'checkbox',
									'label'       => __( 'Open link in new window', 'ibx-wpfomo' ),
									'default'     => '0',
									'description' => __( 'When a user clicks on the notifications, it will open the link in new window.', 'ibx-wpfomo' ),
									'priority'    => 310,
								),
								'conversion_group'       => array(
									'type'     => 'group',
									'title'    => __( 'Conversion', 'ibx-wpfomo' ),
									'priority' => 350,
									'fields'   => array(
										'title'   => array(
											'type'    => 'text',
											'label'   => __( 'Title', 'ibx-wpfomo' ),
											'default' => '',
										),
										'name'    => array(
											'type'    => 'text',
											'label'   => __( 'Name', 'ibx-wpfomo' ),
											'default' => '',
										),
										'email'   => array(
											'type'  => 'text',
											'label' => __( 'Email Address', 'ibx-wpfomo' ),
										),
										'city'    => array(
											'type'  => 'text',
											'label' => __( 'City', 'ibx-wpfomo' ),
										),
										'state'   => array(
											'type'  => 'text',
											'label' => __( 'State', 'ibx-wpfomo' ),
										),
										'country' => array(
											'type'  => 'text',
											'label' => __( 'Country', 'ibx-wpfomo' ),
										),
										'image'   => array(
											'type'  => 'photo',
											'label' => __( 'Image', 'ibx-wpfomo' ),
											'help'  => __( 'Use this field to set custom image in this conversion.', 'ibx-wpfomo' ),
										),
										'url'     => array(
											'type'    => 'text',
											'label'   => __( 'URL', 'ibx-wpfomo' ),
											'default' => '',
										),
										'time'	=> array(
											'type'	=> 'text',
											'label'	=> __( 'Time', 'ibx-wpfomo' ),
											'default' => '',
											'placeholder'	=> __( 'yyyy-mm-dd HH:mm', 'ibx-wpfomo' ),
											'help'	=> __( 'Time format: 24 hours i.e. 14:42', 'ibx-wpfomo' )
										),
									),
								),
								'reviews_group'          => array(
									'type'     => 'group',
									'title'    => __( 'Review', 'ibx-wpfomo' ),
									'priority' => 400,
									'fields'   => array(
										'title'  => array(
											'type'    => 'text',
											'label'   => __( 'Title', 'ibx-wpfomo' ),
											'default' => '',
										),
										'name'   => array(
											'type'    => 'text',
											'label'   => __( 'Name', 'ibx-wpfomo' ),
											'default' => '',
										),
										'email'  => array(
											'type'  => 'text',
											'label' => __( 'Email Address', 'ibx-wpfomo' ),
										),
										'image'  => array(
											'type'  => 'photo',
											'label' => __( 'Image', 'ibx-wpfomo' ),
											'help'  => __( 'Use this field to set custom image in this review.', 'ibx-wpfomo' ),
										),
										'url'    => array(
											'type'    => 'text',
											'label'   => __( 'URL', 'ibx-wpfomo' ),
											'default' => '',
											'sanitize_custom' => 'esc_url',
										),
										'rating' => array(
											'type'    => 'select',
											'label'   => __( 'Rating', 'ibx-wpfomo' ),
											'default' => '',
											'options' => array(
												'5' => 5,
												'4' => 4,
												'3' => 3,
												'2' => 2,
												'1' => 1,
											),
										),
									),
								),
							),
						),
						'countdown'       => array(
							'title'  => __( 'Countdown Timer', 'ibx-wpfomo' ),
							'fields' => array(
								'enable_countdown' => array(
									'type'     => 'checkbox',
									'label'    => __( 'Enable Countdown?', 'ibx-wpfomo' ),
									'default'  => '0',
									'sanitize' => false,
									'toggle'   => array(
										'1' => array(
											'fields' => array( 'countdown_text', 'expire_text', 'countdown_style', 'countdown_time', 'fixed_countdown_time' ),
										),
									),
									'priority' => 50,
								),
								'countdown_text'   => array(
									'type'     => 'text',
									'label'    => __( 'Countdown Text', 'ibx-wpfomo' ),
									'priority' => 100,
								),
								'expire_text'   => array(
									'type'     => 'text',
									'label'    => __( 'Expire Text', 'ibx-wpfomo' ),
									'default'    => __( 'Expired!', 'ibx-wpfomo' ),
									'priority' => 120,
								),
								'countdown_style'	=> array(
									'type'	=> 'select',
									'label'	=> __( 'Timer Style', 'ibx-wpfomo' ),
									'options'	=> array(
										'evergreen'	=> __( 'Evergreen', 'ibx-wpfomo' ),
										'fixed'	=> __( 'Fixed', 'ibx-wpfomo' ),
									),
									'default'	=> 'evergreen',
									'priority' => 130,
								),
								'countdown_time'   => array(
									'type'     => 'time',
									'label'    => __( 'Evergreen Time', 'ibx-wpfomo' ),
									'priority' => 150,
								),
								'fixed_countdown_time'	=> array(
									'type'	=> 'time',
									'label'	=> __( 'Fixed Time', 'ibx-fomo' ),
									'show_date'	=> true,
									'priority' => 140,
								),
							),
						),
					),
				),
				// 'design'		=> array(
				// 	'title'			=> __( 'Design', 'ibx-wpfomo' ),
				// 	'sections'		=> array(
				// 		'presets'		=> array(
				// 			'title'			=> '',
				// 			'fields'		=> array(
				// 				'skin'		=> array(
				// 					'type'			=> 'layout',
				// 					'label'			=> ''
				// 				),
				// 			)
				// 		),
				// 	),
				// ),
				'display'       => array(
					'title'    => __( 'Display', 'ibx-wpfomo' ),
					'sections' => array(
						'image_option' => array(
							'title'       => __( 'Image', 'ibx-wpfomo' ),
							'description' => __( 'By default the notification will display the custom avatar from the email address, below settings will override this.', 'ibx-wpfomo' ),
							'fields'      => array(
								'product_img'     => array(
									'type'        => 'checkbox',
									'label'       => __( 'Display product image', 'ibx-wpfomo' ),
									'description' => __( 'It will display your product image in the notification when available.', 'ibx-wpfomo' ),
									'default'     => '0',
									'hidden'      => true,
									'sanitize'    => false,
									'priority'    => 50,
								),
								'enable_gravatar_img'	=> array(
									'type'        => 'checkbox',
									'label'       => __( 'Enable Image from Gravatar', 'ibx-wpfomo' ),
									'description' => __( 'It will display the image from user\'s gravatar profile (requires user\'s email)', 'ibx-wpfomo' ),
									'default'     => '1',
									'hidden'      => false,
									'sanitize'    => false,
									'priority'    => 70,
								),
								'default_img_url' => array(
									'type'        => 'photo',
									'label'       => __( 'Default Image URL', 'ibx-wpfomo' ),
									'placeholder' => __( 'Upload an image or paste external URL of image.', 'ibx-wpfomo' ),
									'help'        => __( 'Default images are \'backup\' images for new notification. If an image is provided, it will override the default.', 'ibx-wpfomo' ),
									'priority'    => 100,
								),
								'disable_img'     => array(
									'type'        => 'checkbox',
									'label'       => __( 'Force Disable Images', 'ibx-wpfomo' ),
									'default'     => '0',
									'sanitize'    => false,
									'description' => __( 'If checked, it will not display any images in notification.', 'ibx-wpfomo' ),
									'priority'    => 150,
								),
							),
						),
						'visibility'   => array(
							'title'       => __( 'Visibility', 'ibx-wpfomo' ),
							'description' => __( 'You can set the specific targets where the notification will be rendered or be hidden.', 'ibx-wpfomo' ),
							'fields'      => array(
								'show_on'             => array(
									'type'     => 'select',
									'label'    => __( 'Show On?', 'ibx-wpfomo' ),
									'default'  => __( 'everywhere', 'ibx-wpfomo' ),
									'options'  => array(
										'everywhere' => __( 'Show everywhere', 'ibx-wpfomo' ),
										'selected'   => __( 'Show on selected', 'ibx-wpfomo' ),
										'hide'       => __( 'Hide on selected', 'ibx-wpfomo' ),
									),
									'toggle'   => array(
										'selected' => array(
											'fields' => array( 'global_locations', 'custom_locations', 'page_urls' ),
										),
										'hide'     => array(
											'fields' => array( 'global_locations', 'custom_locations', 'page_urls' ),
										),
									),
									'priority' => 50,
								),
								'global_locations'    => array(
									'type'        => 'suggest',
									'label'       => __( 'Global Locations', 'ibx-wpfomo' ),
									'placeholder' => __( 'Choose location...', 'ibx-wpfomo' ),
									'action'      => 'get_locations',
									'options'     => array(
										'type' => 'global',
									),
									'priority'    => 100,
								),
								'custom_locations'    => array(
									'type'        => 'suggest',
									'label'       => __( 'Custom Locations', 'ibx-wpfomo' ),
									'placeholder' => __( 'Choose location...', 'ibx-wpfomo' ),
									'action'      => 'get_locations',
									'render'	  => apply_filters( 'ibx_wpfomo_render_field_custom_locations', true ),
									'options'     => array(
										'type' => 'custom',
									),
									'priority'    => 150,
								),
								'page_urls'           => array(
									'type'     => 'textarea',
									'label'    => __( 'Target by URL(s)', 'ibx-wpfomo' ),
									'default'  => '',
									'rows'     => 5,
									'help'     => __( 'Enter one location fragment per line. Use * character as a wildcard. Example: category/peace/* to target all posts in category peace.', 'ibx-wpfomo' ),
									'render'	=> apply_filters( 'ibx_wpfomo_render_field_page_urls', true ),
									'priority' => 200,
								),
								'visibility_display'  => array(
									'type'     => 'select',
									'label'    => __( 'Display', 'ibx-wpfomo' ),
									'default'  => 'always',
									'options'  => array(
										'always'     => __( 'Always', 'ibx-wpfomo' ),
										'logged_out' => __( 'Logged Out User', 'ibx-wpfomo' ),
										'logged_in'  => __( 'Logged In User', 'ibx-wpfomo' ),
									),
									'priority' => 250,
								),
								'visibility_visitors' => array(
									'type'     => 'select',
									'label'    => __( 'Visitors', 'ibx-wpfomo' ),
									'default'  => 'all',
									'options'  => array(
										'all'       => __( 'All Visitors', 'ibx-wpfomo' ),
										'new'       => __( 'New Visitors Only', 'ibx-wpfomo' ),
										'returning' => __( 'Returning Visitors Only', 'ibx-wpfomo' ),
									),
									'priority' => 300,
								),
							),
						),
					),
				),
				'customize'     => array(
					'title'    => __( 'Customize', 'ibx-wpfomo' ),
					'sections' => array(
						'appearance' => array(
							'title'  => __( 'Appearance', 'ibx-wpfomo' ),
							'fields' => array(
								'position'          => array(
									'type'     => 'select',
									'label'    => __( 'Positon', 'ibx-wpfomo' ),
									'default'  => 'bottom-left',
									'options'  => array(
										'bottom-left'  => __( 'Bottom Left', 'ibx-wpfomo' ),
										'bottom-right' => __( 'Bottom Right', 'ibx-wpfomo' ),
									),
									'priority' => 50,
								),
								'position_fomo_bar' => array(
									'type'     => 'select',
									'label'    => __( 'Position', 'ibx-wpfomo' ),
									'default'  => 'top',
									'options'  => array(
										'top'    => __( 'Top', 'ibx-wpfomo' ),
										'bottom' => __( 'Bottom', 'ibx-wpfomo' ),
									),
									'priority' => 60,
								),
								'sticky'            => array(
									'type'        => 'checkbox',
									'label'       => __( 'Sticky Bar?', 'ibx-wpfomo' ),
									'default'     => '0',
									'sanitize'    => false,
									'description' => __( 'If checked, this will fixed Notification Bar at top or bottom.', 'ibx-wpfomo' ),
									'priority'    => 100,
								),
								'closable'          => array(
									'type'        => 'checkbox',
									'label'       => __( 'Show "Close" button', 'ibx-wpfomo' ),
									'description' => __( 'It will display the close button at the top right corner.', 'ibx-wpfomo' ),
									'default'     => '1',
									'priority'    => 150,
								),
								'hide_mobile'       => array(
									'type'        => 'checkbox',
									'label'       => __( 'Hide on Mobile', 'ibx-wpfomo' ),
									'default'     => '0',
									'description' => __( 'It will hide the notification on mobile devices.', 'ibx-wpfomo' ),
									'priority'    => 200,
								),
							),
						),
						'timing'     => array(
							'title'       => __( 'Timing', 'ibx-wpfomo' ),
							'description' => __( 'We have optimized the timing for highest conversions with the Auto setting. However, in some instances you may want to set your own custom values.', 'ibx-wpfomo' ),
							'collapsable' => true,
							'toggle_type' => 'field',
							'fields'      => array(
								'initial_delay' => array(
									'type'        => 'number',
									'label'       => __( 'Initial Delay', 'ibx-wpfomo' ),
									'default'     => '5',
									'description' => __( 'seconds', 'ibx-wpfomo' ),
									'help'        => __( 'Initial delay before display.', 'ibx-wpfomo' ),
									'priority'    => 50,
									'row_attrs'   => array(
										'data-label-notification' => __( 'Delay before 1st Notification', 'ibx-wpfomo' ),
										'data-help-notification'  => __( 'Initial delay of displaying first notification.', 'ibx-wpfomo' ),
									),
								),
								'auto_hide'     => array(
									'type'        => 'checkbox',
									'label'       => __( 'Auto Hide?', 'ibx-wpfomo' ),
									'default'     => '0',
									'description' => __( 'If checked, notification bar will be hidden after the time set below.', 'ibx-wpfomo' ),
									'priority'    => 100,
								),
								'display_time'  => array(
									'type'        => 'number',
									'label'       => __( 'Hide After', 'ibx-wpfomo' ),
									'default'     => '4',
									'description' => __( 'seconds', 'ibx-wpfomo' ),
									'help'        => __( 'Hide after the given time.', 'ibx-wpfomo' ),
									'priority'    => 150,
									'row_attrs'   => array(
										'data-label-notification' => __( 'Display each Notification for', 'ibx-wpfomo' ),
										'data-help-notification'  => __( 'Hide the Notification after the given time.', 'ibx-wpfomo' ),
									),
								),
								'delay_between' => array(
									'type'        => 'number',
									'label'       => __( 'Delay between notifications', 'ibx-wpfomo' ),
									'default'     => '9',
									'description' => __( 'seconds', 'ibx-wpfomo' ),
									'help'        => __( 'Delay between each notifications.', 'ibx-wpfomo' ),
									'priority'    => 200,
								),
							),
						),
						'behaviour'  => array(
							'title'       => __( 'Behaviour', 'ibx-wpfomo' ),
							'description' => __( 'We have optimized the notification behaviour for maximum conversions. However, you can edit the settings by switching to <strong>Custom</strong> mode.', 'ibx-wpfomo' ),
							'collapsable' => true,
							'toggle_type' => 'field',
							'fields'      => array(
								'display_last'      => array(
									'type'        => 'number',
									'label'       => __( 'Display the last', 'ibx-wpfomo' ),
									'description' => __( 'conversions', 'ibx-wpfomo' ),
									'default'     => 30,
									'priority'    => 50,
								),
								'display_last_days' => array(
									'type'        => 'number',
									'label'       => __( 'Only show conversions from the last', 'ibx-wpfomo' ),
									'description' => __( 'days', 'ibx-wpfomo' ),
									'default'     => 7,
									'priority'    => 100,
								),
								// 'randomize'   => array(
								// 'type'              => 'checkbox',
								// 'label'             => __('Randomize notifications', 'ibx-wpfomo'),
								// 'description'       => __('Makes notifications seem more lifelike by randomizing them.', 'ibx-wpfomo'),
								// ),
								'loop'              => array(
									'type'        => 'checkbox',
									'label'       => __( 'Loop notification', 'ibx-wpfomo' ),
									'default'     => '1',
									'description' => __( 'Repeats the sequence of your notifications, if visitor still on page when they run out.', 'ibx-wpfomo' ),
									'priority'    => 150,
								),
								'link_target'       => array(
									'type'        => 'checkbox',
									'label'       => __( 'Open link in new window', 'ibx-wpfomo' ),
									'default'     => '1',
									'description' => __( 'When a user clicks on the notifications, it will open the link in new window.', 'ibx-wpfomo' ),
									'priority'    => 200,
								),
							),
						),
						'design'     => array(
							'title'       => __( 'Design', 'ibx-wpfomo' ),
							'description' => __( 'We have designed the notification for best appearance. If you would like to make some adjustments, you can set it to <strong>Custom</strong> mode.', 'ibx-wpfomo' ),
							'collapsable' => true,
							'toggle_type' => 'field',
							'fields'      => array(
								'text_color'              => array(
									'type'     => 'color',
									'label'    => __( 'Text Color', 'ibx-wpfomo' ),
									'default'  => '#000000',
									'priority' => 100,
								),
								'background_color'        => array(
									'type'     => 'color',
									'label'    => __( 'Background Color', 'ibx-wpfomo' ),
									'default'  => '#ffffff',
									'priority' => 150,
								),
								'countdown_text_color'    => array(
									'type'     => 'color',
									'label'    => __( 'Countdown Text Color', 'ibx-wpfomo' ),
									'default'  => '#000000',
									'priority' => 200,
								),
								'countdown_background_color' => array(
									'type'     => 'color',
									'label'    => __( 'Countdown Background Color', 'ibx-wpfomo' ),
									'default'  => '#eeeeee',
									'priority' => 250,
								),
								'link_color'              => array(
									'type'     => 'color',
									'label'    => __( 'Link Color', 'ibx-wpfomo' ),
									'default'  => '#000000',
									'priority' => 300,
								),
								'star_color'              => array(
									'type'     => 'color',
									'label'    => __( 'Rating Star Color', 'ibx-wpfomo' ),
									'default'  => '#dd9933',
									'priority' => 350,
								),
								'round_corners'           => array(
									'type'        => 'number',
									'label'       => __( 'Box Round Corners', 'ibx-wpfomo' ),
									'default'     => '0',
									'description' => 'px',
									'priority'    => 400,
								),
								'vertical_padding'	=> array(
									'type'		=> 'number',
									'label'       => __( 'Text Vertical Spacing', 'ibx-wpfomo' ),
									'default'     => '0',
									'description' => 'px',
									'priority'    => 415,
								),
								'horizontal_padding'	=> array(
									'type'		=> 'number',
									'label'       => __( 'Text Horizontal Spacing', 'ibx-wpfomo' ),
									'default'     => '10',
									'description' => 'px',
									'priority'    => 416,
								),
								'img_size'	=> array(
									'type'		=> 'number',
									'label'		=> __( 'Image Size', 'ibx-wpfomo' ),
									'default'	=> '70',
									'description' => 'px',
									'priority'    => 425,
								),
								'img_round_corners'       => array(
									'type'        => 'number',
									'label'       => __( 'Image Round Corners', 'ibx-wpfomo' ),
									'default'     => '0',
									'description' => 'px',
									'priority'    => 450,
								),
								'border_section'          => array(
									'type'     => 'divider',
									'text'     => __( 'Border', 'ibx-wpfomo' ),
									'priority' => 500,
								),
								'border'                  => array(
									'type'        => 'number',
									'label'       => __( 'Border Stroke', 'ibx-wpfomo' ),
									'default'     => '1',
									'description' => 'px',
									'priority'    => 550,
								),
								'border_color'            => array(
									'type'     => 'color',
									'label'    => __( 'Color', 'ibx-wpfomo' ),
									'default'  => '#dddddd',
									'priority' => 600,
								),
								'shadow_section'          => array(
									'type'     => 'divider',
									'text'     => __( 'Shadow', 'ibx-wpfomo' ),
									'priority' => 650,
								),
								'shadow_blur'             => array(
									'type'        => 'number',
									'label'       => __( 'Blur', 'ibx-wpfomo' ),
									'default'     => '25',
									'description' => 'px',
									'priority'    => 700,
								),
								'shadow_spread'           => array(
									'type'        => 'number',
									'label'       => __( 'Spread', 'ibx-wpfomo' ),
									'default'     => '0',
									'description' => 'px',
									'priority'    => 750,
								),
								'shadow_color'            => array(
									'type'     => 'color',
									'label'    => __( 'Color', 'ibx-wpfomo' ),
									'default'  => '#999999',
									'priority' => 800,
								),
								'shadow_opacity'          => array(
									'type'        => 'number',
									'label'       => __( 'Opacity', 'ibx-wpfomo' ),
									'default'     => '30',
									'description' => '%',
									'size'        => 5,
									'priority'    => 850,
								),
								'button_section'          => array(
									'type'     => 'divider',
									'text'     => __( 'Button', 'ibx-wpfomo' ),
									'priority' => 900,
								),
								'button_bg_color'         => array(
									'type'     => 'color',
									'label'    => __( 'Background Color', 'ibx-wpfomo' ),
									'default'  => '#333333',
									'priority' => 950,
								),
								'button_bg_hover_color'   => array(
									'type'     => 'color',
									'label'    => __( 'Background Hover Color', 'ibx-wpfomo' ),
									'default'  => '#000000',
									'priority' => 1000,
								),
								'button_text_color'       => array(
									'type'     => 'color',
									'label'    => __( 'Text Color', 'ibx-wpfomo' ),
									'default'  => '#ffffff',
									'priority' => 1050,
								),
								'button_text_hover_color' => array(
									'type'     => 'color',
									'label'    => __( 'Text Hover Color', 'ibx-wpfomo' ),
									'default'  => '#ffffff',
									'priority' => 1100,
								),
								'button_border'           => array(
									'type'        => 'number',
									'label'       => __( 'Border Width', 'ibx-wpfomo' ),
									'default'     => '0',
									'description' => 'px',
									'priority'    => 1150,
								),
								'button_border_color'     => array(
									'type'     => 'color',
									'label'    => __( 'Border Color', 'ibx-wpfomo' ),
									'default'  => '#333333',
									'priority' => 1200,
								),
								'button_border_hover_color' => array(
									'type'     => 'color',
									'label'    => __( 'Border Hover Color', 'ibx-wpfomo' ),
									'default'  => '#000000',
									'priority' => 1250,
								),
								'button_border_radius'    => array(
									'type'        => 'number',
									'label'       => __( 'Round Corners', 'ibx-wpfomo' ),
									'default'     => '4',
									'description' => 'px',
									'priority'    => 1300,
								),
							),
						),
					),
				),
			)
		),
	);
}

function ibx_wpfomo_fomo_bar_toggle_data() {
	return array(
		'sections' => array(
			'countdown',
			'button_style',
			'timing',
		),
		'fields'   => array(
			'fomo_desc',
			'sticky',
			'button_text',
			'button_url',
			'button_url_target',
			'position_fomo_bar',
			'text_color',
			'background_color',
			'countdown_text_color',
			'countdown_background_color',
			'border_section',
			'border',
			'border_color',
			'button_section',
			'button_bg_color',
			'button_bg_hover_color',
			'button_text_color',
			'button_text_hover_color',
			'button_border',
			'button_border_color',
			'button_border_hover_color',
			'button_border_radius',
			'auto_hide',
			'initial_delay',
			'display_time',
			'closable',
		),
	);
}

function ibx_wpfomo_conversion_toggle_data() {
	return array(
		'sections' => array(
			'image_option',
			'timing',
			'behaviour',
		),
		'fields'   => array(
			'conversions_source',
			'notification_layout',
			'position',
			'text_color',
			'background_color',
			'link_color',
			'border_section',
			'border',
			'border_color',
			'round_corners',
			'vertical_padding',
			'horizontal_padding',
			'img_size',
			'img_round_corners',
			'typography_section',
			'first_row_font_size',
			'second_row_font_size',
			'max_per_page',
			'delay_between',
			'random',
			'closable',
			'loop',
			'randomize',
			'link_target',
			'initial_delay',
			'display_time',
		),
	);
}

function ibx_wpfomo_reviews_toggle_data() {
	 return array(
		'sections' => array(
			'image_option',
			'timing',
			'behaviour',
		),
		'fields'   => array(
			'reviews_source',
			'notification_layout',
			'reviews_group',
			'review_template',
			'position',
			'text_color',
			'background_color',
			'border_section',
			'border',
			'border_color',
			'round_corners',
			'vertical_padding',
			'horizontal_padding',
			'img_size',
			'img_round_corners',
			'star_color',
			'max_per_page',
			'delay_between',
			'random',
			'closable',
			'loop',
			'randomize',
			'link_target',
			'initial_delay',
			'display_time',
		),
	);
}

do_action( 'ibx_wpfomo_before_metabox_load' );

MetaBox_Tabs::add_meta_box( ibx_wpfomo_metabox_args() );
