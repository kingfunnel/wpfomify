;(function($) {
    $(document).ready(function() {
        $('.ibx-wpfomo-envato-connect').on('click', function() {
			var token = $('#ibx_wpfomo_envato_personal_token').val(),
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
					action: 'ibx_wpfomo_connect_envato',
					token: token,
                },
                success: function(response) {
                    if ( ! response.success ) {
                        self.after( '<div class="ibx-wpfomo-error-message">' + response.data + '</div>' );
                    } else {
                        self.parent().find('.ibx-wpfomo-error-message').remove();
                        self.after( '<div class="ibx-wpfomo-success-message">' + wpfomo_envato.messages.connect_success + '</div>' );
                    }

                    self.removeClass('disabled');
                    loader.hide();
                }
            });
        });
    });
})(jQuery);
