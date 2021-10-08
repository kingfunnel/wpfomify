;(function($) {

	var templateVars = $('input[name="ibx_wpfomo_gf_template[]"]').parents('.mbt-field-control-wrapper').find('.ibx-wpfomo-template-vars').html() + '<a href="#" class="ibx-wpfomo-tags-more" style="box-shadow: none !important;">Show more</a> <br />';

	if ( $('#ibx_wpfomo_gf_form').length > 0 ) {

		$('#ibx_wpfomo_gf_form').on('change', function() {
			if ( 'gravity-forms' !== $('#ibx_wpfomo_conversions_source').val() ) {
				return;
			}
			var formId = $(this).val();

			MBT._showLoader( 'gf_field_email' );
			MBT._showLoader( 'gf_template' );

			$.ajax({
				type: 'post',
				url: ajaxurl,
				data: {
					action: 'wpfomo_gforms_get_fields',
					form_id: formId
				},
				success: function( response ) {
					var data = JSON.parse( response );
					if ( data.error ) {
						alert( data.error );
						return;
					}

					$('#ibx_wpfomo_gf_field_email').html( data.data.options );

					$('#ibx_wpfomo_gf_field_email option[value="'+ibx_wpfomo_settings.gf_field_email+'"]').attr('selected', 'selected');

					var tags_obj = data.data.tags;
					var tags = Object.keys( data.data.tags );
					var tagsStr = '<ul class="ibx-wpfomo-custom-tags" style="display: none; margin-top: 10px; margin-bottom: 0;">';
					var count = 1;

					tags.forEach(function( tag ) {
						tagsStr += '<li style="display: inline-block; margin-right: 15px;"><span class="ibx-wpfomo-highlight ibx-wpfomo-merge-tag">'+tag+'</span>' + ' : ' + tags_obj[tag] + '</li>';
						count++;
					});

					tagsStr += '</ul>';

					$('input[name="ibx_wpfomo_gf_template[]"]').parents('.mbt-field-control-wrapper').find('.ibx-wpfomo-template-vars').html(templateVars + tagsStr).css('font-style', 'normal');

					MBT._hideLoader( 'gf_field_email' );
					MBT._hideLoader( 'gf_template' );

					$('.ibx-wpfomo-tags-more').on('click', function(e) {
						e.preventDefault();
						e.stopPropagation();

						var $this = $(this);

						$(this).parent().find('.ibx-wpfomo-custom-tags').slideToggle(function() {
							if ( $(this).is(':visible') ) {
								$this.text('Show less');
							} else {
								$this.text('Show more');
							}
						});
					});
				}
			});
		});

		$('#ibx_wpfomo_gf_form').trigger('change');
	}

})(jQuery);