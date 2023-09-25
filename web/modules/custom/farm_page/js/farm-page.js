(function ($) {
  $(document).ready(function () {
    // Event listener for thumbnail clicks
    $('.thumbnail-img').click(function () {
      // Get the thumbnail's src attribute
      const newImageSrc = $(this).attr('src');

      // Set the main image's src attribute to the clicked thumbnail's src
      $('#mainImage').attr('src', newImageSrc);
    });
  });
})(jQuery);
