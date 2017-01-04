jQuery(function($){
	var $context		= $('.code_pack_templates:first');
	var $prefix			= $('input[name="prefix"]');
	var $tGroups		= $('.sub-heading', $context);
	var $prefixError	= $('.code_pack_box .alert');
	var $submit			= $('#submit');

	//keyup, change template group names
	$prefix.keyup(function(){
		$tGroups.each(function(){
			var $that = $(this);

			$that.find('.heading-name').html($prefix.val() + $that.attr('data-group'));
		});

		if ($prefix.val().match(/[^a-z0-9\-\_]/g))
		{
			$prefixError.show();
			$submit.attr('disabled', 'disabled').addClass('disabled-btn');
		}
		else
		{
			$prefixError.hide();
			$submit.attr('disabled', false).removeClass('disabled-btn');
		}
	});
});