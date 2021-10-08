;(function($) {
    $(document).ready(function() {
        $('.ibx-wpfomo-google-places-api-connect').on('click', function() {
            
			var self        = $(this),
				apiKey		= $('#ibx_wpfomo_google_places_api_key').val(),
                loader      = self.parent().find('.mbt-loader')                

			self.addClass('disabled');
			self.parent().find('.ibx-wpfomo-error-message').remove();
			self.parent().find('.ibx-wpfomo-success-message').remove();
            loader.show();

			$.ajax({
                type: 'post',
                url: ajaxurl,
                data: {
                    action: 'ibx_wpfomo_connect_google_places_api',
                    api_key: apiKey,                  
                },
                success: function(response) {                  
                    if ( ! response.success ) {
                        self.after( '<div class="ibx-wpfomo-error-message">' + response.data + '</div>' );
                    } else {
						self.parent().find('.ibx-wpfomo-error-message').remove();
                        self.after( '<div class="ibx-wpfomo-success-message">' + wpfomo_google_reviews.messages.connect_success + '</div>' );                        
                    }

                    self.removeClass('disabled');
                    loader.hide();
                }
            });
        });        
    });
})(jQuery);
