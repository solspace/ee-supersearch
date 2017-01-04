<?php

namespace Solspace\Addons\SuperSearch\Model;

class LexiconLog extends BaseModel
{
	protected static $_primary_key	= 'lexicon_id';
	protected static $_table_name	= 'super_search_lexicon_log';

	protected $lexicon_id;
	protected $type;
	protected $entry_ids;
	protected $member_id;
	protected $origin;
	protected $action_date;
}
//END Preference
