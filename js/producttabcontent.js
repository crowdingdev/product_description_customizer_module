$(function(){

	/* TODO: Hardcoded group_4 id, Should be added as a preference in module. */

	var getSpec = function(){

		/* Get the id for wich id_attribute is selected in the html interface */
		var current_selected_attribute_id = $('.attribute_list ul input[name="group_4"]:checked').val();
		/* get the pdc item wich corresponds to the selected id_attribute */
		var current_pdc_item = $.grep(spec_items, function(e){ return e.id_attribute == current_selected_attribute_id; })[0];
		/* Get the translation (pdc_lang object) */
		var current_pdc_lang_item = $.grep(ILanguages, function(e){ return e.id_lang == current_language_id && e.id_pdc == current_pdc_item.id_pdc; })[0];

		/* Output the html into the interface */
		$('.product_attribute_specification').html(current_pdc_lang_item.html);

	};

	/* This function needs to run on page finished loading. */
	getSpec();

	/* Load the correct text if user changes attribute. */
	$('.attribute_list ul input[name="group_4"]').on('click',function(){getSpec();});

});

