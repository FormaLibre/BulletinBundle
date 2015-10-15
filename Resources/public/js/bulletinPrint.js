$( document ).ready(function() {
    var totPoint = 0.0;
    var totTot = 0.0
    $('.pourcent').each(function()
    {
        var point = parseFloat($(this).prevAll('.point').html());
        totPoint = totPoint + point;
        var total = parseFloat($(this).prevAll('.total').html());
        totTot = totTot + total;
        
        if (parseInt(total) > 0) {
            var pourc = point / total * 100;
        } else {
            var pourc = 0;
        }
        $(this).text(Number((pourc).toFixed(1)) + " %");
        if (Number((pourc).toFixed(1)) < 50){
            $(this).parent().addClass('echec');
        }
    });
    $('#totPoint').text(totPoint);
    $('#totTot').text(totTot);
        
    if (parseInt(totTot) > 0) {
        var totPour = totPoint / totTot * 100;
    } else {
        var totPour = 0;
    }
    $('#totPour').text(Number((totPour).toFixed(1)) + " %");

});

