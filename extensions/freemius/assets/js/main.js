;(function($) {
    $(document).ready(function() {
        $('.ibx-wpfomo-freemius-connect').on('click', function() {
            
            var store_id = $('#ibx_wpfomo_freemius_store_id').val(),
                public_key  = $('#ibx_wpfomo_freemius_public_key').val(),
                secret_key  = $('#ibx_wpfomo_freemius_secret_key').val(),
                self        = $(this),
                loader      = self.parent().find('.mbt-loader')                

			self.addClass('disabled');
			self.parent().find('.ibx-wpfomo-error-message').remove();
            loader.show();
            self.parent().find('.ibx-wpfomo-success-message').remove();
            $.ajax({
                type: 'post',
                url: ajaxurl,
                data: {
                    action: 'ibx_wpfomo_connect_freemius',
                    store_id: store_id,
                    public_key: public_key,
                    secret_key: btoa( secret_key )
                },
                success: function(response) {
                    var data = JSON.parse(response);                    
                    if ( data.error ) {
                        self.after( '<div class="ibx-wpfomo-error-message">' + data.error + '</div>' );
                    }
                    else {
                        self.parent().find('.ibx-wpfomo-error-message').remove();
                        self.after( '<div class="ibx-wpfomo-success-message">' + wpfomo_freemius.messages.connect_success + '</div>' );                        
                    }

                    self.removeClass('disabled');
                    loader.hide();
                }
            });
        });        
    });
})(jQuery);
