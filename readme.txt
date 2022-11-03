=== Educare ===

Contributors:        fixbd
GitHub link:         https://github.com/fixbd/educare
Tags:                Result management, Academic, Results, Students, Education, School, College, Coaching Center, Exam, School management, publish results
Requires at least:   3.8
Tested up to:        6.1.0
Requires PHP:        5.2.4
Stable tag:          1.3.0
License:             GPLv2 or later
License URI:         http://www.gnu.org/licenses/gpl-2.0.html

Powerful Online School/College Students & Results Management System With Lots Of Cool Features.

== Description ==

<b>Educare is a powerful online School/College students & results management system dev by FixBD. This plugin allows you to manage and publish students results. This is a School/College students & results management plugin that was created to make WordPress a more powerful CMS.</b>

Educare help you to easily control over your institute students at online. You can easily Add/Edit/Delete Students, Results, Class, Group, Exam, Year, Extra field, Custom Result Rules, Auto calculations and much more... Also you can import & export unlimited students and results just one click!

Just install and manage your institute, school, college, coaching center & personal website with existing features of Educare. Remember, all features of Educare is completely free of charge!

### Our Vision

We’re continuously additing features to this plugin that you won’t find in any premium plugin. And we will give you all these premium features for free. Because, we believe in freedom and understand the value of your work or dreams!

### Our Mission

Our mission is to build a great software that will reform education. Our future plan is to make Educare a fully virtual school.

