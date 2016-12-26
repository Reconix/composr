(function ($cms) {
    'use strict';

    $cms.views.PointsGive = PointsGive;
    function PointsGive() {
        PointsGive.base(this, 'constructor', arguments);
    }

    $cms.inherits(PointsGive, $cms.View, {
        events: function () {
            return {
                'submit .js-submit-check-form': 'checkForm'
            };
        },

        checkForm: function (e, form) {
            if (!check_form(form)) {
                e.preventDefault();
            }
        }
    });

}(window.$cms));