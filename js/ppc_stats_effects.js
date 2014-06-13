jQuery(document).ready(function() {
    jQuery('#post_pay_counter_time_start').datepicker({
        dateFormat : 'yy/mm/dd',
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
        dateFormat : 'yy/mm/dd',
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
});