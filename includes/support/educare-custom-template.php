<?php
/**
 * Template Name: Educare Result Page
 *
 * Allow users to view theire results on Frontend.
 * Powered By Educare
 * 
 */

// Include site header
 get_header();
?>

<style>
   /* You can add your custom CSS here */
   /* style results head */
   .results-content .results-head {
      color: white;
      background-color: #0000f5;
      text-align: center;
      padding: 3vw;
   }
   /* result head text color and size (4vw responsive) */
   .results-head h2 {
      font-size: 3vw;
   }
</style>

<!-- Your custom design begin -->
<div class="container results-content">
   <!-- Header Section -->
   <section class="results-head">
      <h2>Header Title</h2>
      <h4>Subtitle</h4>
   </section>

   <?php
   // educare shortcode
   echo do_shortcode( '[educare_results]' );
   ?>
</div>

<?php
// site footer
get_footer();
?>