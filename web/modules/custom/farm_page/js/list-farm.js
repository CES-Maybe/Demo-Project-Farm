/**
 * Perform an AJAX request to search for farms by name.
 *
 * This script initializes an AJAX request to search for farms by name when the
 * page loads and when the Enter key is pressed in the input field.
 *
 * @param {string|null} name - The name to search for (optional).
 */
(function ($) {
  /**
   * Function to make an AJAX request.
   *
   * @param {string|null} name - The name to search for (optional).
   */
  function makeAjaxRequest(name = null) {
    let url = '/search/farm';

    // Append the 'name' parameter to the URL if provided.
    if (name !== null) {
      url += `?name=${name}`;
    }

    // Send an AJAX request to the specified URL.
    $.ajax({
      url: url,
      method: 'GET',
      dataType: 'json', // Adjust as needed.
      success: function (data) {
        // Update the farms container with the received content.
        $('#farms-container').html(data.content);
      },
      error: function (error) {
        // Log any errors to the console.
        console.error(error);
      }
    });
  }

  // Trigger an AJAX request when the page loads.
  $(document).ready(function () {
    makeAjaxRequest();
  });

  // Attach an event listener for the Enter key press in the search input field.
  $('#search-input').on('keydown', function (event) {
    const enterCode = 13;
    if (event.keyCode === enterCode) {
      event.preventDefault();
      // Get the input value.
      const inputValue = $(this).val().trim();
      // Call the AJAX request with the input value.
      makeAjaxRequest(inputValue);
    }
  });
})(jQuery);
