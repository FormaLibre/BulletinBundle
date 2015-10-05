(function () {
    'use strict';
    
    $('#decision-add-btn').on('click', function () {
        var userId = $(this).data('user-id');
        var periodeId = $(this).data('periode-id');
        
        window.Claroline.Modal.displayForm(
            Routing.generate(
                'formalibreBulletinUserDecisionCreateFrom',
                {'user': userId, 'periode': periodeId}
            ),
            refreshPage,
            function() {}
        );
    });
    
    $('.edit-decision-btn').on('click', function () {
        var decisionId = $(this).data('decision-id');
        
        window.Claroline.Modal.displayForm(
            Routing.generate('formalibreBulletinUserDecisionEditFrom', {'decision': decisionId}),
            refreshPage,
            function() {}
        );
    });
    
    $('.delete-decision-btn').on('click', function () {
        var decisionId = $(this).data('decision-id');

        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'formalibreBulletinUserDecisionDelete',
                {'decision': decisionId}
            ),
            refreshPage,
            null,
            'Etes-vous sûr de vouloir supprimer cette décision ?',
            'Suppression de la décision'
        );
    });
    
    var refreshPage = function () {
        window.location.reload();
    };
})();