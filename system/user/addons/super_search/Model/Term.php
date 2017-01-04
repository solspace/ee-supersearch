<?php

namespace Solspace\Addons\SuperSearch\Model;

class Term extends BaseModel
{
	protected static $_primary_key	= 'term_id';
	protected static $_table_name	= 'super_search_terms';

	protected $term_id;
	protected $site_id;
	protected $term;
	protected $term_soundex;
	protected $term_length;
	protected $first_seen;
	protected $last_seen;
	protected $count;
	protected $entry_count;
	protected $curated_count;
	protected $suggestions;

	protected static $_relationships = array(
		'CuratedEntry' => array(
			'model'		=> 'CuratedEntry',
			'type'		=> 'HasMany',
			'from_key'	=> 'term_id',
			'to_key'	=> 'term_id',
			'weak'		=> TRUE
		)
	);
}
//END Preference
