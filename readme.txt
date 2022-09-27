=== Educare ===

Contributors:        fixbd
GitHub link:         https://github.com/fixbd/educare
Tags:                Result management, Academic, Results, Students, Education, School, College, Coaching Center, Exam, School management, publish results
Requires at least:   3.8
Tested up to:        6.0.2
Requires PHP:        5.2.4
Stable tag:          1.3.0
License:             GPLv2 or later
License URI:         http://www.gnu.org/licenses/gpl-2.0.html

Powerful Online School/College Students & Results Management System With Lots Of Cool Features.

== Description ==

<b>Educare is a powerful online School/College students & results management system dev by FixBD. This plugin allows you to manage and publish students results. This is a School/College students & results management plugin that was created to make WordPress a more powerful CMS.</b>

Educare help you to easily control over your institute students at online. You can easily Add/Edit/Delete Students, Results, Class, Exam, Year, Extra field, Custom Result Rules, Auto calculations and much more... Also you can import & export unlimited students and results just one click!

Just install and manage  your School, College, Coaching Center & personal website with existing Features of Educare. Remember, all Features of Educare is completely free of charge!

Finally, we're going to add features to this plugin that you won't find in any premium plugin. And we will give you all these premium features for free. Because, we believe in freedom and understand the value of your work or dreams! One more thing, Features are added based on user (Your) feedback. Because Educare is built for the user. If it doesn't work for the people it's made for, it's useless. So, Educare authorities value of user feedback.

