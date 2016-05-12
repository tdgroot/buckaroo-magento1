document.observe('click', function(event, element) {
	if (element = event.findElement('#paymentguarantee_terms_and_conditions_link')) {
	  	$('paymentguarantee_terms_and_conditions').setStyle({
			display: 'block'
		});
		event.stop();
	}

	if (element = event.findElement('#paymentguarantee_terms_and_conditions_close')) {
	  	$('paymentguarantee_terms_and_conditions').setStyle({
			display: 'none'
		});
		event.stop();
	}
});

document.observe('change', function(e) {
   if (e.findElement('#p_method_buckaroo3extended_paymentguarantee')) {
       var phoneNumber = jQuery_1123("#billing\\:telephone").val();

       if (!phoneNumber && phoneNumber.length == 0) {
           jQuery_1123('#buckaroo3extended_paymentguarantee_BPE_Customerphone').parent().parent().show();
       }
   }
});