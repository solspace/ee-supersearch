!(function($, global){
	"use strict";

	//--------------------------------------------  
	//	build the lexicon
	//--------------------------------------------

	var updateProgress = function(e)
	{
		var n = $('.progress-bar');
		var i = $(".progress", n);
		var p = $('#update_percent');

		n.is(":not(:visible)") && n.show();

		i.css("width", e+"%");
		
		p.html(e+"%");
	};

	function build_live_block($that, url){
	
		$.ajax({
			url : url + '&ajax=yes',
			data : null,
			success : function(data, status){

				if (data.done){
					// add our marker to say we're done
					// ..			
					$that.find('.block_in_progress').hide();
					
					$that.find('.progress-container').fadeOut(function(){
		
						updateProgress(0);

						$that.find('.block_progress_count_current').html('0');

						$that.find('.block_success').show()
							.delay(1500)
							.fadeOut( function(){

								$that.find('.block_action .last_build').hide();

								$that.find('.block_action .just_now').show();

								$that.find('.live_block_build').html('Rebuild');

								$that.find('.block_action').fadeIn();
							});
					});
				}
				else
				{
					var new_url = url.replace(/batch=\d+/, 'batch='+data.batch);
					
					// mark up some progress indicator
					//alert(' new url : \n' + new_url );
					var obj = { current : data.batch, total : $that.find('.block_progress_count_total').html() };

					if (obj.current > obj.total) obj.current = obj.total;
					
					$that.find('.progress-container').show();

					updateProgress(Math.round(obj.current/obj.total * 100));

					$that.find('.block_progress_count_current').html(obj.current);

					build_live_block( $that, new_url );
				}
			},
			dataType : 'json'
		});
	}
	

	//--------------------------------------------  
	//	this will function for all field instances
	//--------------------------------------------
	
	$(function() {
		//--------------------------------------------  
		//	lexicon live build
		//--------------------------------------------

		$('.live_block_build').click( function() {

			var $that = $(this),
				$container = $that.parents('.live_block')

				$container.find('.block_action').fadeOut(function(){
						
					$container.find('.block_state').show();

					$container.find('.block_success').hide();
				
					$container.find('.block_in_progress').show();

					$container.find('.block_progressbar').show();
		
					updateProgress(0);

					build_live_block($container, $that.attr('rel'));

				});

			return false;
		});
	});
}(jQuery, window));