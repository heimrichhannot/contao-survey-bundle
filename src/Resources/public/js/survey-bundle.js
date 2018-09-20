let jQuery = require('jquery');

(function($) {
    window.SurveyBundle = {
        onReady: function() {
            this.checkMatrix();
        },
        checkMatrix: function() {
            $('.matrix td input').each(function(a, b) {
                $(b).parent().click(function(a) {
                    'radio' === $(b).attr('type') ?
                        $(b).parent().parent().find('input[type=radio]').each(function(a, c) {
                            $(c)[0] !== $(b)[0] ? $(c).prop('checked', !1) : $(c).prop('checked', !0);
                        }) :
                        'checkbox' === $(b).attr('type') &&
                        $(b).parent().parent().find('input[type=checkbox]').each(function(c, d) {
                            $(d)[0] === $(b)[0] && 'TD' === a.target.nodeName &&
                            $(d).prop('checked', !$(d).prop('checked'));
                        });
                });
            });
        },
    };

    module.exports = SurveyBundle;

    $(document).ready(function() {
        SurveyBundle.onReady();
    });
})(jQuery);