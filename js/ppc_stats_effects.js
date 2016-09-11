jQuery(document).ready(function() {
    jQuery('#post_pay_counter_time_start').datepicker({
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
            jQuery('#post_pay_counter_time_end').datepicker('option', 'minDate', new Date(inst.selectedYear, inst.selectedMonth, inst.selectedDay));
        }
    });
    jQuery('#post_pay_counter_time_end').datepicker({
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
             jQuery('#post_pay_counter_time_start').datepicker('option', 'maxDate', new Date(inst.selectedYear, inst.selectedMonth, inst.selectedDay));
        }      
    });
    
    //Handles date picker fields display
    jQuery('#ppc-time-range').change(function() {
		var selected = jQuery(this).val();
		
        if(selected == 'custom') {
            jQuery('#ppc-time-range-custom').css('display', 'block');
        } else {
            jQuery('#ppc-time-range-custom').css('display', 'none');
		}

		//Tweaks the datepicker fields dates when a choice is made from select menu
        if(selected == 'this_month') {
			jQuery('#post_pay_counter_time_start').val(ppc_stats_effects_vars.time_start_this_month);
			jQuery('#post_pay_counter_time_end').val(ppc_stats_effects_vars.time_end_this_month);
		}
        if(selected == 'last_month') {
			jQuery('#post_pay_counter_time_start').val(ppc_stats_effects_vars.time_start_last_month);
			jQuery('#post_pay_counter_time_end').val(ppc_stats_effects_vars.time_end_last_month);
		}
        if(selected == 'this_week') {
			jQuery('#post_pay_counter_time_start').val(ppc_stats_effects_vars.time_start_this_week);
			jQuery('#post_pay_counter_time_end').val(ppc_stats_effects_vars.time_end_this_week);
		}
        if(selected == 'this_year') {
			jQuery('#post_pay_counter_time_start').val(ppc_stats_effects_vars.time_start_this_year);
			jQuery('#post_pay_counter_time_end').val(ppc_stats_effects_vars.time_end_this_year);
		}
        if(selected == 'all_time') {
			jQuery('#post_pay_counter_time_start').val(ppc_stats_effects_vars.datepicker_mindate);
			jQuery('#post_pay_counter_time_end').val(ppc_stats_effects_vars.datepicker_maxdate);
		}
			
    });
    
    //Makes sure datepicker fields are displayed if custom is the default choice
    jQuery('#ppc-time-range').trigger('change');
});
