<div class="educare_post update">
    <h1>Edit Results</h1>
    
    <blockquote>Note: Please fill all the details in the form carefully. If you miss one, you may have trouble seeing results. So, check the student admission form well and then give all the details here. (All fields are required).</blockquote>
    
    <?php
    // save forms data
    educare_save_results();
	// Search form for edit/delete results
    if (!isset($_POST['edit']) and !isset($_POST['edit_by_id'])) {
		educare_get_search_forms();
	}
	?>

</div>
	
	
	