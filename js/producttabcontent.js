$(function(){

	/* TODO: Hardcoded group_4 id, Should be added as a preference in module. */

	var getSpec = function(){

		/* Get the id for wich id_attribute is selected in the html interface. */
		var current_selected_attribute_id = $('.attribute_list ul input[name="group_4"]:checked').val();

		/* Get the pdc item wich corresponds to the selected id_attribute. */
		var current_pdc_item = $.grep(pdc_items, function(e){
			return e.id_attribute == current_selected_attribute_id;
		})[0];

		/* Get the translation item (pdc_lang object). */
		var current_pdc_lang_item = $.grep(pdc_lang_items, function(e){
			return e.id_lang == current_lang_id && e.id_pdc == current_pdc_item.id_pdc;
		})[0];

		/* Output the html into the interface. */
		if (current_pdc_lang_item){
			$('.product_attribute_specification').html(current_pdc_lang_item.html);
		}
		else{
			$('.product_attribute_specification').empty();
		}

	}; /* END - getSpec function */

	/* This function needs to run on page finished loading. */
	getSpec();

	/* Load the correct text if the attributes input changes. */
	$('.attribute_list ul input[name="group_4"]').on('change',function(){getSpec();});

});

