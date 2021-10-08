;(function($) {
	$(window).on('load', function() {
		var fieldsToggle = function( type ) {
			$('#mbt-field-woo_review_template').hide();
			if ( 'reviews' === type ) {
				if ( 'woocommerce' === $('#ibx_wpfomo_reviews_source').val() ) {
					$('#mbt-field-woo_review_template').show();
				}
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