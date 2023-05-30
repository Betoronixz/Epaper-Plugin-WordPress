var bs_modal = jQuery('#modal');
var image = document.getElementById('image');
var cropper, reader;
// Function to display image in modal
function showImageInModal(url) {
    image.src = url;
    bs_modal.modal('show');
}

// Event listener for image click
jQuery("body").on("click", ".image", function (e) {
    var imageUrl = jQuery(this).attr('src');
    showImageInModal(imageUrl);
});
// Add a click event listener to all images with the "image" class
jQuery('.im1').on('click', function () {
    // Get the source of the clicked image
    var imageUrl = jQuery(this).attr('src');
    // Update the source of the image inside the "epimage" container
    jQuery('.epimage img').attr('src', imageUrl);
});
// Event listener for shown.bs.modal event
bs_modal.on('shown.bs.modal', function () {
    cropper = new Cropper(image, {
        aspectRatio: NaN, // Set aspect ratio to 2:1 for rectangle
        viewMode: 3,
        preview: '.preview'
    });
}).on('hidden.bs.modal', function () {
    cropper.destroy();
    cropper = null;
});

// Event listener for crop button click
jQuery("#crop").click(function () {
    canvas = cropper.getCroppedCanvas({
        minWidth: 256,
        minHeight: 256,
        maxWidth: 4096,
        maxHeight: 4096,
        fillColor: '#fff',
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });

    canvas.toBlob(function (blob) {
        var link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'cropped_part.jpg'; // Set desired download filename
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(link.href);
    }, 'image/jpeg', 1);

});

// Event listener for store button click
jQuery("#st").click(function () {
    canvas = cropper.getCroppedCanvas({
        minWidth: 256,
        minHeight: 256,
        maxWidth: 4096,
        maxHeight: 4096,
        fillColor: '#fff',
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });

    var imageData = canvas.toDataURL('image/jpeg', 1);
    let ajaxurl = document.getElementsByClassName("aj")[0].innerText;

    // Send AJAX request to store the image in the database
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'my_store_image',
            image_data: imageData,
            nonce: myPluginSettings.nonce
        },
        success: function (response) {
            console.log('Image stored successfully');
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error(errorThrown);
        }
    });
});
// Event listener for modal dismiss
bs_modal.on('hidden.bs.modal', function () {
    location.reload(); // Reload the page
});
