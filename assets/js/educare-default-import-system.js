jQuery(document).ready(function($) {
  $(document).on("click", "#download_demo", function() {
    $(this).attr('disabled', true);
    var educareLoading = $('#educare-loading');
    var form_data = $('#crud-forms').serialize();
    // var class_name = $('#Class').val();
    var total_demo = $('#total_demo').val();

    var demo_nonce = $('.educareImportDemo_demo_nonce').data('value');

    $.ajax({
        url: educareAjax.url,
        type: 'POST',
        data: {
        action: 'educare_process_demo_file',
        nonce: demo_nonce,
        form_data: form_data,
        total_demo: total_demo
      },
      beforeSend:function(event) {
        educareLoading.fadeIn();
      },
      success: function(data) {
        $('#demo_data').html(data);
      },
      error: function() {
        // console.log(data);
        $('#demo_data').html('<div id="demo_data"><div class="sticky_msg"><div class="notice notice-error is-dismissible"><p>Error to generating demo data. Because, There has been a critical error on this website. Please Make sure, your system is up to date (WordPress, PHP or MySQL). To use the <b>Educare Import System</b>, the minimum recommended version of PHP is <b>7.4</b>.</p><button class="notice-dismiss"></button></div></div></div>');
      },
      complete: function() {
        educareLoading.fadeOut();
        $('#download_demo').attr('disabled', false);
      },
    });
  });

  // file selector box
  const $dropContainer = $("#dropcontainer");
  const $fileInput = $("#file_selector_box");

  $dropContainer.on("dragover", function(e) {
    // prevent default to allow drop
    e.preventDefault();
  });

  $dropContainer.on("dragenter", function() {
    $dropContainer.addClass("drag-active");
  });

  $dropContainer.on("dragleave", function() {
    $dropContainer.removeClass("drag-active");
  });

  $dropContainer.on("drop", function(e) {
    e.preventDefault();
    $dropContainer.removeClass("drag-active");
    $fileInput[0].files = e.originalEvent.dataTransfer.files;
  });
});