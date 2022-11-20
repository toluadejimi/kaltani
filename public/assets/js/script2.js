$(document).ready(function() {
    $("#mytable").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#geeks tr").filter(function() {
            $(this).toggle($(this).text()
            .toLowerCase().indexOf(value) > -1)
        });
    });
});
