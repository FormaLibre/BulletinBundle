(function () {
    'use strict';
    
    // Click on widget create button
    $('.addPeriodeBtn').on('click', function () {
        window.Claroline.Modal.displayForm(
            Routing.generate('formalibreBulletinPeriodeAdd'),
            refreshPage,
            function() {}
        );
    });

    $('.editPeriodeBtn').on('click', function (event) {
        var periodeId = $(event.currentTarget).data('periode-id');

        window.Claroline.Modal.displayForm(
            Routing.generate('formalibreBulletinPeriodeEdit', {'periode': periodeId}),
            refreshPage,
            function() {}
        );
    });

    $('.refresh-periode-btn').on('click', function () {
        var periodeId = $(this).data('periode-id');
        
        $.ajax({
            url: Routing.generate(
                'formalibre_bulletin_periode_options_refresh',
                {'periode': periodeId}
            ),
            type: 'POST',
            success: function () {}
        });
    });

    var refreshPage = function () {
        console.log('reload');
        window.location.reload();
    };
})();