jQuery(document).ready(function ($) {
  $("#add_button").on("click", function () {
    $("#product_buttons_container").append(
      '<input type="text" name="product_buttons[]" placeholder="Button Name" />' +
        '<input type="text" name="product_button_urls[]" placeholder="Button URL" />'
    );
  });

  // Image upload functionality
  $(".upload_image_button").on("click", function () {
    var mediaUploader = wp
      .media({
        title: "Select Image",
        button: { text: "Select Image" },
        multiple: false,
      })
      .on("select", function () {
        var attachment = mediaUploader
          .state()
          .get("selection")
          .first()
          .toJSON();
        $("#product_image").val(attachment.url);
      })
      .open();
  });
});
