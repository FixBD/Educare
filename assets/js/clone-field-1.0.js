/**
== Clone Field ==
*
* Contributors: fixbd
* GitHub link: https://github.com/fixbd/clone-field/assets/clone-field-1.0.js
* Tags: jQuery field, jQuery form field copy on click, Clone input field
* Category: jQuery plugins
* Required: jQuery
* Tested up to: jQuery v-2.1.3
* Stable tag: 1.0
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html
*
*****************************************************************
*****************************************************************
*
* Usage =>

cloneField({
	// options => value
	countMsgs: 'Currently you are add #count field of #max',
	maxMsgs: 'Sorry, You can not add more than #max field. Please remove one then try!',
	maxField: 10
});


<!-- ==Clone start== /clone one or more field -->
    <div class='cloneField'>
      <!-- Main menu -->
      <div class='mainField'>
            <input type="text" name="player[]" value="" placeholder=""/>
        <!-- Show/Hide Sub menu -->
        <a class='showSubfield'><i class='dashicons dashicons-ellipsis'></i></a>
        <!-- Remove cloned menu -->
        <a href="javascript:void(0);" class="remove_button"><i class="dashicons dashicons-no"></i></a>
      </div>
      <!-- Sub menu -->
      <div class='subField'>
        <b>Details</b>
        Select Category:
        
        Player Age:
        <input type='number' name='age[]' value='' placeholder=''/>
        Number:
        <input type='number' name='mobile[]' value='' placeholder=''/>
      </div>
    </div>
    <!-- ==Clone end== -->
    <?php
  }

  $grade_system = array(
    '80-100' => 'A+',
    '70-79'  => 'A',
    '60-69'  => 'A-',
    '50-59'  => 'B',
    '40-49'  => 'c',
    '33-39'  => 'D',
    '0-32'  => 'F'
  );
  
  ?>
  <form id='addForm'>
    <div class='fixbd_cloneField'>
      <h2>Players</h2>
      <p id='status' class='warning sticky'></p>
      
      <div id='cloneBody'>
        <?php
        foreach ( $grade_system as $name => $details ) {
          crickup_get_player_formx($name, $details);
        }
        ?>
      </div>
      
      <a href='javascript:void(0);' class='addButton' title='Add more field'><i class='dashicons dashicons-plus-alt'></i></a>
    </div>

    <button id='save_addForm'>Add</button>
  </form>
  
  
  <div id='cloneWrapper' style='display: none;'>
    <?php crickup_get_player_formx();?>
  </div>
  
  <script type="text/javascript">cloneField()</script>

*/

(function( $ ) {
	
	$.fn.cloneField = function(options) {
		
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
			maxClass: 'field_over'						// Add specific class when field is over
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
			$(s.cloneBody + ' ' + s.cloneField + ':last').hide()
			// Open append field with effect
			.slideDown('fast', function() {
				if (s.quickSubfield === true) {
					$(this).find(s.subField).slideDown('fast');
					$(this).find(s.showSubfield).html(s.hideSubfield).addClass('hide');
				}
			});
		}
		
		// Set quick subfield on load
		quickSubfield();
	    
		/***************************************************************************
								ADD, REMOVE AND SHOW/HIDE SUB FIELD
		***************************************************************************/
		
	    // Once add button is clicked
	    $(s.addButton).click(function() {
		
	        //Check maximum number of input fields
	        if(counter < s.maxField) {
	        	// Increment field counter
	            counter++;
	            //Add field html
	            $(s.cloneBody).append('<tr class="cloneField"><td><input type="number" name="rulse1[]" value="" placeholder="Less Mark"/></td><td><input type="number" name="rulse2[]" value="" placeholder="Greater Mark"/></td><td><input class="" type="number" name="point[]" value="" placeholder="Grade point"/></td><td><input class="bold" type="text" name="grade[]" value="" placeholder="Grade"/></td><td><a href="javascript:void(0);" class="remove_button"><i class="dashicons dashicons-no"></i></a></td></tr>');
				
				/*
				$(s.cloneBody + ' ' + s.cloneField + ':last')
				.find('input, select') .each(function () { 
					$(this).val('');
				});
				*/
				
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
	    $(s.cloneBody).on('click', s.removeButton + '.yes', function(e) {
	        e.preventDefault();
			
	        // Remove field html
	        $(this).parents(s.cloneField).fadeOut('fast', function() {
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
		$(s.cloneBody).on('click', s.showSubfield, function(event) {
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
	
}( jQuery ));

