jQuery(document).ready(function($) {
    $('#post_pay_counter_time_start').datepicker({
        dateFormat : 'yy-mm-dd',
        minDate : ppc_stats_effects_vars.datepicker_mindate,
        maxDate: ppc_stats_effects_vars.datepicker_maxdate,
        changeMonth : true,
        changeYear : true,
        showButtonPanel: true,
        showOtherMonths: true,
        selectOtherMonths: true,
        showAnim: "slideDown",
        onSelect: function(dateText, inst) {
        $('#post_pay_counter_time_end').datepicker('option', 'minDate', new Date(inst.selectedYear, inst.selectedMonth, inst.selectedDay));
        }
    });
    $('#post_pay_counter_time_end').datepicker({
        dateFormat : 'yy-mm-dd',
        minDate : ppc_stats_effects_vars.datepicker_mindate,
        maxDate: ppc_stats_effects_vars.datepicker_maxdate,
        changeMonth : true,
        changeYear : true,
        showButtonPanel: true,
        showOtherMonths: true,
        selectOtherMonths: true,
        showAnim: "slideDown",
        onSelect: function(dateText, inst) {
        $('#post_pay_counter_time_start').datepicker('option', 'maxDate', new Date(inst.selectedYear, inst.selectedMonth, inst.selectedDay));
        }      
    });
    
    //Handles date picker fields display
    $('#ppc-time-range').change(function() {
		var selected = $(this).val();
		
        if(selected == 'custom') {
            $('#ppc-time-range-custom').css('display', 'block');
        } else {
            $('#ppc-time-range-custom').css('display', 'none');
		}

		//Tweaks the datepicker fields dates when a choice is made from select menu
        if(selected == 'this_month') {
			$('#post_pay_counter_time_start').val(ppc_stats_effects_vars.time_start_this_month);
			$('#post_pay_counter_time_end').val(ppc_stats_effects_vars.time_end_this_month);
		}
        if(selected == 'last_month') {
			$('#post_pay_counter_time_start').val(ppc_stats_effects_vars.time_start_last_month);
			$('#post_pay_counter_time_end').val(ppc_stats_effects_vars.time_end_last_month);
		}
        if(selected == 'this_week') {
			$('#post_pay_counter_time_start').val(ppc_stats_effects_vars.time_start_this_week);
			$('#post_pay_counter_time_end').val(ppc_stats_effects_vars.time_end_this_week);
		}
        if(selected == 'this_year') {
			$('#post_pay_counter_time_start').val(ppc_stats_effects_vars.time_start_this_year);
			$('#post_pay_counter_time_end').val(ppc_stats_effects_vars.time_end_this_year);
		}
        if(selected == 'all_time') {
			$('#post_pay_counter_time_start').val(ppc_stats_effects_vars.datepicker_mindate);
			$('#post_pay_counter_time_end').val(ppc_stats_effects_vars.datepicker_maxdate);
		}
			
    });

    //Makes sure datepicker fields are displayed if custom is the default choice
    $('#ppc-time-range').trigger('change');

    $('#ppc_stats_role').on('change', function(e) {
        e.preventDefault();
        
        //$('#ppcp_ga_status_ajax_loader').css('display', 'inline');
        $('#ppc_stats_role').attr('disabled', 'disabled');
        
        var data = {
            action: "ppc_stats_get_users_by_role",
            user_role: $('#ppc_stats_role').val(),
            _ajax_nonce: ppc_stats_effects_vars.nonce_ppc_stats_get_users_by_role
        };
        
        $.post(ajaxurl, data, function(response) {
            //$('#ppcp_ga_status_ajax_loader').css('display', 'none');
            $('#ppc_stats_role').removeAttr('disabled');
			
            if(! response.success) {
				alert(response);
            } else {
                $('#ppc_stats_user').html(response.data.html);
            }
        });
    });
});
