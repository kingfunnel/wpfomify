;(function ($) {
	// Load the object.
	var form_values = new Array();
	$(document).ready(function () {
		if (typeof ibx_fomo !== 'undefined' && typeof ibx_fomo.custom_form_details[ibx_fomo.post_id] !== 'undefined' ) {

			Object.keys( ibx_fomo.custom_form_details[ibx_fomo.post_id] ).forEach( function ( inp ) {
				var form = ibx_fomo.custom_form_details[ibx_fomo.post_id][inp];
				if (inp.indexOf('nf-form') >= 0) { //ninja form.
					$(document).on('nfFormSubmitResponse', { form_key: inp, custom_form_url: form.custom_form_url, custom_form_title: form.custom_form_title, form_src_conversion_id: form.custom_form_conversion_id, custom_form_email_select: form.custom_form_email_select, custom_form_name_select: form.custom_form_name_select, custom_form_notification_link: form.custom_form_notification_link }, function (e, submitted_nf_form_data) {

						var email = e.data.custom_form_email_select != '' ? submitted_nf_form_data.response.data.fields_by_key[e.data.custom_form_email_select.toLowerCase()].value : '';
						var name = e.data.custom_form_name_select != '' ? submitted_nf_form_data.response.data.fields_by_key[e.data.custom_form_name_select.toLowerCase()].value : '';					


						form_values = {};
						$this = $(this);
						form_values['form_key'] = e.data.form_key;
						if ('' !== e.data.custom_form_notification_link) {
							form_values['form_src_url'] = e.data.custom_form_notification_link;
						}
						else {
							form_values['form_src_url'] = e.data.custom_form_url;
						}

						form_values['form_src_conversion_id'] = e.data.form_src_conversion_id;
						form_values['custom_form_email_select'] = e.data.custom_form_email_select;
						form_values['custom_form_name_select'] = e.data.custom_form_name_select;
						form_values['custom_form_title'] = e.data.custom_form_title;
						form_values['form_data'] = {};
						if ( email !='' ) {
							var obj = {};
							obj['field_name'] = e.data.custom_form_email_select;
							obj['field_value'] = email;
							form_values['form_data'][0] = obj;
						}

						if ( name != '' ) {
							var obj = {};
							obj['field_name'] = e.data.custom_form_name_select;
							obj['field_value'] = name;
							form_values['form_data'][1] = obj;
						}

						if (!$.isEmptyObject( form_values )) {
							$.ajax({
								type: 'post',
								url: ibx_fomo.ajaxurl,
								data: {
									action: 'ibx_wpfomo_save_custom_form_data',
									nonce: ibx_fomo.nonce,
									post_id: ibx_fomo.post_id,
									ibx_wpfomo_custom_form_data: (form_values),
								},
								success: function (res) {
									// convert string to json object.
									//debugger;
								}
							});
						}
					});
				}
				//else if ($(inp).attr('class').indexOf('pp-contact-form') >= 0 || $(inp).attr('class').indexOf('fl-contact-form') >= 0 || $(inp).attr('class').indexOf('pp-subscribe-form') >= 0 || $(inp).attr('class').indexOf('fl-subscribe-form') >= 0 ) {
				else if ( typeof $(inp).attr('class') !== 'undefined' && ( $(inp).attr('class').indexOf('pp-contact-form') >= 0 || $(inp).attr('class').indexOf('fl-contact-form') >= 0 || ( ibx_fomo.form_classes.some(function (item) { return $(inp).attr('class').indexOf(item) >= 0; } ) ) ) ) {
					//PP contact form OR standard or subscribe form

					$send_btn = $(inp).find('.fl-button-wrap > .fl-button');
					$($send_btn).on('click', { form_key: inp, custom_form_url: form.custom_form_url, custom_form_title: form.custom_form_title, form_src_conversion_id: form.custom_form_conversion_id, custom_form_email_select: form.custom_form_email_select, custom_form_name_select: form.custom_form_name_select, custom_form_notification_link: form.custom_form_notification_link }, function (e) {
						form_values = {};
						$this = $(inp);
						form_values['form_key'] = e.data.form_key;
						if ('' !== e.data.custom_form_notification_link) {
							form_values['form_src_url'] = e.data.custom_form_notification_link;
						}
						else {
							form_values['form_src_url'] = e.data.custom_form_url;
						}

						form_values['form_src_conversion_id'] = e.data.form_src_conversion_id;
						form_values['custom_form_email_select'] = e.data.custom_form_email_select;
						form_values['custom_form_name_select'] = e.data.custom_form_name_select;
						form_values['custom_form_title'] = e.data.custom_form_title;
						form_values['form_data'] = {};
						// $.each(
						// 	$this.serializeArray(),
						// 	function (i, field) {
						// 		if (e.data.custom_form_name_select == field.name || e.data.custom_form_email_select == field.name) {
						// 			if( field.value != '' ){
						// 				var obj = {};
						// 				obj['field_name'] = field.name;
						// 				obj['field_value'] = field.value;
						// 				form_values['form_data'][i] = obj;
						// 			}
						// 		}

						// 	}
						// );

						var email = e.data.custom_form_email_select != '' ? $('*[name=' + e.data.custom_form_email_select + ']').val() : '';
						var name = e.data.custom_form_name_select != '' ? $('*[name=' + e.data.custom_form_name_select + ']').val() : '';

						if (email != '') {
							var obj = {};
							obj['field_name'] = e.data.custom_form_email_select;
							obj['field_value'] = email;
							form_values['form_data'][0] = obj;
						}

						if (name != '') {
							var obj = {};
							obj['field_name'] = e.data.custom_form_name_select;
							obj['field_value'] = name;
							form_values['form_data'][1] = obj;
						}
						if (!$.isEmptyObject( form_values )) {
							$.ajax({
								type: 'post',
								url: ibx_fomo.ajaxurl,
								data: {
									action: 'ibx_wpfomo_save_custom_form_data',
									nonce: ibx_fomo.nonce,
									post_id: ibx_fomo.post_id,
									ibx_wpfomo_custom_form_data: (form_values),
								},
								success: function (res) {
									// convert string to json object.
									//debugger;
								}
							});
						}
					});
				}
				else {

					$(inp).on('submit', { form_key: inp, custom_form_url: form.custom_form_url, custom_form_title: form.custom_form_title, form_src_conversion_id: form.custom_form_conversion_id, custom_form_email_select: form.custom_form_email_select, custom_form_name_select: form.custom_form_name_select, custom_form_notification_link: form.custom_form_notification_link }, function (e) {
						form_values = {};
						$this = $(this);
						form_values['form_key'] = e.data.form_key;
						if ('' !== e.data.custom_form_notification_link) {
							form_values['form_src_url'] = e.data.custom_form_notification_link;
						}
						else {
							form_values['form_src_url'] = e.data.custom_form_url;
						}

						form_values['form_src_conversion_id'] = e.data.form_src_conversion_id;
						form_values['custom_form_email_select'] = e.data.custom_form_email_select;
						form_values['custom_form_name_select'] = e.data.custom_form_name_select;
						form_values['custom_form_title'] = e.data.custom_form_title;
						form_values['form_data'] = {};
						$.each(
							$this.serializeArray(),
							function (i, field) {
								if (e.data.custom_form_name_select == field.name || e.data.custom_form_email_select == field.name) {
									if (field.value != '') {
										var obj = {};
										obj['field_name'] = field.name;
										obj['field_value'] = field.value;
										form_values['form_data'][i] = obj;
									}
								}

							}
						);

						if (!$.isEmptyObject(form_values)) {
							$.ajax({
								type: 'post',
								url: ibx_fomo.ajaxurl,
								data: {
									action: 'ibx_wpfomo_save_custom_form_data',
									nonce: ibx_fomo.nonce,
									post_id: ibx_fomo.post_id,
									ibx_wpfomo_custom_form_data: (form_values),
								},
								success: function (res) {
									// convert string to json object.
									//debugger;
								}
							});
						}
					});
				}
			});
		}
	});
})(jQuery);
