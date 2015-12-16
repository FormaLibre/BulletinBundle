$( document ).ready(function() {
    $('.point').each(function(){
        var point = parseFloat($(this).children('input').val());
        var total = parseFloat($(this).nextAll('.total').children('input').val());
        if (point === 0 || point === 888 || point === 999){
            $(this).addClass('has-success has-feedback');
            $(this).append('<span class="fa fa-check form-control-feedback"></span>');
        }
        else if (( point > 0) && (point <= total)){
            $(this).addClass('has-success has-feedback');
            $(this).append('<span class="fa fa-check form-control-feedback"></span>');
        }
        else {
            $(this).addClass('has-error has-feedback');
            $(this).append('<span class="fa fa-close form-control-feedback"></span>');
        }
    });
    $('.presence').each(function(){
        var presence = parseFloat($(this).children('input').val());
        if (presence === 0){
            $(this).addClass('has-success has-feedback');
            $(this).append('<span class="fa fa-check form-control-feedback"></span>');
        }
        else if (( presence > 0) && (presence <= 100)){
            $(this).addClass('has-success has-feedback');
            $(this).append('<span class="fa fa-check form-control-feedback"></span>');
        }
        else {
            $(this).addClass('has-error has-feedback');
            $(this).append('<span class="fa fa-close form-control-feedback"></span>');
        }
    });
    $('.comportement').each(function(){
        var comportement = parseFloat($(this).children('input').val());
        if (comportement === 0){
            $(this).addClass('has-success has-feedback');
            $(this).append('<span class="fa fa-check form-control-feedback"></span>');
        }
        else if (( comportement > 0) && (comportement <= 10)){
            $(this).addClass('has-success has-feedback');
            $(this).append('<span class="fa fa-check form-control-feedback"></span>');
        }
        else {
            $(this).addClass('has-error has-feedback');
            $(this).append('<span class="fa fa-close form-control-feedback"></span>');
        }
    });
});

$('.point').focusout(function(){
    var point = parseFloat($(this).children('input').val());
    var total = parseFloat($(this).nextAll('.total').children('input').val());
    console.log(point);
    console.log(total);
    $(this).children('span').remove();
    $(this).removeClass("has-error");
    $(this).removeClass("has-success");
    if (point === 0 || point === 888 || point === 999){
        $(this).addClass('has-success has-feedback');
        $(this).append('<span class="fa fa-check form-control-feedback"></span>');
    }
    else if (( point > 0) && (point <= total)){
        $(this).addClass('has-success has-feedback');
        $(this).append('<span class="fa fa-check form-control-feedback"></span>');
    }
    else {
        $(this).addClass('has-error has-feedback');
        $(this).append('<span class="fa fa-close form-control-feedback"></span>');
    }
});
$('.presence').focusout(function(){
    var presence = parseFloat($(this).children('input').val());
    $(this).children('span').remove();
    $(this).removeClass("has-error");
    $(this).removeClass("has-success");
    if (presence === 0){
        $(this).addClass('has-success has-feedback');
        $(this).append('<span class="fa fa-check form-control-feedback"></span>');
    }
    else if (( presence > 0) && (presence <= 100)){
        $(this).addClass('has-success has-feedback');
        $(this).append('<span class="fa fa-check form-control-feedback"></span>');
    }
    else {
        $(this).addClass('has-error has-feedback');
        $(this).append('<span class="fa fa-close form-control-feedback"></span>');
    }
});
$('.comportement').focusout(function(){
    var comportement = parseFloat($(this).children('input').val());
    $(this).children('span').remove();
    $(this).removeClass("has-error");
    $(this).removeClass("has-success");
    if (comportement === 0){
        $(this).addClass('has-success has-feedback');
        $(this).append('<span class="fa fa-check form-control-feedback"></span>');
    }
    else if (( comportement > 0) && (comportement <= 10)){
        $(this).addClass('has-success has-feedback');
        $(this).append('<span class="fa fa-check form-control-feedback"></span>');
    }
    else {
        $(this).addClass('has-error has-feedback');
        $(this).append('<span class="fa fa-close form-control-feedback"></span>');
    }
});

$('.delete-pemp-btn').on('click', function () {
    console.log("coucou");
    var periodeId = $(this).data('periode-id');
    var eleveId = $(this).data('eleve-id');
    var parent = $(this).closest('tr');
    var matiereId = $(this).data('matiere-id');
    
    window.Claroline.Modal.confirmRequest(
        Routing.generate(
            'formalibre_bulletin_pemp_delete',
            {'periode': periodeId, 'eleve': eleveId, 'matiere': matiereId}
        ),
        function () {parent.remove();},
        null,
        'Voulez-vous vraiment retirer cette matière ?',
        'Retrait de la matière'
    );
});