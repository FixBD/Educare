<?php
/** 
 * ### Educare Settings
 * 
 * Features for manage educare settings.
 * 
 * @since 1.0.0
 * @last-update 1.4.0
 */

// Create tab
$action = 'settings';
$tab = array (
  // Tab name => Icon
	'settings' => 'admin-generic',
	'default_photos' => 'format-image',
	'grading_system' => 'welcome-learn-more',
);

educare_tab_management($action, $tab);

?>

<script>
	// =========== Script for Setting Page ===========

  jQuery( document ).ready( function( $ ) {
		var advance = '<?php echo educare_esc_str(educare_check_status('advance'));?>';
		if (advance == 'unchecked') {
			$( '#advance_settings' ).css( 'display', "none" );
		}
	});

	$(document).on("click", "[name=educare_update_settings_status], [name=educare_reset_default_settings]", function(event) {
		event.preventDefault();
		// var currenTab = $(".head[name=subject]:checked").attr("id");
		var current = $(this);
		var form_data = $(this).parent('form').serialize();
		var action_for = $(this).attr("name");
    var active_menu = $('.head:checked').attr('id');
		$.ajax({
			url: "<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
			data: {
				action: 'educare_process_content',
				form_data: form_data,
        active_menu: active_menu,
				action_for
			},
			type: 'POST',
			beforeSend:function(event) {
				if (action_for == 'educare_reset_default_settings') {
					<?php 
					if (educare_check_status('confirmation') == 'checked') {
						echo 'return confirm("Are you sure to reset default settings? This will not effect your content (Class, Subject, Exam, Year, Extra Field), Its only reset your current settings status and value.")';
					}
					?>
				} else {
					$('#educare-loading').fadeIn();
				}
				current.children('.dashicons').addClass('educare-loader');
			},
			success: function(data) {
				$('#educare-data').html(data);
			},
			error: function(data) {
				$('#educare-data').html("<?php echo educare_guide_for('db_error', '', false)?>");
			},
			complete: function() {
				$('#educare-loading').fadeOut();
				current.children('.dashicons').removeClass('educare-loader');
				// event.remove();
			},
		});
		
	});


	
  // =========== Script for Grading System Page ===========

  // Edit button
  var result_msg_data = false;

  $(document).on("click", "#edit_grade", function() {
    $(this).attr('disabled', true);
    var class_name = $('#grading').val();
    result_msg_data = $('#result_msg').html();

    $.ajax({
      url: "<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
      data: {
        action: 'educare_proccess_grade_system',
        class: class_name
      },
      type: 'POST',
      beforeSend:function(event) {
        $('#educare-loading').fadeIn();
      },
      success: function(data) {
        // $('#result_msg').hide();
        $('#result_msg').html(data).fadeIn();
        $('#update_button').fadeOut();
        $('#edit_grade').attr('disabled', false);
      },
      error: function(data) {
        $('#result_msg').html("<?php echo educare_guide_for('db_error')?>");
      },
      complete: function() {
        $('#educare-loading').fadeOut();
      }
    });
  });

  // Update buttton
  $(document).on("click", "#save_addForm", function() {
    $(this).attr('disabled', true);
    var form_data = $(this).parents('form').serialize();

    $.ajax({
      url: "<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
      data: {
        action: 'educare_save_grade_system',
        form_data: form_data,
        update_grade_rules: true
      },
      type: 'POST',
      beforeSend:function(event) {
        $('#educare-loading').fadeIn();
      },
      success: function(data) {
        $('#result_msg').hide();
        $('#result_msg').html(data).fadeIn();
        $('#update_button').fadeIn();
        $('#edit_grade').attr('disabled', false);
      },
      error: function(data) {
        $('#result_msg').html("<?php echo educare_guide_for('db_error')?>");
      },
      complete: function() {
        $('#educare-loading').fadeOut();
      }
    });
  });
  
  $("#help").click(function() {
    $(this).css('color', 'green');
    $("#show_help").slideToggle();
  });

  $(document).on("click", ".notice-dismiss", function(event) {
    $(this).parent('div').fadeOut();
    $('#result_msg').hide().html(result_msg_data).fadeIn();
    $('#update_button').fadeIn();
  });

  // =========== End Script for Grading System Page ===========

</script>
