jQuery(function($){
	SyntaxHighlighter.autoloader(
		'php  assets/syntaxhighlighter/scripts/shBrushPhp.js',
		'html assets/syntaxhighlighter/scripts/shBrushXml.js',
		'js assets/syntaxhighlighter/scripts/shBrushJs.js',
		'css assets/syntaxhighlighter/scripts/shBrushCss.js'
	);
	SyntaxHighlighter.defaults['gutter'] = false;
	SyntaxHighlighter.defaults['auto-links'] = false;
	SyntaxHighlighter.all();

	$("a.fancybox, .docs a[href$='gif'], .docs a[href$='png'], .docs a[href$='jpg']").fancybox({
		'transitionIn'	: 'elastic',
		'transitionOut'	: 'elastic'
	});
});