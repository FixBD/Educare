<div class="educare_post imoport">
	<h1>Import Results</h1>

	<?php
	educare_import_result();
	echo educare_guide_for('import');
	?>

	<!-- Import Form -->
	<form  class="add_results" method="post" action="<?php esc_url($_SERVER['REQUEST_URI']); ?>" enctype="multipart/form-data" id="upload_csv">
		<div class="content">
			<p>Files must be an <b>.csv</b> extension for import the results.</p>
			<input type="file" name="import_file">
			<select name="Class" class="form-control">
				<?php educare_get_options('Class', '');?>
			</select><br>
			<button class="educare_button" type="submit" name="educare_import_results"><i class="dashicons dashicons-database-import"></i> Import</button>
		</div>
	</form>
	<br>

	<div class='demo'>
		<strong>Optional Subject Selection Guide</strong>
		<p>Educare add 1 before optional subject marks <code>1 [space] Marks</code>.</p>
		<li style="font-size: small;">Exp: <code>1 85</code></li>
		<li style="font-size: small;">Here <code>1</code> 	= Define optional subject</li> 
		<li style="font-size: small;">and <code>85</code> 	= Marks</li>
		<p>In this way educare define and identify optional subjects. So, when you add a result to the csv files - you need to add <code>1</code> symbol before the optional subject marks.</p>
		
		<p>Select class for demo files:</p>
		
		<select id="Class" name="Class" class="form-control">
			<option value="">Select Class</option>
			<?php educare_get_options('Class', $Class);?>
		</select>

		<div id="result_msg"><br><p><a class='educare_button disabled' title='Download Import Demo.csv Error'><i class='dashicons dashicons-download'></i> Download Demo</a></p></div>

		<script>
		$(document).on("change", "#Class", function() {
			$(this).attr('disabled', true);
			var class_name = $('#Class').val();
			// var id_no = $('#id_no').val();
			$.ajax({
					url: "<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
					data: {
					action: 'educare_demo',
					class: class_name,
				},
					type: 'POST',
					success: function(data) {
						$('#result_msg').html(data);
						$('#Class').attr('disabled', false);
					},
					error: function(data) {
						$('#result_msg').html("<?php echo educare_guide_for('db_error')?>");
					},
			});
		});
		</script>
	</div>
</div>

