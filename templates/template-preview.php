<?php
function educare_template_preview() {
  $template_dir = EDUCARE_URL . 'assets/img/templates-preview/';

  $templates = array(
    // Results card template
    'educare_results_card_template' => array(
      'educare_classic_results_card' => array(
        'title' => 'Classic Template',
        'thumbnail' => $template_dir . 'results-card-templates/classic-template.png',
        'callback' => 'educare_classic_results_card',
        'advance' => true
      ),
      'educare_modern_results_card' => array(
        'title' => 'Modern Results Card',
        'thumbnail' => $template_dir . 'results-card-templates/modern-template.png',
        'callback' => 'educare_modern_results_card',
        'advance' => true
      ),
      'educare_dynamic_results_card' => array(
        'title' => 'Dynamic Results Card',
        'thumbnail' => $template_dir . 'results-card-templates/dynamic-template.jpg',
        'callback' => 'educare_dynamic_results_card',
        'advance' => true
      ),
      'educare_academic_template' => array(
        'title' => 'Academic Template',
        'thumbnail' => $template_dir . 'results-card-templates/academic-template.png',
        'callback' => 'educare_academic_template',
        'advance' => true
      ),
      'educare_academic_transcript' => array(
        'title' => 'Academic Transcript',
        'thumbnail' => $template_dir . 'results-card-templates/academic-transcript-template.png',
        'callback' => 'educare_academic_transcript',
        'advance' => true
      ),
      'educare_pathway_mapping_template' => array(
        'title' => 'Pathway Mapping Template',
        'thumbnail' => $template_dir . 'results-card-templates/pathway-mapping-template.jpg',
        'callback' => 'educare_pathway_mapping_template',
        'advance' => true
      ),
      'educare_scholarship_result_card' => array(
        'title' => 'Scholarship Result Card',
        'thumbnail' => $template_dir . 'results-card-templates/scholarship-result-card.jpg',
        'callback' => 'educare_scholarship_result_card',
        'advance' => true
      ),
      'educare_bulk_result_card' => array(
        'title' => 'Bulk Result Template',
        'thumbnail' => $template_dir . 'results-card-templates/bulk-result-template.jpg',
        'callback' => 'educare_bulk_result_card',
        'advance' => true
      ),
    ),

    // Search form template
    'educare_search_form_template' => array(
      'educare_modern_search_form' => array(
        'title' => 'Modern Search Form',
        'thumbnail' => $template_dir . 'search-form-templates/modern-search-form.jpg',
        'callback' => 'educare_modern_search_form',
        'advance' => true
      ),
    ),

    // Certificate template
    'educare_certificate_template' => array(
      'educare_default_certificate' => array(
        'title' => 'Default Certificate',
        'thumbnail' => $template_dir . 'certificate-templates/default-certificate.jpg',
        'callback' => 'educare_modern_certificate',
        'advance' => true
      ),
      'educare_modern_certificate' => array(
        'title' => 'Modern Certificate',
        'thumbnail' => $template_dir . 'certificate-templates/modern-certificate.jpg',
        'callback' => 'educare_modern_certificate',
        'advance' => true
      ),
      'educare_classic_certificate' => array(
        'title' => 'Classic Certificate',
        'thumbnail' => $template_dir . 'certificate-templates/classic-certificate.jpg',
        'callback' => 'educare_classic_certificate',
        'advance' => true
      ),
    ),
    
    // Profiles template
    'educare_profiles_template' => array(
      'educare_default_profiles' => array(
        'title' => 'Default Profiles',
        'thumbnail' => $template_dir . 'profiles-templates/default-profiles.jpg',
        'callback' => 'educare_dashboard_profiles',
        'advance' => true
      ),
      'educare_dashboard_profiles' => array(
        'title' => 'Dashboard Profiles',
        'thumbnail' => $template_dir . 'profiles-templates/dashboard-profiles.jpg',
        'callback' => 'educare_dashboard_profiles',
        'advance' => true
      ),
      'educare_dynamic_profiles' => array(
        'title' => 'Dynamic Profiles',
        'thumbnail' => $template_dir . 'profiles-templates/dynamic-profiles.png',
        'callback' => 'educare_dynamic_profiles',
        'advance' => true
      ),
      'educare_classic_profiles' => array(
        'title' => 'Classic Profiles',
        'thumbnail' => $template_dir . 'profiles-templates/classic-profiles.jpg',
        'callback' => 'educare_classic_profiles',
        'advance' => true
      ),
    )
  );

  // Install template
  foreach ($templates as $template_key => $template) {
    foreach ($template as $template_function => $template_info) {
      // Check if this function is already hooked
      if (!has_action($template_key, $template_function)) {
        add_action($template_key, function ($print = null, $template_details = false, $sttings = false) use ($template_info) {
          if ($template_details) {
            return $template_info;
          }
        });
      }
    }
  }
}

add_action('admin_init', 'educare_template_preview');
