/**
* == FixBD Clone Field ==
*
* Contributors: fixbd
* GitHub link: https://github.com/fixbd/clone-field/assets/clone-field-1.0.js
* Tags: jQuery Clone, jQuery form field copy on click, Clone input field
* Category: jQuery plugins
* Required: jQuery
* Tested up to: jQuery v-2.1.3
* Stable tag: 1.0
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html
*
*/

(function ($) {

	$.fn.cloneField = function (options) {

		// Default options
		var settings = $.extend({
			// Main options
			maxField: 16,									  	// Input fields limitation
			minField: 1,											 // Required at least 1 field
			cloneBody: '#cloneBody',					 // Where to past/append cloned field
			wrapper: '#cloneWrapper',				   // Where to copy for append field
			addButton: '.addButton',		 		 	 // Add button selector
			removeButton: '.remove_button',    	// Remove button
			cloneField: '.cloneField',    				   // For count cloned field
			// Sub field options
			subField: '.subField',
			showSubfield: '.showSubfield',	   	// Button for show sub field
			hideSubfield: '<i class="dashicons dashicons-yes"></i>',
			quickSubfield: false,							 // Open sub field Immediately, when add a field
			// Others options
			autoCunter: true,							      // Automatically count cloned field
			counter: 1,										     // Initial field counter is 1 (if manually count cloned field
			status: '#status',						 	     // Where to display clone status
			countMsgs: 'Field Added #max/#count',
			maxMsgs: 'You can not add more than #max field',
			maxClass: 'field_over',						// Add specific class when field is over
			copyData: '<tr class="cloneField"><td><input type="number" name="rules1[]" value="" placeholder="Less Mark"/></td><td><input type="number" name="rules2[]" value="" placeholder="Greater Mark"/></td><td><input class="" type="number" name="point[]" value="" placeholder="Grade point"/></td><td><input class="bold" type="text" name="grade[]" value="" placeholder="Grade"/></td><td><a href="javascript:void(0);" class="remove_button"><i class="dashicons dashicons-no"></i></a></td></tr>'
		}, options);

		// Make it simple!
		var s = settings;

		var showSub = $('.showSubfield').html();
		var cloneHtml = $(s.wrapper).html();
		var cloneField = $(s.cloneBody + ' ' + s.cloneField).length;
		var counter;

		// Check auto counter settings, manual or auto
		if (s.autoCunter === true) {
			counter = cloneField;
		} else {
			counter = s.counter;
		}

		/**
		* Minimum field settings
		* Disable remove button if field is equal and less than minimum field
		* @since: v1.0
		* @param int arg, for compare with minimum field
		* @return class sync/add or removed class
		*/
		function minField(arg) {

			var removeBtn = $(s.removeButton);

			if (arg <= s.minField) {
				removeBtn.removeClass('yes').addClass('disabled');
			} else {
				removeBtn.removeClass('disabled').addClass('yes');
			}

		}

		// Set minimum field on load
		minField(counter);

		/**
		* Support short key (#max, #count) for message.
		* This way, user can easily customize there msgs.
		* function use for this futures - replace().
		* It will replace #max to s.maxField,
		 * And #count to var counter.
		* @since: v1.0
		* @param object||str arg, for display field status
		* @return replaced str
		*/
		function msgs(arg) {
			var msgs = arg;
			// add #max short key
			msgs = msgs.replace(/#max/g, s.maxField);
			// add #count short key
			msgs = msgs.replace(/#count/g, counter);
			msgs = $(s.status).html(msgs);
			return msgs;
		}

		// Display clone/copied field status/msgs on load
		msgs(s.countMsgs);

		/**
		* Function for open sub field Immediately, when add a field
		* If quick sub field is true, it's open sub field at a same time when main field is added.
		* @since: v1.0
		*/
		function quickSubfield() {
			$(s.cloneBody + ' ' + s.cloneField + ':last').hide().slideDown('fast', function () {
					if (s.quickSubfield === true) {
						$(this).find(s.subField).slideDown('fast');
						$(this).find(s.showSubfield).html(s.hideSubfield).addClass('hide');
					}
				}
			);
		}

		// Set quick subfield on load
		quickSubfield();

		/*
		===================================
		ADD, REMOVE AND SHOW/HIDE SUB FIELD
		===================================
		*/

		// Once add button is clicked
		$(s.addButton).click(function () {

			//Check maximum number of input fields
			if (counter < s.maxField) {
				// Increment field counter
				counter++;
				//Add field html
				$(s.cloneBody).append(s.copyData);

				quickSubfield();
				minField(counter);
				msgs(s.countMsgs);
				$(this).removeClass('disabled');
			} else {
				msgs(s.maxMsgs);
				$(this).addClass('disabled');
				$(s.status).addClass(s.maxClass);
			}

		});

		// Once remove button is clicked
		$(s.cloneBody).on('click', s.removeButton + '.yes', function (e) {
			e.preventDefault();

			// Remove field html
			$(this).parents(s.cloneField).fadeOut('fast', function () {
				$(this).remove();
			});

			// Decrement field counter
			counter--;
			minField(counter);
			msgs(s.countMsgs);
			$(s.addButton).removeClass('disabled');
			$(s.status).removeClass(s.maxClass);

		});

		// Show/Hide sub menu button is clicked
		$(s.cloneBody).on('click', s.showSubfield, function (event) {
			event.preventDefault();

			// select parent elements
			var cloneField = $(this).parents(s.cloneField);
			// find/search current s.subField for show/hide
			var subField = cloneField.find(s.subField);
			// hide or show subField
			$(this).toggleClass('hide');

			// slice(1) Remove (.) from s.showSubfield
			if ($(this).attr('class') == s.showSubfield.slice(1)) {
				// Change icon
				$(this).html(showSub);
				// show subField
				subField.slideUp('fast');
			} else {
				$(this).html(s.hideSubfield);
				// hide subField
				subField.slideDown();
			}

		});

		// Close main function
	}

	// define function as cloneField()
	cloneField = $.fn.cloneField;

}(jQuery));