You can send your feedback here:
[https://wordpress.org/plugins/educare/#reviews](https://wordpress.org/plugins/educare/#reviews)

Also, If you have face any problems and need our support (Totally Free!), Please contact us with this email:
[fixbd.org@gmail.com](mailto:fixbd.org@gmail.com)

### Current Features

#### Educare Marksheed Systems

<p>Using this features admin (teacher) can add subject wise multiple student results at a same time. So, it's most usefull for (single) teacher. There are different teachers for each subject. Teachers can add marks for their specific subject using this feature. And can print all student marks as a marksheet. After, the mark addition is done for all the subjects, students can view and print their results when admin publish it as results. Also, teacher can publish single subject results. (We call it - THE GOLDEN FEATURES FOR TEACHER!).</p>

#### Result Rules (Grading Systems):
<p>In the preview version of Educare, admins can add results with only one (default) grading system. Now by adding (v1.2.0+) this feature admin can add his country result system/rules. So, <strong>Using this feature you (admin) can add, modify, manage or automatically calculate any type of result based on your country or demand</strong>. Eg: India, Bangladesh or US result GPA (CGPA) and calculation methods/rules are different. So, maintaining a defined result using one rule is a bit tricky. The result rule feature solves this problem. If you manage results for Indian students, you can add Indian grading system or rules. Also, you can add Bangladesh or US grading system in the same way. We know, it's a bit difficult. Please share your experience while using these features to improve Educare.</p>

* Users/Students can find result by Name, Registration number, Roll Number, Exam, Passing Year
* Auto/Manual results calculations
* Auto GPA (CGPA) - based on your (admin) rules
* Automatically show letter grade
* Results table with students details
* Single subject result
* Subject marks and grade
* Users/Students Photos
* Optional subject detection
* Field validation
* Error notice
* Custom Themes
* Print results

#### Admin Can -

* Admin can add and manage students
* Public Student Results
* Add Custom Result Rules
* Modify Grading Systems Based On Country or Demand
* Marksheed systems (Admin or teacher can add subject wise multiple student results at a same time.)
* Add/Update/Delete Students, Results, Subject, Class, Exam, Year, Extra (custom) field.
* Import/Export Results, Students and Marksheed
* View (All) Results by Class, Exam, Year With Asc/Desc Mode
* Add Default Students Photos
* Control And Manage Educare Settings
* Also, You can find lots of feature when you use it.

### Future Update:

* Custom student profiles
* Student Registration
* Register the admission form
* Attend/watch classes and exams
* Building connections between teachers and students
* Our future plan is to make Educare a fully virtual school

We know, it's a bit difficult. Please share your experience (feedback) while using these features to improve Educare.

For more info, please visit the [Educare on GitHub](https://github.com/fixbd/educare).

### Shortcodes

Currently, there are only one shortcodes available that you can use in your post editor, template or any shortcode-ready area...

**`[educare_results]`**

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
	
	/* Button style */
	.educare_results button {
		color: white;
		background-color: blue;
	}

	/* Students photos */
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

	/* Headers style */
	.result_body h2,
	.result_body h3 {
		color: white;
		background: rgb(250, 0, 196);
		padding: 22px;
	}
	.result_body .grade_sheet th {
		background: rgba(250, 0, 196, 0.300);
	}
	
	/* Table style */
	.result_body table {
		background: rgba(250, 0, 196, 0.075);
		border-collapse: collapse;
	}
	.result_body .result_details td,
	.result_body .grade_sheet td,
	.result_body .grade_sheet th {
		border: 1px solid rgba(20,20,20,0.10);
	}

	/* Status style */
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

The Educare plugin is a massive project with lot's of code to maintain. A major update can take weeks or months of work. We don't make any money from this plugin users, We glad to say that, All (PREMIUM) Features of Educare is completely free of charge!. So, no money will be required to install or update this plugin. Please consider helping just -

* [Rating the plugin](https://wordpress.org/support/plugin/educare/reviews/?filter=5#new-post).
Yours Ratings Inspired us to discover more and more features!

### Professional Support

If you need professional plugin support from us, you can [visit our support page](https://wordpress.org/support/plugin/educare).

== Installation ==

1. Download `Educare` from the WP plugin directory. (For manualy installation, Upload `Educare` to the `/wp-content/plugins/` directory.)
2. Activate the plugin (Educare) through the 'Plugins' menu in WordPress.
3. Now, you can see Educare icon appears into menu bar.
3. Go to "Educare > Settings" for configure the plugin. or 
3. Go to "Educare > Grading Systems" for configure Result Rules.

#### Important thing: You need to add [educare_results] shortcode into WP editor or Template for front end results system.

More detailed instructions are included in the plugin's `README.md` files.

### Why User Like Educare?

1. Usefull features
1. Suit for any themes
1. Easy to use (Everything is simple)
1. Everything is customizable
1. Support all type of results system
1. Powerful student and results management facilities
1. The best school management plugin for WordPress users
1. Features are added based on user feedback
1. Always updated
1. All feature like premium but FREE!

== Frequently Asked Questions ==

### Why was this plugin created?

Currently, there are no alternative way to manage students and publish results in WordPress by default.
I wasn't satisfied with some educational plugins available in WP plugins directory. Yes, some of them are good, but nothing fit what I had in mind perfectly. And all of this requred a lot of money to use there premium Features.

So, I just built something that I actually enjoyed and used lots of premium features without any charge!.

### Can I use it for school management?
Yes, you can manage all students and teachers in your institute.

### Can I publish single subject results?
Yes. Educare has a features called 'Add Marks'. Using these features admin (teacher) can add or publish subject wise multiple student results at a same time. So, it's most usefull for (single) teacher and subject.

### How to show result on (Front End) page?

`[educare_results]`

You need to add `[educare_results]` shortcode into WP post editor, template or any shortcode-ready area for front end results system. This shortcode display search form and results. So, users/students can easily find and view there results.

### Can the result card be custom designed?

Yes, In Educare v1.2.2+ you (admin) can customize results card, search results forms. Everything on the front end is customizable. Please [Follow this topics](https://wordpress.org/support/topic/can-the-result-card-be-custom-designed).

### How do I use it?

Most things of Educare fairly straight-forward, but we've included an in-depth guide in the plugin download.  It's a file called `readme.md` in the plugin folder.

You can also [view the readme](https://github.com/FixBD/Educare/blob/FixBD/README.md) online.

### Minimum PHP requirements.

Current version of Educare requires PHP 4.4+
And Wordpress 3.8+

### Why is Educare free?

We will give you all these premium features for free. Because, we believe in freedom and understand the value of your work and dreams!

Attention please: You only need to give us 5 stars!

== Screenshots ==

1. Front end search results
2. Results table (Details)
3. Results table (Grade sheet)
4. Print or save results
5. View Results
6. Add Results
7. Import Results
8. Update Results
9. Grading Systems
10. Educare Settings
11. Add Contents
12. Add Extra Fields

== Changelog ==

= [1.3.0] =

* **Back-End**
* [Add Results] Fixed showing the nearest value issue in GPA
* [View Results] You can view (15) results on page load 
* Increse students/results per page number

= [1.2.9] =

* (Front-End) Results Not Found Issues Fixed

= [1.2.8] =

* Update issues fixed
* Educare database update required message issue has been resolved
* Improved all student menu
* Fixed some bugs

* **Please note:** If you see database update required message, Just click update button. This is an most stable version of Educare. If you face any problem (any bugs issues), please inform us.

= 1.2.7 =

* Improved Students Profiles
* Added Students Import System
* Added Students Import Demo Files
* Fixed Settings Save Issues
* Improve Educare Problem Detection
* Fixed All Well Khown Bugs

= 1.2.6 =

* Added Students Profiles
* PHP Error Fixed From Results Card

= 1.2.5 =

* Fixed Error on publishing marks
* PHP Error Fixed

= 1.2.4 =

* Improved perfomance
* Added Student Management System
* Added Add Marks (Marksheed) System
* Now It's Possible To Publish Single Subject Results
* Teacher Can Publish Subject Wise Multiple Student Results At A Same Time.
* Improved Educare Smart Guidelines

* **Back-End Improved**
* Added Ajax Functionality
* Now It's 10X Faster Than Older Version
* Added (AI) Problem Detection And Auto Fix
* Fixed All Well Khown Bugs

**Features are added based on user (Your) feedback. Because Educare is built for the user. If it doesn't work for the people it's made for, it's useless. So, Educare authorities value of user feedback. Please share your experience (feedback) while using these features to improve Educare.**

= 1.2.3 =

* Improve perfomance
* Added Customize options in settings
* Now admin can enable/disable all default required fields in search forms. (Roll Number, Reg Number, Class, Exam, Year)
* Added more options to view results (admin can find results of his choice!)
* Added single results delete button in view results
* Fixed issue with Delete All Results button not working
* Fixed all well khown bugs
* Custom result calculation option will be added after next update. Using these options admin can define, how to calculate results and GPA (CGPA).

* **Please note:** This is an most stable version of Educare. If you face any problem (any bugs issues), please inform us.

= 1.2.2 =

* Now admin can customize results card
* Admin can customize search Forms
* Customize optional subject selection
* Improved import demo
* Fixed grading system new rules save issues
* Improve perfomance
* Fixed some bugs 

= 1.2.1 =

* Update issues fixed

= 1.2.0 =

= Improve Core Features =

* Added result rules feature
* Now admin can add or change his own grading system
* Reg no and roll no name are changeable
* and only one/both can be mandatory
* Admin can add subject according to class
* Removed unwanted code
* Improve import demo
* Fixed some bugs

= Improve front-end =
* Admin can add/change default (front-end) result search form
* Improved (default) search form style
* Improved color issues when print results
* More powerful and excited features coming soon!

* **Please note:** You should backup your (Educare) database before updating to this new version (only for v1.0.2 or earlier users).

The (full) change log is located in the `changelog.md` file in the plugin folder. You may also [view the change log](https://github.com/FixBD/Educare/blob/FixBD/changelog.md) online.