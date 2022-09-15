<?php
 /** =====================( Features Details )======================
  
	# Educare Students Managemet Systems

	* Using this features admin (teacher) can add, edit, upadate students.

  * @since 1.2.4
	* @last-update 1.2.4
	
===================( Features for Students Management )=================== **/

if (educare_database_check('educare_students')) {
	educare_database_table('educare_students');
}
?>

<div class="container educare-page">

	<div class="tab">
	  <button class="tablinks active" onclick="openTabs(event, 'all-students')" id="default" title="View All Students" data="all-students"><i class="dashicons dashicons-businessman"></i><span>All</span></button>
	  <button class="tablinks" onclick="openTabs(event, 'add-students')" title="Add New Students" data="add-students"><i class="dashicons dashicons-plus-alt"></i><span>Add</span></button>
		<button class="tablinks" onclick="openTabs(event, 'update-students')" title="Update Students Data" data="update-students"><i class="dashicons dashicons-update"></i><span>Edit</span></button>
		<button class="tablinks" onclick="openTabs(event, 'import-students')" title="Import Students" data="import-students"><i class="dashicons dashicons-database-import"></i><span>Import</span></button>
	</div>
	
	<div class="educare_post educare_settings">

		<div id="tab-loading"><center>Loading..</center></div>
		
		<div id="all-students" class="tab_content" style="display: block;">
			<?php educare_students_management();?>
		</div>
		
		<div id="add-students" class="tab_content" style="display: none;"></div> 

		<div id="update-students" class="tab_content" style="display: none;"></div>

		<div id="import-students" class="tab_content" style="display: none;"></div>

	</div> <!-- / .educare Settings -->

</div>

<script type="text/javascript">
	function openTabs(evt, tabName) {
		// Declare all variables
		var i, tab_content, tablinks;

		// Get all elements with class="tab_content" and hide them
		tab_content = document.getElementsByClassName("tab_content");
		for (i = 0; i < tab_content.length; i++) {
			tab_content[i].style.display = "none";
		}

		// Get all elements with class="tablinks" and remove the class "active"
		tablinks = document.getElementsByClassName("tablinks");
		for (i = 0; i < tablinks.length; i++) {
			tablinks[i].className = tablinks[i].className.replace(" active", "");
		}

		// Show the current tab, and add an "active" class to the button that opened the tab
		document.getElementById(tabName).style.display = "block";
		evt.currentTarget.className += " active";
	}
	
	// Get the element with id="defaultOpen" and click on it
	document.getElementById("default").click();

	
	<?php
	$url = admin_url();
	$url .= 'admin.php?page=educare-all-students';
	?>

	$(document).on("click", ".tablinks", function(event) {
		event.preventDefault();
		
		// var currenTab = $(".head[name=subject]:checked").attr("id");
		var current = $(this);
		// $(current).css('color', 'red');
		var form_data = current.attr('data');
		var action_for = 'tab';
		$.ajax({
			url: "<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
			data: {
				action: 'educare_process_students',
				form_data: form_data,
				action_for
			},
			type: 'GET',
			beforeSend:function(event) {
				// $('#' + form_data).html("<center>Loading</center>");
				$('#tab-loading').fadeIn();
			},
			success: function(data) {
				// window.history.pushState('', form_data, window.location.href + '&' + form_data);
				history.pushState('', 'form_data', '<?php echo esc_url($url);?>' + '&' + form_data);
				$('#tab-loading').fadeOut();
				$('#' + form_data).html(data);
			},
			error: function(data) {
				$('#' + form_data).html("<?php echo educare_guide_for('db_error')?>");
			},
			complete: function() {
				// event.remove();
			},
		});
		
	});

	$(document).on("click", ".onlyForTest", function(event) {
		event.preventDefault();
		
		// var currenTab = $(".head[name=subject]:checked").attr("id");
		var current = $(this);
		// $(current).css('color', 'red');
		var form_data = $(this).parents('form').serialize();
		var action_for = $(this).attr("name");
		$.ajax({
			url: "<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
			data: {
				action: 'educare_process_forms',
				form_data: form_data,
				action_for,
				data_for: 'students'
			},
			type: 'POST',
			success: function(data) {
				if (action_for == 'edit_by_id') {
					$('#msgs_forms').html(data);
					$('#msgs').fadeOut().empty();
				} else {
					$('#msgs').html(data).fadeIn();
				}
				
			},
			error: function(data) {
				$('#msgs').html("<?php echo educare_guide_for('db_error')?>");
			},
			complete: function() {
				// event.remove();
			},
		});
		
	});

</script>

<?php
if ( isset($_GET['add-students'])) {
	$tab = 'add-students';
}
elseif ( isset($_GET['update-students'])) {
	$tab = 'update-students';
}
elseif ( isset($_GET['import-students'])) {
	$tab = 'import-students';
} else {
	$tab = 'all-students';
}
?>

<script>
	$(".active").removeClass('active');
	$("[data=<?php echo esc_attr( $tab );?>]").addClass('active');
</script>


