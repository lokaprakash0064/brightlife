$(document).ready(function () {
    $.ajaxSetup({
        error: function (jqXHR, exception, err) {
            if (exception === 'parsererror') {
                $('#ErrMsg').html('<div class="alert alert-warning alert-dismissable">Sorry, Requested JSON parse failed<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button></div>').fadeIn('slow');
            } else if (exception === 'timeout') {
                $('#ErrMsg').html('<div class="alert alert-warning alert-dismissable">Sorry, Server took too long time to respond<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button></div>').fadeIn('slow');
            } else if (exception === 'abort') {
                $('#ErrMsg').html('<div class="alert alert-warning alert-dismissable">Sorry, AJAX request cancelled<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button></div>').fadeIn('slow');
            } else {
                $('#ErrMsg').html('<div class="alert alert-warning alert-dismissable">' + jqXHR.responseText + '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button></div>').fadeIn('slow');
            }
            console.log(jqXHR.responseText);
            console.log(exception);
            console.log(err);
            return false;
        }
    });
    $(".navbar-brand > img").mouseover(function () {
        $(this).removeClass().addClass("animated tada")
    }).mouseout(function () {
        $(this).removeClass().addClass("animated bounceIn")
    });
    if ($('[data-toggle="tooltip"]').length > 0) {
        $('[data-toggle="tooltip"]').tooltip()
    }
    if ($(".navbar-nav > li > a").length > 0) {
        $(".navbar-nav > li > a").mouseover(function () {
            $(this).attr("data-aos", "zoom-out")
        }).mouseout(function () {
            $(this).removeAttr("data-aos", "zoom-out")
        })
    }
    if ($(".col-md-3 > .card").length > 0) {
        $(".col-md-3 > .card").mouseover(function () {
            $(this).removeClass().addClass("animated bounce card bg-dark-grad box-shadow")
        }).mouseout(function () {
            $(this).removeClass().addClass("animated rubberBand card bg-dark-grad box-shadow")
        })
    }
    if ($('#notification-tab').length > 0) {
        var source = $('#notification-tab').attr('data-get-ajax'),
                DataTable = $('#notification-tab').dataTable({
            "processing": true,
            "autoWidth": false,
            "responsive": true,
            "paging": true,
            "pagingType": "full_numbers",
            "ajax": {
                "url": source,
                "dataSrc": function (json) {
                    return json;
                }
            }
        });
    }
    if ($('#example').length > 0) {
        var source = $('#example').attr('data-get-ajax'),
                DataTable = $('#example').dataTable({
            "processing": true,
            "autoWidth": false,
            "responsive": true,
            "paging": true,
            "pagingType": "full_numbers",
            "ajax": {
                "url": source,
                "dataSrc": function (json) {
                    return json;
                }
            }
        });
    }
});