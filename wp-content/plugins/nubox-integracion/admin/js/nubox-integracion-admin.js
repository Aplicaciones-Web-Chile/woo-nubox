(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */


	$( window ).load(function() {
		
		$("#nubox_settings_form").on('submit', function(){

			var msg = '';
			if( $('#nubox_api_key').val() == '' ){
				msg = '<p>La API KEY de Nubox no puede estar vacía </p> \n';
			}

			if( $('#nubox_secret_key').val() == '' ){
				msg += '<p>La SECRET KEY de Nubox no puede estar vacía </p> \n';
			}


			$("#nubox_mensajes").html(msg);

			if( msg.length > 0){
				event.preventDefault();
				$("#nubox_mensajes").show();
			}

		});


		$('#nubox_secret_key').click(function(){
			$(this).is(':checked') ? $('#nubox_secret_key').attr('type', 'text') : $('#nubox_secret_key').attr('type', 'password');
		});

	});

})( jQuery );
