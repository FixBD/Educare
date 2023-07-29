/**
 * Educare functionality
 *
 * Autor: FixBD
 * Autor Link: https://github.com/fixbd
 * Source: https://github.com/fixbd/educare/assets/js/educare.js
 *
 */

jQuery(document).ready(function($) {
    // settings functionality
    function educareSettingsPage() {
        $(document).on("click", "[name=educare_update_settings_status], [name=educare_reset_default_settings]", function(event) {
            event.preventDefault();
            // var currenTab = $(".head[name=subject]:checked").attr("id");
            var current = $(this);
            var form_data = $(this).parent('form').serialize();
            var action_for = $(this).attr("name");
            var active_menu = $('.head:checked').attr('id');
            $.ajax({
                url: educareAjax.url,
                type: 'POST',
                data: {
                action: 'educare_process_content',
                nonce: educareAjax.nonce,
                form_data: form_data,
                active_menu: active_menu,
                action_for
                },
                beforeSend:function(event) {
                if (action_for == 'educare_reset_default_settings') {
                    if (educareSettings.confirmation == 'checked') {
                    return confirm("Are you sure to reset default settings? This will not effect your content (Class, Subject, Exam, Year, Extra Field), Its only reset your current settings status and value.");
                    }
                } else {
                    $('#educare-loading').fadeIn();
                }
                current.children('.dashicons').addClass('educare-loader');
                },
                success: function(data) {
                $('#educare-data').html(data);
                },
                error: function(data) {
                $('#educare-data').html(educareSettings.db_error);
                },
                complete: function() {
                $('#educare-loading').fadeOut();
                current.children('.dashicons').removeClass('educare-loader');
                // event.remove();
                },
            });
        });
        
        // =========== Script for Grading System Page ===========
        // Edit button
        var result_msg_data = false;

        $(document).on("click", "#edit_grade", function() {
            $(this).attr('disabled', true);
            var class_name = $('#grading').val();
            result_msg_data = $('#result_msg').html();

            $.ajax({
                url: educareAjax.url,
                type: 'POST',
                data: {
                action: 'educare_proccess_grade_system',
                nonce: educareAjax.nonce,
                class: class_name
                },
                beforeSend:function(event) {
                $('#educare-loading').fadeIn();
                },
                success: function(data) {
                // $('#result_msg').hide();
                $('#result_msg').html(data).fadeIn();
                $('#update_button').fadeOut();
                $('#edit_grade').attr('disabled', false);
                },
                error: function(data) {
                $('#result_msg').html(educareSettings.db_error);
                },
                complete: function() {
                $('#educare-loading').fadeOut();
                }
            });
        });

        // Update buttton
        $(document).on("click", "#save_addForm", function() {
            $(this).attr('disabled', true);
            var form_data = $(this).parents('form').serialize();

            $.ajax({
                url: educareAjax.url,
                type: 'POST',
                data: {
                action: 'educare_save_grade_system',
                nonce: educareAjax.nonce,
                form_data: form_data,
                update_grade_rules: true
                },
                beforeSend:function(event) {
                $('#educare-loading').fadeIn();
                },
                success: function(data) {
                $('#result_msg').hide();
                $('#result_msg').html(data).fadeIn();
                $('#update_button').fadeIn();
                $('#edit_grade').attr('disabled', false);
                },
                error: function(data) {
                $('#result_msg').html(educareSettings.db_error);
                },
                complete: function() {
                $('#educare-loading').fadeOut();
                }
            });
        });
        
        $(document).on("click", "#help", function() {
            $(this).css('color', 'green');
            $("#show_help").slideToggle();
        });

        $(document).on("click", ".notice-dismiss", function(event) {
            $(this).parent('div').fadeOut();
            $('#result_msg').hide().html(result_msg_data).fadeIn();
            $('#update_button').fadeIn();
        });

        // Default roll and regi no checked term
        $(document).on("click", ".collapse-content input[name='Roll_No']", function() {
            if ($(this).val() == 'checked') {
                $('#Regi_No_no').attr("disabled",false);
            }
            else {
                $("input[name='Regi_No']").prop("checked", true);
            }
        });

        $(document).on("click", ".collapse-content input[name='Regi_No']", function() {
            if ($(this).val() == 'checked') {
                $('#Roll_No_no').attr("disabled",false);
            }
            else {
                $("input[name='Roll_No']").prop("checked", true);
            }
        });

        
    }
    // settings functionality callback
    educareSettingsPage();

    // settings functionality
    function educarePerformancePage() {
        $(document).on("click", "#promote", function(event) {
            event.preventDefault();
            var current = $(this);
            var form_data = $(this).parents('form').serialize();
            // alert('Ok');
            $.ajax({
                url: educareAjax.url,
                type: 'POST',
                data: {
                action: 'educare_proccess_promote_students',
                nonce: educareAjax.nonce,
                form_data: form_data
                },
                beforeSend: function(data) {
                $('#educare-loading').fadeIn();
                },
                success: function(data) {
                $('#promote_msgs').html(data);
                },
                error: function(data) {
                $('#educare-loading').fadeOut();
                $('#promote_msgs').html(educareSettings.db_error);
                },
                complete: function() {
                $('#educare-loading').fadeOut();
                // do some
                },
            });
        });
    }
    // settings functionality callback
    educarePerformancePage();

    // settings functionality
    function educareFilesSelectorPage() {
        // Uploading files
        var file_frame;
        var wp_media_post_id = 0; // Store the old id
        var educare_media_post_id = ''; // Set this
        // default value
        var educareFileSelector_educare_attachment_id = $('.educareFileSelector_educare_attachment_id').data('value');
        var educareFileSelector_img = $('.educareFileSelector_img').data('value');
        var educareFileSelector_img_type = $('.educareFileSelector_img_type').data('value');
        var educareFileSelector_guide = $('.educareFileSelector_guide').data('value');
        var educareFileSelector_default_img = $('.educareFileSelector_default_img').data('value');

        $(document).on("click", "#educare_upload_button", function(event) {
            event.preventDefault();
            // not important!!
            // If the media frame already exists, reopen it.
            if ( file_frame ) {
                // Set the post ID to what we want
                file_frame.uploader.uploader.param( 'post_id', educare_media_post_id );
                // Open frame
                file_frame.open();
                return;
            } else {
                // Set the wp.media post id so the uploader grabs the ID we want when initialised
                // wp.media.model.settings.post.id = educare_media_post_id;
            }

            // Create the media frame.
            file_frame = wp.media.frames.file_frame = wp.media({
                title: 'Select Students Photos',
                button: {
                    text: 'Use this image',
                },
                multiple: false // Set to true to allow multiple files to be selected
            });

            // When an image is selected, run a callback.
            file_frame.on( 'select', function() {
                // We set multiple to false so only get one image from the uploader
                attachment = file_frame.state().get('selection').first().toJSON();
                // Do something with attachment.id and/or attachment.url here
                // $( '#educare_attachment_preview' ).attr( 'src', attachment.url ).css( 'width', '100px' );
                $( '#educare_attachment_preview' ).attr( 'src', attachment.url );
                $( '#educare_upload_button' ).val( 'Edit Photos' );
                $( '#educare_attachment_clean' ).css( 'display', 'block' );
                $("#educare_img_type").html('Custom photos');
                $("#educare_guide").html('Please click edit button for change carently selected photos or click close/clean button for default photos');
                $( '#educare_attachment_id' ).val( attachment.id );
                $( '#educare_attachment_url' ).val( attachment.url );
                $( '#educare_attachment_title' ).val( attachment.title ).attr( 'value', this.val );
                // Restore the main post ID
                wp.media.model.settings.post.id = wp_media_post_id;
            });

            // Finally, open the modal
            file_frame.open();
        });

        // Restore the main ID when the add media button is pressed
        $( 'a.add_media' ).on( 'click', function() {
            wp.media.model.settings.post.id = wp_media_post_id;
        });

        // clean files/photos
        $(document).on("click", "input.educare_clean", function() {
            $("#educare_attachment_url").val(educareFileSelector_img);
            $("#educare_attachment_id").val(educareFileSelector_educare_attachment_id);
            $("#educare_attachment_preview").attr("src", educareFileSelector_img);
            $("input.educare_clean").css('display', 'none');
            $( '#educare_attachment_title' ).val('Cleaned! please select onother one');
            $( '#educare_upload_button' ).val( 'Upload photos again' );
            $("#educare_img_type").html(educareFileSelector_img_type);
            $("#educare_guide").html(educareFileSelector_guide);
            $("#educare_attachment_default").css("display", "block");
        });
    
        // set default photos
        $(document).on("click", "#educare_attachment_default", function() {
            $('#educare_attachment_url').val(educareFileSelector_default_img);
            $("#educare_attachment_id").val("");
            $("#educare_attachment_preview").attr("src", educareFileSelector_default_img);
            $("#educare_attachment_clean").css("display", "block");
            $(this).css("display", "none");
            $("#educare_attachment_title").val('Successfully set default photos!');
        });

        // disabled photos
        var photos = educareSettings.photos;
        if (photos == 'disabled') {
            $('#educare_default_help').innerHTML = 'Currently students photos are disabled. If you upload or display student photos, first check/enable students photos from the settings sections';
            $('#educare_upload_button').attr('disabled', 'disabled');
            $('#educare_attachment_default').attr('disabled', 'disabled');
            $('#educare_files_selector_disabled').className = 'educare_files_selector_disabled';
            $('#educare_upload_button').attr('disabled', 'disabled');
            $('#educare_default_photos').attr('disabled', 'disabled');
            $('#educare_attachment_clean').style.display= 'none';
        }
    }
    // settings functionality callback
    educareFilesSelectorPage();

    // settings functionality
    function educareProcessMarksPage() {
        $(document).on("change", "#Class, #Group", function(event) {
            event.preventDefault();
            var current = $(this);
            var form_data = $(this).parents('form').serialize();
            var action_for = "get_" + $(this).attr("name");
            $.ajax({
                url: educareAjax.url,
                type: 'POST',
                data: {
                    action: 'educare_process_marks',
                    nonce: educareAjax.nonce,
                    form_data: form_data,
                    action_for: action_for
                },
                beforeSend: function(data) {
                    $('#educare-loading').fadeIn();
                    $('#Subject').html('<option value="">Loading Subject</option>');
                },
                success: function(data) {
                    if ($.trim(data)) { 
                        $('#Subject').html(data);
                    } else {
                        $('#Subject').html('<option value="">Subject Not Found</option>');
                    }
                },
                error: function(data) {
                    $('#educare-loading').fadeOut();
                    $('#Subject').html('<option value="">Loading Error</option>');
                },
                complete: function() {
                    $('#educare-loading').fadeOut();
                    // do some
                },
            });
        });

        $(document).on("click", ".educareProcessMarksCrud [type=submit]", function(event) {
            event.preventDefault();
            var current = $(this);
            var form_data = $(this).parents('form').serialize();
            var action_for = $(this).attr("name");
            $.ajax({
                url: educareAjax.url,
                type: 'POST',
                data: {
                    action: 'educare_process_marks',
                    nonce: educareAjax.nonce,
                    form_data: form_data,
                    action_for
                },
                beforeSend: function(data) {
                    $('#educare-loading').fadeIn();
                },
                success: function(data) {
                    $('#msgs').html(data);
                },
                error: function(data) {
                    $('#educare-loading').fadeOut();
                    $('#msgs').html(educareSettings.db_error);
                },
                complete: function() {
                    $('#educare-loading').fadeOut();
                    // event.remove();
                },
            });
        });

        $(document).on("click", ".notice-dismiss", function(event) {
            event.preventDefault();
            $(this).parent('div').fadeOut();
            $('#update_button').fadeIn();
        });

        $(document).on("click", "#print", function(event) {
            event.preventDefault();

            var content = $('.educare_print').html();
            var headerContent = '<style>body {padding: 4%;} .view_results {width: 100%;} th:nth-child(2), td:nth-child(2), button {display: none;} thead {background-color: #00ac4e !important; color: white !important; -webkit-print-color-adjust: exact;} table, td, th {border: 1px solid black; text-align: left; padding: 8px; border-collapse: collapse;} input {border: none;}</style>';
            var realContent = document.body.innerHTML;
            var mywindow = window.open();
            mywindow.document.write(headerContent + content);
            mywindow.document.title = "Marksheed";
            mywindow.document.close(); // necessary for IE >= 10
            mywindow.focus(); // necessary for IE >= 10*/
            mywindow.print();
            document.body.innerHTML = realContent;
            mywindow.close();
            return true;
        });

        $(document).on("click", ".notice-dismiss", function(event) {
            $(this).parent('div').fadeOut();
            $('#update_button').fadeIn();
        });
    }
    // settings functionality callback
    educareProcessMarksPage();

    // settings functionality
    function educareDataManagementPage() {
        // default value
        var educareDataManagement_url = $('.educareDataManagement_url').data('value');
        var educareDataManagement_students = $('.educareDataManagement_students').data('value');
        var educareDataManagement_tab = $('.educareDataManagement_tab').data('value');

        $(document).on("click", ".students .tablinks", function(event) {
            event.preventDefault();
            tablinks = $(".tablinks");

            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace("active", "");
            }

            // var currenTab = $(".head[name=subject]:checked").attr("id");
            var current = $(this);
            current.addClass('active');
            // $(current).css('color', 'red');
            var form_data = current.attr('data');
            
            $.ajax({
                url: educareAjax.url,
                type: 'GET',
                data: {
                    action: 'educare_process_data',
                    form_data: form_data,
                    action_for: educareDataManagement_students
                },
                beforeSend:function() {
                    // $('#' + form_data).html("<center>Loading</center>");
                    $('#educare-loading').fadeIn();
                },
                success: function(data) {
                    // window.history.pushState('', form_data, window.location.href + '&' + form_data);
                    history.pushState('', 'form_data', educareDataManagement_url + '&' + form_data);
                    $('#educare-data').html(data);
                },
                error: function(data) {
                    $('#educare-data').html(educareSettings.db_error);
                },
                complete: function() {
                    // event.remove();
                    $('#educare-loading').fadeOut();
                },
            });
            
        });

        $(".students .active").removeClass('active');
        $(".students [data=" + educareDataManagement_tab + "]").addClass('active');
    }
    // DataManagemen functionality callback
    educareDataManagementPage();

    // DataManagemen options by ajax functionality
    function educareOptionsByAjaxPage() {
        // replacement to educare_options_by_ajax();
        var educareLoading = $('#educare-loading');
        var connectionsError = '<div class="notice notice-error is-dismissible"><p>Sorry, (database) connections error!</p></div>';

        var target = "Group";
        var students_data = $('.educareDataManagement_students_data').data('value');
        // var add_students = "<?php //echo esc_js($add_students)?>";
        var add_students = students_data;
        
        function changeClass(currentData) {
            var class_name = $('#Class').val();
            var id_no = $('#id_no').val();
            var form_data = $(currentData).parents('form').serialize();

            $.ajax({
                url: educareAjax.url,
                type: 'POST',
                data: {
                    action: 'educare_class',
                    nonce: educareAjax.nonce,
                    class: class_name,
                    id: id_no,
                    form_data: form_data,
                    add_students: add_students,
                },
                beforeSend: function(data) {
                    educareLoading.fadeIn();
                    // educare_crud.prop('disabled', true);
                    $('#sub_msgs').html('<div class="notice notice-success is-dismissible"><p>Loading Subject</b></p></div>');
                },
                success: function(data) {
                    $('#result_msg').html(data);
                    $('#Class').attr('disabled', false);
                    $('#sub_msgs').html('<div class="notice notice-error is-dismissible"><p>Please select the group. If this class has a group, then select group. otherwise ignore it.</p></div>');
                },
                error: function(data) {
                    $('#result_msg').html('<div class="notice notice-error is-dismissible"><p>Sorry, database connection error!</p></div>');
                },
                complete: function() {
                    educareLoading.fadeOut();
                    educare_crud.prop('disabled', false);
                }
            });
        }

        // select optional subject
        function educareOptional() {
            var optional = $('#optional_subject').val();
            var subValue = $('#' + optional).val();

            $('#optional').val(1 + ' ' + subValue).attr('name', optional);
        }

        $(document).on("change", "#optional_subject", function() {
            educareOptional();
        });
        $(document).on("click", ".educare_button.educare_crud", function() {
            educareOptional();
        });


        function educareGroupSub(action_for, currentData) {
            var educare_crud = $('.educare_crud');

            if (action_for) {
                $.ajax({
                    url: educareAjax.url,
                    type: 'POST',
                    data: {
                        action: 'educare_process_options_by',
                        data_for: action_for,
                        // subject: 'Science'
                    },
                    beforeSend: function(data) {
                        educareLoading.fadeIn();
                        educare_crud.prop('disabled', true);
                        $('#sub_msgs').html('<div class="notice notice-success is-dismissible"><p>Loading Subject</b></p></div>');
                    },
                    success: function(data) {
                        var closeSub = "<input type='submit' id='" + target + "_close_subject' class='educare_button' value='&#xf158'>";

                        if ($.trim(data)) { 
                            var add_subject = "<div class='button-container'><input type='submit' id='" + target + "_add_subject' class='educare_button' value='&#xf502'>" + closeSub + "</div>";
                            $('#' + target + '_list').html(data);
                            $("#add_to_button").html(add_subject);
                            $('#sub_msgs').html('');
                        } else {
                            $('#' + target + '_list').html('');

                            $('#sub_msgs').html('<div class="notice notice-error is-dismissible"><p>Sorry, subject not found in this <b>('+action_for+')</b> group. <a href="/wp-admin/admin.php?page=educare-management&Group&Group_' + action_for + '" target="_blank">Click here</a> to add subject</b></p></div>');
                            $("#add_to_button").html(closeSub);
                        }
                    },
                    error: function(data) {
                        $('#sub_msgs').html(connectionsError);
                    },
                    complete: function() {
                        educareLoading.fadeOut();
                        // do some
                        // educare_crud.prop('disabled', false);
                    },
                });
            } else {
                changeClass(currentData);
            }
        }

        $(document).on("change", "#crud-forms #Class", function(event) {
            event.preventDefault();
            currentData = $(this);
            changeClass(currentData);
        });

        $(document).on("change", "#"  + target, function(event) {
            event.preventDefault();
            // var current = $(this);
            var action_for = $(this).val();
            educareGroupSub(action_for, this);
        });

        $(document).on("click", "#edit_add_subject", function(event) {
            event.preventDefault();
            var action_for = $('#Group').val();
            educareGroupSub(action_for, this);
        });

        function checkGroup() {
            var numberOfChecked = $("[name|='select_subject[]']:checked").length;
            var group_subject = educareSettings.group_subject;

            var changeLink = 'You can change this group wise requred subject from <code>Educare Settings > Results System > Group Subject</code>. <a href="/wp-admin/admin.php?page=educare-settings" target="_blank">Click here</a> to change';
            
            if (group_subject == 0 || !group_subject) {
                return true;
            } else if (numberOfChecked == false) {
                $('#sub_msgs').html('<div class="notice notice-error is-dismissible"><p>Please choice subject to add</b></p></div>');
                return false;
            } else if(numberOfChecked < group_subject) {
                $('#sub_msgs').html('<div class="notice notice-error is-dismissible"><p>Please select minimum <b>(' + group_subject + ')</b> subject. ' + changeLink + '</p></div>');
                return false;
            } else if (numberOfChecked > group_subject) {
                $('#sub_msgs').html('<div class="notice notice-error is-dismissible"><p>Sorry, you are trying to add miximum number of subject! Please select only requred <b>(' + group_subject + ')</b> subject. ' + changeLink + '</p></div>');
                return false;
            } else {
                return true;
            }

        }

        // when trying to add (group) subject into the subject list
        $(document).on("click", "#" + target + "_add_subject", function(event) {
            event.preventDefault();
            var class_name = $('#Class').val();
            var id_no = $('#id_no').val();
            var form_data = $(this).parents('form').serialize();

            if (checkGroup() === true) {
                $.ajax({
                    url: educareAjax.url,
                    type: 'POST',
                    data: {
                    action: 'educare_class',
                    nonce: educareAjax.nonce,
                    class: class_name,
                    id: id_no,
                    form_data: form_data,
                    add_students: add_students,
                },
                beforeSend: function(data) {
                    educareLoading.fadeIn();
                    $('#sub_msgs').html('<div class="notice notice-success is-dismissible"><p>Addeting Subject</b></p></div>');
                },
                success: function(data) {
                    $('#result_msg').html(data);
                    $('#Class').attr('disabled', false);
                },
                error: function(data) {
                    $('#result_msg').html(connectionsError);
                },
                complete: function() {
                    educareLoading.fadeOut();
                    $('.educare_crud').prop('disabled', false);
                }
            });

            } else {
                checkGroup(currentData);
            }
        });

        // when click close button
        $(document).on("click", "#" + target + "_close_subject", function(event) {
            event.preventDefault();
            var class_name = $('#' + target + '_list').empty();
            $('#sub_msgs').empty();
            $('#add_to_button').html("<div id='edit_add_subject' class='educare_button'><i class='dashicons dashicons-edit'></i></div>");

            var oldGroup = $('#old-Group').val();
            
            $('#Group').val(oldGroup);
            $('.educare_crud').prop('disabled', false);
        });


        // import data from students
        $(document).on("click", "#data_from_students", function(event) {
            // event.preventDefault();
            var current = $(this);
            var form_data = $(this).parents('form').serialize();
            // alert('Ok');
            $.ajax({
                url: educareAjax.url,
                type: 'POST',
                data: {
                    action: 'educare_get_data_from_students',
                    nonce: educareAjax.nonce,
                    form_data: form_data
                },
                beforeSend: function(data) {
                    $('#educare-loading').fadeIn();
                },
                success: function(data) {
                    $('#educare-form').html(data);
                },
                error: function(data) {
                    $('#educare-loading').fadeOut();
                    alert('Error');
                },
                complete: function() {
                    $('#educare-loading').fadeOut();
                    // do some
                },
            });
        });
    }
    // Educare options by ajax functionality callback
    educareOptionsByAjaxPage();

    // eTabManagement functionality
    function educareTabManagementPage() {
        var educareTabManagement_url = $('.educareTabManagement_url').data('value');
        var educareTabManagement_action_for = $('.educareTabManagement_action_for').data('value');
        var educareTabManagement_menu = $('.educareTabManagement_menu').data('value');
        var educareTabManagement_active_tab = $('.educareTabManagement_active_tab').data('value');

        $(document).on("click", ".tab_management .tablinks", function(event) {
            event.preventDefault();
            
            tablinks = $(".tablinks");

            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace("active", "");
            }

            var current = $(this);
            current.addClass('active');
            var tab = current.attr('id');
            
            $.ajax({
                url: educareAjax.url,
                data: {
                    action: 'educare_process_tab',
                    tab: tab,
                    action_for: educareTabManagement_action_for
                },
                type: 'POST',
                beforeSend:function() {
                    $('#educare-loading').fadeIn();
                },
                success: function(data) {
                    history.pushState('', 'tab', educareTabManagement_url + '&' + tab);

                    $('#educare-loading').fadeOut();
                    $('#educare-data').html(data);
                },
                error: function(data) {
                    $('#educare-data').html(educareSettings.db_error);
                },
                complete: function() {
                    $('#educare-loading').fadeOut();
                },
            });
            
        });

        if (educareTabManagement_active_tab) {
            $(".tab_management .active").removeClass('active');
            $(".tab_management #" + educareTabManagement_active_tab).addClass('active');
        }

        if (educareTabManagement_menu) {
            $('#' + educareTabManagement_menu + '_menu').prop("checked", true);
        }
    }
    // eTabManagement functionality callback
    educareTabManagementPage();

    // ProcessContent functionality
    function educareProcessContentPage() {
        // Function for Class and Group
        $(document).on("click", ".proccess_Class, .proccess_Group", function(event) {

            event.preventDefault();
            var current = $(this);
            var form_data = $(this).parents('form').serialize();
            // alert(form_data);
            var action_for = $(this).attr("name");
            // alert(action_for);
            var action_data = $(this).attr("class");
            var msgs = '#msg_for_Class';

            if (action_data.indexOf('proccess_Group') > -1) {
                msgs = '#msg_for_Group';
            }

            $.ajax({
                url: educareAjax.url,
                type: 'POST',
                data: {
                    action: 'educare_process_content',
                    nonce: educareAjax.nonce,
                    form_data: form_data,
                    action_for
                },
                beforeSend:function(event) {
                    current.children('.dashicons').addClass('educare-loader');
                    if (action_for == 'remove_class' || action_for == 'remove_subject') {
                        if (action_for == 'remove_class') {
                            var target = $(current).prevAll("[name='class']").val();
                        } else {
                            var target = $(current).prevAll("[name='subject']").val();
                        }
                        
                        if (educareSettings.confirmation == 'checked') {
                            return confirm("Are you sure to remove (" + target + ") from this list?");
                        }
                    } else {
                        $('#educare-loading').fadeIn();
                    }
                },
                success: function(data) {
                    $(msgs).html(data);
                },
                error: function(data) {
                    $(msgs).html(educareSettings.db_error);
                },
                complete: function() {
                    $('#educare-loading').fadeOut();
                    current.children('.dashicons').removeClass('educare-loader');
                    // event.remove();
                },
            });
                
        });

        
        // management add class or group form tab
        $(document).on("click", ".form_tab .tablink", function(event) {
            event.preventDefault();
            var i, allTab, tablinks;
            var crntButton = $(this);
            tablinks = $(this).attr('data');
            var educareTabs = $(this).parents('.educare_tabs');
            // remove active class
            allButton = $(this).siblings(".tablink").removeClass('educare_button');
            allTab = educareTabs.children(".section_name");

            allTab.each(function() {
                var crntTabs = $(this).attr('id');
                if (crntTabs == tablinks) {
                    $(this).css('display', 'block');
                    // add active class
                    crntButton.addClass('educare_button');
                } else {
                    $(this).css('display', 'none');
                }
            });

        });

        var list = $('.educareSettingSubForm').data('value');
        // Auto select class or group in select box
        $(document).on("click", ".collapse [name="+list+"]", function() {
            $("#add_"+list).val($(this).attr("data"));
        });
    }
    // ProcessContent functionality callback
    educareProcessContentPage();

    // AjaxContent functionality
    function educareAjaxContentPage($list) {
        var educareLoading = $('#educare-loading');
        var $list_button = $list.replace(/_/g, '');

        $(document).on("click", "#educare_add_" + $list, function(event) {
            event.preventDefault();
            // $(this).attr('disabled', true);
            var current = $(this);
            var form_data = $(this).parents('form').serialize();
            var action_for = "educare_add_" + $list;
            $.ajax({
                url: educareAjax.url,
                type: 'POST',
                data: {
                    action: 'educare_process_content',
                    nonce: educareAjax.nonce,
                    form_data: form_data,
                    action_for
                },
                beforeSend:function(event) {
                    educareLoading.fadeIn();
                    current.children('.dashicons').addClass('educare-loader');
                },
                success: function(data) {
                    $("#msg_for_" + $list).html(data);
                    $("#educare_add_" + $list).attr('disabled', false);
                },
                error: function(data) {
                    educareLoading.fadeOut();
                    $("#msg_for_" + $list).html(educareSettings.db_error);
                },
                complete: function() {
                    // event.remove();
                    educareLoading.fadeOut();
                    current.children('.dashicons').removeClass('educare-loader');
                },
            });
            
        });

        $(document).on("click", "input.remove" + $list_button, function(event) {
            // $(this).attr('disabled', true);
            event.preventDefault();
            var form_data = $(this).parents('form').serialize();
            var target = $(this).prevAll("[name='remove']").val();
            var action_for = "remove_" + $list;
            $.ajax({
                url: educareAjax.url,
                type: 'POST',
                data: {
                    action: 'educare_process_content',
                    nonce: educareAjax.nonce,
                    form_data: form_data,
                    action_for
                },
                beforeSend:function() {
                    if (educareSettings.confirmation == 'checked') {
                        return confirm("Are you sure to remove (" + target + ") from this "+ $list.replace(/_/g, ' ') +" list?");
                    }
                },
                success: function(data) {
                    $("#msg_for_" + $list).html(data);
                },
                error: function(data) {
                    $("#msg_for_" + $list).html(educareSettings.db_error);
                },
            });
        });

        
        $(document).on("click", "input.edit" + $list_button, function(event) {
            // $(this).attr('disabled', true);
            event.preventDefault();
            var form_data = $(this).parents('form').serialize();
            var action_for = "educare_edit_" + $list;
            $.ajax({
                url: educareAjax.url,
                type: 'POST',
                data: {
                    action: 'educare_process_content',
                    nonce: educareAjax.nonce,
                    form_data: form_data,
                    action_for
                },
                beforeSend:function(event) {
                    educareLoading.fadeIn();
                },
                success: function(data) {
                    $("#msg_for_" + $list).html(data);
                },
                error: function(data) {
                    educareLoading.fadeOut();
                    $("#msg_for_" + $list).html(educareSettings.db_error);
                },
                complete: function() {
                    // event.remove();
                    educareLoading.fadeOut();
                },
            });
        });


        $(document).on("click", "input.update" + $list_button, function(event) {
            // $(this).attr('disabled', true);
            event.preventDefault();
            var form_data = $(this).parents('form').serialize();
            var action_for = "educare_update_" + $list;
            $.ajax({
                url: educareAjax.url,
                type: 'POST',
                data: {
                    action: 'educare_process_content',
                    nonce: educareAjax.nonce,
                    form_data: form_data,
                    action_for
                },
                success: function(data) {
                    $("#msg_for_" + $list).html(data);
                },
                error: function(data) {
                    educareLoading.fadeOut();
                    $("#msg_for_" + $list).html(educareSettings.db_error);
                },
                complete: function() {
                    // event.remove();
                    educareLoading.fadeOut();
                },
            });
        });

        $(document).on("click", ".notice-dismiss", function(event) {
            $(this).parent('div').fadeOut();
        });
    }
    // AjaxContent functionality callback
    educareAjaxContentPage('Class');
    educareAjaxContentPage('Group');
    educareAjaxContentPage('Exam');
    educareAjaxContentPage('Year');
    educareAjaxContentPage('Extra_field');

    // ImportDemo functionality
    function educareImportDemoPage() {
        $(document).on("change", ".demo #Class", function(event) {
            event.preventDefault();
            $(this).attr('disabled', true);
            var educareLoading = $('#educare-loading');
            var class_name = $('#Class').val();
            var total_demo = $('#total_demo').val();
            var students = $('.educareImportDemo_students').data('value');

            $.ajax({
                url: educareAjax.url,
                type: 'POST',
                data: {
                    action: 'educare_demo',
                    nonce: educareAjax.nonce,
                    Class: class_name,
                    total_demo: total_demo,
                    data_for: students,
                },
                beforeSend:function(event) {
                    educareLoading.fadeIn();
                },
                success: function(data) {
                    $('#result_msg').html(data);
                    $('#Class').attr('disabled', false);
                },
                error: function(data) {
                    $('#result_msg').html(educareSettings.db_error);
                },
                complete: function() {
                    educareLoading.fadeOut();
                },
            });
        });
    }
    // ImportDemo functionality callback
    educareImportDemoPage();

    // AllView functionality
    function educareAllViewPage() {
        // action button togle (view, edit, delete button)
        $(document).on("click", ".action_button", function() {
            $(this).parent('div').find('menu').toggle();
        });
    }
    // AllView functionality callback
    educareAllViewPage();

    // // demo structure functionality
    // function educareDemoStructurePage() {

    // }
    // // demo structure functionality callback
    // educareDemoStructurePage();
});

