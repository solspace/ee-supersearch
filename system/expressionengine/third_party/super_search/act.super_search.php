<?php if ( ! defined('EXT')) exit('No direct script access allowed');

/**
 * Super Search - Actions
 *
 * @package		Solspace:Super Search
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2009-2016, Solspace, Inc.
 * @link		https://solspace.com/expressionengine/super-search
 * @license		https://solspace.com/software/license-agreement
 * @version		2.2.4
 * @filesource	super_search/act.super_search.php
 */

require_once 'addon_builder/addon_builder.php';

class Super_search_actions extends Addon_builder_super_search
{
	var $errors				= array();

	var $module_preferences = array();

	// --------------------------------------------------------------------

	/**
	 * Clear cache
	 *
	 * Clear cache for a given site
	 *
	 * @access	public
	 * @return	bool
	 */

    function clear_cache ()
    {
		do
		{
			ee()->db->query(
				"DELETE FROM exp_super_search_cache
				WHERE site_id = ".ee()->db->escape_str(ee()->config->item( 'site_id' ))."
				LIMIT 1000 /* Super Search act.super_search.php clear_cache() */"
			);
		} 
		while ( ee()->db->affected_rows() == 1000 );
		
		do
		{			
			ee()->db->query(
				"DELETE FROM exp_super_search_history
				WHERE site_id = ".ee()->db->escape_str(ee()->config->item( 'site_id' ))."
				AND saved = 'n'
				AND cache_id NOT IN (
					SELECT cache_id
					FROM exp_super_search_cache
				)
				LIMIT 1000 /* Super Search act.super_search.php clear_cache() clear history */"
			);
		} 
		while ( ee()->db->affected_rows() == 1000 );
		
		return TRUE;
    }

	//	End clear cache

	// --------------------------------------------------------------------

	/**
	 *  Get the Preferences for This Module
	 *
	 * @access	public
	 * @return	array
	 */

	function module_preferences()
	{
	}

	//	END module_preferences()
}

/* END Class */