<?php

return array(
	'author'			=> 'Solspace',
	'author_url'		=> 'https://solspace.com/expressionengine',
	'docs_url'			=> 'https://solspace.com/expressionengine/super-search/docs',
	'name'				=> 'Super Search',
	'description'		=> 'Powerful and flexible searching on channel entries.',
	'version'			=> '3.1.5',
	'namespace'			=> 'Solspace\Addons\SuperSearch',
	'settings_exist'	=> true,
	'models' => array(
		'Cache'			=> 'Model\Cache',
		'CuratedEntry'	=> 'Model\CuratedEntry',
		'Indexes'		=> 'Model\Index',
		'LexiconLog'	=> 'Model\LexiconLog',
		'Log'			=> 'Model\Log',
		'Preference'	=> 'Model\Preference',
		'RefreshRule'	=> 'Model\RefreshRule',
		'Term'			=> 'Model\Term',
	),
	'models.dependencies' => array(
		'CuratedEntry'   => array(
			'ee:ChannelEntry'
		)
	)
);
