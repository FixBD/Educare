/**
 * == Educare front-end functionality ==
 * 
 * Autor: FixBD
 * Author URI: http://fixbd.com
 * Source: https://github.com/fixbd/educare/blob/educare/assets/js/educare-front.js
 * @since v1.0.0
 * @version: v1.2.0
 * @last-update v1.6.0
 */

// jQuery 
jQuery(document).ready(function($) {
  $(document).on("click", "#educareResults #educareForm .results_button", function(event) {
    // prevent the default form submission
    event.preventDefault();
    // Retrieve the name attribute of the submit button that triggered the form submission.
    var actionFor = $(this).attr('name');
    var form_data = $(this).parents('form').serialize();
    // disables all submit buttons and buttons within the form whenever a form is submitted.
    $(this).parents('form').find(':submit', 'button').prop('disabled', true);

    $.ajax({
      url: educareAjax.url,
      type: 'POST',
      data: {
        action: 'educare_results_form', // call educare shortcode
        nonce: educareAjax.nonce,       // form security
        form_data: form_data,           // form data to pass
        action_for: actionFor,          // results || certificate
        settings: shortcodeSettings     // sent shortcode_atts() || $attr
      },
      beforeSend: function(event) {
        // Show loading spinner
        $('#educare-loading').fadeIn();
      },
      success: function(data) {
        // Show data
        $('#educareResults').html(data);
      },
      error: function(data) {
        // Show error
        // console.log(data);
        $('#educareResults').html('<div class="notice notice-error is-dismissible"><p>Sorry, database connection error!</p></div>');
      },
      complete: function() {
        // anabled all submit buttons and buttons within the form whenever process is complete.
        $(this).parents('form').find(':submit', 'button').prop('disabled', false);
        // Hide loading spinner
        $('#educare-loading').fadeOut();
        // Reset google reCHAPTCHA
        grecaptcha.reset();
      }
    });
  });

  $(document).on("click", ".print_button", function(event) {
    event.preventDefault();
    window.print();
  });

  $(document).on("click", ".undo-button", function(event) {
    window.location.href = window.location.href;
  });
});
