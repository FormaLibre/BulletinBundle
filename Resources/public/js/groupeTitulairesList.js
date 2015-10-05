(function () {
    'use strict';
    
    $('#groupe-titulaire-create-btn').on('click', function () {
        window.Claroline.Modal.displayForm(
            Routing.generate(
                'formalibreBulletinGroupeTitulaireCreateForm',
                {'user': userId, 'periode': periodeId}
            ),
            refreshPage,
            function() {}
        );
    });
    
    $('.edit-groupe-titulaire-btn').on('click', function () {
        var groupTitulaireId = $(this).data('groupe-titulaire-id');
        
        window.Claroline.Modal.displayForm(
            Routing.generate(
                'formalibreBulletinGroupeTitulaireEditForm',
                {'groupTitulaire': groupTitulaireId}
            ),
            refreshPage,
            function() {}
        );
    });
    
    $('.delete-groupe-titulaire-btn').on('click', function () {
        var groupTitulaireId = $(this).data('groupe-titulaire-id');

        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'formalibreBulletinGroupeTitulaireDelete',
                {'groupTitulaire': groupTitulaireId}
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