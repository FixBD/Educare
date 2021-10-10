# Educare

Educare is a plugin that help you to easily control over your institute students at online. It's a School/College students & results management plugin that was created to make WordPress a more powerful CMS.

Educare is a powerful online School/College students & results management system dev by FixBD . This plugin allows you to manage and publish students results. You can easily Add/Edit/Delete Students, Results, Class, Exam, Year Custom field and much more... Also you can import & export unlimited students and results just one click!

## Current Features

### Results System -

1. Users/Students can find result by Name, Registration number, Roll Number, Exam, Passing Year
1. Optional subject
1. Auto/Manual results calculation
1. Results table with students details
1. Subject marks and grade
1. Users/Students Photos
1. Field validation
1. Error notice
1. Custom Themes
1. Print results

### Admin Can -

1. Add/Update/Delete Results
1. Import Results
1. Export Results
1. Public Results
1. Add/Update/Delete Subject
1. Add/Update/Delete Class
1. Add/Update/Delete Exam
1. Add/Update/Delete Year
1. Add/Update/Delete Extra field
1. Control Settings

## Futures Update:

### Students/Users Can -

1. Register Admissions Forms
1. Edit Profiles
1. Change Profiles picture
1. View/Attend class
1. Attend Exam
  ...
  
### Admin Can -

<p>We work for add <b>Results Rules</b> futures. this is a one of the most important futures for add results. In current version of this plugin, admin can add one rules results.</p>

<p><b>What is Results Rules futures?</b><br>
Current version of this plugin, admin can add one rules results. for example: we need to add JSC, SSC and HSC exam results. but admin can add only one of this three exam. Because, JSC students subject are 9, SSC students Subject 11, and HSC students subject 7. So, it's little be complicated to Add, Show and Auto calculate the results. Results Rules futures solve this issue. We will add this futures after next update!</p>

#### Back end -
1. Add Teachers
1. Received Payment
1. Aprove Admissions Forms
1. Delete Admissions Forms

#### Front In -
1. Custom Login page
1. Custom Registration page
1. Custom Forgot Password page
1. Custom User Profiles page
1. Custom Profiles Edit page
1. Redirect Facility
1. Widgets
1. Private site (login permission)
  And soo on...