// With pure JavaScript

function educareConfirmation() {
    if (educareSettings.confirmation == 'checked') {
        return confirm("Are you sure to remove this data?");
    } else {
        // If confirmation is not required, simply return true to proceed with the form submission
        return true;
    }
}

function educarePagination(perPage) {
    let options = {
        // How many content per page
        numberPerPage:perPage,
        // enable or disable go button
        goBar:true,
        // count page based on numberPerPage
        pageCounter:true,
    };

    let filterOptions = {
        // filter or search specific content
        el:'#searchBox'
    };

    paginate.init('.view_results',options,filterOptions);
}
perPage = document.querySelector('#results_per_page').value; 
educarePagination(perPage);


function add(form) {
    var type = form.type.value;
    var field = form.field.value;
    if (field) {
        form.Extra_field.value = type+ " " +field;
    }
}


function select_Table() {
    var x = document.getElementById("select_table").value;
    var term = document.getElementById("term");
    var term_label = document.getElementById("term_label");
    
    var select_class = document.querySelector('.educareAllView_select_class').innerHTML;
    var select_exam = document.querySelector('.educareAllView_select_exam').innerHTML;
    var sub_select_class = document.querySelector('.educareAllView_sub_select_class').innerHTML;
    var sub_select_exam = document.querySelector('.educareAllView_sub_select_exam').innerHTML;
    var all = '<option>All</options>';

    if (x == 'All') {
        select_data.disabled = 'disabled';
        term.disabled = 'disabled';
        term_label.innerHTML = 'All:';
    }

    if (x == 'Class') {
        select_data.disabled = '';
        term.disabled = '';
        select_data.innerHTML = select_class;
        term.innerHTML = all + sub_select_exam;
        term_label.innerHTML = 'Select Exam:';
    }

    if (x == 'Exam') {
        select_data.disabled = '';
        term.disabled = '';
        select_data.innerHTML = select_exam;
        term.innerHTML = all + sub_select_class;
        term_label.innerHTML = 'Select Class:';
    }

}

function select_Year() {
    var x = document.getElementById("year").value;
    var year = document.getElementById("select_year");
    
    if (x == 'All') {
        year.disabled = 'disabled';
    }
    if (x == 'Year') {
        year.disabled = '';
        year.innerHTML = document.querySelector('.educareAllView_select_year').innerHTML;
    }
}

// keep selected
select_Table();
select_Year();
