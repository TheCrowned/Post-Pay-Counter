//Javascript snippet to hide two different set of settings depending on the selected radio
function post_pay_counter_radio_auto_toggle(toggle_click_1, toggle_click_1_content, toggle_click_2, toggle_click_2_content) {
	var element_1 = jQuery(toggle_click_1);
	var element_2 = jQuery(toggle_click_2);
	
	//At page load, check which radio field is unchecked and hide the relative settings content
	if(element_1.attr(("checked")) == undefined) {
		jQuery(toggle_click_1_content).hide();
	} else if(element_2.attr(("checked")) == undefined) {
		jQuery(toggle_click_2_content).hide();
	}
	
	//When a radio field gets changed, update the opacity and hide (slide) the other set
	jQuery.each([element_1, element_2], function(i,v) {
		v.bind("click", function() {
			if(jQuery(this).attr(("id")) == jQuery(toggle_click_1).attr("id")) {
				jQuery(toggle_click_2_content).css("opacity", "0.40");
				jQuery(toggle_click_2_content).slideUp();
				jQuery(toggle_click_1_content).css("opacity", "1");
				jQuery(toggle_click_1_content).slideDown();
			} else if(jQuery(this).attr(("id")) == jQuery(toggle_click_2).attr("id")) {
				jQuery(toggle_click_1_content).css("opacity", "0.40");
				jQuery(toggle_click_1_content).slideUp();
				jQuery(toggle_click_2_content).css("opacity", "1");
				jQuery(toggle_click_2_content).slideDown();
			}
		});
	});
}

//The same, but with checkbox fields
function post_pay_counter_checkbox_auto_toggle(toggle_click, toggle_click_content) {
	var element = jQuery(toggle_click);
	
	//At page load, check whether checkbox is checked, if not do not show div
	if(element.attr(("checked")) == undefined) {
		jQuery(toggle_click_content).hide();
	}
	
	//When the checkbox field gets changed, update the opacity and hide (slide) the div
	jQuery(element).bind("click", function() {
		if(jQuery(this).attr(("checked")) == undefined) {
			jQuery(toggle_click_content).css("opacity", "0.40");
			jQuery(toggle_click_content).slideUp();
		} else if(jQuery(this).attr(("checked")) == "checked") {
			jQuery(toggle_click_content).css("opacity", "1");
			jQuery(toggle_click_content).slideDown();
		}
	});
}

