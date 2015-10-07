(function () {
    'use strict';
    
    $('#point-divers-create-btn').on('click', function () {
        window.Claroline.Modal.displayForm(
            Routing.generate('formalibre_bulletin_point_divers_create_form'),
            refreshPage,
            function() {}
        );
    });
    
    $('.point-divers-edit-btn').on('click', function () {
        var pointDiversId = $(this).data('point-divers-id');
        
        window.Claroline.Modal.displayForm(
            Routing.generate(
                'formalibre_bulletin_point_divers_edit_form',
                {'pointDivers': pointDiversId}
            ),
            refreshPage,
            function() {}
        );
    });
    
    $('.point-divers-delete-btn').on('click', function () {
        var pointDiversId = $(this).data('point-divers-id');

        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'formalibre_bulletin_point_divers_delete',
                {'pointDivers': pointDiversId}
            ),
            refreshPage,
            null,
            'Etes-vous sûr de vouloir supprimer ce point divers ?',
            'Suppression du point divers'
        );
    });
    
    var refreshPage = function () {
        window.location.reload();
    };
})();