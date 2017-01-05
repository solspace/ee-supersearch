;(function($, global){
	
	//--------------------------------------------  
	//	vars and settings
	//--------------------------------------------
		
	var settings = {
		//static because this is for the hidden field
		'taSeparator' : "\n" 
	}
	
	//--------------------------------------------  
	//	ghetto templates. No plugins required
	//	the array joining just makes reading them easier
	//--------------------------------------------
	
	var views = {};

	views.currentWord = [
		'<div class="current_word white_grad" data-word="{wordName}">',
			'<span class="ex"><\/span>',
			'<span class="exclude_word">',
				'{wordName}',
			'<\/span>',
			'<input type="hidden" name="word_hidden[]" value="{wordName}"/>',
		'<\/div>'
	].join('\n');
	
	//parses out brackets from view files a bit like EE
	/*
		use like:
		$users.append(view(views.choice, {
			'id' 			: data.users[item].id,
			'screen_name'	: data.users[item].name
		}));
	*/
	function view(template, data)
	{
		for (var item in data)
		{
			if (data.hasOwnProperty(item))
			{
				//could do regex here, but filtering out regex operators is messy
				do {
					template = template.replace('{' + item + '}', data[item]);
				}
				while(template.indexOf('{' + item + '}') > -1)
			}
		}
		return template;
	}
	//END view
	
	
	//--------------------------------------------  
	//	stringy stringy cleany cleany
	//--------------------------------------------
	
	function cleanString(str)
	{
		str = $.trim(
			//strip HTML
			str.replace(/(<([^>]+)>)/ig,''). 
			//strip EE tags
			replace(/(\{([^\}]+)\})/ig,''). 
			// strip punctuation
			replace(/([^\u3400-\u4db5\u4e00-\u9fa5\uf900-\ufa6a\u3041-\u3094\u30a1-\u30fa\w\d\s]+)/ig, '')
		); 
	
		return str.match(/^ *$/) ? '' : str.split(/\s+/g).join('||').toLowerCase();
	}
	//END cleanString
	
	
	//--------------------------------------------  
	//	prevent default shortcut
	//	abstracted in case we need to add more
	//--------------------------------------------
	
	function preventDefault(event)
	{
		event.stopPropagation();

		event.preventDefault();
		return false;
	}
		
	//--------------------------------------------  
	//	add word
	//--------------------------------------------
	
	function addWord(wordName)
	{
		var currentWords = $('#word_groups_hidden');			
		
		wordName = cleanString(wordName);

		var words = wordName.split('||');

		for( var i in words )
		{
			
			//is it actually there?
			if( $('.solspace_word_group .current_word[data-word=\'' + words[i] +'\']').length < 1 )
			{
				currentWords.push( words[i] );
				
				//add word to items div  
				$('.solspace_word_group .ignore_list').append(view(views.currentWord, {'wordName' : changeQuotes( words[i] , 'entities')}));	

			}
			else
			{
				// This word is already in the list, flash it up
				$('.solspace_word_group .current_word[data-word=\'' + words[i] + '\']')
					.animate({ 'opacity' : 'toggle' }, 200)
					.animate({ 'opacity' : 'toggle' }, 200);

			}

		}
	}

		
	//--------------------------------------------  
	//	Filter Words
	//--------------------------------------------
	
	function filterWords(wordName)
	{
		if( $('.solspace_word_group .current_word').length > 0 && wordName != '')
		{
			// Reset any previous matches 
			$('.solspace_word_group .exclude_word.filter_current')
				.addClass('filter_dead')
				.removeClass('filter_current');

			matches = $('.solspace_word_group .exclude_word:contains(\'' + wordName + '\')')
							.filter(function(){
								regex = new RegExp( wordName + '(.?)' ); 
								return this.innerHTML.match(regex);
							})
							.addClass('filter_current')
							.addClass('filter')
							.parent().animate({ boxShadow : '0 0 5px #5F6C74' }, 200);

			

			$('.solspace_word_group .exclude_word.filter_dead')
					.not('.filter_current')
					.removeClass('filter_dead')
					.parent().animate({ boxShadow : '0 0 5px #EBF0F2' }, 200 );

			$('.solspace_word_group .exclude_word.filter').removeClass('filter'); 

		}

	}
	
	//
	function changeQuotes(wordName, convertTo)
	{
		if (convertTo == 'real')
		{
			do {
				wordName 	= wordName.replace('&quot;', '"');
			}
			while (wordName.indexOf('&quot;') > -1);
		}
		else
		{
			do {
				wordName 	= wordName.replace('"', '&quot;');
			}
			while (wordName.indexOf('"') > -1);
		}
		
		return wordName;
	}
	
	//--------------------------------------------  
	//	remove word
	//--------------------------------------------
	
	function removeWord(wordName)
	{
		var currentWords = $('div.solspace_word_group'),
			wordName 	= changeQuotes(wordName, 'real'),
			//is it there?
			position 	= $.inArray(wordName, currentWords);
		
		if (position > -1)
		{
			//remove word
			currentWords.splice(position, 1);

			$('#word_groups_hidden').val(currentWords.join(settings.taSeparator));
		}
	}
	
	//--------------------------------------------  
	//	get hight of a hidden element
	//--------------------------------------------
	
	function getJQHeight($element)
	{
		var height, position, cssHeight;
		
		if ($element.css('display') == "none")
		{
			position 	= $element.css('position');
			cssHeight 	= $element.css('height');
			
			$element.css({
				//'position'	:'absolute',
				'visibility':'hidden',
			    'display'	:'block',
				'height'	: ''
			});
			
			height = $element.height();
			
			$element.css({
				//'position'	:position,
				'visibility':'visible',
			    'display'	:'none',
				'height'	: cssHeight
			});
		}
		else
		{
			height = $element.height();
		}
		
		return height;
	}

	//--------------------------------------------  
	//	build the lexicon
	//--------------------------------------------

	function build_live_block( $that, url ) {
	
		$.ajax({
			url : url + '&ajax=yes',
			data : null,
			success : function( data, status ){

				if( data.done ){
					// add our marker to say we're done
					// ..			
					$that.find('.block_in_progress').hide();
					
					$that.find('.block_progressbar').fadeOut(function(){

						$(this).progressbar({value: 0});

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
				else {
					var new_url = url.replace(/batch=\d+/, 'batch='+data.batch);
					// mark up some progress indicator
					//alert(' new url : \n' + new_url );
					var obj = { current : data.batch, total : $that.find('.block_progress_count_total').html() };

					if( obj.current > obj.total ) obj.current = obj.total;

					$that.find('.block_progressbar').progressbar({value: obj.current/obj.total * 100 });

					$that.find('.block_progress_count_current').html(obj.current);

					build_live_block( $that, new_url );
				}
			},
			dataType : 'json'
		});

	}


	//--------------------------------------------  
	//	build the spelling suggestions
	//--------------------------------------------

	function build_suggestions( url ) {

		$.ajax({
			url : url + '&ajax=yes',
			data : null,
			success : function( data, status ){

				if( data.done ){
					// add our marker to say we're done
					// ..					
					$('#lexicon_in_progress').hide();
					
					$( "#lexicon_progressbar").fadeOut(function(){

						$(this).progressbar({value: 0});

						$('#lexicon_progress_count_current').html('0');

						$('#lexicon_success').show()
							.delay(1500)
							.fadeOut( function(){

								$('#build_lexicon_block .last_build').hide();

								$('#build_lexicon_block .just_now').show();

								$('#build_lexicon').html('Rebuild');

								$('#build_lexicon_block').fadeIn();
								
							});

					});


				}
				else {
					var new_url = url.replace(/batch=\d+/, 'batch='+data.batch);
					// mark up some progress indicator
					//alert(' new url : \n' + new_url );
					var obj = { current : data.batch, total : $('#lexicon_progress_count_total').html() };

					if( obj.current > obj.total ) obj.current = obj.total;

					$( "#lexicon_progressbar" ).progressbar({value: obj.current/obj.total * 100 });

					$('#lexicon_progress_count_current').html(obj.current);

					build_lexicon( new_url );
				}
			},
			dataType : 'json'
		});

	}
	


	//--------------------------------------------  
	//	this will function for all field instances
	//--------------------------------------------
	
	$(function(){
					
		//dom item cache
		var $parent 				= $(this),
			$current_words			= $('div.solspace_word_group'),
			ie7						= $parent.hasClass('ie7'),			
			currentVal				= $("input[name='word_input']").val();		

		//--------------------------------------------  
		//	fix stupid ie7 because divs default 100% width
		//	even if you do display inline
		//--------------------------------------------
		
		//fixIE7Widths(fieldID);
		//fixIE7TopWidths(fieldID);
		
		$("input[name='word_input']").keydown(function(e){ 

			//did someone click enter?
			if (e.which == 13)	
			{
				//this has to be done here or we lose it :/
				var $that 		= $(this),
					wordName 	= $.trim($that.val());

				setTimeout(function(){

					if (wordName !== '')
					{			
						if( $('.solspace_word_group .empty_ignore_list').is(':visible') )
						{							
							// make sure the empty word list message is hidden
							$('.solspace_word_group .empty_ignore_list').fadeOut('fast', function(){
								addWord(wordName);
							});
						}
						else
						{
							//fieldID is named earlier in this $.ready statement
							addWord(wordName);
						}

						//remove val
						$("input[name='word_input']").val('');
					}
				}, 50);

				//prevent those events!
				return preventDefault(e);
			}
		});


		$("input[name='word_input']").keyup(function(e){ 

			//did someone click enter?
			if (e.which != 13)	
			{
				if( $("input[name='word_input']").val() != currentVal )
				{
					currentVal = $("input[name='word_input']").val();

					filterWords(currentVal);
				}
			}
		});


		//--------------------------------------------  
		//	remove word (live for new additions)
		//--------------------------------------------
		
		$('.current_word .ex').live('click', function(e){
			var $that 		= $(this),
				$thatParent	= $that.parent(),
				wordName 	= $thatParent.attr('data-word');
			
			//fieldID is named earlier in this $.ready statement
			removeWord(wordName);
			
			$thatParent.fadeOut(200, function(){ 

				$(this).remove();
				
				if( $('.solspace_word_group .current_word').length < 1 ) 
				{
					$('.solspace_word_group .empty_ignore_list').fadeIn('fast');

				}	

			});
			
			return preventDefault(e);
		});

		$('.date_wordy_formatted').click( function() {
			$(this).children('span.date_wordy').toggle();
			$(this).children('span.date_formatted').toggle();
		});

		$('.search_item_more').click( function() { 
			$(this).children('ul').toggle();
		});

		$('.notification .close').click( function() { 
			$(this).parents('.super_search_notification').slideUp();
		});
	

		//--------------------------------------------  
		//	lexicon live build
		//--------------------------------------------

		$('.live_block_build').click( function() {

			var $that = $(this),
				$container = $that.parents('.live_block')

			$container.find('.block_action').fadeOut(function() {
						
				$container.find('.block_state').show();

				$container.find('.block_success').hide();
				
				$container.find('.block_in_progress').show();

				$container.find('.block_progressbar').show();

				$container.find('.block_progressbar').progressbar({value: 0});

				build_live_block( $container, $that.attr('rel') );

			});

			return false;
		});

		//--------------------------------------------  
		//	spelling live build
		//--------------------------------------------

		$('#build_spelling').click( function() {

			container = $(this).parent('.live_block');

			alert('y;');

			$('#build_spelling_block').fadeOut(function(){
					
				$('.lexicon_build_state').show();
				
				$('#lexicon_success').hide();

				$('#lexicon_in_progress').show();

				$('#lexicon_progressbar').show();

				$('#lexicon_progressbar').progressbar({value: 0});

				build_spelling( $('#build_spelling').attr('rel') );

			});


			return false;
		});
		
	});	//END document ready
	
}(jQuery, window));