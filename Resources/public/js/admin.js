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

    // Click on widget create button
    $('.editPeriodeBtn').on('click', function (event) {
        var periodeId = $(event.currentTarget).data('periode-id');

        window.Claroline.Modal.displayForm(
            Routing.generate('formalibreBulletinPeriodeEdit', {'periode': periodeId}),
            refreshPage,
            function() {}
        );
    });

    var refreshPage = function () {
        console.log('reload');
        window.location.reload();
    };
})();