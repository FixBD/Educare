<?php
/**
 * Display data (Students, Teachers, Results, Marks)
 * Usage: educare_display_data('students', '10');
 * 
 * @since 1.4.2
 * @last-update 1.4.9
 * 
 * @param string $roles			for specific users or data (Students, Teachers, Results, Marks...)
 * @param int $per_page 		Data per (one) page. Default is 15 (baseed on educare settings)
 * 
 * 
 * @return void|HTML
 */
function educare_display_data($roles = 'students', $per_page = null) {
	// verify nonce
	if (isset($_GET['filter'])) {
		educare_verify_nonce('educare_filter_nonce', 'nonce', true);
	}
	if (isset($_POST['delete'])) {
		educare_verify_nonce();
	}
	
  global $wpdb;
	// Define table name to access data
	$table = $wpdb->prefix.EDUCARE_PREFIX.$roles;
	
	// Define all empty fields to ignore php error
  $Year = $Class = $Group = $Exam = $Status = $change_status = $pin_status = $change_pin_status = $search = $order_by = $order = $print = '';

	// default data per page when page load
	if (!$per_page) {
		$per_page = educare_check_status('data_per_page');
	}
	// default page when page load
	$page_no = 1;
	// Center pagination
	$center = 2;
	
	if (isset($_GET['page-no']) && $_GET['page-no']!='' && $_GET['page-no']!='0') {
		$page_no = $_GET['page-no'];
	}
	if (isset($_GET['per-page']) && $_GET['per-page']!=''&& $_GET['per-page']!='0') {
		$per_page = $_GET['per-page'];
	}

	$url = admin_url().'admin.php?';

	if ($_GET) {
		$pageURL = $url;
		foreach ($_GET as $key => $value) {
			if (!$value) {
				continue;
			}
			$$key = $value;
			$pageURL .= $key.'='.$value.'&';
		}
	} else {
		// default
		$pageURL = admin_url().'admin.php?page=educare-all-students&';
	}

	// process same query when request $_POST method
	if ($_POST) {
		$pageURL = $url;
		foreach ($_POST as $key => $value) {
			if (!$value) {
				continue;
			}
			$$key = $value;
			$pageURL .= $key.'='.$value.'&';
		}
	}

	// echo $pageURL;

	$admin_page = isset($_REQUEST['page']) ? $_REQUEST['page'] : '';

	// ignore index page -1, and increase $per_page (by default 5) in each page
  $offset = ($page_no-1) * $per_page;
	// current page - 1
	$previous = $page_no - 1;
	// current page + 1
	$next = $page_no + 1;

	// Create SQL query for face data
	$offset_query = array();
	/*
	// Sample query structure

	$offset_query = array (
    'Class' => 'Class 6',
    'Exam' => 'Exam no 1',
    'Year' => 2022,
    'Status' => 'publish',
		'order_by' => 'id',
    'order' => 'DESC',
		'search' => 'StuDent',
		'offset' => 0,
		'per-page' => 5
	);
	*/

	// Dynamically create $offset_query based on $_GET method. You can use $_POST, whatever you like. But, this is an data filtaring/searching functionality. So, it's bettter to use $_GET method.
	foreach ($_GET as $key => $value) {
		if ($key == 'page' or $key == 'page-no' or $key == 'action' or $key == 'tab' or $key == 'action_for' or $key == 'change_status' or $key == 'change_pin_status' or !$value) {
			continue;
		}

		$offset_query[$key] = sanitize_text_field( $value );
	}

	// process same query when request $_POST method
	if ($_POST && !isset($_POST['id'])) {
		$offset_query = array();

		foreach ($_POST as $key => $value) {
			if ($key == 'page' or $key == 'page-no' or $key == 'action' or $key == 'tab' or $key == 'action_for' or $key == 'change_status' or $key == 'change_pin_status' or !$value) {
				continue;
			}

			$offset_query[$key] = sanitize_text_field( $value );
		}
	}

	// Define default order_by and order DESC
	if (!key_exists('order_by', $offset_query)) {
		$offset_query['order_by'] = 'id';
	}
	if (!key_exists('order', $offset_query)) {
		$offset_query['order'] = 'DESC';
	}

	$user_school = get_user_meta(get_current_user_id(), 'School', true);
	if ($user_school) {
		// unset user data if include
		unset($offset_query["school"]);
		// Then add real data
		$offset_query['School'] = $user_school;
	}

	if ($roles == 'results') {
		if ($print && !$Exam) {
			$offset_query['Exam'] = 'Exam no 1';
		}
	}

	$all_status_data = array(
		'publish' => 0,
		'scheduled' => 0,
		'pending' => 0,
		'rejected' => 0
	);

	$all_status = $offset_query;

	foreach ($all_status_data as $status_key => $status_value) {
		$all_status['Status'] = $status_key;
		$all_status_query = educare_dynamic_sql($all_status, $roles);

		if ($all_status_query) {
			$all_status_data[$status_key] = $wpdb->get_var("SELECT COUNT(*) FROM  $table $all_status_query");
		} else {
			$all_status_data[$status_key] = 0;
		}
	}

	// Count total data for pagination, So, we need to ignore offset
	$total_query = $offset_query;
	// remove per-page data.
	unset($total_query['per-page']);
	// Dynamically genarate SQL
	$total_query = educare_dynamic_sql($total_query, $roles);
	// Getting offset data (requred/main/called data)
	// for this we need to define $offset and $per_page to LIMIT query like this: "LIMIT $offset, $per_page" || "LIMIT 0, 5"
	$offset_query['offset'] = $offset;
	$offset_query['per-page'] = $per_page;
	$offset_query = educare_dynamic_sql($offset_query, $roles);

	// Process delete request
	$process = false;
	if ($total_query) {
		if (isset($_POST['update_status'])) {
			echo educare_get_advance_banner('The features you are requesting are supported in the Educare Premium version. It includes advanced functionalities like bulk update pin or visibility status.', false);
		}

		// delete multiple data
		if (isset($_POST['delete_all'])) {
			// delete data
			$process = $wpdb->query("DELETE FROM $table $total_query");
		}

		// delete single data
		if (isset($_POST['delete'])) {
			if (isset($_POST['id']) && !empty($_POST['id'])) {
				$id = sanitize_text_field( $_POST['id'] );

				$process = $wpdb->delete( 
					$table, 
					array(
						// replace with the ID of the row to delete
						'id' => $id,
					)
				);
			}
		}

		// Show messages
		if (isset($_POST['update_status']) or isset($_POST['delete']) or isset($_POST['delete_all'])) {
			// Check if the update was successful
			if ( $process === false ) {
				// handle error
				echo educare_show_msg(__('There was an error processing your request.', 'educare'), false);
			} elseif ( $process == 0 ) {
				// no rows were updated, handle accordingly
				if (isset($_POST['update_status']) or isset($_POST['update_pin_status'])) {
					echo educare_show_msg(__('No changes were found in this request.', 'educare'));
				} else {
					echo educare_show_msg(sprintf(
						__('No %s found to delete', 'educare'),
						esc_html__($roles, 'educare')
					));
				}
				
			} else {
				// update was successful, handle accordingly
				// Execute the DELETE query
				if (isset($_POST['update_status'])) {
					echo educare_show_msg(sprintf(
						__('Successfully updated %s status', 'educare'),
						esc_html__($roles, 'educare')
					));
				} elseif (isset($_POST['delete'])) {
					echo educare_show_msg(sprintf(
						__('Successfully deleted %s', 'educare'),
						esc_html__($roles, 'educare')
					));
				} else {
					echo educare_show_msg(sprintf(
						__('Successfully deleted all %s', 'educare'),
						esc_html__($roles, 'educare')
					));
				}
			}
		}

		// Display data
		// Proccess query to face data
		// Count total data
		$total_data = $wpdb->get_var("SELECT COUNT(*) FROM  $table $total_query");
		// Face||Getting main data
		$opset_data = $wpdb->get_results("SELECT * FROM $table $offset_query");
	} else {
		$total_data = $opset_data = 0;

		if (isset($_POST['update_status']) or isset($_POST['update_pin_status'])) {
			echo educare_show_msg(__('Data not found.', 'educare'), false);
		}
	}

	$total_pages = ceil($total_data / $per_page);
	$second_last = $total_pages - 1;

	// Check requred fields data
	$requred = educare_check_status('display');
	// Getting all requered field key and title
	$requred_title = educare_requred_data($requred, true, true);
	$requred_title_only = educare_requred_data($requred, true);
	$advance = array(
		'teachers'
	);

	if ($requred_title) {
		// Show banner
		educare_show_school_banner();
		// create nonce
		$educare_filter_nonce = wp_create_nonce( 'educare_filter_nonce' );
		?>

		<!-- Search||filter form -->
		<form method="get" id="filter_data" class="add_results overflow-visible">
			<div class="content">
				<?php
				// Show page title
				echo educare_get_page_title($roles, 'all');
				// Nonce field
				echo '<input type="hidden" name="nonce" value="'.esc_attr($educare_filter_nonce).'">';

				foreach ($_GET as $key => $value) {
					if ($key == 'print') {
						continue;
					}

					echo '<input type="hidden" name="'.esc_attr( $key ).'" value="'.esc_attr( $value ).'">';
				}

				?>

				<div class="content d-block d-sm-flex align-items-center text-center w-100">
					<div class="p-1 w-100">
						<select id='Year' class="rounded-pill" name="Year">
							<option value=""><?php _e(sprintf(__('Select %s', 'educare'), __(esc_html($requred_title['Year']), 'educare'))); ?></options>
							<?php educare_get_options('Year', $Year);?>
						</select>
					</div>

					<div class="p-1 w-100">
						<select id='Class' class="rounded-pill" name="Class">
							<option value=""><?php _e(sprintf(__('Select %s', 'educare'), __(esc_html($requred_title['Class']), 'educare'))); ?></options>
							<?php educare_get_options('Class', $Class);?>
						</select>
					</div>

					<?php
					if ($roles == 'results' && key_exists('Exam', $requred_title_only)) {
						?>
						<div class="p-1 w-100">
							<select id='Exam' class="rounded-pill" name="Exam">
								<option value=""><?php _e(sprintf(__('Select %s', 'educare'), __(esc_html($requred_title['Exam']), 'educare'))); ?></options>
								<?php educare_get_options('Exam', $Exam);?>
							</select>
						</div>
						<?php
					}
					?>
					
					<div class="p-1 w-100">
						<input type="text" class="rounded-pill text-center" name="search" value="<?php echo esc_attr($search);?>" placeholder="<?php _e(__('Search', 'educare')); ?>" title="Search specific data">
					</div>

					
					<div class="d-block d-sm-flex align-items-center justify-content-center text-center">
						<div class="p-1 w-100">
							<button id="filter" type="submit" name="filter" class="btn d-flex align-items-center justify-content-center d-block text-center rounded-pill w-100"><i class="dashicons dashicons-search" title="Search"></i></button>
						</div>

						<div class="btn-group d-block d-sm-flex align-items-center justify-content-center text-center">
							<button type="button" class="btn d-flex align-items-center justify-content-center mx-auto d-block text-center rounded-pill" data-bs-toggle="dropdown" aria-expanded="false">

								<?php
								if ($all_status_data['pending'] >= 1) {
									echo '<span class="position-absolute top-0 start-50 translate-middle p-1 border border-light rounded-circle data-tatus pending" title="pending"><span class="visually-hidden">Status</span></span>';
								}
								if ($all_status_data['rejected'] >= 1) {
									echo '<span class="position-absolute bottom-50 start-50 translate-middle p-1 border border-light rounded-circle data-tatus rejected" title="rejected"><span class="visually-hidden">Status</span></span>';
								}
								if ($all_status_data['scheduled'] >= 1) {
									echo '<span class="position-absolute bottom-100 start-50 translate-middle p-1 border border-light rounded-circle data-tatus scheduled" title="scheduled"><span class="visually-hidden">Status</span></span>';
								}
								?>

								<i class="dashicons dashicons-menu" title="<?php echo __('More options', 'educare');?>"></i>
							</button>
							<ul class="dropdown-menu p-0 overflow-hidden" style="width: 200px;">
								<li class="m-0 border-bottom"><span class="dropdown-item d-flex align-items-center py-2 px-2" data-bs-toggle="modal" data-bs-target="#staticBackdrop"><i class="dashicons dashicons-ellipsis me-2"></i><?php echo __('More options', 'educare');?></span></li>

								<li class="m-0 border-bottom"><span class="dropdown-item d-flex align-items-center py-2 px-2" data-bs-toggle="modal" data-bs-target="#jumpPage"><i class="dashicons dashicons-admin-page me-2"></i><?php echo __('Jump page', 'educare');?></span></li>

								<li class="m-0 border-bottom <?php echo esc_attr(educare_advance_fields());?>"><span class="dropdown-item d-flex align-items-center py-2 px-2" data-bs-toggle="modal" data-bs-target="#printMenuBox"><i class="dashicons dashicons-printer me-2"></i><?php echo __('Bulk Print', 'educare').'<span class="px-1"></span>'.educare_advance_fields_badge();?></span></li>

								<li class="m-0 border-bottom"><span class="dropdown-item d-flex align-items-center py-2 px-2" data-bs-toggle="modal" data-bs-target="#statusBackdrop"><i class="dashicons dashicons-info me-2"></i><?php echo __('Status', 'educare');?></span></li>

								<li class="m-0 remove-item"><button id="delete_all" type="submit" name="delete_all" class="dropdown-item d-flex align-items-center py-2 px-2"><i class="dashicons dashicons-trash me-2"></i><?php _e('Delete', 'educare');?></button></li>
							</ul>
						</div>
					</div>
				</div>

				<!-- More options dialouge box -->
				<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
					<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
						<div class="modal-content bg-white">
							<div class="modal-header">
								<h5 class="modal-title d-flex align-items-center gap-2" id="staticBackdropLabel"><i class="dashicons dashicons-filter"></i><?php echo __('Advance Filtering', 'educare');?></h5>
								<button type="button" class="btn-close me-1" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							<div class="modal-body">
								<div class="select mb-3">
									<div>
										<label for="Groups" class="form-label"><?php _e(sprintf(__('Select %s', 'educare'), __(esc_html($requred_title['Group']), 'educare'))); ?></label>
										<select id='Groups' name="Group">
											<option value=""><?php _e(__('All', 'educare')); ?></options>
											<?php educare_get_options('Group', $Group);?>
										</select>
									</div>
								</div>

								<div class="select mb-3">
									<div>
										<label for="order_by" class="form-label"><?php _e(__('Order By', 'educare')); ?></label>
										<select id='order_by' name="order_by">
											<option value='id' <?php esc_attr(selected( 'id', $order_by )) ;?>><?php _e(__('Time', 'educare')); ?></option>
											<option value='Name' <?php esc_attr(selected( 'Name', $order_by )) ;?>><?php _e(__(esc_html($requred_title['Name']), 'educare')); ?></option>
											<option value='Roll_No' <?php esc_attr(selected( 'Roll_No', $order_by )) ;?>><?php _e(__(esc_html($requred_title['Roll_No']), 'educare')); ?></option>
											<option value='Regi_No' <?php esc_attr(selected( 'Regi_No', $order_by )) ;?>><?php _e(__(esc_html($requred_title['Regi_No']), 'educare')); ?></option>
										</select>
									</div>
									
									<div>
										<label for="select_order" class="form-label"><?php _e('Sort By', 'educare');?></label>
										<select id='select_order' name="order">
											<option value='DESC' <?php esc_attr(selected( 'DESC', $order )) ;?>><?php _e('Desc', 'educare'); ?></option>
											<option value='ASC' <?php esc_attr(selected( 'ASC', $order )) ;?>><?php _e('Asc', 'educare'); ?></option>
										</select>
									</div>
								</div>

								<div class="select mb-3">
									<div>
										<label for="pin_status" class="form-label"><?php _e(__(esc_html($requred_title['user_pin']), 'educare')); ?></label>
										<select id="pin_status" name="pin_status">
											<option value=""><?php _e(__('All', 'educare')); ?></options>
											<option value="valid" <?php esc_attr(selected( 'valid', $pin_status )) ;?>><?php _e(__('Valid', 'educare')); ?></option>
											<option value="expire" <?php esc_attr(selected( 'expire', $pin_status )) ;?>><?php _e(__('Expire', 'educare')); ?></option>
										</select>
									</div>

									<div>
										<label for="Status" class="form-label"><?php _e(__('Visibility', 'educare')); ?></label>
										<select id="Status" name="Status">
											<option value=""><?php _e(__('All', 'educare')); ?></options>
											<option value="publish" <?php esc_attr(selected( 'publish', $Status )) ;?>><?php _e(__('Publish', 'educare')); ?></option>
											<option value="pending" <?php esc_attr(selected( 'pending', $Status )) ;?>><?php _e(__('Pending', 'educare')); ?></option>
											<option value="scheduled" <?php esc_attr(selected( 'scheduled', $Status )) ;?>><?php _e(__('Scheduled', 'educare')); ?></option>
											<option value="rejected" <?php esc_attr(selected( 'rejected', $Status )) ;?>><?php _e(__('Rejected', 'educare')); ?></option>
										</select>
									</div>
								</div>


								<div class="p-4 mt-3 rounded bg-light <?php echo esc_attr(educare_advance_fields());?>">
									<?php
									if (current_user_can( 'administrator' ) || educare_check_status('publish_user_by_admin') == 'checked') {
										echo '<h6 class="mb-3">One Click Update '.educare_advance_fields_badge().'</h6>';
									}
									?>

									<div class="row">
										<?php
										if (current_user_can( 'administrator' ) || current_user_can( 'educare_admin' )) {
											?>
											<div class="col-sm-6 mb-2">
											<label for="change_user_pin" class="form-label"><?php _e(sprintf(__('Change User Pin', 'educare'), 'educare')); ?></label>
												<select id="change_user_pin" name="change_pin_status">
													<option value=""><?php _e(__('No Change', 'educare')); ?></options>
													<option value="valid" <?php esc_attr(selected( 'valid', $change_pin_status )) ;?>><?php _e(__('Valid', 'educare')); ?></option>
													<option value="expire" <?php esc_attr(selected( 'expire', $change_pin_status )) ;?>><?php _e(__('Expire', 'educare')); ?></option>
												</select>
											</div>
											<?php
										}

										if ($roles === 'results') {
											if (current_user_can( 'administrator' ) || educare_check_status('publish_results_by_admin') == 'checked') {
												?>
												<div class="col-sm-6 mb-2">
													<label for="change_visibility" class="form-label"><?php _e(__('Change Visibility', 'educare')); ?></label>
													
													<select id="change_visibility" name="change_status">
														<option value=""><?php _e(__('No Change', 'educare')); ?></options>
														<option value="publish" <?php esc_attr(selected( 'publish', $change_status )) ;?>><?php _e(__('Publish', 'educare')); ?></option>
														<option value="pending" <?php esc_attr(selected( 'pending', $Status )) ;?>><?php _e(__('Pending', 'educare')); ?></option>
														<option value="scheduled" <?php esc_attr(selected( 'scheduled', $change_status )) ;?>><?php _e(__('Scheduled', 'educare')); ?></option>
														<option value="rejected" <?php esc_attr(selected( 'rejected', $Status )) ;?>><?php _e(__('Rejected', 'educare')); ?></option>
													</select>
												</div>
												<?php
											}
										} else {
											if (current_user_can( 'administrator' ) || educare_check_status('publish_user_by_admin') == 'checked') {
												?>
												<div class="col-sm-6 mb-2">
													<label for="change_visibility" class="form-label"><?php _e(__('Change Visibility', 'educare')); ?></label>
													
													<select id="change_visibility" name="change_status">
														<option value=""><?php _e(__('No Change', 'educare')); ?></options>
														<option value="publish" <?php esc_attr(selected( 'publish', $change_status )) ;?>><?php _e(__('Publish', 'educare')); ?></option>
														<option value="pending" <?php esc_attr(selected( 'pending', $Status )) ;?>><?php _e(__('Pending', 'educare')); ?></option>
														<option value="scheduled" <?php esc_attr(selected( 'scheduled', $change_status )) ;?>><?php _e(__('Scheduled', 'educare')); ?></option>
														<option value="rejected" <?php esc_attr(selected( 'rejected', $Status )) ;?>><?php _e(__('Rejected', 'educare')); ?></option>
													</select>
												</div>
												<?php
											}
										}
										?>
									</div>

									<?php
									if (current_user_can( 'administrator' ) || educare_check_status('publish_user_by_admin') == 'checked') {
										?>
										<button id="update_status" type="submit" name="update_status" class="btn btn-warning text-white mt-2"><?php _e('Update', 'educare');?></button>
										<?php
									}
									?>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo __('Ok', 'educare');?></button>
								
								<button type="submit" name="filter" class="btn btn-success"><?php echo __('Filter Data', 'educare');?></button>
							</div>
						</div>
					</div>
				</div>

				<!-- Status dialouge box -->
				<div class="modal fade" id="statusBackdrop" data-bs-backdrop="status" data-bs-keyboard="false" tabindex="-1" aria-labelledby="statusBackdropLabel" aria-hidden="true">
					<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
						<div class="modal-content bg-white">
							<div class="modal-header">
								<h5 class="modal-title d-flex align-items-center" id="statusBackdropLabel"><i class="dashicons dashicons-info text-info me-2"></i><?php echo __('Status', 'educare');?></h5>
								<button type="button" class="btn-close me-1" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							<div class="modal-body">
								<?php
								if ($all_status_data) {
									echo '<div class="row g-2">';

									foreach ($all_status_data as $status_key => $status_value) {
										echo '<div class="col-md-6 col-lg-3"><div class="card h-100 m-0 p-4 p-xl-5 fs-4 educare-data-status status-'.esc_attr($status_key).'">'.esc_html__(ucfirst($status_key). ':', 'educare').' '.esc_html($status_value).'</div></div>';
									}

									echo '</div>';
								}
								?>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo __('Ok', 'educare');?></button>
							</div>
						</div>
					</div>
				</div>

				<?php
				if ($search) {
					echo '<div class="text-center fw-bold">'.__('Search results for', 'educare').' : '.esc_html($search).'</div>';
				}
			
				if ($total_data) {
					echo '<p class="center">'.sprintf(__('Total %s data found', 'educare'), __(esc_html($total_data))).'</p>';
				}
				?>
			</div>
		</form>
		<?php

	} else {
		// database error
		echo educare_show_msg(__('Oops! It looks like the search and filter system isn’t showing right now due to an error. No worries! Just head over to the Educare settings, scroll down, and click the Reset Settings button to fix it.', 'educare'), false);
	}

	if (in_array($roles, $advance) && educare_get_unlock_banner()) {
		echo wp_kses_post(educare_get_unlock_banner());
		return;
	}

	// Print data
	if (isset($_GET['print']) && !empty($_GET['print'])) {
		if ($total_data) {
			echo educare_get_advance_banner('The features you are requesting are supported in the Educare Premium version. It includes advanced functionalities like bulk print student and staffer ID card, results data and more.', false);
			return;
		} else {
			echo educare_show_msg("No data found", false);
		}

	} else {
		// Display data

		if (!$total_data || $total_data > 3) {
			$overflow = 'overflow-hidden';
		} else {
			$overflow = 'overflow-auto';
		}
		
		echo '<div class="table-responsive">';
			echo '<table class="table align-middle table-bordered view_results '.esc_attr($overflow).' bg-white data-list '.esc_attr( $roles ).'">';
				echo '<thead>';
					echo '<tr>';
						echo '<th class="text-center serial-no">'.__('No.', 'educare').'</th>';

						$user_photos = educare_check_status('photos');
						$default_data = educare_check_status('display');
						// hide password fields
						if ($roles != 'students') unset($default_data->user_pin);

						$col = 0;

						if ($user_photos == 'checked' && $roles != 'marks') {
							$col++;
							echo '<th class="text-center data_list_img">'.__('Photos', 'educare').'</th>';
						}

						$ignore_data = educare_roles_wise_filed(array('roles' => $roles, 'get_ignore' => true));
						$ignore_thead = array();

						foreach ($ignore_data as $key => $value) {
							$ignore_thead[$value] = $value;
						}

						// Show group for student and results and hide for teachers or others roles
						if ($roles == 'students' or $roles == 'results') unset($ignore_thead['Group']);
						
						if ($default_data) {
							foreach ($default_data as $key => $value) {
								if (key_exists($key, $ignore_thead)) {
									continue;
								}

								$default_check = educare_check_status($key, true);
								if ($default_check) {
									$col++;
									echo '<th class="data_list_'.esc_attr(strtolower($key)).'">'.esc_html__($default_check, 'educare').'</th>';
								}
							}
						}

						echo '<th class="text-center data_list_action">'.__('Action', 'educare').'</th>';
					echo '</tr>';
				echo '</thead>';

				echo '<tbody>';
					if ($total_data) {
						$count = $offset + 1;

						$wp_page_url = $url;

						foreach($opset_data as $print) {
							$id = $print->id;
							
							if (isset($_POST['remove'])) {
								$wpdb->delete( $tablename, array( 'id' => $id ));
							} else {
								$School = isset($print->School) ? get_the_title($print->School) : '';
								$Details = $print->Details;
								$Details = json_decode($Details);
								$others = json_decode($print->Others);

								echo '<tr>';
									echo '<td data-bs-toggle="collapse" data-bs-target="#data-'.esc_attr($count).'" class="text-center accordion-toggle">'.esc_html($count).'</td>';
									
									if ($user_photos == 'checked' && $roles != 'marks') {
										$Photos = isset($others->Photos) ? $others->Photos : '';
										$Photos = educare_get_attachment($Photos);
										echo '<td data-bs-toggle="collapse" data-bs-target="#data-'.esc_attr($count).'" class="p-0 accordion-toggle data_list_img"><img src="'.esc_url($Photos).'" class="user-img" alt="IMG"/></td>';
									}
										
									$results_button = '';
									$results_title = sprintf(__('View %s', 'educare'), esc_html__($roles, 'educare'));
									$results_value = 'dashicons-visibility';

									if ($default_data) {
										foreach ($default_data as $key => $value) {
											if (key_exists($key, $ignore_thead)) {
												continue;
											}

											$default_check = educare_check_status($key, true);

											if ($default_check) {
												if (isset($print->$key) && $print->$key) {
													if ($key == 'School') {
														$display_value = $School;
													} else {
														$display_value = $print->$key;
													}

													echo '<td data-bs-toggle="collapse" data-bs-target="#data-'.esc_attr($count).'" class="accordion-toggle data_list_'.esc_attr(strtolower($key)).'">'.esc_html($display_value).'</td>';
													
												} else {
													if ($key == 'Group') {
														echo '<td data-bs-toggle="collapse" data-bs-target="#data-'.esc_attr($count).'" class="accordion-toggle data_list_'.esc_attr(strtolower($key)).'">'.__('N/A', 'educare').'</td>';
													} else {
														echo '<td class="error" data-bs-toggle="collapse" data-bs-target="#data-'.esc_attr($count).'" class="accordion-toggle data_list_'.esc_attr(strtolower($key)).'">'.__('N/A', 'educare').'</td>';
														$results_button = 'error';
														$results_value = 'dashicons-hidden';
														$results_title = sprintf(__('This %s is not visible for users. Because, some required field are empty. Fill all the required field carefully. Otherwise, users getting arror notice when someone find this %s. Click pen (Edit) button for fix this issue.', 'educare'), esc_html( $roles ), esc_html__( $roles, 'educare' ));
													}
												}
											}
										}
									}

									echo '<td>';

									$data_status = sanitize_text_field($print->Status);
									?>
									<div class="btn-group">
										<button type="button" class="btn rounded d-flex align-items-center justify-content-center" data-bs-toggle="dropdown" aria-expanded="false">
											<span class="position-absolute top-0 start-50 translate-middle p-1 border border-light rounded-circle data-tatus <?php echo esc_attr($data_status);?>" title="<?php echo esc_attr__($data_status, 'educare')?>">
												<span class="visually-hidden"><?php echo __('Status', 'educare');?></span>
											</span>
											<i class="dashicons dashicons-menu" title="<?php echo __('More options', 'educare');?>"></i>
										</button>

										<?php
											$link = $wp_page_url;
											$get_page = 'page';

											if (isset($_GET['page'])) {
												$page = sanitize_text_field( $_GET['page'] );
												$link .= esc_attr($get_page).'='.$page;
											} else {
												$link .= esc_attr($get_page).'=educare-all-'.$roles.'';
											}

											if ($roles == 'results') {
												$url = '/'.educare_check_status("results_page");
											} elseif ($roles == 'students' || $roles == 'teachers') {
												$url = '/'.educare_check_status("profiles_page");
												$url .= '?&profiles_id=' . $id.'&profiles_for=' . $roles;
											} else {
												$url = $link;
												$url .= '&profiles=' . $id;
											}

											echo '<form method="post">';
											echo '<ul class="dropdown-menu p-0 overflow-hidden" style="width: 200px;">';

												// Define data ID
												echo '<input type="hidden" name="id" value="'.esc_attr( $id ).'">';
												echo '<input type="hidden" name="roles" value="'.esc_attr( $roles ).'">';

												if ($roles == 'marks') {
													$students_list_nonce = wp_create_nonce( 'students_list' );
													echo '<input type="hidden" name="students_list_nonce" value="'.esc_attr($students_list_nonce).'">';
													echo '<input type="hidden" name="students_list" value="students_list">';

													$current_user_id = get_current_user_id();
													$educare_user_id = get_user_meta($current_user_id, 'user_id', true);
													$educare_user_data = educare_get_users_data($educare_user_id, 'teachers');

													// Check if users is a teachers
													if ($educare_user_data) {
														$educare_user_sub = json_decode($educare_user_data->Subject, true);

														if ($educare_user_sub) {
															if (key_exists('all', $educare_user_sub)) {
																// current($educare_user_sub['all']);
																echo '<input type="hidden" name="Subject" value="'.esc_attr(current($educare_user_sub['all'])).'">';
															}
														}
													}

													echo '<input type="hidden" name="Class" value="'.esc_attr($print->Class).'">';
													echo '<input type="hidden" name="Exam" value="'.esc_attr($print->Exam).'">';
													echo '<input type="hidden" name="Year" value="'.esc_attr($print->Year).'">';
													echo '<input type="hidden" name="Group" value="'.esc_attr($print->Group).'">';

													echo '<li class="m-0 border-bottom"><button type="submit" '. esc_attr($results_button) . ' name="view" value="view" title="' . esc_attr($results_title) . '" formaction="'.esc_url($link).'&add-data" formtarget="_blank" class="dropdown-item d-flex align-items-center py-2 px-2"><i class="dashicons '.esc_attr($results_value).' me-2"></i>'.__('Preview', 'educare').'</button></li>';

													echo '<li class="m-0 border-bottom"><button type="submit" '. esc_attr($results_button) . ' name="edit" value="edit" title="'.sprintf(__('Edit %s', 'educare'), esc_attr__( $roles, 'educare' )).'" formaction="'.esc_url($link).'&add-data" formtarget="_blank" class="dropdown-item d-flex align-items-center py-2 px-2"><i class="dashicons dashicons-edit me-2"></i>'.__('Edit', 'educare').'</button></li>';
												} else {
													$nonce = wp_create_nonce( 'educare_form_nonce' );
													echo '<input type="hidden" name="nonce" value="'.esc_attr($nonce).'">';

													if ($roles == 'results') {
														echo '<li class="m-0 border-bottom"><button type="submit" '. esc_attr($results_button) . ' name="view" value="view" title="' . esc_attr($results_title) . '" formaction="' . esc_url($url) . '" formtarget="_blank" class="dropdown-item d-flex align-items-center py-2 px-2"><i class="dashicons '.esc_attr($results_value).' me-2"></i>'.__('View Results', 'educare').'</button></li>';
													} else {
														echo '<li class="m-0 border-bottom '.esc_attr(educare_advance_fields()).'"><button type="submit" '. esc_attr($results_button) . ' name="view" value="view" title="' . esc_attr($results_title) . '" formaction="' . esc_url($url) . '" formtarget="_blank" class="dropdown-item d-flex align-items-center py-2 px-2"><i class="dashicons '.esc_attr($results_value).' me-2"></i>'.__('View Profile', 'educare').'<span class="px-1"></span>'.educare_advance_fields_badge().'</span></button></li>';
													}

													if ($roles == 'students') {
														echo '<li class="m-0 border-bottom '.esc_attr(educare_advance_fields()).'"><span class="dropdown-item d-flex align-items-center py-2 px-2" data-bs-toggle="modal" data-bs-target="#addResultsModal-'.esc_attr__( $id).'"><i class="dashicons dashicons-plus me-2"></i>'.__('Add Results', 'educare').'<span class="px-1"></span>'.educare_advance_fields_badge().'</span></li>';
													}

													echo '<li class="m-0 border-bottom"><button type="submit" '. esc_attr($results_button) . ' name="edit" value="edit" title="'.sprintf(__('Edit %s', 'educare'), esc_attr__( $roles, 'educare' )).'" formaction="'.esc_url($link).'&update-data" formtarget="" class="dropdown-item d-flex align-items-center py-2 px-2"><i class="dashicons dashicons-edit me-2"></i>'.__('Edit', 'educare').'</button></li>';
												}

												echo '<li class="m-0 remove-item"><button type="submit" '. esc_attr($results_button) . ' name="delete" value="delete" title="'.sprintf(__('Delete %s', 'educare'), esc_attr__( $roles, 'educare' )).'" class="dropdown-item d-flex align-items-center py-2 px-2"><i class="dashicons dashicons-trash me-2"></i>'.__('Delete', 'educare').'</button></li>';

											echo '</ul>';
											echo '</form>';
											
										if ($roles == 'students') {
											?>
											<div class="modal fade" id="addResultsModal-<?php echo esc_attr( $id);?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addResultsModal-<?php echo esc_attr( $id);?>Label" aria-hidden="true">
												<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
													<div class="modal-content bg-white">
														<div class="modal-header">
															<h5 class="modal-title d-flex align-items-center" id="addResultsModal-<?php echo esc_attr( $id);?>Label"><i class="dashicons dashicons-plus me-2"></i><?php echo __('Add Results', 'educare');?></h5>
															<button type="button" class="btn-close me-1" data-bs-dismiss="modal" aria-label="Close"></button>
														</div>

														<div class="modal-body">
															<div class="msgs"></div>

															<form method="post">
																<?php
																echo '<div class="text-center mb-3">'.esc_html($print->Name).'</div>';

																$nonce = wp_create_nonce( 'educare_crud_results' );
																echo '<input type="hidden" name="educare_crud_results_nonce" value="'.esc_attr($nonce).'">';

																$requred = educare_check_status('display');
																$requred_title = educare_requred_data($requred, true);
																$requred_title = educare_roles_wise_filed(array('roles' => 'students', 'fields' => $requred_title));

																// Default data
																if ($requred_title) {
																	foreach ($requred_title as $requred_field => $field_title) {

																		if ($requred_field == 'Exam') {
																			continue;
																		}

																		echo '<input type="hidden" name="default['.esc_attr($requred_field).']" value="'.esc_attr($print->$requred_field).'">';
																	}
																}

																echo '<div class="row">';
																	echo '<div class="col-md-6 mb-3">
																		<select name="default[Exam]">';
																		educare_get_option('Exam');
																		echo '</select>
																	</div>';

																	echo '<div class="col-md-6 mb-3">
																		<select name="default[Year]">';
																		educare_get_options('Year', $print->Year);
																		echo '</select>
																	</div>';
																echo '</div>';

																$subject = json_decode($print->Subject, true);
																$subject = array_keys($subject);

																if ($subject) {
																	// Show Mark sheet fields
																	educare_get_marks_fields('results', $subject);
																} else {
																	echo '<div class="alert alert-info" role="alert">'.__('No subject found for this students', 'educare').'</div>';
																}
																?>
															</form>
														</div>

														<div class="modal-footer">
															<button type="submit" name="crud" class="btn btn-success d-flex align-items-center crud-results"><i class="dashicons dashicons-plus me-2"></i><?php echo __('Add Results', 'educare');?></button>
														</div>
														
													</div>
												</div>
											</div>
											<!-- End Add results modal -->
											<?php
										}
										?>
									</div>
									<?php

									echo '</td>';
								echo '</tr>';

								echo '<tr id="data-'.esc_attr($count).'-content">
									<td colspan="'.esc_attr($col+2).'" class="m-0 p-0">
										<div id="data-'.esc_attr($count).'" class="collapse">
										<div class="p-2">';

										// Forms
										// Check requred fields data
										$requred = educare_check_status('display');
										// Getting all requered field key and title
										$requred_title = educare_requred_data($requred, true, false);

										if ($roles == 'results') {
											// echo esc_attr($count). ' Content';
										} elseif ($roles == 'marks') {
											$marks = json_decode($print->Marks);
											$total_students = count((array)$marks);
											echo '
											<div class="notice notice-success text-start">
												<p>';
												
													if (isset($requred_title['Class'])) {
														echo '<b>'.esc_html__($requred_title['Class'], 'educare').':</b> '.esc_html__($print->Class, 'educare').'<br>';
													}
													if (isset($requred_title['Group'])) {
														echo '<b>'.esc_html__($requred_title['Group'], 'educare').':</b> '.esc_html__($print->Group, 'educare').'<br>';
													}

													if (isset($requred_title['Exam'])) {
														echo '<b>'.esc_html__($requred_title['Exam'], 'educare').':</b> '.esc_html__($print->Exam, 'educare').'<br>';
													}
													
													echo '
													<b>'.esc_html__($requred_title['Year'], 'educare').':</b> '.esc_html__($print->Year, 'educare').'<br>
													<b>Total Students: '.esc_html($total_students).'</b>
													
												</p>
											</div>
											';
										} else {
											$photos = isset($others->Photos) ? sanitize_text_field($others->Photos) : '';
											$photos = educare_get_attachment($photos);

											echo '<div class="users-attachment m-auto my-4">
												<div class="row justify-content-center">';

													if ($photos) {
														echo '<div class="col">
															<div class="text-center">
																<div class="getAttachment users-attachment">
																	<div class="caption">'.__('Photos', 'educare').'</div>
																	<div class="attachmentPreview bg-white" data-bs-toggle="modal" data-bs-target="#attachmentPhotosPreview'.esc_attr($id).'">
																		<div class="attachmentImg">';

																			if ($photos) {
																				echo '<img class="educare-attachment attachment-id-0" src="'.esc_url($photos).'">';
																			}
																			
																		echo '</div>
																	</div>
																</div>
															</div>
														</div>';
													}

												echo '</div>
											</div>';

											?>
											<!-- Preview Photos dialouge box -->
											<div class="modal fade" id="attachmentPhotosPreview<?php echo esc_attr($id);?>" data-bs-backdrop="status" data-bs-keyboard="false" tabindex="-1" aria-labelledby="attachmentPhotosPreview<?php echo esc_attr($id);?>Label" aria-hidden="true">
												<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
													<div class="modal-content bg-white">
														<div class="modal-header">
															<h5 class="modal-title d-flex align-items-center" id="attachmentPhotosPreview<?php echo esc_attr($id);?>Label"><i class="dashicons dashicons-format-image me-2"></i><?php echo __('Preview Photos', 'educare');?></h5>
															<button type="button" class="btn-close me-1" data-bs-dismiss="modal" aria-label="Close"></button>
														</div>
														<div class="modal-body">
															<?php
															if ($photos) {
																echo '<img class="w-100" src="'.esc_url($photos).'">';
															} else {
																echo '<div class="alert alert-info" role="alert">'.__('Preview data not found.', 'educare').'</div>';
															}
															?>
														</div>
														<div class="modal-footer">
															<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo __('Ok', 'educare');?></button>
														</div>
													</div>
												</div>
											</div>
											<?php

											if ($Details) {
												echo '<div class="container mb-4">';
													echo '<div class="row">';
														echo '<div class="col">';
															echo '<div class="border m-1 p-3">';
																foreach ($Details as $key => $value) {
																	echo '<div class="row text-start border-bottom py-2">';
																	echo '<div class="col">'.esc_html__($key, 'educare').'</div>';
																	echo '<div class="col text-center" style="max-width: 4px">:</div>';
																	echo '<div class="col text-start">'.esc_html($value).'</div>';
																	echo '</div>';
																}
															echo '</div>';
														echo '</div>';
													echo '</div>';
												echo '</div>';
											}
										}
										
										echo '</div>
										</div>
									</td>
								</tr>';

								$count++;
							}
						}
					} else {
						echo "<tr><td colspan='".esc_attr($col+2)."'>".educare_show_msg(sprintf(__('%s not found', 'educare'), esc_html__( ucfirst($roles), 'educare' )), 'info')."</td></tr>";
					}

				echo "</tbody>";
			echo "</table>";
		echo "</div>";
	}

	// Pagination
	if ($total_data) {
		?>
    <div class='page_status center'>
      <small>
        <?php _e(sprintf(__('Page %d Of %d', 'educare'), esc_html($page_no), esc_html($total_pages))) ?>
      </small>
    </div>

    <ul class="pagination">
      <li <?php if ($page_no <= 1) { echo "class='disabled'"; } ?>>
      <a <?php echo "data-id='".esc_attr($previous)."'";?> <?php if($page_no > 1) { echo "href='". esc_url($pageURL) ."page-no=".esc_attr($previous)."'"; } ?>>&laquo;</a>
      </li>
          
      <?php
      // Display first and second page
      $first_page = "<li><a data-id='1' href='". esc_url($pageURL) ."page-no=1'>1</a></li>
      <li><a data-id='2' href='". esc_url($pageURL) ."page-no=2'>2</a></li>
      <li><span>...</span></li>";
      // Display last and second-last page
      $last_page = "<li><span>...</span></li>
      <li><a data-id='".esc_attr($second_last)."' href='". esc_url($pageURL) ."page-no=".esc_attr($second_last)."'>".esc_html($second_last)."</a></li>
      <li><a data-id='".esc_attr($total_pages)."' href='". esc_url($pageURL) ."page-no=".esc_attr($total_pages)."'>". esc_html($total_pages) ."</a></li>";
      
      function fixbd_get_page($counter, $page_no, $url = '?') {
        if ($counter == $page_no) {
          echo "<li><a class='current'>".esc_html($counter)."</a></li>";
        } else {
          echo "<li><a data-id='".esc_attr($counter)."' href='". esc_url($url) ."page-no=".esc_attr($counter)."'>".esc_html($counter)."</a></li>";
        }
      }
      
      if ($total_pages <= 10) {
        /**
        * Display all (10) page if total page qual and less then 10
        * Exp structure: 
        
        1.2.3.4.5.6.7.8.9.10
        
        *
        */
        for ($counter = 1; $counter <= $total_pages; $counter++) {
          fixbd_get_page($counter, $page_no, $pageURL);
        }
      }
      
      elseif ($total_pages > 10) {
        if ($page_no <= 4) {
          /**
          * Display last 2 page and first 8 page (with ...)
          * Exp structure: 
          
                        ($last_page)
          1.2.3.4.5.6.7 ... 11.12
          
          *
          */
          for ($counter = 1; $counter < 8; $counter++) {		 
            fixbd_get_page($counter, $page_no, $pageURL);
          }
          
          // Display last 2 page
          echo wp_kses_post($last_page);
        }
      
        elseif ($page_no > 4 && $page_no < $total_pages - 4) {
          /**
          * let's do -
          * Begin display first 2 page
          * Then center pagination with 2 page before and after
          * Then display last 2 page
          * Exp structure:
          
                      					($center)
          ($first_page) 1.2 ... 3.4.5.6.7 ... 11.12 ($last_page)
          
          *
          */
          // Display first 2 page
          echo wp_kses_post($first_page);
          
          /**
          * Display center with 2 page before and after
          * Exp structure: 
          
                	center
          3 < 4 < =(5)= > 6 > 7
          
          *
          */

          for ($counter = $page_no - $center; $counter <= $page_no + $center; $counter++) {			
            fixbd_get_page($counter, $page_no, $pageURL);
          }
          
          // Display last 2 page
          echo wp_kses_post($last_page);
        } else {
          /**
          * Display first 2 page and last 8 page (with ...)
          * Exp structure: 
          
          ($first_page)
          1.2 ... 6.7.8.9.10.11.12
          
          *
          */
            echo wp_kses_post($first_page);
          
          for ($counter = $total_pages - 6; $counter <= $total_pages; $counter++) {
            fixbd_get_page($counter, $page_no, $pageURL);      
          }
        }
      }
      ?>
      
      <li <?php if($page_no >= $total_pages){ echo "class='disabled'"; } ?>>
      <a <?php echo "data-id='".esc_attr($next)."'";?> <?php if($page_no < $total_pages) { echo "href='". esc_url($pageURL) ."page-no=".esc_attr($next)."'"; } ?>>&raquo;</a>
      </li>

    </ul>

    <!-- Vertically centered scrollable modal -->
    <div class="modal fade" id="jumpPage" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="jumpPageLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content bg-white">
          <form method="get" id="jump-page">
            <div class="modal-header">
              <h5 class="modal-title d-flex align-items-center" id="jumpPageLabel"><i class="dashicons dashicons-admin-page me-2"></i><?php echo __('Jump Page', 'educare');?></h5>
              <button type="button" class="btn-close me-1" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
							<div class="educare-form">
								<div class="content">
									<?php
									// Nonce field
									echo '<input type="hidden" name="nonce" value="'.esc_attr($educare_filter_nonce).'">';

									foreach ($_GET as $key => $value) {
										echo '<input type="hidden" name="'.esc_attr( $key ).'" value="'.esc_attr( $value ).'">';
									}
									?>

									<div class="row mb-3">
										<label class="col-md-2 col-form-label d-flex align-items-center" for="page-no"><?php _e('Page No', 'educare');?></label>
										<div class="col-md-10">
											<input class="form-control" type="number" id="page-no" name="page-no" value="<?php echo esc_attr($page_no); ?>" placeholder="<?php echo esc_attr($page_no); ?>">
										</div>
									</div>
									
									<div class="row">
										<label class="col-md-2 col-form-label d-flex align-items-center" for="per-page"><?php _e('Per Page', 'educare');?>:</label>
										<div class="col-md-10">
											<input class="form-control" type="number" id="per-page" name="per-page" value="<?php echo esc_attr($per_page); ?>" placeholder="<?php echo esc_attr($per_page); ?>">
										</div>
									</div>
								</div>
							</div>
            </div>
						
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo __('Ok', 'educare');?></button>
              <button type="submit" name="filter" class="btn btn-success"><?php _e('Go', 'educare');?></button>
            </div>
          </form>
        </div>
      </div>
    </div>
		<?php
	} else {
		?>
		<div class="modal fade" id="jumpPage" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="jumpPageLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content bg-white">
					
					<div class="modal-header">
						<h5 class="modal-title d-flex align-items-center" id="jumpPageLabel"><i class="dashicons dashicons-admin-page me-2"></i><?php echo __('Jump Page', 'educare');?></h5>
						<button type="button" class="btn-close me-1" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>

					<div class="modal-body">
						<?php echo educare_show_msg( 'No page available to navigate.', false )?>
					</div>

					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo __('Ok', 'educare');?></button>
					</div>

        </div>
      </div>
    </div>
		<?php
	}
}



?>