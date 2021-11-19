<?php

// Create table for results system
function educare_database_table(){

   global $wpdb;
   $charset_collate = $wpdb->get_charset_collate();

   $Educare_results = $wpdb->prefix."Educare_results";

	$table1 = "CREATE TABLE $Educare_results (
		id mediumint(11) NOT NULL AUTO_INCREMENT,
		Name varchar(80) NOT NULL,
		Roll_No varchar(80) NOT NULL,
		Regi_No varchar(80) NOT NULL,
		Class varchar(80) NOT NULL,
		Exam varchar(80) NOT NULL,
		Date_of_Birth varchar(80) NOT NULL,
		Fathers_Name varchar(80) NOT NULL,
		Mothers_Name varchar(80) NOT NULL,
		Institute varchar(80) NOT NULL,
		Type varchar(80) NOT NULL,
		Email varchar(80) NOT NULL,
		Mobile_No varchar(80) NOT NULL,
		Photos varchar(100) NOT NULL,
		Year varchar(80) NOT NULL,
		Result varchar(80),
		GPA varchar(80),
		Mathematics varchar(80) NOT NULL,
		English varchar(80) NOT NULL,
		ICT varchar(80) NOT NULL,
		PRIMARY KEY (id)
   ) $charset_collate;";
   
   
   // Create table for educare settings
   $Educare_settings = $wpdb->prefix."Educare_settings";

   $table2 = "CREATE TABLE $Educare_settings (
		id int(11) NOT NULL AUTO_INCREMENT,
		list varchar(255) NOT NULL,
		data text NOT NULL,
		PRIMARY KEY (id),
		UNIQUE KEY list (list)
   ) ENGINE=InnoDB DEFAULT CHARSET=latin1";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	
	dbDelta( $table2 );
	dbDelta( $table1 );

}

?>