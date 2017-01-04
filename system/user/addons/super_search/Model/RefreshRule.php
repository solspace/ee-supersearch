<?php

namespace Solspace\Addons\SuperSearch\Model;

class RefreshRule extends BaseModel
{
	protected static $_primary_key	= 'rule_id';
	protected static $_table_name	= 'super_search_refresh_rules';

	protected $rule_id;
	protected $site_id;
	protected $date;
	protected $refresh;
	protected $template_id;
	protected $channel_id;
	protected $category_group_id;
	protected $member_id;
}
//END Preference
