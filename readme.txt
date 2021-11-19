=== Educare ===

Contributors: fixbd
GitHub link: https://github.com/fixbd/educare
Tags: Results, Students, Education, School, College, Coaching Center, Exam, publish results
Requires at least: 3.8
Tested up to: 5.8.1
Requires PHP: 5.2.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Powerful online School/College students & results management system with lots of cool features.

== Description ==

<b>Educare is a plugin dev by [FixBD](https://github.com/fixbd) that help you to easily control over your institute students at online. It's a School/College students & results management plugin that was created to make WordPress a more powerful CMS.</b>

This plugin allows you to manage and publish students results. You can easily Add/Edit/Delete Students, Results, Class, Exam, Year Custom field and much more... Also you can import & export unlimited students and results just one click!

Just install and manage  your School, College, Coaching Center & personal website with existing futures of Educare. Remember, all futures of Educare is completely free of charge!

### Current Features

#### Results System -

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

#### Admin Can -

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

### Futures Update:

#### Students/Users Can -

1. Register Admissions Forms
1. Edit Profiles
1. Change Profiles picture
1. View/Attend class
1. Attend Exam
  ...
  
#### Admin Can -

<p>We work for add <b>Results Rules</b> futures. this is a one of the most important futures for add results. In current version of this plugin, admin can add one rules results.</p>

<p><b>What is Results Rules futures?</b><br>
Current version of this plugin, admin can add one rules results. for example: we need to add JSC, SSC and HSC exam results. but admin can add only one of this three exam. Because, JSC students subject are 9, SSC students Subject 11, and HSC students subject 7. So, it's little be complicated to Add, Show and Auto calculate the results. Results Rules futures solve this issue. We will add this futures after next update!</p>

##### Back end -
1. Add Teachers
1. Received Payment
1. Aprove Admissions Forms
1. Delete Admissions Forms

##### Front In -
1. Custom Login page
1. Custom Registration page
1. Custom Forgot Password page
1. Custom User Profiles page
1. Custom Profiles Edit page
1. Redirect Facility
  And soo on...

For more info, please visit the [Educare on GitHub](https://github.com/fixbd/educare).

### Shortcodes

Currently, there are only one shortcodes available that you can use in your post editor, template or any shortcode-ready area..

#### [educare_results]

You need to add [educare_results] shortcode into WP editor or Template for front end results system. This shortcode display search form and results table. So, users/students can easily find and view there results.

## Plugin Development

If you're a theme author, plugin author, or just a code hobbyist, you can follow the development of this plugin on it's [GitHub repository](https://github.com/fixbd/educare). Here, some of the basics introduction given below:

#### To Change Results Table Style/Colors

##### In function.php files (php)
	
	add_action('wp_enqueue_scripts', 'custom_results_style');
	function custom_results_style() {
	    wp_enqueue_style('custom_results_style', get_template_directory_uri().'/assets/css/results.css');
	}
	
	** Notes: Make sure to change (assets/css/results.css) to your own URL
	
##### In results.css files (css)
	
	/*Button style*/
	.educare_results button {
	  color: white;
	  background-color: blue;
	}
	
	/*Headers style*/
	.result_body h2,
	.result_body h3 {
		color: white;
		background: green;
		padding: 22px;
	}
	.result_body .grade_sheet th {
		background: rgba(0,115,0,0.20);
	}
	
	/*Students photos*/
	.result_body .student_photos {
	text-align: center;
	}
	.result_body .student_photos img {
		width: 40%;
		height: 40%;
		max-width: 150px;
		max-height: 150px;
		padding: 8px;
		border-radius: 8px;
	}
	 
	/*Table style*/
	.result_body table {
		background: #fff;
		border-collapse: collapse;
	}
	.result_body .result_details td,
	.result_body .grade_sheet td,
	.result_body .grade_sheet th {
		border: 1px solid rgba(20,20,20,0.10);
	}
	
	/*Status style*/
	.result_body .failed,
	.result_body .error,
	.errnotice b {
		color: red;
	}
	.result_body .success {
		color: green;
	}
	
	
	* If you wish to know more about, how to change educare results table style? please view this [GitHub repository](https://github.com/fixbd/educare/includes/support/educare_themes).
	
	* For Complete Educare Development Resource, Please visit [Educare home page](https://github.com/fixbd/educare).
	
### Like this plugin?

The Educare plugin is a massive project with lot's of code to maintain. A major update can take weeks or months of work. We don't make any money from this plugin users, We glad to say that, All (PREMIUM) futures of Educare is completely free of charge!. So, no money will be required to install or update this plugin. Please consider helping just -

* [Rating the plugin](https://wordpress.org/support/plugin/educare/reviews/?filter=5#new-post).
Yours Ratings Inspired us to discover more and more features!

### Professional Support

If you need professional plugin support from us, you can [visit our support page](https://github.com/fixbd/support).

== Installation ==

1. Download `Educare` from the WP plugin directory. (For mental installation, Upload `Educare` to the `/wp-content/plugins/` directory.)
2. Activate the plugin (Educare) through the 'Plugins' menu in WordPress.
3. Now, you can see Educare icon appears into menu bar.
3. Go to "Educare > Settings" for configure the plugin.

#### Important thing: You need to add [educare_results] shortcode into WP editor or Template for front end results system.

More detailed instructions are included in the plugin's `readme.html` file.

== Frequently Asked Questions ==

### Why was this plugin created?

Currently, there are not any options to manage Students and Results in WordPress by default.
I wasn't satisfied with some educational plugins available in WP plugins directory. Yes, some of them are good, but nothing fit what I had in mind perfectly. And all of this requred a lot of money to use there premium futures.

So, I just built something I actually enjoyed using and use lots of premium fetuses without any charge!.

### How do I use it?

Most things should be fairly straightforward, but we've included an in-depth guide in the plugin download.  It's a file called `readme.md` in the plugin folder.

You can also [view the readme](https://github.com/fixbd/educare/guide/readme.md) online.

### Minimum PHP requirements.

Current Educare requires PHP 4.4+

### Help! All Results status failed !

Please read the documentation for the plugin before actually using it,
You need to add all Subject first. Then, add students results

== Screenshots ==

1. Front end search results
2. Front end results table
3. View Results
4. Add results
5. Import results
6. Update Results
7. Educare Settings
8. Add Contents
9. Add Extra Fields

== Changelog ==

The change log is located in the `changelog.md` file in the plugin folder. You may also [view the change log](https://github.com/fixbd/educare/guide/changelog.md) online.