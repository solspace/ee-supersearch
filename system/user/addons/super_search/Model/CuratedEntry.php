<?php

namespace Solspace\Addons\SuperSearch\Model;

class CuratedEntry extends BaseModel
{
	protected static $_primary_key	= 'curated_id';
	protected static $_table_name	= 'super_search_curated_entries';

	protected $curated_id;
	protected $term_id;
	protected $entry_id;

	protected static $_relationships = array(
		'ChannelEntry' => array(
			'model'		=> 'ee:ChannelEntry',
			'type'		=> 'HasOne',
			'from_key'	=> 'entry_id',
			'to_key'	=> 'entry_id',
			'weak'		=> TRUE,
			'inverse' => array(
				'name' => 'CuratedEntry',
				'type' => 'hasMany'
			)
		),
		'Term' => array(
			'model'		=> 'Term',
			'type'		=> 'HasOne',
			'from_key'	=> 'term_id',
			'to_key'	=> 'term_id',
			'weak'		=> TRUE
		)
	);
}
//END Preference
