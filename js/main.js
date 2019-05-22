// DOM Elements
var $file_input;
var $preview_wrapper;
var $startUpload;
var $progress_bar_photo_upload;

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
        var uniqueId = Math.random().toString(36).substr(2, 9);
        var wrapperImg = $("<div class='wrapper d-inline-block mr-2' id='_" + uniqueId + "'>");
        var img = $("<img width='200' height='200' />");
        var progressBar = $(
            "<div class=\"progress mt-2\">\n" +
            "  <div class=\"progress-bar\" id=\"progressbar-" + uniqueId + "\" role=\"progressbar\" aria-valuenow=\"0\" aria-valuemin=\"0\" aria-valuemax=\"100\"></div>\n" +
            "</div>"
        );

        reader.onload = function (e) {
            img.attr('src', e.target.result);
            img.attr('data-id', '_' + uniqueId);
        };

        wrapperImg.appendTo($preview_wrapper);
        img.appendTo(wrapperImg);
        progressBar.appendTo(wrapperImg);

        reader.readAsDataURL(file);
        file.uniqueId = uniqueId;

    });

}

function upload_photo() {
    $.each(files, function (i, file) {

        var formdata = new FormData();

        formdata.append('photo-' + i, file);

        // Send the photo
        $.ajax({
            url: "/upload.php",
            type: "POST",
            dataType: "json",
            contentType: false,
            cache: false,
            processData: false,
            cache: false,
            data: formdata,
            xhr: function(event)
            {
                var myXhr = $.ajaxSettings.xhr();
                if(myXhr.upload){
                    myXhr.upload.addEventListener('progress', function (event) {
                        var percentage = (event.loaded / event.total) * 100;
                        var percentage_string = percentage + "%";
                        $('#progressbar-' + file.uniqueId).width(percentage_string);
                    }, false);
                }
                return myXhr;
            },
            success: function () {

            },
            error: function (response) {

            }
        });
    });
}