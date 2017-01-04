<?php

namespace Solspace\Addons\SuperSearch\Model;

class Log extends BaseModel
{
	protected static $_primary_key	= 'log_id';
	protected static $_table_name	= 'super_search_log';

	protected $log_id;
	protected $site_id;
	protected $results;
	protected $search_date;
	protected $term;
	protected $query;
}
//END Preference
