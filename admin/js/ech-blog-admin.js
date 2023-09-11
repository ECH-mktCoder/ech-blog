(function( $ ) {
	'use strict';
	
	$(function(){
		/************* GENERAL FORM **************/
		$('#ech_blog_settings_form').on('submit', function(e){
			e.preventDefault();

			$('.statusMsg').removeClass('error');
			$('.statusMsg').removeClass('updated');

			var statusMsg = '';
			var validStatus = false;

			/*** admin form validations ***/
			var ppp = $('#ech_blog_settings_form #ech_blog_ppp').val();
			var channelID = $('#ech_blog_settings_form #ech_blog_channel_id').val();
			var brandID = $('#ech_blog_settings_form #ech_blog_brand_id').val();



			if ( ppp == '' || channelID == '' || brandID == '' ) {
				validStatus = false;
			} else {
				validStatus = true;
			}

			if ( ppp == '') {
				statusMsg += 'Post per page cannot be empty<br>';
			}			
			if ( channelID == '') {
				statusMsg += 'Channel ID cannot be empty<br>';
			}
			if ( brandID == '') {
				statusMsg += 'Brand ID cannot be empty<br>';
			}


			/*** (end) admin form validations ***/

			if ( !validStatus ) {
				$('.statusMsg').html(statusMsg);
				$('.statusMsg').addClass('error');
				return;
			} else {
				$('#ech_blog_settings_form').attr('action', 'options.php');
				$('#ech_blog_settings_form')[0].submit();
				// output success msg
				statusMsg += 'Settings updated <br>';
				$('.statusMsg').html(statusMsg);
				$('.statusMsg').addClass('updated');
			}
			
			
		});
		/************* (END) GENERAL FORM **************/



		/************* COPY SAMPLE SHORTCODE **************/
		$('#copyShortcode').click(function(){

			var shortcode = $('#sample_shortcode').text();

			navigator.clipboard.writeText(shortcode).then(
				function(){
					$('#copyMsg').html('');
					$('#copyShortcode').html('Copied !'); 
					setTimeout(function(){
						$('#copyShortcode').html('Copy Shortcode'); 
					}, 3000);
				},
				function() {
					$('#copyMsg').html('Unable to copy, try again ...');
				}
			);
		});
		/************* (END)COPY SAMPLE SHORTCODE **************/
	}); // ready


	

})( jQuery );
