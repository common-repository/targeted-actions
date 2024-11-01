jQuery(document).ready(function($){

	jQuery(".js-ajax-form").submit(function(e){
	    e.preventDefault();
	    
	    let form = jQuery(this);
	    let submit_button = form.find('input[type="submit"]').first();
	    let form_values = form.serialize();

	    submit_button.attr('value', submit_button.data('loading')).attr('disabled', 'disabled');
	    $('.form-message').hide();
	    $('.error-state').removeClass('error-state');

	    $.ajax({
		    url: form.attr('action'),
		    data: form_values,
		    type: form.attr('method'),
		    dataType: 'json',
		    success: function (result, status, xhr) {
		    
		    	console.log("Result:");
		    	console.log(result);

		    	if(result.status == 'failure') {

		    		let errorText = $('.js-form-failure').html();

		    		if("message_error" in result) {
		    			errorText = result.message_error;
		    		}

		   			// Error handling messaging

		   			if("errors" in result) {

		   				if(result.errors.length > 0) {

			   				errorText = errorText + '<ul>';

			   				result.errors.forEach(function(error){
	  							errorText = errorText + '<li>' + error + '</li>';
							})

			   				errorText = errorText + '</ul>';
			   			}
		   			}

		   			// Error Handling Fields

		   			if("invalid_fields" in result) {
		   				if(result.invalid_fields != null && result.invalid_fields.length > 0) {
			   				result.invalid_fields.forEach(function(field){
	  							form.find('#' + field).addClass('error-state');
							})
			   			}
		   			}

		    		// Show the error message
		    		$('.js-form-failure').html(errorText).show();

		    		// Scroll to the messages
		    		let scrollTarget = $('.js-form-failure').first();

		    		$('html, body').stop().animate({
				        'scrollTop': scrollTarget.offset().top - 50
				    }, 300, 'swing', function () {
				        window.location.hash = scrollTarget;
				    });

		    		// All done, re-enable the submit button so user can try again
		    		submit_button.attr('value', submit_button.data('default')).removeAttr('disabled');



		    	}
		    	else {
		    		
		    		if("message_success" in result) {
		    			$('.js-form-success').html(result.message_success);
		    		}

		    		$('.js-form-success').show();
		    		form.hide();
		    	}

		    },
		    error: function() {
		    	console.log('The form submission failed on the server');
		    	$('.js-form-failure').show();
		    	submit_button.attr('value', submit_button.data('default')).removeAttr('disabled');
		    }
		});

	});

	$('.js-show-on-checked').change(function() {
		if($(this).is(':checked')) {
			$(this).parents('.col').find('.js-show-after-check').show();
		}
		else {
			$(this).parents('.col').find('.js-show-after-check').hide();
		}
	});

});