One more thing, Features are added based on user (Your) feedback. Because Educare is built for the user. If it doesn't work for the people it's made for, it's useless. So, Educare authorities value of user feedback. You can send your feedback here:
[https://wordpress.org/plugins/educare/#reviews](https://wordpress.org/plugins/educare/#reviews)

Also, If you have face any problems and need our support (Totally Free!), Please contact us with this email:
[fixbd.org@gmail.com](mailto:fixbd.org@gmail.com)

### Current Features

#### * Student Management

Admin can add sigle and import unlimited students via .csv (Exel) file. Once, student details are added then no need to fill student details again while adding or publishing any results. Also, Educare provides student profile ID card, analysis, details, subject, old (class) data and much more.

#### * Results Management

Educare provite powerfull results management functionality. Admin can publish sigle and unlimited results via .csv (Exel) files. Student can view results with photos, details, points, grade sheed, save or print results. Also, students can view their profile (under construction).

#### * Educare Marksheed Systems

<p>Using this features admin (teacher) can add subject wise multiple student results at a same time. So, it's most usefull for (single) teacher. There are different teachers for each subject. Teachers can add marks for their specific subject using this feature. And can print all student marks as a marksheet. After, the mark addition is done for all the subjects, students can view and print their results when admin publish it as results. Also, teacher can publish single subject results. (We call it - THE GOLDEN FEATURES FOR TEACHER!).</p>

#### * Performance or Promote

Admin can promote or change classes, years, groups of multiple students with just one click! Most useful when students need to be promoted (from one class to another class) or when multiple students need to be updated.

#### * Result Rules (Grading Systems)

<p>In the preview version of Educare, admins can add results with only one (default) grading system. Now by adding (v1.2.0+) this feature admin can add his country result system/rules. So, <strong>Using this feature you (admin) can add, modify, manage or automatically calculate any type of result based on your country or demand</strong>. Eg: India, Bangladesh or US result GPA (CGPA) and calculation methods/rules are different. So, maintaining a defined result using one rule is a bit tricky. The result rule feature solves this problem. If you manage results for Indian students, you can add Indian grading system or rules. Also, you can add Bangladesh or US grading system in the same way. We know, it's a bit difficult. Please share your experience while using these features to improve Educare.</p>

#### * Content Management

Admin can add class or group wise subject, exam, pass year, extra (custom) fields and more.

Educare is a 100% mobile responsive, So educare will always look great on all devices (mobile, tablet, laptop, and desktop). And everything is Ajax compatibility (Back-End and Front-End).

[Video - About Educare]

#### * Admin Can -

* Admin can add and manage students
* Admin can publish and manage student results
* Unlimited/Single subject results adding facility
* Add/Update/Delete Students, Results, Subject, Class, Group, Exam, Year, Extra (custom) field.
* Class and group wise subject added facilities
* Add custom result rules (grading systems)
* Modify grading systems based on country or demand
* Marksheed systems (Admin or teacher can add subject wise multiple student results at a same time.)
* Auto/Manual results calculations facility
* Unlimited import/export results, students and marksheed 
* Auto GPA (CGPA) - based on your (admin) rules
* Students profile with - analytics, details, subject, old (class) data
* Filter and view all results/students (Like - class, exam, year with Asc/Desc mode and more...)
* Modify default students photos
* Control and manage educare settings
* Also, you can find many features while using it.

#### * Students/Users Can -

* Users/Students can find result by name, registration number, roll number, exam, passing year
* Results table with students details
* Subject marks and gradesheed
* Users/Students Photos
* Optional subject detection
* Field validation
* Print results

### Future Update:

* Custom student profiles
* Student Registration
* Register the admission form
* Attend/watch classes and exams
* Building connections between teachers and students
* Our future plan is to make Educare a fully virtual school

### Why You Should Choose Educare?

* Powerful functionality that you need
* Everything is Ajax compatibility (Back-End and Front-End)
* Everything is customizable
* Suit for any themes
* Import/Export facility
* Support all type of device (Responsive UI)
* Strong coumunity support
* Price - ($) Free
* Allways up to date

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
1. Everything is Ajax compatibility (Back-End and Front-End)
1. Support all type of results system
1. Powerful student and results management facilities
1. The best school management plugin for WordPress users
1. Features are added based on user feedback
1. All feature like premium but FREE!
1. Allways up to date

== Frequently Asked Questions ==

### Why was this plugin created?

Currently, there are no alternative way to manage students and publish results in WordPress by default.
I wasn't satisfied with some educational plugins available in WP plugins directory. Yes, some of them are good, but nothing fit what I had in mind perfectly. And all of this requred a lot of money to use there premium Features.

So, I just built something that I actually enjoyed and used lots of premium features without any charge!.

### How to show result on (Front End) page?

`[educare_results]`

You need to add `[educare_results]` shortcode into WP post editor, template or any shortcode-ready area for front end results system. This shortcode display search form and results. So, users/students can easily find and view there results.

### Can I use it for school management?

Yes, Educare has exactly the features you want. So, feel free to use Educare on your website.

### Can I publish single subject results?
Yes. Educare has a features called 'Add Marks'. Using these features admin (teacher) can add or publish subject wise multiple student results at a same time. So, it's most usefull for (single) teacher and subject.

### Can the result card be custom designed?

Yes, In Educare v1.2.2+ you (admin) can customize results card, search results forms. Everything on the front end is customizable. Please [Follow this topics](https://wordpress.org/support/topic/can-the-result-card-be-custom-designed).

### How do I use it?

Most things of Educare fairly straight-forward, but we've included an in-depth guide in the plugin download.  It's a file called `readme.md` in the plugin folder.

You can also [view the readme](https://github.com/FixBD/Educare/blob/FixBD/README.md) online.

### Is there YouTube video tutorial?

Currently there is no video tutorial of Educare on YouTube. However, video tutorials will be added very soon.

But there is nothing to despair about. Because, Educare has a feature called (Smart Guidelines). In this feature, the necessary details of each topic and what to do, how it works, how to use it all have been discussed. So, through this feature you will learn to use Educare easily.

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

= [1.4.0] =

* Added group system
* Now admin can add group wise subject
* Added students performance and promote system
* Added management option in admin dashboard
* Added auto fill option in add/update results forms
* Everything is Ajax compatibility (Back-End and Front-End)
* Fixed GPS issues in front-end
* Improve students profiles
* Improve add marks system
* Improve import demo
* Improve UI

* Improve front-end search form and result card customization system
**Notes:** If you use custom functionality for customize educare results card or search forms, Please follow this new method. Or if you find any bugs, please inform us.

= [1.3.0] =

* Added more options in settings
* Admin can hide student details from result card
* Admin can hide grade sheet from result card
* Fixed front-end roll no and regi no short fields

= [1.2.*] =

= **New Features** =

* Added Ajax Functionality
* Added result rules feature
* Added Add Marks (Marksheed) System
* Added Student Management System
* Added students Profiles
* Added students import system
* Added more options to filter and view results
* Added (AI) Problem Detection And Auto Fix
* Now admin can add or change his own grading system
* Now all default fields name are changeable
* Now it's possible po publish single subject results
* Teacher can publish subject wise multiple student results at a same time.

= **Improve Core Features** =

* Everything is Ajax compatibility (Back-End)
* Now It's 10X Faster Than Older Version
* Admin can add subject according to class and group
* Now admin can enable/disable all default required fields in search forms. (Roll Number, Reg Number, Class, Exam, Year)
* Customize optional subject selection
* Removed unwanted code
* Improve import demo
* Fixed all well khown bugs

= **Improve Front-End** =

* Now admin can customize results card
* Admin can customize search Forms
* Improved (default) search form style
* Improved color issues when print results
* More powerful and excited features coming soon!

= [1.0.0] =

* **Happy Release!!!**

* Plugin launch. Everything's new!

* **Please note:** You should backup your (Educare) database before updating to this new version (only for v1.0.2 or earlier users).

The (full) change log is located in the `changelog.md` file in the plugin folder. You may also [view the change log for every single update](https://github.com/FixBD/Educare/blob/FixBD/changelog.md) online.