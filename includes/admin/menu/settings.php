<?php

/*=====================( END SETTINGS FUNCTIONS )=======================
========================================================================
								NOW PRINT OUT OUR DATA IN SETTINGS PAGE
========================================================================
========================( BEGIN SETTINGS PAGE )========================*/

?>


<!-- Tab Head -->
<div class="container">
	<div class="tab">
	  <button class="tablinks" onclick="openTabs(event, 'add_content')" id="default"><i class="dashicons dashicons-plus-alt"></i><span>Add Content</span></button>
	  <button class="tablinks" onclick="openTabs(event, 'settings')"><i class="dashicons dashicons-admin-generic"></i><span>Settings</span></button>
	</div>
		
	<div class="educare_post educare_settings">
		
		<!-- Tab add_content -->
		<div id="add_content" class="tab_content">
			
			<div class="cover">
				<img src="<?php echo esc_url(EDUCARE_URL.'assets/img/cover.svg'); ?>" alt="educare cover"/>
			</div>
				
			<!-- <h1>Add Content</h1> -->
			
			<?php 
			echo '<div id="msg_for_class">';
				educare_setting_subject("subject");
			echo '</div>';

			educare_setting_subject("subject", true);

			?>
			<script>
				$(document).on("click", "[name=add_class], [name=edit_class], [name=update_class], [name=remove_class], [name=add_subject], [name=edit_subject], [name=update_subject], [name=remove_subject]", function(event) {
					event.preventDefault();
					var current = $(this);
					var form_data = $(this).parents('form').serialize();
					// alert(form_data);
					var action_for = $(this).attr("name");
					// alert(action_for);

					$.ajax({
						url: "<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
						data: {
							action: 'educare_process_content',
							form_data: form_data,
							action_for
						},
						type: 'POST',
						beforeSend:function(event) {
							if (action_for == 'remove_class' || action_for == 'remove_subject') {
								if (action_for == 'remove_class') {
									var target = $(current).prevAll("[name='class']").val();
								} else {
									var target = $(current).prevAll("[name='subject']").val();
								}
								
								<?php 
								if (educare_check_status('confirmation') == 'checked') {
									echo 'return confirm("Are you sure to remove (" + target + ") from this list?")';
								}
								?>
							}
						},
						success: function(data) {
							$('#msg_for_class').html(data);
						},
						error: function(data) {
							$('#msg_for_class').html("<?php echo educare_guide_for('db_error')?>");
						},
						complete: function() {
							// event.remove();
						},
					});
					
				});
			</script>

			<?php educare_get_content();?>
					
		</div> <!-- End Tab add_content -->
		
		<!-- Tab settings -->
		<div id="settings" class="tab_content">

			<?php
				if ( isset( $_POST['educare_default_photos'] ) && isset( $_POST['educare_attachment_id'] ) ) :
					update_option( 'educare_files_selector', absint( sanitize_text_field($_POST['educare_attachment_id'] )) );
				endif;
				
				wp_enqueue_media();
				$educare_save_attachment = get_option( 'educare_files_selector', 0 );
			
				$default_photos = wp_get_attachment_url( get_option( 'educare_files_selector' ) );
				if ($default_photos == null) {
					$visibility = 'none';
					$img = EDUCARE_URL.'assets/img/default.svg';
					$img_type = "<h3 id='educare_img_type' class='title'>Default Photos</h3>";
					$guide = "<p id='educare_guide'>Current students photos are default. Please upload or select  a custom photos from gallery that's you want!</p>";
				} else {
					$visibility = 'block';
					$img = wp_get_attachment_url( get_option( 'educare_files_selector' ) );
					$img_type = "<h3 id='educare_img_type' class='title'>Custom Photos</h3>";
					$guide = "<p id='educare_guide'></p>";
				}
			?>
						
			<form method='post'>
				<div id='educare_files_selector_disabled'>
					<div id='educare_files_uploader' class='educare_upload'>
						<div class='educare_files_selector'>
							<img id='educare_attachment_preview' class='educare_student_photos' src='<?php echo esc_url($img);?>'>
							<?php echo wp_kses_post($img_type);?>
						</div>
						
						<?php echo wp_kses_post($guide);?>
						<div id="photos_help"></div>
							<div class="select">
								<input type="hidden" name='educare_attachment_id' id='educare_attachment_id' value='<?php echo esc_attr(get_option( 'educare_files_selector' )); ?>'>
									
								<input type='hidden' name='educare_attachment_url' id='educare_attachment_url' value='<?php echo esc_url(get_option( 'educare_files_selector' )); ?>'>
								
								<input id="educare_upload_button" type="button" class="button" value="<?php _e( 'Upload Students Photos' ); ?>"/>
								
								<input type='button' id='educare_attachment_title' class="button" value='Pleace Select a students photos' disabled>
								
								<a id='educare_attachment_clean' class='dashicons dashicons-no educare_clean' style='width: auto; display: <?php echo esc_attr($visibility);?>' href='<?php echo esc_js('javascript:;');?>'></a>
							</div>
							<button id='educare_default_photos' type="submit" name="educare_default_photos" class="educare_button full"><i class="dashicons dashicons-yes-alt"></i> Save</button>
					</div>
				</div>
			</form>
			
			<h1>Settings</h1>
			
			<div id="msg_for_settings"><?php educare_settings_form();?></div>
		</div>

	</div> <!-- / .educare Settings -->
