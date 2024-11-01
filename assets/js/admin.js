jQuery(document).ready(function($){

	// Javascript for showing / hiding the fields for the selected rule

	$('.js-rule-selection').change(function() {

		if($(this).is(':checked')) {
			$('.rule').hide();
			
			let value = $(this).val();

			$('.rule[data-rule="' + value + '"]').show();
		}

	});

	$('.js-modal-choice-selection').change(function() {

		if($(this).is(':checked')) {
			$('.modal-choice').hide();
			
			let value = $(this).val();

			$('.modal-choice[data-choice="' + value + '"]').show();
		}

	});

	// CodeMirror
	
	if($('.js-tua-codemirror').length > 0) {
		$( ".js-tua-codemirror" ).each(function( index ) {
			wp.codeEditor.initialize($(this), cm_settings);
		});
	}

	// Editing, Disabling and Editing Rules
	
	$('.js-enable-disable-rule').click(function() {

		let nonce = $('.js-rule-nonce').data('nonce');
		let action = $('.js-rule-nonce').data('action');
		let enableOrDisable = 'disable';
		let ruleID = $(this).data('id');
		let theButton = $(this);

		if($(this).hasClass('js-enable-rule')) {
			enableOrDisable = 'enable';
		}

		theButton.html("Working...");

		$.ajax({
		    url: action,
		    data: {action: 'enable_disable_rule', rule_action: enableOrDisable, _wpnonce: nonce, ruleID: ruleID},
		    type: 'post',
		    dataType: 'json',
		    success: function (result, status, xhr) {

		    	console.log('TUA Enable or Disable Rule Result:');
		    	console.log(result);

		    	if(result.status == 'success') {

			    	if(enableOrDisable == 'disable') {
			    		theButton.html('Enable');
			    		theButton.removeClass('js-disable-rule').addClass('js-enable-rule');
			    	}
			    	else {
			    		theButton.html('Disable');
			    		theButton.addClass('js-disable-rule').removeClass('js-enable-rule');
			    	}
			    }
			    else {
			    	theButton.html('An error occurred').attr('disabled', 'disabled');
			    }

		    },
		    error: function() {
		    	console.log('The form submission failed on the server');
		    	theButton.html('An error occurred').attr('disabled', 'disabled');
		    }
		});

	});

	// Delete a Rule
	
	$('.js-delete-rule').click(function() {

		$(this).parent().find('.js-confirm-delete').fadeIn(250, "swing");

	});

	$('.js-cancel-delete-btn').click(function() {
		$(this).parent().fadeOut(250, "swing");
	});

	// Actually delete a rule
	
	$('.js-confirm-delete-btn').click(function() {

		let ruleID = $(this).data('id');
		let nonce = $('.js-rule-nonce').data('nonce');
		let action = $('.js-rule-nonce').data('action'); 
		let theButton = $(this);

		theButton.html('Working...');

		$.ajax({
		    url: action,
		    data: {action: 'delete_rule', _wpnonce: nonce, ruleID: ruleID},
		    type: 'post',
		    dataType: 'json',
		    success: function (result, status, xhr) {

		    	console.log('TUA Delete Rule Result:');
		    	console.log(result);

		    	if(result.status == 'success') {

			    	theButton.parents('.tua-rule-row').fadeOut(500, "swing");

			    }
			    else {
			    	theButton.html('An error occurred').attr('disabled', 'disabled');
			    }

		    },
		    error: function() {
		    	console.log('The form submission failed on the server');
		    	theButton.html('An error occurred').attr('disabled', 'disabled');
		    }
		});

	});

});