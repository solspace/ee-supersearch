<?php

namespace Solspace\Addons\SuperSearch\Model;

class Index extends BaseModel
{
	protected static $_primary_key	= 'entry_id';
	protected static $_table_name	= 'super_search_indexes';

	protected $entry_id;
	protected $site_id;
	protected $index_date;
}
//END Preference