</div> <!-- / .Container -->


<script type='text/javascript'>
	jQuery( document ).ready( function( $ ) {
		// Uploading files
		var file_frame;
		var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
		var educare_media_post_id = <?php echo esc_attr($educare_save_attachment); ?>; // Set this
		jQuery('#educare_upload_button').on('click', function( event ){
				event.preventDefault();
				// If the media frame already exists, reopen it.
				if ( file_frame ) {
						// Set the post ID to what we want
						file_frame.uploader.uploader.param( 'post_id', educare_media_post_id );
						// Open frame
						file_frame.open();
						return;
				} else {
						// Set the wp.media post id so the uploader grabs the ID we want when initialised
						wp.media.model.settings.post.id = educare_media_post_id;
				}
				// Create the media frame.
				file_frame = wp.media.frames.file_frame = wp.media({
						title: 'Select Students Photos',
						button: {
								text: 'Use this image',
						},
						multiple: false // Set to true to allow multiple files to be selected
				});
				// When an image is selected, run a callback.
				file_frame.on( 'select', function() {
						// We set multiple to false so only get one image from the uploader
						attachment = file_frame.state().get('selection').first().toJSON();
						// Do something with attachment.id and/or attachment.url here
						// $( '#educare_attachment_preview' ).attr( 'src', attachment.url ).css( 'width', '100px' );
						$( '#educare_attachment_preview' ).attr( 'src', attachment.url );
						$( '#educare_upload_button' ).val( 'Edit Photos' );
						$( '#educare_attachment_clean' ).css( 'display', 'block' );
						$("#educare_img_type").html('Custom photos');
						$( '#educare_attachment_id' ).val( attachment.id );
						$( '#educare_attachment_url' ).val( attachment.url );
						$( '#educare_attachment_title' ).val( attachment.title ).attr( 'value', this(val) );
						// Restore the main post ID
						wp.media.model.settings.post.id = wp_media_post_id;
				});
						// Finally, open the modal
						file_frame.open();
		});
		// Restore the main ID when the add media button is pressed
		jQuery( 'a.add_media' ).on( 'click', function() {
				wp.media.model.settings.post.id = wp_media_post_id;
		});
		// clean files
		$("a.educare_clean").on("click", function() { 
				$("#educare_attachment_url").val("");
				$("#educare_attachment_id").val("");
				$( '#educare_attachment_preview' ).attr( 'src', "<?php echo esc_url(EDUCARE_URL.'assets/img/default.svg');?>" );
				$("a.educare_clean").css('display', 'none');
				$( '#educare_attachment_title' ).val('Cleaned! please select onother one');
				$( '#educare_upload_button' ).val( 'Upload photos again' );
				$("#educare_img_type").html('Default photos');
				$("#educare_guide").html("Current students photos are default. Please upload or select  a custom photos from gallery that's you want!");
		});
	});
    
    
  <?php
	if (educare_check_status('photos') == 'unchecked') {
		$photos = 'disabled';
	} else {
		$photos = '';
	}
	?>
	
	var photos = '<?php echo educare_esc_str($photos);?>';
	if (photos == 'disabled') {
		document.getElementById('educare_upload_button').setAttribute('disabled', 'disabled');
		document.getElementById('educare_default_photos').setAttribute('disabled', 'disabled');
		document.getElementById('educare_attachment_clean').style.display= 'none';
		document.getElementById('educare_files_selector_disabled').className = 'educare_files_selector_disabled';
		document.getElementById('photos_help').innerHTML = 'Currently students photos are disabled. If you upload or display student photos, first check/anable students photos under the settings sections';
	}
</script>


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
		$.ajax({
			url: "<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
			data: {
				action: 'educare_process_content',
				form_data: form_data,
				action_for
			},
			type: 'POST',
			beforeSend:function(event) {
				$(current).css('color', 'red');
				if (action_for == 'educare_reset_default_settings') {
					<?php 
					if (educare_check_status('confirmation') == 'checked') {
						echo 'return confirm("Are you sure to reset default settings? This will not effect your content (Class, Subject, Exam, Year, Extra Field), Its only reset your current settings status and value.")';
					}
					?>
				}
			},
			success: function(data) {
				$('#msg_for_settings').html(data);
			},
			error: function(data) {
				$('#msg_for_settings').html("<?php echo educare_guide_for('db_error')?>");
			},
			complete: function() {
				$(current).css('color', 'white');
				// event.remove();
			},
		});
		
	});
</script>
	
	