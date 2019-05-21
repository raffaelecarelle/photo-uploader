// DOM Elements
var $file_input;
var $preview_wrapper;
var $startUpload;

// Variables
var files;

$(document).ready(function () {

    // Variables init
    $file_input = $("#photos");
    $preview_wrapper = $("#preview-wrapper");
    $startUpload = $("#start-upload");

    files = [];

    $file_input.on("change", loadPreview);
    $startUpload.on('click', upload_photo);
});

function loadPreview() {

    // Get the file from the input field
    files = event.target.files;

    // Attach to the files the POST's parameter's name: 'photo-n'
    $.each(files, function (i, file) {

        var reader = new FileReader();

        var img = $("<img width='200' height='200' id='photo-" + i + "' src='\"+  +\"' />");

        reader.onload = function(e) {
            img.attr('src', e.target.result);
        };

        img.appendTo($preview_wrapper);

        reader.readAsDataURL(file);
    });

}

function upload_photo() {
    $.each(files, function (i, file) {

        var formdata = new FormData();

        formdata.append('photo-'+i, file);

        // Send the photo
        $.ajax({
            url: "/photo-uploader/upload.php",
            type: "POST",
            dataType: "json",
            contentType: false,
            cache: false,
            processData: false,
            data: formdata,
            // xhr: image_upload_xhr_handler,
            success: function () {
                console.log("ciao");
            },
            error: function (response) {
                console.log(response);
            }
        });
    });
}
