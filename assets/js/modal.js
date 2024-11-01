jQuery( document ).ready(function() {

	if(jQuery(".js-tua-modal-container").length > 0) {
		jQuery('body').addClass('tua-overflow-hidden');
		jQuery(".js-tua-modal-container").fadeIn(500, "swing");
	}

	jQuery(".js-tua-modal-close").click(function() {
		jQuery(".js-tua-modal-container").fadeOut(500, "swing");
		jQuery('body').removeClass('tua-overflow-hidden');
	});

});