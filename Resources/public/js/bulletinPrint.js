$( document ).ready(function() {
    var totPoint = 0.0;
    var totTot = 0.0
    $('.pourcent').each(function()
    {
        var point = parseFloat($(this).prevAll('.point').html());
        totPoint = totPoint + point;
        var total = parseFloat($(this).prevAll('.total').html());
        totTot = totTot + total;
        var pourc = point / total * 100
        $(this).text(Number((pourc).toFixed(1)) + " %");
        if (Number((pourc).toFixed(1)) < 50){
            $(this).parent().addClass('echec');
        }
    });
    $('#totPoint').text(totPoint);
    $('#totTot').text(totTot);
    var totPour = totPoint / totTot * 100;
    $('#totPour').text(Number((totPour).toFixed(1)) + " %");

});

