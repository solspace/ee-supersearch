<?php

namespace Solspace\Addons\SuperSearch\Model;

class Cache extends BaseModel
{
	protected static $_primary_key	= 'cache_id';
	protected static $_table_name	= 'super_search_cache';

	protected $cache_id;
	protected $site_id;
	protected $type;
	protected $date;
	protected $results;
	protected $hash;
	protected $ids;
	protected $query;
}
//END Cache
