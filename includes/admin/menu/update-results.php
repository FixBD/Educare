<div class="educare_post update">
	
    <h1>Edit Result</h1>
    <blockquote>Notes: Please carefully fill out all the details of the forms. If you miss one, you may have problems to see the results. So, verify the student's admission form well and then give all details here. (All fields require).</blockquote>
    
    <?php
    // save forms data
    educare_save_results();
	// Search form for edit/delete results
    if (!isset($_POST['edit']) and !isset($_POST['edit_by_id'])) {
		educare_get_search_forms();
	}
	?>

</div>
	
	
	