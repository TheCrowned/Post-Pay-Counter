jQuery(document).ready(function($) {
    
    /* <COUNTING SETTINGS BOX CALLS> */
    $('#ppc_save_counting_settings').on("click", function(e) {
        e.preventDefault();
        $('#ppc_counting_settings_ajax_loader').css('display', 'inline');
        $('#ppc_counting_settings_error').css('display', 'none');
		$('#ppc_counting_settings_success').css('display', 'none');
        
        var data = {
            action: "ppc_save_counting_settings",
            _ajax_nonce: ppc_options_ajax_stuff_vars.nonce_ppc_save_counting_settings,
            form_data: $('#ppc_counting_settings_form').serialize()
        };
        
        $.post(ajaxurl, data, function(response) {
            $('#ppc_counting_settings_ajax_loader').css('display', 'none');
            
            if(response.indexOf('ok') < 0) {
                $('#ppc_counting_settings_error').html(response);
                $('#ppc_counting_settings_error').css('display', 'inline');
            } else {
                $('#ppc_counting_settings_success').css('display', 'inline');
            }
        });
    });
    /* </COUNTING SETTINGS BOX CALLS> */
    
    /* <MISC SETTINGS BOX CALLS> */
    $('#ppc_save_misc_settings').on("click", function(e) {
        e.preventDefault();
        $('#ppc_misc_settings_ajax_loader').css('display', 'inline');
        $('#ppc_misc_settings_error').css('display', 'none');
		$('#ppc_misc_settings_success').css('display', 'none');
        
        var data = {
            action: "ppc_save_misc_settings",
            _ajax_nonce: ppc_options_ajax_stuff_vars.nonce_ppc_save_misc_settings,
            form_data: $('#ppc_misc_settings_form').serialize()
        };
        
        $.post(ajaxurl, data, function(response) {
            $('#ppc_misc_settings_ajax_loader').css('display', 'none');
            
            if(response.indexOf('ok') < 0) {
                $('#ppc_misc_settings_error').html(response);
                $('#ppc_misc_settings_error').css('display', 'inline');
            } else {
                $('#ppc_misc_settings_success').css('display', 'inline');
            }
        });
    });
    /* </MISC SETTINGS BOX CALLS> */
    
    /* <PERMISSIONS BOX CALLS> */
    $('#ppc_save_permissions').on("click", function(e) {
        e.preventDefault();
        $('#ppc_permissions_ajax_loader').css('display', 'inline');
        $('#ppc_permissions_error').css('display', 'none');
		$('#ppc_permissions_success').css('display', 'none');
        
        var data = {
            action: "ppc_save_permissions",
            _ajax_nonce: ppc_options_ajax_stuff_vars.nonce_ppc_save_permissions,
            form_data: $('#ppc_permissions_form').serialize()
        };
        
        $.post(ajaxurl, data, function(response) {
            $('#ppc_permissions_ajax_loader').css('display', 'none');
            
            if(response.indexOf('ok') < 0) {
                $('#ppc_permissions_error').html(response);
                $('#ppc_permissions_error').css('display', 'inline');
            } else {
				$('#ppc_permissions_success').css('display', 'inline');
            }
        });
    });
    /* </PERMISSIONS BOX CALLS> */
    
    /* <PERSONALIZE SETTINGS BOX CALLS> */
    $('.ppc_personalize_roles').on('click', function(e) {
        e.preventDefault();
        $('#ppc_personalize_settings_ajax_loader').css('display', 'inline');
        $('#ppc_personalize_user_roles').css('opacity', '0.6');
        $("#ppc_personalize_users").css('display', 'none');
        $("#ppc_users").html("");
        
        var data = {
            action: "ppc_personalize_fetch_users_by_roles",
            user_role: $(this).attr("id"),
            _ajax_nonce: ppc_options_ajax_stuff_vars.nonce_ppc_personalize_fetch_users_by_roles
        };
        
        $.post(ajaxurl, data, function(response) {
            if(response.indexOf('ok') < 0) {
                alert(response);
            } else {
				response = response.substr(2);
                $('#ppc_personalize_settings_ajax_loader').css('display', 'none');
                $("#ppc_personalize_users").css('display', 'block');
                $('#ppc_personalize_user_roles').css('opacity', '1');
                $("#ppc_users").html(response);
            }
        });
    });
    /* </PERSONALIZE SETTINGS BOX CALLS> */
    
    /* <DELETE USER'S PERSONALIZED SETTINGS> */
    $('#vaporize_user_settings').on('click', function(e) {
        e.preventDefault();
        
        var data = {
            action: "ppc_vaporize_user_settings",
            user_id: $(this).attr("accesskey"),
            _ajax_nonce: ppc_options_ajax_stuff_vars.nonce_ppc_vaporize_user_settings
        };
        
        $.post(ajaxurl, data, function(response) {
            if(response.indexOf('ok') < 0) {
                alert(response);
            } else {
                alert(ppc_options_ajax_stuff_vars.localized_vaporize_user_success);
                window.location.replace(ppc_options_ajax_stuff_vars.ppc_options_url);
            }
        });
    });
    /* </DELETE USER'S PERSONALIZED SETTINGS> */
    
    /* <IMPORT/EXPORT SETTINGS> */
    $('#ppc_import_settings').on('click', function(e) {
        e.preventDefault();
        $('#ppc_import_settings_ajax_loader').css('display', 'inline');
        $('#ppc_import_settings_error').css('display', 'none');
		$('#ppc_import_settings_success').css('display', 'none');
        
        var data = {
            action: "ppc_import_settings",
            import_settings_content: $("#ppc_import_settings_content").val(),
            userid: $("#ppc_import_settings_userid").val(),
            _ajax_nonce: ppc_options_ajax_stuff_vars.nonce_ppc_import_settings
        };
        
        $.post(ajaxurl, data, function(response) {
            $('#ppc_import_settings_ajax_loader').css('display', 'none');

            if(! response.success) {
				
                $('#ppc_import_settings_error').html(response.data.message);
                $('#ppc_import_settings_error').css('display', 'inline');
            } else {
				$('#ppc_import_settings_success').css('display', 'inline');
            }
        });
    });
    /* </IMPORT/EXPORT SETTINGS> */
    
    /* <ERROR LOG> */
    $('#ppc_clear_error_log').on('click', function(e) {
        e.preventDefault();
        $('#ppc_error_log_ajax_loader').css('display', 'inline');
        $('#ppc_error_log_error').css('display', 'none');
        $('#ppc_error_log_success').css('display', 'none');
        
        var data = {
            action: "ppc_clear_error_log",
            _ajax_nonce: ppc_options_ajax_stuff_vars.nonce_ppc_clear_error_log
        };
        
        $.post(ajaxurl, data, function(response) {
            $('#ppc_error_log_ajax_loader').css('display', 'none');
            
            if(response.indexOf('ok') < 0) {
                $('#ppc_error_log_error').html(response);
                $('#ppc_error_log_error').css('display', 'inline');
            } else {
				$('#ppc_error_log_success').css('display', 'inline');
            }
        });
    });
    /* </ERROR LOG> */
	
	/* <LICENSE BOX CALLS> */
    $('.ppc_license_deactivate').on('click', function(e) {
        e.preventDefault();
		
		var agree = confirm(ppc_options_ajax_stuff_vars.localized_license_deactivate_warning);
		if(!agree) { return false; }
		
		var clicked = $(this);
		
        $('#ppc_license_ajax_loader').css('display', 'inline');
        clicked.attr('disabled', 'disabled');
		$('#ppc_license_error').css('display', 'none');
		
        var data = {
            action: "ppc_license_deactivate",
			plugin_slug: clicked.attr('accesskey'),
            _ajax_nonce: ppc_options_ajax_stuff_vars.nonce_ppc_license_key_deactivate
        };
        
        $.post(ajaxurl, data, function(response) {
            $('#ppc_license_ajax_loader').css('display', 'none');
            
            if(response.indexOf('ok') < 0) {
				clicked.removeAttr('disabled');
				$('#ppc_license_error').css('display', 'inline');
                $('#ppc_license_error').html(response);
            } else {
                clicked.closest('tr').fadeOut();
            }
        });
    });
    
    $('#ppc_license_key').bind('input', function() {
        if($('#ppc_license_key').val() != '') {
            $('#ppc_license_key_submit').removeAttr('disabled');
        } else {
            $('#ppc_license_key_submit').attr('disabled', 'disabled');
        }
    });
    
    $('#ppc_license_key_submit').on('click', function(e) {
        e.preventDefault();
        $('#ppc_license_ajax_loader').css('display', 'inline');
		$('#ppc_license_error').css('display', 'none');
        $('#ppc_license_key').attr('disabled', 'disabled');
        $('#ppc_license_key_submit').attr('disabled', 'disabled');
        
        var data = {
            action: "ppc_license_activate",
            _ajax_nonce: ppc_options_ajax_stuff_vars.nonce_ppc_license_key_activate,
            license_key: $('#ppc_license_key').val()
        };
        
        $.post(ajaxurl, data, function(response) {
			$('#ppc_license_ajax_loader').css('display', 'none');
			
            if(response.indexOf('ok') < 0) {
                $('#ppc_license_key_submit').removeAttr('disabled');
                $('#ppc_license_key').removeAttr('disabled');
				$('#ppc_license_error').css('display', 'inline');
                $('#ppc_license_error').html(response);
            } else {
				$('#ppc_license_success').css('display', 'inline');
            }
        });
    });
    /* </LICENSE BOX CALLS> */

});