For more info, visit the [Educare on GitHub](https://github.com/fixbd/educare).

## Professional Support

If you need professional plugin support from me, the plugin author, you can [visit our support page](https://github.com/fixbd/support). 

## Copyright and License

This project is licensed under the [GNU GPL](http://www.gnu.org/licenses/old-licenses/gpl-2.0.html), version 2 or later.

## Documentation

### How to use the plugin

This plugin is set up everything clearly and simply. So, you can easily manage this plugin features. Currently, there are six (6) main futures available for Admin: 
1. View Results
2. Add Results
3. Import Results
4. Update Results
5. Settings Results

One (1) of them for Admin & Users/Students:
6. Front end results table

#### 1. View Results

Dir: Educare/View Results

There are lots of options to find students results. You can view results by Class, Exam, Year. And short results by Asc or Decs. For this, you need to change that options. Also, You can view all the results at a same times. For view all results, keep entire options by default and click View Results button. That's it!

#### 2. Add Results

Dir: Educare/Add Results

Notes: Please carefully fill out all the details of the form. If you miss one, you may have problems to see the result. So, verify the student's admission form well and then give all details here. All (Class, Exam, Roll No, Regi No, Year) fields are required.

#### 3. Import Results

Dir: Educare/Import Results

Please carefully fill out all the details of your import (.csv) files. If you miss one, you may have problems to import the results. So, verify the student's admission form well and then give all the details in your import files. Required field are: Name, Roll No, Regi No, Exam, Class and Year. So, don't miss all of this required field!

If you don't know, how to create import files. Please [download](https://github.com/fixbd/Educare/assets/files/import_demo.csv) the demo files given below.

This is an auto generate  .csv file for import demo, based on Educare current settings (Subject, Exam, Extra field...).
If problem to download the flies, you can manually get this file in plugins dir: Educare/assets/files/import_demo.csv

Notes: Files must be an .csv extension for import the results.

#### 4. Update Results

Dir: Educare/Update Results

Here admin can update/edit/delete the students results that was previously added.

#### 5. Educare Settings

Dir: Educare/Settings

Here you can see two sections. One is for Add Content and another one is Settings.

* Add Content:
1. Add/Update/Delete Subject
2. Add/Update/Delete Class
3. Add/Update/Delete Exam
4. Add/Update/Delete Year
5. Add/Update/Delete Extra field

* Settings
Here you can change or disable students default photos.

* Switch buttons details:
Delete confirmation
Anable and disable delete/remove confirmation

#### Guidelines
Anable and disable guide/help messages

**Recommend:** You can keep anabled **Guidelines** to get help pop message. It's help you to know - How to use the Educare.

#### Students Photos
Show or Hide students photos

#### Auto Results
Automatically calculate students results status Passed/Failed and GPA

#### Advance Settings
Anable and disable Advance/Developers menu. Note: it's only for developers or advance users

#### Automatically Delete Subject
Automatically Delete Subject from Results Table When You Delete Subject From Subject List?

#### Delete and Clear field data
Tips: If you set No that's mean only field will be delete. And, if you set Yes - clear field data when you delete any (current) field. Delete and Clear field data?

#### 6. Front end results table

There are a shortcodes `[educare_results]` that you can use in your post editor or any shortcode-ready area for display front end results system. Mor info given at shortcode sections.

### Shortcodes

Currently there are only one shortcodes available that you can use in your (WP) post editor, template or any shortcode-ready area..

#### [educare_results]

You need to add `[educare_results]` shortcode into editor, template or any shortcode-ready area for front end results system. This shortcode display search form and results table. So, users/students can easily find and view there results.

## Developers only

If you're a theme author, plugin author, or just a code hobbyist, you can follow this development introductions given below -

### Let's Know Educare Functionality

* String to object
* Educare using this method to configure settings
* Main function: json_encode() and json_decode()

* @return object
exp: =>

	$settings = ["delete_subject"=>"checked", "clear_field"=>"unchecked"];
	$data = json_encode($settings);
	$status = json_decode($data);
	
	// Preview return
	print_r($status);
	echo $status->clear_field;


************************************( Next )***********************************


* Search and remove array content
* Educare using this method to Remove the content
* Main function:
	* 1. array_search() for search array content
	* 2. unset() for remove content
	* 3. array_values() for reindex array content/key

* @return array
exp: =>

	$data = array('English', 'Mathematics', 'Chemistry', 'Biology', 'ICT');
	$target = 'Mathematics';
	if (($target = array_search($target, $data)) != false) {
	    unset($data[$target]);
		// currently $data index/key is => 0, 2, 3, 4
		// reindex $data
	    $data = array_values($data);
		// Now $data index/key is => 0, 1, 2, 3
	}
	
	// Preview return
	print_r($data);


************************************( Next )***********************************


* discover function cleanData() for cleaning array data for specific characters
* Educare using this method to configure Extra fields content or Optional Subject
* Main function: preg_match()

* @return array
exp: =>

	$data = Array (
		0 => "text 1",
		1 => "x text 2",
		2 => "text 3",
		3 => "x text 4",
		4 => "text 5",
	);
	
	// create function for clean data
	function cleanData($data){ 
	    $clean = array();
	    foreach($data as $val){ 
	    	// for global match: /[x 2]/
	        preg_match ('/x /', $val, $matches); 
	        if(count($matches) == 0) $clean[] = $val;
	    }
	    return $clean;
	}
	
	// Preview return
	print_r(cleanData($data));
	// output: Array ( [0] => text 1 [1] => text 3 [2] => text 5 )
	

************************************( Next )***********************************


* Similar to above

	$data = array(1,3,4,1,3,1,5,8,9,10);
	$clean = array();
	
	foreach($data as $value) {
		if($value=='3') {
	        continue;
	    } else {
	        $clean[]=$value;
	    }     
	}
	
	print_r($clean);


************************************( Next )***********************************


* Getting first and last word
* Educare using this method to detect (forms) field type
* @return string
exp: =>

	$target = "text Fathers Name";
						 1. type	2. name
	// $target = "number Mobile Number";
	
	// sclice/remove first world
	// for replace all white space
	// $name = str_replace(' ', '_', $name);
	$name = substr(strstr($target, ' '), 1);
	
	// getting first world
	$type = strtok($target, ' ');
	
	echo "
		input name: $name<br>
		input type: $type<br>
	";
	
	// more example:
	echo "<input type='".$type."' name='".$name."' placeholder='Type students ".$name."'>";

* Notes: Change $target (1) value number to text, email, file, date...







