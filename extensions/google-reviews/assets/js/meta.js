;(function($) {
	$(window).on('load', function() {
		var fieldsToggle = function( type ) {
			if ( 'reviews' === type ) {
				if ( 'google-reviews' === $('#ibx_wpfomo_reviews_source').val() ) {
					$('#mbt-field-display_last').hide();
					$('#mbt-field-display_last_days').hide();
					$('#ibx_wpfomo_gr_notification_link').trigger('change');
				}
			} else {
				$('#mbt-field-display_last').show();
				$('#mbt-field-display_last_days').show();
			}
		};

		setTimeout(function() {
			fieldsToggle( $('#ibx_wpfomo_type').val() );

			$(document).on('ibx_wpfomo_type_change', function(e, type) {
				fieldsToggle( type );
			});

			$(document).on('ibx_wpfomo_source_change', function(e, field, type) {
				fieldsToggle( type );
			});
		}, 600);
	});
})(jQuery);