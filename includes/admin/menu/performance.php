<?php
/**
 * ### Educare Performance
 * 
 * Here admin can change multiple students class, year, group just one click! Most usefull when need to promote students (one class to onother) or need to update mulltiple studens.
 * 
 * @since 1.4.0
 * @last-update 1.4.0
 */

// Create tab
$action = 'performance';
$tab = array (
  // Tab name => Icon
  'promote_students' => 'chart-bar',
  'attendance' => 'clipboard'
);

educare_tab_management($action, $tab);

?>

<script type="text/javascript">
  $(document).on("click", "#promote", function(event) {
    event.preventDefault();
    var current = $(this);
    var form_data = $(this).parents('form').serialize();
    // alert('Ok');
    $.ajax({
      url: "<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
      data: {
        action: 'educare_proccess_promote_students',
        form_data: form_data
      },
      type: 'POST',
      beforeSend: function(data) {
        $('#educare-loading').fadeIn();
      },
      success: function(data) {
        $('#promote_msgs').html(data);
      },
      error: function(data) {
        $('#educare-loading').fadeOut();
        $('#promote_msgs').html("<?php echo educare_guide_for('db_error')?>");
      },
      complete: function() {
        $('#educare-loading').fadeOut();
        // do some
      },
    });
  });
</script>

