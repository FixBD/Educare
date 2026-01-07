/**
 * == Educare functionality only (mainly for ajax) ==
 *
 * Autor: FixBD
 * Autor Link: https://fixbd.com
 * Source: https://github.com/fixbd/educare/assets/js/educare.js
 */

// jQuery 
jQuery(document).ready(function ($) {
  // Settings functionality
  function educareSettingsPage() {
    $(document).on("submit", ".educareUpdateSettings", function (event) {
      event.preventDefault();

      var form_data = $(this).serialize();
      var active_menu = $('.head:checked').attr('id');

      // Find the clicked button within the form
      var clickedButton = $(this).find(':submit:focus');
      // Get the 'name' attribute value of the clicked button
      var action_for = clickedButton.attr('name');

      // Check if the clicked button has the name "undefined"
      if (!action_for) {
        // The "undefined" button was clicked. set default button
        action_for = 'educare_update_settings_status';
      }

      $.ajax({
        url: educareAjax.url,
        type: 'POST',
        data: {
          action: 'educare_process_content',
          form_data: form_data,
          active_menu: active_menu,
          action_for
        },
        beforeSend: function (event) {
          if (action_for == 'educare_reset_default_settings') {
            if (educareSettings.confirmation == 'checked') {
              return confirm("Are you sure to restore default settings? This will not effect your content (Class, Subject, Exam, Year, Extra Field), It's only reset your current settings status and value.");
            }
          } else if (action_for == 'educare_reset_everything') {
            return confirm("Are you sure you want to reset all settings? This action will restore all Educare-related data to it's default state. Please back up any important data before proceeding.");
          } else {
            $('#educare-loading').fadeIn();
          }

          clickedButton.children('.dashicons').addClass('educare-loader');
        },
        success: function (data) {
          $('#educare-data').html(data);
        },
        error: function (data) {
          $('#educare-data').html(educareSettings.db_error);
        },
        complete: function () {
          $('#educare-loading').fadeOut();
          clickedButton.children('.dashicons').removeClass('educare-loader');
          // event.remove();
        },
      });

    });

    // Copy button
    // When the container is clicked
    $(document).on("click", ".educare-copy-button", function () {
      // Get the input field within the container
      var input = $(this).find('input');

      // Copy the input value to the clipboard
      navigator.clipboard.writeText(input.val()).then(function () {
        // Show the "Copied" tooltip
        var tooltip = $(this).find('.copy-tooltip-text');
        tooltip.fadeIn(200);

        // Hide the tooltip after 3 seconds
        setTimeout(function () {
          tooltip.fadeOut(200);
        }, 3000);
      }.bind(this)).catch(function (err) {
        console.error('Failed to copy text: ', err);
      });
    });

    // =========== Script for Grading System Page ===========
    // Edit button
    var result_msg_data = false;

    $(document).on("click", "#edit_grade", function (event) {
      event.preventDefault();

      $(this).attr('disabled', true);
      var class_name = $('#grading').val();
      result_msg_data = $('#result_msg').html();

      $.ajax({
        url: educareAjax.url,
        type: 'POST',
        data: {
          action: 'educare_proccess_grade_system',
          nonce: educareNonce.edit_grade_system,
          class: class_name
        },
        beforeSend: function (event) {
          $('#educare-loading').fadeIn();
        },
        success: function (data) {
          // $('#result_msg').hide();
          $('#result_msg').html(data).fadeIn();
          $('#update_button').fadeOut();
          $('#edit_grade').attr('disabled', false);
        },
        error: function (data) {
          $('#result_msg').html(educareSettings.db_error);
        },
        complete: function () {
          $('#educare-loading').fadeOut();
        }
      });
    });

    // Update buttton
    $(document).on("click", "#save_addForm", function (event) {
      event.preventDefault();

      $(this).attr('disabled', true);
      var form_data = $(this).parents('form').serialize();

      $.ajax({
        url: educareAjax.url,
        type: 'POST',
        data: {
          action: 'educare_save_grade_system',
          form_data: form_data,
          update_grade_rules: true
        },
        beforeSend: function (event) {
          $('#educare-loading').fadeIn();
        },
        success: function (data) {
          $('#result_msg').hide();
          $('#result_msg').html(data).fadeIn();
          $('#update_button').fadeIn();
          $('#edit_grade').attr('disabled', false);
        },
        error: function (data) {
          $('#result_msg').html(educareSettings.db_error);
        },
        complete: function () {
          $('#educare-loading').fadeOut();
        }
      });
    });

    $(document).on("click", "#help", function () {
      $(this).css('color', 'green');
      $("#show_help").slideToggle();
    });

    // show edit button when close Grading Systems (Edit) window
    $(document).on("click", ".grading_system .notice-dismiss", function () {
      $(this).parent('div').fadeOut();
      $('#result_msg').hide().html(result_msg_data).fadeIn();
      $('#update_button').fadeIn();
    });

    // Default roll and regi no checked term
    $(document).on("click", 'input[name="display[Roll_No][status]"]', function () {
      if ($(this).val() == 'checked') {
        $('#Regi_No_no').attr("disabled", false);
      } else {
        $('input[name="display[Regi_No][status]"]').prop("checked", true);
      }
    });

    $(document).on("click", 'input[name="display[Regi_No][status]"]', function () {
      if ($(this).val() == 'checked') {
        $('#Roll_No_no').attr("disabled", false);
      } else {
        $('input[name="display[Roll_No][status]"]').prop("checked", true);
      }
    });

    // Show submenu when click title on Educare Settings > Card Settings
    $(document).on("click", ".educare-settings .title", function () {
      // hide other submenus
      $('.submenu').not($(this).siblings('.submenu')).slideUp();
      $('.submenu').not($(this).siblings('.submenu')).siblings('.title').removeClass('current');
      // Show curret submenu
      $(this).siblings('.submenu').slideDown(300);
      // toggle submenu and rotate icon
      $(this).addClass('current');
    });

    $(document).on("mouseenter", ".educare-advance-fields", function () {
      $(this).attr("title", "Unlock To Premium"); // Set title dynamically
    }).on("mouseleave", ".educare-advance-fields", function () {
      $(this).removeAttr("title"); // Remove title on mouse leave to force refresh
    });

  }

  // settings functionality callback
  educareSettingsPage();

  // Performance functionality
  function educarePerformancePage() {
    $(document).on("click", "#promote", function (event) {
      event.preventDefault();

      var current = $(this);
      var form_data = $(this).parents('form').serialize();
      // alert('Ok');
      $.ajax({
        url: educareAjax.url,
        type: 'POST',
        data: {
          action: 'educare_proccess_promote_students',
          form_data: form_data
        },
        beforeSend: function (data) {
          $('#educare-loading').fadeIn();
        },
        success: function (data) {
          $('#promote_msgs').html(data);
        },
        error: function (data) {
          $('#educare-loading').fadeOut();
          // educare_guide_for('db_error')
          $('#promote_msgs').html(educareSettings.db_error);
        },
        complete: function () {
          $('#educare-loading').fadeOut();
          // do some
        },
      });
    });
  }
  // performance functionality callback
  educarePerformancePage();

  // FileSelector functionality
  function educareFileSelectorPage() {
    if (typeof educareSettings !== 'undefined' && educareSettings.photos == 'checked') {
      // console.log(educareFileSelector);
      // Uploading files
      var file_frame;
      var wp_media_post_id = 0; // Store the old id
      var educare_media_post_id = ''; // Set this

      $(document).on("click", "#educare_upload_button", function (event) {
        event.preventDefault();
        // not important!!
        // If the media frame already exists, reopen it.
        if (file_frame) {
          // Set the post ID to what we want
          file_frame.uploader.uploader.param('post_id', educare_media_post_id);
          // Open frame
          file_frame.open();
          return;
        } else {
          // Set the wp.media post id so the uploader grabs the ID we want when initialised
          // wp.media.model.settings.post.id = educare_media_post_id;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
          title: 'Select Photos',
          button: {
            text: 'Use this image',
          },
          multiple: false // Set to true to allow multiple files to be selected
        });

        // When an image is selected, run a callback.
        file_frame.on('select', function () {
          // We set multiple to false so only get one image from the uploader
          attachment = file_frame.state().get('selection').first().toJSON();
          $('#educare_attachment_preview').attr('src', attachment.url);
          $('#educare_upload_button').val('Edit Photos');
          $('#educare_attachment_clean').css('display', 'block');
          $("#educare_img_type").html('Custom photos');
          $("#educare_guide").html('Please click edit button for change carently selected photos or click close/clean button for default photos');
          $('#educare_attachment_id').val(attachment.id);
          $('#educare_attachment_url').val(attachment.url);
          $('#educare_attachment_title').val(attachment.title).attr('value', this.val);
          // Restore the main post ID
          wp.media.model.settings.post.id = wp_media_post_id;
        });

        // Finally, open the modal
        file_frame.open();
      });

      // Restore the main ID when the add media button is pressed
      $('a.add_media').on('click', function () {
        wp.media.model.settings.post.id = wp_media_post_id;
      });

      // clean files/photos
      $(document).on("click", "input.educare_clean", function (event) {
        event.preventDefault();
        // default value
        var educareFileSelector_img_src = $('.educareFileSelector_img_src').data('value');
        var educareFileSelector_img = $('.educareFileSelector_img').data('value');
        var educareFileSelector_img_type = $('.educareFileSelector_img_type').data('value');
        var educareFileSelector_guide = $('.educareFileSelector_guide').data('value');

        $("#educare_attachment_url").val(educareFileSelector_img_src);
        $("#educare_attachment_id").val(educareFileSelector_img);
        $("#educare_attachment_preview").attr("src", educareFileSelector_img_src);
        $("input.educare_clean").css("display", "none");
        $("#educare_attachment_title").val("Cleaned! please select onother one");
        $("#educare_upload_button").val("Upload photos again");
        $("#educare_img_type").html(educareFileSelector_img_type);
        $("#educare_guide").html(educareFileSelector_guide);
        $("#educare_attachment_default").css("display", "block");
      });

      // set default photos
      $(document).on("click", "#educare_attachment_default", function (event) {
        event.preventDefault();
        // default photos
        var educareFileSelector_default_img = $('.educareFileSelector_default_img').data('value');

        $("#educare_attachment_url").val(educareFileSelector_default_img);
        $("#educare_attachment_id").val("");
        $("#educare_attachment_preview").attr("src", educareFileSelector_default_img);
        $("#educare_attachment_clean").css("display", "block");
        $(this).css("display", "none");
        $("#educare_attachment_title").val('Successfully set default photos!');
      });
    } else {
      $("#educare_default_help").html("Currently students photos are disabled. If you upload or display student photos, first check/enable students photos from the settings sections");
      $("#educare_upload_button").attr("disabled", "disabled");
      $("#educare_attachment_default").attr("disabled", "disabled");
      $("#educare_files_selector_disabled").className = 'educare_files_selector_disabled';
      $("#educare_upload_button").attr("disabled", "disabled");
      // $("#educare_default_photos").setAttribute("disabled", "disabled");
      $("#educare_attachment_clean").css("display", "none");
    }
  }
  // FileSelector functionality callback
  educareFileSelectorPage();

  // DataManagemen functionality
  function educareDataManagementPage() {
    // default value
    var educareDataManagement_url = $('.educareDataManagement_url').data('value');
    var educareDataManagement_students = $('.educareDataManagement_students').data('value');
    var educareDataManagement_tab = $('.educareDataManagement_tab').data('value');

    $(document).on("click", ".students .tablinks", function (event) {
      event.preventDefault();

      tablinks = $(".tablinks");

      for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace("active", "");
      }

      var current = $(this);
      current.addClass('active');
      var form_data = current.attr('data');

      $.ajax({
        url: educareAjax.url,
        type: 'GET',
        data: {
          action: 'educare_process_data',
          form_data: form_data,
          action_for: educareDataManagement_students
        },
        beforeSend: function () {
          $('#educare-loading').fadeIn();
        },
        success: function (data) {
          history.pushState('', 'form_data', educareDataManagement_url + '&' + form_data);
          $('#educare-data').html(data);
        },
        error: function (data) {
          $('#educare-data').html(educareSettings.db_error);
        },
        complete: function () {
          $('#educare-loading').fadeOut();
        },
      });

    });

    $(".students .active").removeClass('active');
    $(".students [data=" + educareDataManagement_tab + "]").addClass('active');
  }
  // DataManagemen functionality callback
  educareDataManagementPage();

  // OptionsByAjax options by ajax functionality
  function educareOptionsByAjaxPage() {
    var educareLoading = $('#educare-loading');
    var connectionsError = '<div class="notice notice-error is-dismissible"><p>Sorry, (database) connections error!</p></div>';

    var target = "Group";
    var students_data = $('.educareDataManagement_students_data').data('value');
    var add_students = students_data;

    function changeClass(currentData) {
      var class_name = $('#Class').val();
      var id_no = $('#id_no').val();
      var form_data = $(currentData).parents('form').serialize();

      $.ajax({
        url: educareAjax.url,
        type: 'POST',
        data: {
          action: 'educare_class',
          class: class_name,
          id: id_no,
          form_data: form_data,
          add_students: add_students,
        },
        beforeSend: function (data) {
          educareLoading.fadeIn();
          $('#sub_msgs').html('<div class="notice notice-success is-dismissible"><p>Loading Subject</b></p></div>');
        },
        success: function (data) {
          $('#result_msg').html(data);
          $('#Class').attr('disabled', false);
          $('#sub_msgs').html('<div class="notice notice-error is-dismissible"><p>Please select the group. If this class has a group, then select group. otherwise ignore it.</p></div>');
        },
        error: function (data) {
          $('#result_msg').html('<div class="notice notice-error is-dismissible"><p>Sorry, database connection error!</p></div>');
        },
        complete: function () {
          educareLoading.fadeOut();
          educare_crud.prop('disabled', false);
        }
      });
    }

    function educareGroupSub(action_for, currentData) {
      var educare_crud = $('.educare_crud');
      var nonce_value = $('[name=crud_data_nonce]').val();

      if (action_for) {
        $.ajax({
          url: educareAjax.url,
          type: 'POST',
          data: {
            action: 'educare_process_options_by',
            nonce: nonce_value,
            data_for: action_for
          },
          beforeSend: function (data) {
            educareLoading.fadeIn();
            educare_crud.prop('disabled', true);
            $('#sub_msgs').html('<div class="notice notice-success is-dismissible"><p>Loading Subject</b></p></div>');
          },
          success: function (data) {
            var closeSub = "<button type='submit' id='" + target + "_close_subject' class='btn btn-sm btn-outline-danger d-inline-flex align-items-center justify-content-center'><span class='dashicons dashicons-no'></span></button>";

            if ($.trim(data)) {
              var add_subject = "<div class='d-flex gap-2 mt-3'><button type='submit' id='" + target + "_add_subject' class='btn btn-sm btn-outline-success d-inline-flex align-items-center justify-content-center'><span class='dashicons dashicons-plus'></span></button>" + closeSub + "</div>";
              $('#' + target + '_list').html(data);
              $("#add_to_button").html(add_subject);
              $('#sub_msgs').html('');
            } else {
              $('#' + target + '_list').html('');

              $('#sub_msgs').html('<div class="notice notice-error is-dismissible"><p>Sorry, subject not found in this <b>(' + action_for + ')</b> group. <a href="/wp-admin/admin.php?page=educare-management&Group&Group_' + action_for + '" target="_blank">Click here</a> to add subject</b></p></div>');

              $("#add_to_button").html(closeSub);
            }
          },
          error: function (data) {
            $('#sub_msgs').html(connectionsError);
          },
          complete: function () {
            educareLoading.fadeOut();
          },
        });
      } else {
        changeClass(currentData);
      }
    }

    $(document).on("change", "#crud-forms #Class", function (event) {
      event.preventDefault();
      currentData = $(this);
      changeClass(currentData);
    });

    $(document).on("change", "#" + target, function (event) {
      event.preventDefault();
      // var current = $(this);
      var action_for = $(this).val();
      educareGroupSub(action_for, this);
    });

    $(document).on("click", "#edit_add_subject", function (event) {
      event.preventDefault();
      var action_for = $('#Group').val();
      educareGroupSub(action_for, this);
    });

    function checkGroup() {
      var numberOfChecked = $("[name|='select_subject[]']:checked").length;
      var group_subject = educareSettings.group_subject;

      var changeLink = 'You can change this group wise requred subject from <code>Educare Settings > Results System > Group Subject</code>. <a href="/wp-admin/admin.php?page=educare-settings&menu=Results_System" target="_blank">Click here</a> to change';

      if (group_subject == 0 || !group_subject) {
        return true;
      } else if (numberOfChecked == false) {
        $('#sub_msgs').html('<div class="notice notice-error is-dismissible"><p>Please choice subject to add</b></p></div>');
        return false;
      } else if (numberOfChecked < group_subject) {
        $('#sub_msgs').html('<div class="notice notice-error is-dismissible"><p>Please select minimum <b>(' + group_subject + ')</b> subject. ' + changeLink + '</p></div>');
        return false;
      } else if (numberOfChecked > group_subject) {
        $('#sub_msgs').html('<div class="notice notice-error is-dismissible"><p>Sorry, you are trying to add miximum number of subject! Please select only requred <b>(' + group_subject + ')</b> subject. ' + changeLink + '</p></div>');
        return false;
      } else {
        return true;
      }

    }

    // when trying to add (group) subject into the subject list
    $(document).on("click", "#" + target + "_add_subject", function (event) {
      event.preventDefault();
      var class_name = $('#Class').val();
      var id_no = $('#id_no').val();
      var form_data = $(this).parents('form').serialize();

      if (checkGroup() === true) {
        $.ajax({
          url: educareAjax.url,
          type: 'POST',
          data: {
            action: 'educare_class',
            class: class_name,
            id: id_no,
            form_data: form_data,
            add_students: add_students,
          },
          beforeSend: function (data) {
            educareLoading.fadeIn();
            $('#sub_msgs').html('<div class="notice notice-success is-dismissible"><p>Addeting Subject</b></p></div>');
          },
          success: function (data) {
            $('#result_msg').html(data);
            $('#Class').attr('disabled', false);
          },
          error: function (data) {
            $('#result_msg').html(connectionsError);
          },
          complete: function () {
            educareLoading.fadeOut();
            $('.educare_crud').prop('disabled', false);
          }
        });

      } else {
        checkGroup(currentData);
      }
    });

    // when click close button
    $(document).on("click", "#" + target + "_close_subject", function (event) {
      event.preventDefault();
      var class_name = $('#' + target + '_list').empty();
      $('#sub_msgs').empty();
      $('#add_to_button').html("<div id='edit_add_subject' class='btn btn-sm btn-outline-success d-inline-flex align-items-center justify-content-center'><i class='dashicons dashicons-edit'></i></div>");

      var oldGroup = $('#old-Group').val();

      $('#Group').val(oldGroup);
      $('.educare_crud').prop('disabled', false);
    })

    // Save (CRUD) forms data
    // import data from students
    $(document).on("click", ".crud-forms", function (event) {
      event.preventDefault();
      var current = $(this);
      var action_for = $(this).attr('name');

      if (action_for === 'delete' ) {
        if (educareSettings.confirmation === "checked") {
          if (!confirm("Are you sure you want to delete this data?")) {
            return;
          }
        }
      }

      var form_data = $(this).parents('form').serialize() + '&' + action_for + '=' + action_for;
      $.ajax({
        url: educareAjax.url,
        type: 'POST',
        data: {
          action: 'educare_get_data_from_students',
          form_data: form_data
        },
        beforeSend: function (data) {
          $('#educare-loading').fadeIn();
        },
        success: function (data) {
          $('#educare-form').html(data);
        },
        error: function (data) {
          $('#educare-loading').fadeOut();
          alert('Error');
        },
        complete: function () {
          $('#educare-loading').fadeOut();
          // do some
        },
      });
    });



    $(document).on("click", ".crud-results", function (event) {
      event.preventDefault();

      var msgs = $(this).parents('.modal-content').find('.modal-body .msgs');
      
      $.ajax({
        url: educareAjax.url,
        type: 'POST',
        data: {
          action: 'educare_crud_results'
        },
        beforeSend: function (data) {
          $('#educare-loading').fadeIn();
        },
        success: function (data) {
          msgs.html(data);
        },
        error: function (data) {
          $('#educare-loading').fadeOut();
          alert('Error');
        },
        complete: function () {
          $('#educare-loading').fadeOut();
          // do some
        },
      });
    });
  }
  // Educare options by ajax functionality callback
  educareOptionsByAjaxPage();


  // eTabManagement functionality
  function educareTabManagementPage() {
    var educareTabManagement_url = $('.educareTabManagement_url').data('value');
    var educareTabManagement_action_for = $('.educareTabManagement_action_for').data('value');
    var educareTabManagement_menu = $('.educareTabManagement_menu').data('value');
    var educareTabManagement_active_tab = $('.educareTabManagement_active_tab').data('value');
    var educareTabManagement_front = $('.educareTabManagement_front').data('value');

    $(document).on("click", ".tab_management .tablinks", function (event) {
      event.preventDefault();

      tablinks = $(".tablinks");

      for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace("active", "");
      }

      var current = $(this);
      current.addClass('active');
      var tab = current.attr('id');

      $.ajax({
        url: educareAjax.url,
        data: {
          action: 'educare_process_tab',
          tab: tab,
          action_for: educareTabManagement_action_for,
          front: educareTabManagement_front,
        },
        type: 'POST',
        beforeSend: function () {
          $('#educare-loading').fadeIn();
        },
        success: function (data) {
          history.pushState('', 'tab', educareTabManagement_url + '&' + tab);

          $('#educare-loading').fadeOut();
          $('#educare-data').html(data);
        },
        error: function (data) {
          $('#educare-data').html(educareSettings.db_error);
        },
        complete: function () {
          $('#educare-loading').fadeOut();
        },
      });

    });

    if (educareTabManagement_active_tab) {
      $(".tab_management .active").removeClass('active');
      $(".tab_management #" + educareTabManagement_active_tab).addClass('active');
    }

    if (educareTabManagement_menu) {
      $('#' + educareTabManagement_menu + '_menu').prop("checked", true);
    }
  }
  // eTabManagement functionality callback
  educareTabManagementPage();

  // ProcessContent functionality
  function educareProcessContentPage() {
    // Function for Class and Group
    $(document).on("click", ".proccess_Class, .proccess_Group", function (event) {
      event.preventDefault();
      var current = $(this);
      var form_data = $(this).parents('form').serialize();
      var action_for = $(this).attr("name");
      var action_data = $(this).attr("class");
      var msgs = '#msg_for_Class';

      if (action_data.indexOf('proccess_Group') > -1) {
        msgs = '#msg_for_Group';
      }

      // Function to perform AJAX request after confirmation
      function performAjaxRequest() {
        $.ajax({
          url: educareAjax.url,
          type: 'POST',
          data: {
            action: 'educare_process_content',
            form_data: form_data,
            action_for
          },
          beforeSend: function (event) {
            current.children('.dashicons').addClass('educare-loader');
            $('#educare-loading').fadeIn();
          },
          success: function (data) {
            $(msgs).html(data);
          },
          error: function (data) {
            $(msgs).html(educareSettings.db_error);
          },
          complete: function () {
            $('#educare-loading').fadeOut();
            current.children('.dashicons').removeClass('educare-loader');
            // event.remove();
          },
        });
      }

      if (action_for == 'remove_class' || action_for == 'remove_subject') {
        if (educareSettings.confirmation == 'checked') {
          var target = '';
          if (action_for == 'remove_class') {
            target = $(this).attr('data-class');
          } else {
            target = $(this).attr('data-subject');
          }

          // Show the confirm dialog
          var confirmResult = confirm("Are you sure to remove (" + target + ") from this list?");

          // Check the result of the confirm dialog
          if (confirmResult) {
            // If the user clicked 'OK' (confirmed), then perform the AJAX request
            performAjaxRequest();
          } else {
            // If the user clicked 'Cancel' (not confirmed), do nothing.
            // You can add additional handling here if needed.
          }
        } else {
          // If confirmation is not required, perform the AJAX request directly
          performAjaxRequest();
        }
      } else {
        // For other actions, perform the AJAX request directly
        performAjaxRequest();
      }
    });



    // management add class or group form tab
    $(document).on("click", ".form_tab .tablink", function (event) {
      event.preventDefault();
      var i, allTab, tablinks;
      var crntButton = $(this);
      tablinks = $(this).attr('data');
      var educareTabs = $(this).parents('.educare_tabs');
      // remove active class
      allButton = $(this).siblings(".tablink").removeClass('educare_button');
      allTab = educareTabs.children(".section_name");

      allTab.each(function () {
        var crntTabs = $(this).attr('id');
        if (crntTabs == tablinks) {
          $(this).css('display', 'block');
          // add active class
          crntButton.addClass('educare_button');
        } else {
          $(this).css('display', 'none');
        }
      });

    });

    var list = $('.educareSettingSubForm').data('value');
    // Auto select class or group in select box
    $(document).on("click", ".collapse [name=" + list + "]", function () {
      $("#add_" + list).val($(this).attr("data"));
    });
  }
  // ProcessContent functionality callback
  educareProcessContentPage();

  // AjaxContent functionality
  function educareAjaxContentPage($list) {
    var educareLoading = $('#educare-loading');
    var $list_button = $list.replace(/_/g, '');

    $(document).on("click", "#educare_add_" + $list, function (event) {
      event.preventDefault();
      // $(this).attr('disabled', true);
      var current = $(this);
      var form_data = $(this).parents('form').serialize();
      var action_for = "educare_add_" + $list;
      $.ajax({
        url: educareAjax.url,
        type: 'POST',
        data: {
          action: 'educare_process_content',
          form_data: form_data,
          action_for
        },
        beforeSend: function (event) {
          educareLoading.fadeIn();
          current.children('.dashicons').addClass('educare-loader');
        },
        success: function (data) {
          $("#msg_for_" + $list).html(data);
          $("#educare_add_" + $list).attr('disabled', false);
        },
        error: function (data) {
          educareLoading.fadeOut();
          $("#msg_for_" + $list).html(educareSettings.db_error);
        },
        complete: function () {
          // event.remove();
          educareLoading.fadeOut();
          current.children('.dashicons').removeClass('educare-loader');
        },
      });

    });

    $(document).on("click", "button.remove" + $list_button, function (event) {
      // $(this).attr('disabled', true);
      event.preventDefault();
      var form_data = $(this).parents('form').serialize();
      var target = $(this).prevAll("[name='remove']").val();
      var action_for = "remove_" + $list;
      $.ajax({
        url: educareAjax.url,
        type: 'POST',
        data: {
          action: 'educare_process_content',
          form_data: form_data,
          action_for
        },
        beforeSend: function () {
          if (educareSettings.confirmation == 'checked') {
            var confirmThis = confirm("Are you sure to remove (" + target + ") from this " + $list.replace(/_/g, ' ') + " list?");

            // Confirm and decide to abort the request
            if (confirmThis) {
              $('#educare-loading').fadeIn();
            } else {
              // Abort the request
              this.abort();
            }

          } else {
            $('#educare-loading').fadeIn();
          }
        },
        success: function (data) {
          $("#msg_for_" + $list).html(data);
          $('#educare-loading').fadeOut();
        },
        error: function (data) {
          $("#msg_for_" + $list).html(educareSettings.db_error);
        },
        complete: function () {
          $('#educare-loading').fadeOut();
        },
      });
    });


    $(document).on("click", "button.edit" + $list_button, function (event) {
      // $(this).attr('disabled', true);
      event.preventDefault();
      var form_data = $(this).parents('form').serialize();
      var action_for = "educare_edit_" + $list;
      $.ajax({
        url: educareAjax.url,
        type: 'POST',
        data: {
          action: 'educare_process_content',
          form_data: form_data,
          action_for
        },
        beforeSend: function (event) {
          educareLoading.fadeIn();
        },
        success: function (data) {
          $("#msg_for_" + $list).html(data);
        },
        error: function (data) {
          educareLoading.fadeOut();
          $("#msg_for_" + $list).html(educareSettings.db_error);
        },
        complete: function () {
          // event.remove();
          educareLoading.fadeOut();
        },
      });
    });


    // update Class, Exam, Year, Extra field
    $(document).on("click", "button.update" + $list_button, function (event) {
      // $(this).attr('disabled', true);
      event.preventDefault();
      var form_data = $(this).parents('form').serialize();
      var action_for = "educare_update_" + $list;
      $.ajax({
        url: educareAjax.url,
        type: 'POST',
        data: {
          action: 'educare_process_content',
          form_data: form_data,
          action_for
        },
        beforeSend: function (event) {
          educareLoading.fadeIn();
        },
        success: function (data) {
          $("#msg_for_" + $list).html(data);
        },
        error: function (data) {
          educareLoading.fadeOut();
          $("#msg_for_" + $list).html(educareSettings.db_error);
        },
        complete: function () {
          // event.remove();
          educareLoading.fadeOut();
        },
      });
    });

    $(document).on("click", "[name=" + $list_button + "]", function () {
      $('#add_' + $list_button).val($(this).attr('data'));
    });

    $(document).on("click", ".notice-dismiss", function (event) {
      $(this).parent('div').fadeOut();
    });
  }
  // AjaxContent functionality callback
  educareAjaxContentPage('Class');
  educareAjaxContentPage('Group');
  educareAjaxContentPage('Exam');
  educareAjaxContentPage('Year');
  educareAjaxContentPage('Extra_field');


  // DisplayData functionality
  function educareDisplayDataPage() {
    var confirmation = educareSettings.confirmation;

    $(document).on("click", "#update_status, #delete_all", function (event) {
      // $('#update_status, #delete_all').click(function (e) {
      if (confirmation == 'checked') {
        var action;
        var current = $(this).attr('name');

        if (current == 'update_status') {
          action = 'update to this status?';
        } else {
          action = 'delete?';
        }

        // Show the confirmation dialog
        if (confirm("Are you sure you want to " + action)) {
          // If confirmed, update the form method and submit
          $('#filter_data').attr('method', 'post').submit();
        } else {
          // If canceled, prevent form submission
          event.preventDefault();
        }
      } else {
        $('#filter_data').attr('method', 'post').submit();
      }
    });

    $(document).on("click", "[name='delete']:not(.crud-forms)", function (event) {
      if (educareSettings.confirmation == 'checked') {
        return confirm("Are you sure you want to delete this data?");
      }
    });
  }
  // DisplayData functionality callback
  educareDisplayDataPage();

  // AddMarks functionality
  function educareAddMarksPage() {
    $(document).on("change", ".add_marks #Class, .add_marks #Group", function (event) {
      event.preventDefault();
      var current = $(this);
      var form_data = $(this).parents('form').serialize();
      var action_for = "get_" + $(this).attr("name");
      $.ajax({
        url: educareAjax.url,
        type: 'POST',
        data: {
          action: 'educare_process_marks',
          form_data: form_data,
          action_for: action_for
        },
        beforeSend: function (data) {
          $('#educare-loading').fadeIn();
          $('#Subject').html('<option value="">Loading Subject</option>');
        },
        success: function (data) {
          if ($.trim(data)) {
            $('#Subject').html(data);
          } else {
            $('#Subject').html('<option value="">All Subject</option><option value="" disabled>Subject Not Found</option>');
          }
        },
        error: function (data) {
          $('#educare-loading').fadeOut();
          $('#Subject').html('<option value="">Loading Error</option>');
        },
        complete: function () {
          $('#educare-loading').fadeOut();
          // do some
        },
      });
    });

    $(document).on("click", "#print", function (event) {
      event.preventDefault();

      var db = $('.educare_print').data('value');
      var pageTitle = 'Attendance'

      if (db === 'marks') {
        pageTitle = "Marksheet";
      }

      var content = $('.educare_print').html();
      var headerContent = '<style>body {padding: 4%;} .view_results {width: 100%;} th:nth-child(2), td:nth-child(2), button, .action_link {display: none;} thead {background-color: #00ac4e !important; color: white !important; -webkit-print-color-adjust: exact;} .collapse thead {background-color: #f3f3f3 !important; color: #444 !important; -webkit-print-color-adjust: exact;} table, td, th {border: 1px solid black; text-align: center; padding: 8px; border-collapse: collapse;} .student-info * {text-align: center;} th {white-space: nowrap;} input {border: none;} th small {display: block;}</style>';

      var realContent = document.body.innerHTML;
      var mywindow = window.open();
      mywindow.document.write(headerContent + content);
      mywindow.document.title = pageTitle;
      mywindow.document.close(); // necessary for IE >= 10
      mywindow.focus(); // necessary for IE >= 10*/
      mywindow.print();
      document.body.innerHTML = realContent;
      mywindow.close();
      return true;
    });

    $('#add-marks').unsavedChangesAlert();
  }
  // AddMarks functionality callback
  educareAddMarksPage();

  // TemplateForm functionality
  function educareTemplateFormPage() {
    // Upload || Select attachmen
    $(document).on("click", ".attachmentInput", function (event) {
      event.preventDefault();
      var clickedElement = $(this).parents('.getAttachment');

      // Open the WordPress media library dialog box
      wp.media.editor.open();

      // Override the "send" function of the media frame
      wp.media.editor.send.attachment = function (props, attachment) {
        // Set the selected attachment ID as the value of the clicked element
        // console.log(attachment);
        $(this).html(attachment.title);
        clickedElement.children('.attachmentPreview').html('<div class="attachmentImg"><img src="' + attachment.url + '"></div>');
        clickedElement.children('input').val(attachment.id);

        // Show remove button
        clickedElement.children('.attachmentControl').addClass('attachmentRemove btn-danger').removeClass('attachmentInput').html('<span class="dashicons dashicons-remove"></span> Remove');

        // Restore the default "send" function for future use
        wp.media.editor.send.attachment = sendAttachment;
      };

      // Store the default "send" function for future use
      var sendAttachment = wp.media.editor.send.attachment;
    });

    // Remove attachmen
    $(document).on("click", ".attachmentRemove", function (event) {
      event.preventDefault();
      var clickedElement = $(this).parents('.getAttachment');
      clickedElement.children('.attachmentPreview').html('');
      clickedElement.children('input').val('');

      // Show upload button
      $(this).addClass('attachmentInput btn-success').removeClass('btn-danger').html('<span class="dashicons dashicons-cloud-upload"></span> Upload Now');
    });
  }
  // TemplateForm functionality callback
  educareTemplateFormPage();

  $(document).on("submit", "#educareActivation", function (event) {
    event.preventDefault();

    // Find the clicked button within the form
    var $clickedButton = $(this).find(':submit:focus');
    var $action_for = $clickedButton.attr('name') || 'educare_activate_license';

    var $licenseKeyField = $('#educare_license_key');
    var licenseKey = $licenseKeyField.val().trim();
    var nonceVal = $('#educare_license_nonce').val();

    if (licenseKey === '') {
      // Set placeholder text and highlight the field
      $licenseKeyField.attr('placeholder', 'Please enter a license key')
        .css('outline-color', 'red').focus();

      // Remove the red border when the user starts typing
      $licenseKeyField.on('input', function () {
        $(this).css('outline-color', '').attr('placeholder', '');
      });

      return; // Stop form submission
    }

    $.ajax({
      url: ajaxurl, // Assuming ajaxurl is already defined as server URL
      method: 'POST',
      data: {
        action: $action_for,
        key: licenseKey,
        nonce: nonceVal
      },
      beforeSend: function () {
        if ($action_for == 'educare_deactivate_license') {
          var confirmThis = confirm("You are about to deactivate premium access. If you proceed, all premium features will be disabled, and you will no longer have access to premium functionality. Are you sure you want to deactivate premium access?");
          if (!confirmThis) {
            return false; // Abort the request
          }
        }
        $('#educare-loading').fadeIn();
      },
      success: function (response) {
        if (response.data.body.success) {
          if ($action_for == 'educare_deactivate_license') {
            $licenseKeyField.val('');
          }
        }

        $('#status-messege').html(response.data.body.message).fadeIn();
      },
      error: function () {
        // alert('An unexpected error occurred. Please try again later.');
        $('#status-messege').html('<div class="notice notice-error is-dismissible"><p>An unexpected error occurred. Please try again later.</p></div>');
      },
      complete: function () {
        $('#educare-loading').fadeOut();
      },
    });
  });



  $(document).on("click", ".educare-notice .notice-dismiss", function () {
    var noticeId = $(this).closest('.educare-notice').data('id');

    if (!noticeId) {
      // console.error("Notice ID not found");
      return;
    }

    $.post(
      ajaxurl,
      {
        action: "educare_dismiss_notice",
        noticeKey: noticeId
      }
    );
  });


  $(document).on("click", "#educare-license-notice .notice-dismiss", function (event) {
    // event.preventDefault();
    jQuery.post(
      ajaxurl,
      {
        action: "dismiss_educare_renewal_notice"
      }
    );
  });

});


// === With Pure JavaScript ===

function add(form) {
  var type = form.type.value;
  var field = form.field.value;
  if (field) {
    form.Extra_field.value = type + " " + field;
  }
}

function clearFormData(myForm) {
  var form = document.getElementById(myForm);
  var formElements = form.elements;

  for (var i = 0; i < formElements.length; i++) {
    var element = formElements[i];

    // Exclude buttons from clearing
    if (element.type !== "button" && element.type !== "submit") {
      // Clear input fields and textareas
      if (element.tagName === "INPUT" || element.tagName === "TEXTAREA") {
        element.value = "";
      }
      // Clear selected radio buttons
      if (element.type === "radio") {
        element.checked = false;
      }
      // Clear selected checkboxes
      if (element.type === "checkbox") {
        element.checked = false;
      }
      // Handle select elements (dropdowns)
      if (element.tagName === "SELECT") {
        element.selectedIndex = 0;
      }
    }
  }
}