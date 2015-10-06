(function () {
    'use strict';
    
    $('#groupe-titulaire-create-btn').on('click', function () {
        window.Claroline.Modal.displayForm(
            Routing.generate('formalibreBulletinGroupeTitulaireCreateForm'),
            refreshPage,
            function() {}
        );
    });
    
    $('.edit-groupe-titulaire-btn').on('click', function () {
        var groupeTitulaireId = $(this).data('groupe-titulaire-id');
        
        window.Claroline.Modal.displayForm(
            Routing.generate(
                'formalibreBulletinGroupeTitulaireEditForm',
                {'groupeTitulaire': groupeTitulaireId}
            ),
            refreshPage,
            function() {}
        );
    });
    
    $('.delete-groupe-titulaire-btn').on('click', function () {
        var groupeTitulaireId = $(this).data('groupe-titulaire-id');

        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'formalibreBulletinGroupeTitulaireDelete',
                {'groupeTitulaire': groupeTitulaireId}
            ),
            refreshPage,
            null,
            'Etes-vous sûr de vouloir supprimer ce titulaire de ce groupe ?',
            'Suppression du titulaire du groupe'
        );
    });
    
    var refreshPage = function () {
        window.location.reload();
    };
})();