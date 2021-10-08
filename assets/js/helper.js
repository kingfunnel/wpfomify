/**
 * WPfomify Helper Script
 * @since 1.0.0
 */
;(function($) {

    // Hex to RGBA
    $.ibx_wpfomo_hex2rgba = function( hex, opacity ) {
        var c;
        if(/^#([A-Fa-f0-9]{3}){1,2}$/.test(hex)){
            c = hex.substring(1).split('');
            if (c.length == 3) {
                c = [c[0], c[0], c[1], c[1], c[2], c[2]];
            }
            c = '0x' + c.join('');
            return 'rgba('+[(c>>16)&255, (c>>8)&255, c&255].join(',')+','+opacity+')';
        }

        return hex;
    }

    // Preview
    IBXFomoPreview = {
        init: function( data = [] )
        {
            if ( data.length === 0 ) {
                return;
			}
			
			var postId = $('#post_ID').val();
			if ( $('#ibx-wpfomo-preview-style').length === 0 ) {
				$('<style id="ibx-wpfomo-preview-style"></style>').appendTo('head');
			}

            data.forEach(function(item) {
                if ( typeof item === 'object' ) {
                    var unit = '';
                    if ( typeof item.unit !== 'undefined' ) {
                        unit = item.unit;
                    }
                    IBXFomoPreview._preview( item.field, item.selector, item.property, unit );
                }
            });
        },
        _preview: function( field, selector, property, unit = '' )
        {
            if ( '' === field ) {
                return;
			}
			
			var css = '';

            if ( 'box-shadow' === property && typeof field === 'object' ) {
                Object.keys( field ).forEach(function(item) {
                    $(field[item]).on('focus blur change keyup', function() {

                        var h = ( typeof field.horizontal !== 'undefined' && $(field.horizontal).val() !== '' ) ? $(field.horizontal).val() + 'px' : '0';
                        var v = ( typeof field.vertical !== 'undefined' && $(field.vertical).val() !== '' ) ? $(field.vertical).val() + 'px' : '0';
                        var b = ( typeof field.blur !== 'undefined' && $(field.blur).val() !== '' ) ? $(field.blur).val() + 'px' : '0';
                        var s = ( typeof field.spread !== 'undefined' && $(field.spread).val() !== '' ) ? $(field.spread).val() + 'px' : '0';
                        var c = ( typeof field.color !== 'undefined' && $(field.color).val() !== '' ) ? $(field.color).val() : '0';
                        var o = ( typeof field.opacity !== 'undefined' && $(field.opacity).val() !== '' ) ? parseInt($(field.opacity).val()) / 100 : '30';

                        if ( isNaN(o) ) {
                            o = '0.3';
                        }

                        c = $.ibx_wpfomo_hex2rgba( c, o );

                        var val = [h, v, b, s, c].join(' ');

						$(selector).css(property, val);

						css += selector + ' { ' + property + ': ' + val + ' !important; }';
						$('#ibx-wpfomo-preview-style').html(css);
                    });
                });
            } else {
                $(field).on('focus blur change keyup', function() {
                    var val = $(this).val();
                    if ( val !== '' ) {
						$(selector).css(property, val + unit);
						
						css += selector + ' { ' + property + ': ' + val + unit + ' !important; }';
						$('#ibx-wpfomo-preview-style').html(css);
                    }
                });
			}
        }
    };

})(jQuery);
