(function () {
    'use strict';

    var translator = window.Translator;
    
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

    $('.delete-periode-btn').on('click', function(event) {
        var periode = $(event.target).attr('data-periode');
        var periodeName = $(event.target).attr('data-name');
        var url = Routing.generate('formalibre_bulletin_remove_periode', {'periode': periode});
        window.Claroline.Modal.confirmRequest(
            url, 
            function(event, successParameters, data) {
                $('#periode-' + periode + '-panel').remove();
            },
            {'periode': periode},
            translator.trans('remove_periode_confirm', {'periodeName': periodeName}, 'platform'),
            translator.trans('remove_periode', {}, 'platform')
        );
    });

    var refreshPage = function () {
        console.log('reload');
        window.location.reload();
    };
})();