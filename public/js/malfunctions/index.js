$(document).ready(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    if(FROM_DATE != "" && TO_DATE != "") {
        $('[name="date-range"]').val(FROM_DATE + ' - ' + TO_DATE);
    }
    
    $('[name="date-range"]').on('cancel.daterangepicker', function(ev, picker) {
        location.href = FORM_LIST_URL + "";
    });
    
    $(".search-toggle").on("click", function(e){
        e.stopPropagation();

        var search_input = $( e.target).closest("th").find("input");
        search_input.toggleClass("hidden");
        if(search_input.hasClass("hidden") && search_input.val()) {
            search_input.val("");
            updateData();
        }
    });

    $(".search-data").on('keyup', function (e, contents) {
        delay(updateData(), 500);
    });

    $('.search-data').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
    });

    $('.toggle input').on('change', function() {
        var $tr = $(this).closest('tr');
        var $status = $(this).is(":checked") ? 'publish' : 'pending';

        var data = {
            id: $tr.data('id'),
            type: $tr.data('type'),
            status: $status,
            admin_name: user.name 
        };

        $.ajax({
            url: CHANGE_STATUS_URL,
            method: "POST",
            data: data
        }).done(function(data) {
            // console.log(data);
        });
    });

    $('#malfunctions-table .sortStyle').click(function() {
        var index = $(this).data('index');
        var classes = $(this)[0].classList.value.split(' ');

        var dir = 'asc';
        if(classes.indexOf('sorting-desc') != -1) dir = 'asc';
        else if(classes.indexOf('sorting-asc') != -1) dir = 'desc';

        $.cookie('form_list', JSON.stringify({index: index, dir:dir}));
    });

    var sort_info = {index: 1, dir: 'asc'}; 
    if($.cookie('form_list')) {
        sort_info = JSON.parse($.cookie('form_list'));
    }

    $('#malfunctions-table').stupidtable();
    $("#malfunctions-table thead th").eq(sort_info.index).stupidsort(sort_info.dir);
});

function updateData() {
    var employee = $("input[name='employee']").hasClass("hidden") ? "" : $("input[name='employee']").val(),
        site = $("input[name='site']").hasClass("hidden") ? "" : $("input[name='site']").val(),
        subsite = $("input[name='subsite']").hasClass("hidden") ? "" : $("input[name='subsite']").val();

    location.href = FORM_LIST_URL + "?employee=" + employee + "&site=" + site + "&subsite=" + subsite;
}

