<?php
 /** =====================( Features Details )======================
  
	# Educare Marksheed Systems

	* Using this features admin (teacher) can add subject wise multiple student results at a same time. So, it's most usefull for (single) teacher. There are different teachers for each subject. Teachers can add marks for their specific subject using this feature. And can print all student marks as a marksheet. After, the mark addition is done for all the subjects, students can view and print their results when admin publish it as results. Also, teacher can publish single subject results. (We call it - THE GOLDEN FEATURES FOR TEACHER!).

  * @since 1.2.4
	* @last-update 1.2.4
	
===================( Features for Marksheed )=================== **/

if (educare_database_check('educare_marks')) {
	educare_database_table('educare_marks');
}

?>

<div class="educare_post imoport">
	<h1>Add Marks</h1>
	<?php
	echo educare_guide_for("Using this features admin (teacher) can add subject wise multiple student results at a same time. So, it's most usefull for (single) teacher. There are different teachers for each subject. Teachers can add marks for their specific subject using this feature. And can print all student marks as a marksheet. After, the mark addition is done for all the subjects, students can view and print their results when admin publish it as results. Also, teacher can publish single subject results. (We call it - <b>THE GOLDEN FEATURES FOR TEACHER!</b>)");
	?>
	
	<?php
	if (isset($_POST['students_list'])) {
		$Class = sanitize_text_field($_POST['Class']);
		$Exam = sanitize_text_field($_POST['Exam']);
		$Subject = sanitize_text_field($_POST['Subject']);
		$Year = sanitize_text_field($_POST['Year']);
	}
	?>
	
	<form method='post' action="" class="add_results">
		<div class="content">
		<div class="select">
				<select id="Class" name="Class" class="form-control">
				<option value="">Select Class</option>
					<?php educare_get_options('Class', $Class);?>
				</select>

				<select id="Exam" name="Exam" class="form-control">
					<?php educare_get_options('Exam', $Exam);?>
				</select>
			</div>

			<div class="select">
				<select id="Subject" name="Subject" class="form-control">
					<option value="">Select Subject</option>
				</select>

				<select id="Year" name="Year" class="form-control">
					<?php educare_get_options('Year', $Year);?>
				</select>
			</div>

			<p>Students Per Page: <input id="student_per_page" type="number" value="30"></p>

			<input type="submit" name="students_list" class="educare_button" value="Students List">
		</div>
	</form>

	<div id="msgs"></div>

</div>

<script type="text/javascript">
	$(document).on("change", "#Class", function(event) {
		event.preventDefault();
		var current = $(this);
		var form_data = $(this).parents('form').serialize();
		var action_for = $(this).attr("name");
		$.ajax({
			url: "<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
			data: {
				action: 'educare_process_marks',
				form_data: form_data,
				action_for: 'get_subject'
			},
			type: 'POST',
			beforeSend: function(data) {
				$('#Subject').html('<option value="">Loading Subject</option>');
			},
			success: function(data) {
				if ($.trim(data)) { 
					$('#Subject').html(data);
				} else {
					$('#Subject').html('<option value="">Subject Not Found</option>');
				}
			},
			error: function(data) {
				$('#Subject').html('<option value="">Loading Error</option>');
			},
			complete: function() {
				// do some
			},
		});
	});

	$(document).on("click", "[type=submit]", function(event) {
		event.preventDefault();
		var current = $(this);
		var form_data = $(this).parents('form').serialize();
		var action_for = $(this).attr("name");
		$.ajax({
			url: "<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
			data: {
				action: 'educare_process_marks',
				form_data: form_data,
				action_for
			},
			type: 'POST',
			success: function(data) {
				$('#msgs').html(data);
			},
			error: function(data) {
				$('#msgs').html("<?php echo educare_guide_for('db_error')?>");
			},
			complete: function() {
				// event.remove();
			},
		});
	});

	$(document).on("click", ".notice-dismiss", function(event) {
		event.preventDefault();
    $(this).parent('div').fadeOut();
    $('#update_button').fadeIn();
	});

	$(document).on("click", "#print", function(event) {
		event.preventDefault();

		var content = $('.educare_print').html();
		var headerContent = '<style>body {padding: 4%;} .view_results {width: 100%;} th:nth-child(2), td:nth-child(2), button {display: none;} thead {background-color: #00ac4e !important; color: white !important; -webkit-print-color-adjust: exact;} table, td, th {border: 1px solid black; text-align: left; padding: 8px; border-collapse: collapse;} input {border: none;}</style>';
		var realContent = document.body.innerHTML;
		var mywindow = window.open();
		mywindow.document.write(headerContent + content);
		mywindow.document.title = "Marksheed";
		mywindow.document.close(); // necessary for IE >= 10
		mywindow.focus(); // necessary for IE >= 10*/
		mywindow.print();
		document.body.innerHTML = realContent;
		mywindow.close();
		return true;
	});

	$(document).on("click", ".notice-dismiss", function(event) {
    $(this).parent('div').fadeOut();
    $('#update_button').fadeIn();
  });
	
</script>

