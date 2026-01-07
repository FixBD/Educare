<?php
/**
 * Generates and displays an HTML form for adding, editing, or viewing data based on user roles.
 *
 * This function generates and displays an HTML form for adding, editing, or viewing data based on user roles.
 * It takes an optional parameter `$roles` to specify the user's role (e.g., 'results', 'students', 'teachers').
 * If not provided, the default role is 'results'. The function constructs and outputs the HTML form elements
 * including input fields, dropdowns, buttons, and other necessary components for the form.
 *
 * @param string $roles The user's role (e.g., 'results', 'students', 'teachers').
 * @return void Outputs the HTML form elements for adding, editing, or view data.
 */
function educare_get_forms($roles = 'results') {
	if (isset($_POST['roles'])) {
		$roles = sanitize_text_field( $_POST['roles'] );
	}

	if (isset($_POST['edit'])) {
		educare_verify_nonce();
	}
	
	// Show banner
	educare_show_school_banner();
	?>
	
	<form id="crud-forms" class="add_results" method="post" action="<?php echo esc_url($_SERVER["PHP_SELF"]);?>">
		<div class="content">
			<?php
			// Show page title
			echo educare_get_page_title($roles, 'add');

			$id = '';
			// Security nonce for this form.
			$nonce = wp_create_nonce( 'educare_crud_data' );
			echo '<input type="hidden" name="crud_data_nonce" value="'.esc_attr($nonce).'">';
			$nonce = wp_create_nonce( 'educare_form_nonce' );
			echo '<input type="hidden" name="nonce" value="'.esc_attr($nonce).'">';
			echo '<input type="hidden" name="roles" value="'.esc_attr($roles).'">';

			if (isset($_POST['default'])) {
				if (key_exists('id', $_POST['default'])) {
					$id = sanitize_text_field( $_POST['default']['id'] );
					echo '<input type="hidden" name="default[id]" value="'.esc_attr( $id ).'">';
				}
			}

			if (isset($_POST['Others']['Photos'])) {
				$img_id = $_POST['Others']['Photos'];
			} else {
				$img_id = '';
			}

			echo '<div class="container users-attachment my-5">';
				echo '<div class="row justify-content-center">';
					$student_photos = educare_check_status('photos');

					if ($student_photos == 'checked') {
						echo '<div class="col">';
							echo '<div class="text-center">';
								$attachment_url = educare_get_attachment($img_id, true);
								$img_url = educare_get_attachment($img_id);
								echo '<div class="getAttachment users-attachment">';
									echo '<div class="caption">'.__('Photos', 'educare').'</div>';

									echo '<div class="attachmentPreview attachmentInput bg-white">';
										echo '<div class="attachmentImg">';
											
											if ($img_url) {
												echo '<img class="educare-attachment attachment-id-'.esc_attr($img_id).'" src="'.esc_url($img_url).'">';
											}
											
										echo '</div>';
									echo '</div>';

									if ($attachment_url) {
										echo '<span class="btn btn-danger d-inline-flex align-items-center gap-1 attachmentControl attachmentRemove">'.sprintf(__('%s Remove', 'educare'), '<span class="dashicons dashicons-remove"></span>').'</span>';
									} else {
										echo '<span class="btn btn-success d-inline-flex align-items-center gap-1 attachmentControl attachmentInput">'.sprintf(__('%s Upload Now', 'educare'), '<span class="dashicons dashicons-cloud-upload"></span>').'</span>';
									}

									echo '<input type="hidden" name="Others[Photos]" value="'.esc_attr($img_id).'">';
								echo '</div>';
							echo '</div>';
						echo '</div>';
					} else {
						echo '<input type="hidden" name="Others[Photos]" value=""></input>';
					}

				echo '</div>';
			echo '</div>';

			$show_roles = $roles;

			if ($roles == 'results') {
				$show_roles = 'students';
			}
			if ($show_roles == 'teachers') {
				$show_roles = 'Staff';
			}

			// Details section begin
			echo '<h6 class="my-4">'.sprintf(__('%s Details', 'educare'), esc_html__(ucfirst($show_roles), 'educare')).'</h6>';
			
			$required = educare_check_status('display');
			$required_title = educare_requred_data($required, true);
			$required_fields = $required_title;
			$required_title['auto_fill'] = true;
			$required_title = educare_roles_wise_filed(array('roles' => $roles, 'fields' => $required_title));

			unset($required_title['School'], $required_title['Term'], $required_title['user_pin'], $required_title['user_pass'], $required_title['user_name'], $required_title['user_email']);

			foreach ($required_title as $key => $value) {
				if($key == 'Name' || $key == 'Roll_No' || $key == 'Regi_No') {
					$field_value = '';

					if (isset($_POST['default'])) {
						if (key_exists($key, $_POST['default'])) {
							$field_value  = sanitize_text_field( $_POST['default'][$key] );
						}
					}

					if ($key == 'Name') {
						echo '<div class="row">
							<div class="col">
								<div class="row mb-3">
									<label for="default_'.esc_attr($key).'" class="col-md-2 col-form-label d-flex align-items-center">'.esc_html__($value, 'educare').'</label>

									<div class="col-md-10">
										<input type="text" name="default['.esc_attr($key).']" value="'.esc_attr($field_value).'" placeholder="'.esc_attr__($key, 'educare').'" class="form-control" id="default_'.esc_attr($key).'">
									</div>
								</div>
							</div>';

						echo '</div>';
					} else {
						echo '<div class="mb-3 row">
							<label for="default_'.esc_attr($key).'" class="col-md-2 col-form-label d-flex align-items-center">'.esc_html__($value, 'educare').'</label>
							<div class="col-md-10">
								<input type="text" name="default['.esc_attr($key).']" value="'.esc_attr($field_value).'" placeholder="'.sprintf(__('Enter %s', 'educare'), esc_attr__($value, 'educare')).'" class="form-control" id="default_'.esc_attr($key).'">
							</div>
						</div>';
					}
					
				} else {
					if ($key == 'auto_fill') {
						// We need to remove name="auto_fill" when direcly request. otherwise it's auto fill bassed on old results. that massed query! (this is for AJAX only)
						echo '<div class="mb-3 row">
							<div class="col-md-2"></div>
							<div class="col-md-10">
								<div name="auto_fill" class="btn btn-outline-success crud-forms" style="margin: 0px;" title="'.__('Retrieve student data using Roll Number, Registration Number, Class, and Year. The form will automatically populate with details of previously added students from the student list.', 'educare').'">'.__('Auto Fill', 'educare').'</div>
							</div>
						</div>';
					} else {
						echo '<div class="mb-3 row">';
							echo '<label for="'.esc_attr($key).'" class="col-md-2 col-form-label d-flex align-items-center">'.esc_html__($value, 'educare').'</label>';

							echo '<div class="col-md-10">
							<select id="'.esc_attr($key).'" name="default['.esc_attr($key).']">';
							educare_get_option($key);
							echo '</select>
							</div>';
						echo '</div>';
					}
				}
			}

			if ($roles != 'results') {
				echo educare_get_advance_banner('The premium version of Educare supports student and staff profiles with a dedicated dashboard. Teachers or staff members can log into their profiles and manage tasks such as adding marks and attendance records.');
			}

			echo '<div class="educare_data_field">
				<div class="educareTemplateForm_id" data-value="'.esc_attr($id).'"></div>
			</div>';
			
			// Extra fields
			echo '<h6 class="my-4">'.__('Others Info', 'educare').'</h6>';
			echo educare_guide_for('add_extra_field');
			echo '<div class="row">';
				echo '<div class="col">';
				educare_get_extra_field($roles);
				echo '</div>';

			echo '</div>';
			
			if ($roles == 'students' or $roles == 'results') {
				echo '<h6 class="my-4">'.__('Subject List', 'educare').'</h6>';

				echo '<div id="result_msg">';
					educare_get_subject_field($roles);
				echo '</div>';
			} else {
				echo '<p>'.__('Select Subject', 'educare').':</p>';
				echo educare_guide_for(__('Select specific subjects to grant staffer/teachers access for adding marks or recording attendance. Simply select (All) to grant access to all subjects. You can also select multiple subjects by pressing <code>Ctrl + Subject</code>', 'educare'));
				
				// keep all selected
				$all_selected = '';
				if (isset($_POST['Subject'][0]['all'])) {
					if (in_array('', $_POST['Subject'][0]['all'])) {
						$all_selected = 'all';
					}
				}

				echo '<select name="Subject[0][all][]" required multiple>';
				echo '<option value="" '.esc_attr( selected( $all_selected, 'all', false ) ).'>'.__('All', 'educare').'</option>';
				echo educare_get_all_subject(true);
				echo '</select>';
			}

			$Status = isset($_POST['default']['Status']) ? sanitize_text_field( $_POST['default']['Status'] ) : '';
			?>

			<div class="d-flex gap-3 mt-3">
				<?php
				if ($roles !== 'teachers' && key_exists('Group', $required_fields)) {
					// Show All Group
					?>
					<div class="w-100">
						<label for="Group" class="form-label"><?php _e($required_fields['Group'], 'educare')?></label>
						<select id="Group" name="default[Group]">
							<option value=""><?php _e('None (Default)', 'educare')?></option>
							<?php educare_get_option("Group");?>
						</select>
					</div>
					<?php
				}
				?>

				<div class="w-100">
					<label for="publish" class="form-label"><?php _e('Visibility Status', 'educare')?></label>

					<select id="publish" name="default[Status]">
						<?php
						if ($roles === 'results') {
							if (current_user_can( 'administrator' ) || educare_check_status('publish_results_by_admin') == 'checked') {
								?>
								<option value="publish" <?php selected( 'publish', esc_attr($Status) ) ;?>><?php _e('Publish', 'educare')?></option>

								<option value="pending" <?php esc_attr(selected( 'pending', $Status )) ;?>><?php _e(__('Pending', 'educare')); ?></option>

								<option value="scheduled" <?php selected( 'scheduled', esc_attr($Status) ) ;?>><?php _e('Scheduled', 'educare')?></option>

								<option value="rejected" <?php esc_attr(selected( 'rejected', $Status )) ;?>><?php _e(__('Rejected', 'educare')); ?></option>
								<?php
							} else {
								?>
								<option value="pending" <?php esc_attr(selected( 'pending', $Status )) ;?>><?php _e(__('Pending', 'educare')); ?></option>
								<?php
							}
						} else {
							if (current_user_can( 'administrator' ) || educare_check_status('publish_user_by_admin') == 'checked') {
								?>
								<option value="publish" <?php selected( 'publish', esc_attr($Status) ) ;?>><?php _e('Publish', 'educare')?></option>

								<option value="pending" <?php esc_attr(selected( 'pending', $Status )) ;?>><?php _e(__('Pending', 'educare')); ?></option>

								<option value="scheduled" <?php selected( 'scheduled', esc_attr($Status) ) ;?>><?php _e('Scheduled', 'educare')?></option>

								<option value="rejected" <?php esc_attr(selected( 'rejected', $Status )) ;?>><?php _e(__('Rejected', 'educare')); ?></option>
								<?php
							} else {
								?>
								<option value="pending" <?php esc_attr(selected( 'pending', $Status )) ;?>><?php _e(__('Pending', 'educare')); ?></option>
								<?php
							}
						}
						?>
					</select>
				</div>
				
			</div>
			<?php
			
			$submit = 'Add';
			if (isset($_POST['default']['id'])) {
				$submit = 'Update';
			}

			echo '<div class="d-flex gap-2 mt-3">';
				if ($submit == 'Update') {
					echo '<button type="submit" name="crud" class="btn btn-success d-flex gap-1 align-items-center justify-content-center w-100 crud-forms"><i class="dashicons dashicons-update"></i> '.esc_html__($submit, 'educare').'</button>';
				} else {
					echo '<button type="submit" name="crud" class="btn btn-success d-flex gap-1 align-items-center justify-content-center w-100 crud-forms"><i class="dashicons dashicons-plus-alt"></i> '.esc_html__($submit, 'educare').'</button>';
				}

				if (isset($_POST['default']['id'])) {
					if ($roles == 'results') {
						$preview_button = 'View';
						$url = '/'.educare_check_status("results_page");
					} else {
						$preview_button = 'Preview Profiles';
						$url = '/profiles';
					}

					echo '<input type="hidden" name="id" value="'.esc_attr( $id ).'">';
					echo '<button type="submit" name="view" formaction="'.esc_url($url).'" formtarget="_blank" class="btn btn-success d-flex gap-1 align-items-center justify-content-center w-100"><i class="dashicons dashicons-visibility"></i> '.__($preview_button, 'educare').'</button>';
					echo '<button type="submit" name="delete" class="btn btn-danger d-flex gap-1 align-items-center justify-content-center w-100 crud-forms"><i class="dashicons dashicons-trash"></i> '.__('Delete', 'educare').'</button>';
				}

			echo '</div>';
			?>
			
		</div>
	</form>
	
	<?php
}

?>