jQuery(document).ready(function($) {
	//Counting types
    post_pay_counter_checkbox_auto_toggle("#basic_payment", "#ppc_basic_payment_content");
    post_pay_counter_checkbox_auto_toggle("#counting_words", "#ppc_counting_words_content");
    post_pay_counter_checkbox_auto_toggle("#counting_visits", "#ppc_counting_visits_content");
    post_pay_counter_checkbox_auto_toggle("#counting_images", "#ppc_counting_images_content");
    post_pay_counter_checkbox_auto_toggle("#counting_comments", "#ppc_counting_comments_content");
    
    //Payments systems
	post_pay_counter_radio_auto_toggle("#counting_words_system_zonal", "#counting_words_system_zonal_content", "#counting_words_system_incremental", "#counting_words_system_incremental_content");
	post_pay_counter_radio_auto_toggle("#counting_visits_system_zonal", "#counting_visits_system_zonal_content", "#counting_visits_system_incremental", "#counting_visits_system_incremental_content");
	post_pay_counter_radio_auto_toggle("#counting_images_system_zonal", "#counting_images_system_zonal_content", "#counting_images_system_incremental", "#counting_images_system_incremental_content");
	post_pay_counter_radio_auto_toggle("#counting_comments_system_zonal", "#counting_comments_system_zonal_content", "#counting_comments_system_incremental", "#counting_comments_system_incremental_content");
	
    //Stats default time date range
	post_pay_counter_radio_auto_toggle("#default_stats_time_range_custom", "#default_stats_time_range_custom_content", "#default_stats_time_range_month", "#default_stats_time_range_month_content");
	post_pay_counter_radio_auto_toggle("#default_stats_time_range_custom", "#default_stats_time_range_custom_content", "#default_stats_time_range_week", "#default_stats_time_range_week_content");
    
    //Initializes tooltips
	$(".ppc_tooltip_container").tipTip({
		activation: "click",
		keepAlive:  "true",
		maxWidth:   "300px"
	});
	
	//Prevents counting_payment_only_when_total_threshold from being checked if not threshold is set
	$("#counting_payment_only_when_total_threshold").change(function() {
		if(this.checked == true) { 
			if($("#counting_payment_total_threshold").val() == 0) {
				$(this).removeAttr("checked");
				alert(ppc_options_effects_vars.localized_need_threshold);
			}
		}
	});
		
    //Handles adding/removing of zones
    //WORDS
    var words_zones_count = (ppc_options_effects_vars.counting_words_current_zones_count-1);
    $("#counting_words_more_zones").click(function(e) {
        e.preventDefault();
        
        if(words_zones_count > 9){
            alert(ppc_options_effects_vars.localized_too_many_zones);
            return false;
        }
		words_zones_count++;
    
        $('<tr><td><input type="text" name="words_'+words_zones_count+'_zone_threshold" id="words_'+words_zones_count+'_zone_threshold" /></td><td><input type="text" name="words_'+words_zones_count+'_zone_payment" id="words_'+words_zones_count+'_zone_payment" /></td></tr>').appendTo($("#counting_words_system_zonal_content").find("#words_0_zone_threshold").parent().parent().parent());
     });
 
     $("#counting_words_less_zones").click(function(e) {
        e.preventDefault();
        
        if(words_zones_count == 1){
            alert(ppc_options_effects_vars.localized_too_few_zones);
            return false;
        }
        
        $("#words_"+words_zones_count+"_zone_threshold").parent().parent().remove();
        words_zones_count--;
	});
     
	//VISITS
	var visits_zones_count = (ppc_options_effects_vars.counting_visits_current_zones_count-1);
    $("#counting_visits_more_zones").click(function(e) {
		e.preventDefault();
        
        if(visits_zones_count > 9){
            alert(ppc_options_effects_vars.localized_too_many_zones);
            return false;
        }
		visits_zones_count++;
    
        $('<tr><td><input type="text" name="visits_'+visits_zones_count+'_zone_threshold" id="visits_'+visits_zones_count+'_zone_threshold" /></td><td><input type="text" name="visits_'+visits_zones_count+'_zone_payment" id="visits_'+visits_zones_count+'_zone_payment" /></td></tr>').appendTo($("#counting_visits_system_zonal_content").find("#visits_0_zone_threshold").parent().parent().parent());
     });
 
     $("#counting_visits_less_zones").click(function(e) {
        e.preventDefault();
        
        if(visits_zones_count == 1){
            alert(ppc_options_effects_vars.localized_too_few_zones);
            return false;
        }
        
        $("#visits_"+visits_zones_count+"_zone_threshold").parent().parent().remove();
        visits_zones_count--;
	});
	
	//IMAGES
	var images_zones_count = (ppc_options_effects_vars.counting_images_current_zones_count-1);
    $("#counting_images_more_zones").click(function(e) {
		e.preventDefault();
        
        if(images_zones_count > 9){
            alert(ppc_options_effects_vars.localized_too_many_zones);
            return false;
        }
		images_zones_count++;
    
        $('<tr><td><input type="text" name="images_'+images_zones_count+'_zone_threshold" id="images_'+images_zones_count+'_zone_threshold" /></td><td><input type="text" name="images_'+images_zones_count+'_zone_payment" id="images_'+images_zones_count+'_zone_payment" /></td></tr>').appendTo($("#counting_images_system_zonal_content").find("#images_0_zone_threshold").parent().parent().parent());
     });
 
     $("#counting_images_less_zones").click(function(e) {
        e.preventDefault();
        
        if(images_zones_count == 1){
            alert(ppc_options_effects_vars.localized_too_few_zones);
            return false;
        }
        
        $("#images_"+images_zones_count+"_zone_threshold").parent().parent().remove();
        images_zones_count--;
	});
	
	//COMMENTS
	var comments_zones_count = (ppc_options_effects_vars.counting_comments_current_zones_count-1);
    $("#counting_comments_more_zones").click(function(e) {
		e.preventDefault();
        
        if(comments_zones_count > 9){
            alert(ppc_options_effects_vars.localized_too_many_zones);
            return false;
        }
		comments_zones_count++;
    
        $('<tr><td><input type="text" name="comments_'+comments_zones_count+'_zone_threshold" id="comments_'+comments_zones_count+'_zone_threshold" /></td><td><input type="text" name="comments_'+comments_zones_count+'_zone_payment" id="comments_'+comments_zones_count+'_zone_payment" /></td></tr>').appendTo($("#counting_comments_system_zonal_content").find("#comments_0_zone_threshold").parent().parent().parent());
     });
 
     $("#counting_comments_less_zones").click(function(e) {
        e.preventDefault();
        
        if(comments_zones_count == 1){
            alert(ppc_options_effects_vars.localized_too_few_zones);
            return false;
        }
        
        $("#comments_"+comments_zones_count+"_zone_threshold").parent().parent().remove();
        comments_zones_count--;
	});
});