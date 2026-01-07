// To use the jQuery plugin on a specific form, you need to include the unsaved-changes-alert.js file and call the plugin on the form element using jQuery. For example:
/**
  <script src="path/to/jquery.js"></script>
  <script src="path/to/unsaved-changes-alert.js"></script>
  <script>
    jQuery(document).ready(function($) {
      $('#your-form-id').unsavedChangesAlert();
    });
  </script>

  this is for remainder when modify add marks or attendace form but tyr to close or reload thr page it will show alert for keep pages.
 */

(function($) {
  $.fn.unsavedChangesAlert = function() {
    var form = this;
    var isDirty = false;
    var initialFormData = null;
    var isSaved = false;

    // Check if form inputs are dirty (changes not saved)
    function checkDirty() {
      $(':input', form).on('change input', function() {
        isDirty = true;
      });

      // Display alert when leaving the page with unsaved changes
      $(window).on('beforeunload', function() {
        if (isDirty && isFormModified() && !isSaved) {
          return 'Changes you made may not be saved.';
        }
      });
    }

    // Save the initial form data
    function saveInitialFormData() {
      initialFormData = form.serialize();
    }

    // Compare current form data with initial form data
    function isFormModified() {
      var currentFormData = form.serialize();
      return currentFormData !== initialFormData;
    }

    // Attach event handlers
    function attachEventHandlers() {
      form.on('submit', function(event) {
        // Set the save flag when form is submitted
        isSaved = true;
      });

      $(window).on('beforeunload', function() {
        if (isDirty && isFormModified() && !isSaved) {
          form.on('submit.preventUnload', function(event) {
            // event.preventDefault();
          });
        }
      });
    }

    // Initialize the plugin
    function init() {
      checkDirty();
      saveInitialFormData();
      attachEventHandlers();
    }

    // Initialize the plugin for each form element
    return this.each(function() {
      var form = $(this);
      init();
    });
  };
})(jQuery);
