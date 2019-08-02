$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

//set view
$('.item.viewBlock').click(function () {
    var viewData = $(this).data('view');
    var newTitle = '';
    var newData = '';

    switch (viewData) {
        case 0:
            newTitle = 'List view';
            newData = 1;
            break;
        case 1:
            newTitle = 'Thumbnails view';
            newData = 0;
            break;
    }

    $(this).attr('title', newTitle);
    $(this).data('view', newData);
    $(this).toggleClass('list');

    $('.fileContent').fadeOut(200, function () {
        $(this).toggleClass('list').fadeIn('slow');
    })
});

//upload
$('.item.uploadBlock').click(function (e) {
    if(typeof e.buttons === 'undefined')
        return;
    $('#queue').html('');
    $('#uploadifive-file_upload input:nth-child(2)').click();
});

//sort
$('.item.sortBlock').click(function () {
    $('#sortPopup').addClass('show');
});

//print
function sf_print(target) {
    var type = $(target).data('type');
    var mywindow = window.open($(target).data('path'), 'Print', 'width=1200, height=600');
    mywindow.focus();
    
    $(mywindow).on('load', function() {
        window_printing = true;
        mywindow.print();

        if(type != 'pdf') {
            mywindow.close();
        }
    });

    var window_printing = false;
    mywindow.onfocus = function() {
        if(window_printing && type == 'pdf') {
            window_printing = false;
            mywindow.close();
        } 
    }
}

//share
function sf_share(target) {
    $('#fileShareId').val($(target).data('id'));
    $('.modal.sharing').addClass('show');
}

//delete
function sf_delete(target) {
    var id = $(target).data('id');
    var pointer = $(target);

    swal({
        title: Lang['Are you sure you want to delete it?'],
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: Lang['Yes'],
        cancelButtonText: Lang['Cancel(no)']
    }).then((result) => {
        if (result.value) {
            $('#loadingBlock').fadeIn(100);
            $.ajax({
                type: "POST",
                url: SF_DELET_ITEM_URL,
                dataType: "html",
                data: "_token=" + SF_TOKEN + "&id=" + id,
                success: function (response) {
                    pointer.closest('.item').fadeOut(300, function () {
                        $(target).remove();
                        $('#loadingBlock').fadeOut(100);
                    });
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    window.location.reload();
                }
            });
        }
    });
}

$('.close-button').click(function (e) {
    e.stopPropagation();
    $(this).closest('.modal').removeClass('show');
});

//copy share text
function sendFile() {
    var file_id = $('#fileShareId').val();
    var to_address = $('#to_address').val();
    var message_subject = $('#message_subject').val();
    var message_body = $('#message_body').val();
    message_body = message_body.replace(/(\n|\r)/g, '<br>');

    if(to_address == '' || message_subject == '' || message_body == '')
        return;

    $('.modal.sharing').removeClass('show');

    var data = {
        _token: SF_TOKEN,
        fileId: file_id,
        mail: to_address,
        subjest: message_subject,
        message: message_body
    };

    $.ajax({
        url: SF_SEND_EMAIL_URL,
        method: "POST",
        data: data,
    }).done(function(data) {
        showAlert('success', Lang['Message sent succusfully']);
    }).fail(function() {
        showAlert('success', Lang['Email was not sent']);
    });
};

$(document).mouseup(function (e) {
    var container = $(".modal");
    if (container.has(e.target).length === 0){
        container.removeClass('show');
    }
});

$(function() {
    $('#file_upload').uploadifive({
        'auto'             : false,
        'queueSizeLimit' : 5,
        'formData'         : {
            'timestamp' : SF_TIMESTAMP,
            '_token'     : SF_TOKEN
        },
        'queueID'          : 'queue',
        'uploadScript'     : SF_UPLOAD_ITEM_URL,
        'onSelect':     function() {
            setTimeout(() => { $('#file_upload').uploadifive('upload'); }, 100);
        },
        'onUploadComplete' : function(file, data) {
            $.ajax({
                type: "POST",
                url: SF_UPDATE_CONTENT_URL,
                dataType: "html",
                data: 'url=' + SF_CURRENT_URL + "&_token=" + SF_TOKEN,
                success: function (data) {
                    $('.fileContent').html(data);
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    window.location.reload();
                }
            });
        }
    });
});

$('#sortingSelect').change(function () {
    var sorting = $(this).val(); 

    $('#sortPopup').removeClass('show');
    $('.item.sortBlock').find('span').text(sortTextArray[sorting]);

    var data = {
        _token: SF_TOKEN,
        sorting: sorting
    };

    $.ajax({
        type: "POST",
        url: SF_RESORTING_URL,
        dataType: "html",
        data: data,
        success: function (data) {
            $('.fileContent').html(data);
        },
        error: function (xhr, ajaxOptions, thrownError) {
            // window.location.reload();
        }
    });
});

$(document).ready(function () {
    $('#search').keyup(function(){
        var filter = $(this).val();
        $(".fileContent .item").each(function(){
            if ($(this).find('.content').find('.name').text().search(new RegExp(filter, "i")) < 0) {
                $(this).fadeOut();
            } else {
                $(this).show();
            }
        });
    });
});