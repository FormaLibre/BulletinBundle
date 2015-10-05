function openFormModal(title, content)
{
    $('#form-modal-title').html(title);
    $('#form-modal-body').html(content);
    $('#form-modal-box').modal('show');
}

function closeFormModal()
{
    $('#form-modal-box').modal('hide');
    $('#form-modal-title').empty();
    $('#form-modal-body').empty();
}

// Click on widget create button
$('.addPeriodeBtn').on('click', function () {
    $.ajax({
        url: Routing.generate(
            'formalibreBulletinPeriodeAdd'
        ),
        type: 'GET',
        success: function (datas) {
            openFormModal(
                "Ajouter une période",
                datas
            );
        }
    });
});

// Click on widget create button
$('.editPeriodeBtn').on('click', function (event) {
    var periodeId = $(event.currentTarget).data('periode-id');
    $.ajax({
        url: Routing.generate(
            'formalibreBulletinPeriodeEdit', {'periode': periodeId}
        ),
        type: 'GET',
        success: function (datas) {
            openFormModal(
                "Modifier une période",
                datas
            );
        }
    });
});

// Click on OK button of the Create Widget instance form modal
$('body').on('click', '#form-Periode-ok-btn', function (e) {
    e.stopImmediatePropagation();
    e.preventDefault();

    var form = document.getElementById('Periode-form');
    var action = form.getAttribute('action');
    var formData = new FormData(form);

    $.ajax({
        url: action,
        data: formData,
        type: 'POST',
        processData: false,
        contentType: false,
        success: function(datas, textStatus, jqXHR) {
            switch (jqXHR.status) {
                case 201:
                    closeFormModal();
                    window.location.reload();
                    break;
                default:
                    $('#form-modal-body').html(jqXHR.responseText);
            }
        }
    });
});

$('#decisions-list-btn').on('click', function () {
    window.location = Routing.generate('formalibreBulletinDecisionsList');
});

$('#groupe-titulaires-list-btn').on('click', function () {
    window.location = Routing.generate('formalibreBulletinGroupeTitulairesList');
});