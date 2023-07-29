/**
 * Educare functionality only (mainly for ajax)
 *
 * Autor: FixBD
 * Autor Link: https://fixbd.net
 * Source: https://github.com/fixbd/educare/assets/js/educare.js
 *
 */

// jQuery 
// Front-End Results System
jQuery(document).ready(function($) {
  // Ajax functionality for educare_results shortcode
  $(document).on("click", "#results_btn", function(event) {
    event.preventDefault();
    $(this).attr('disabled', true);
    var current = $(this);
    var form_data = $(this).parents('form').serialize();

    $.ajax({
      url: educareAjax.url,
      type: 'POST',
      data: {
        action: 'educare_proccess_view_results',
        nonce: educareAjax.nonce,
        form_data: form_data
      },
      beforeSend: function(event) {
        $('#educare-loading').fadeIn();
      },
      success: function(data) {
        if (data.message) {
          var arr;

          if (data.message == 'Result not found. Please try again') {
            arr = 'success'
          } else {
            arr = 'error';
          }

          $('#msgs').html('<div class="results_form error_notice ' + arr + '">' + data.message) + '</div>';
        } else {
          $('#educare-results-body').html(data);
        }
        
      },
      error: function(data) {
        $('#educare-results-body').html(data + '<div class="notice notice-error is-dismissible"><p>Sorry, database connection error!</p></div>');
      },
      complete: function() {
        current.prop('disabled', false);
        $('#educare-loading').fadeOut();
        grecaptcha.reset();
      }
    });
  });
});