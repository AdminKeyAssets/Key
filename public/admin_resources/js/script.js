$(document).on('click', '.popup_selector', function(event){

    var dataInputID = $(this).data('inputid');
    var url = "elfinder/popup";

    event.preventDefault();
    // trigger the reveal modal with elfinder inside
    var triggerUrl = url +'/' + dataInputID;
    $.colorbox({
        href: triggerUrl,
        fastIframe: true,
        iframe: true,
        width: '80%',
        height: '80%',
        getfile : {
            // send only URL or URL+path if false
            onlyURL  : false,

            // allow to return multiple files info
            multiple : true,

            // allow to return folders info
            folders  : false,

            // action after callback (close/destroy)
            oncomplete : ''
        },
    });



});

// function to update the file selected by elfinder
function processSelectedFile(filePath, requestingField) {
    // console.log(filePath);
    // console.log(requestingField);
    $('#' + requestingField).val(filePath.replace(/\\/g,"/"));
}

$(document).on('click', '.clear_elfinder_picker', function(event){
    event.preventDefault();
    var updateID = $(this).attr('data-inputid'); // Btn id clicked
    $("#"+updateID).val("");

});

$(function () {
    $('.remove-item').click(function () {
        var form = $('.delete-item');
        var id = $(this).data().id;

        Modal.show({
            yesClass: 'btn-danger',
            body: 'ნამდვილად გსურთ წაშლა?',
            yes: 'წაშლა',
            callback: function (btn) {
                Modal.hide();

                if (btn === 'yes') {
                    var action = form.attr('action');
                    form.attr({action: action + '/' + id}).submit();
                }
            }
        });
    });
});


function divAction(className, action = "show"){

    if(action == "show") {
        $(className).show();
    } else if (action == "hide") {
        $(className).hide();
    }
}
