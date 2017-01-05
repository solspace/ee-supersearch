<?php if ( ! defined('EXT')) exit('No direct script access allowed');

/**
 * Super Search - Data Models
 *
 * @package		Solspace:Super Search
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2009-2016, Solspace, Inc.
 * @link		https://solspace.com/expressionengine/super-search
 * @license		https://solspace.com/software/license-agreement
 * @version		2.2.4
 * @filesource	super_search/data.super_search.php
 */

require_once 'addon_builder/data.addon_builder.php';

class Super_search_data extends Addon_builder_data_super_search
{
	var $plural = array(
	        '/(quiz)$/i'               => '$1zes',
	        '/^(ox)$/i'                => '$1en',
	        '/([m|l])ouse$/i'          => '$1ice',
	        '/(matr|vert|ind)ix|ex$/i' => '$1ices',
	        '/(x|ch|ss|sh)$/i'         => '$1es',
	        '/([^aeiouy]|qu)y$/i'      => '$1ies',
	        '/(hive)$/i'               => '$1s',
	        '/(?:([^f])fe|([lr])f)$/i' => '$1$2ves',
	        '/(shea|lea|loa|thie)f$/i' => '$1ves',
	        '/sis$/i'                  => 'ses',
	        '/([ti])um$/i'             => '$1a',
	        '/(tomat|potat|ech|her|vet)o$/i'=> '$1oes',
	        '/(bu)s$/i'                => '$1ses',
	        '/(alias)$/i'              => '$1es',
	        '/(octop)us$/i'            => '$1i',
	        '/(ax|test)is$/i'          => '$1es',
	        '/(us)$/i'                 => '$1es',
	        '/s$/i'                    => 's',
	        '/$/'                      => 's'
	    );

	var $singular = array(
	        '/(quiz)zes$/i'             => '$1',
	        '/(matr)ices$/i'            => '$1ix',
	        '/(vert|ind)ices$/i'        => '$1ex',
	        '/^(ox)en$/i'               => '$1',
	        '/(alias)es$/i'             => '$1',
	        '/(octop|vir)i$/i'          => '$1us',
	        '/(cris|ax|test)es$/i'      => '$1is',
	        '/(shoe)s$/i'               => '$1',
	        '/(o)es$/i'                 => '$1',
	        '/(bus)es$/i'               => '$1',
	        '/([m|l])ice$/i'            => '$1ouse',
	        '/(x|ch|ss|sh)es$/i'        => '$1',
	        '/(m)ovies$/i'              => '$1ovie',
	        '/(s)eries$/i'              => '$1eries',
	        '/([^aeiouy]|qu)ies$/i'     => '$1y',
	        '/([lr])ves$/i'             => '$1f',
	        '/(tive)s$/i'               => '$1',
	        '/(hive)s$/i'               => '$1',
	        '/(li|wi|kni)ves$/i'        => '$1fe',
	        '/(shea|loa|lea|thie)ves$/i'=> '$1f',
	        '/(^analy)ses$/i'           => '$1sis',
	        '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i'  => '$1$2sis',
	        '/([ti])a$/i'               => '$1um',
	        '/(n)ews$/i'                => '$1ews',
	        '/(h|bl)ouses$/i'           => '$1ouse',
	        '/(corpse)s$/i'             => '$1',
	        '/(us)es$/i'                => '$1',
	        '/s$/i'                     => ''
	    );

	var $irregular = array(
	        'move'   => 'moves',
	        'foot'   => 'feet',
	        'goose'  => 'geese',
	        'sex'    => 'sexes',
	        'child'  => 'children',
	        'man'    => 'men',
	        'tooth'  => 'teeth',
	        'person' => 'people'
	    );

	var $uncountable = array(
	        'sheep',
	        'fish',
	        'deer',
	        'series',
	        'species',
	        'money',
	        'rice',
	        'information',
	        'equipment'
	    );

	// Fields encountered that impliment third_party_search_index
	private $fields = array();

	// --------------------------------------------------------------------

	/**
	 * Caching is enabled
	 *
	 * @access	public
	 * @return	boolean
	 */

	function caching_is_enabled( $site_id = '' )
	{
		// --------------------------------------------
		//  Set site id
		// --------------------------------------------

		$site_id	= ( $site_id == '' ) ? $this->EE->config->item('site_id'): $site_id;

		// --------------------------------------------
		//  Prep Cache, Return if Set
		// --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder( array( $site_id ) );

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = FALSE;

		// --------------------------------------------
		//  Caching info
		// --------------------------------------------

		$sql = "/* Super Search data.super_search.php caching_is_enabled() */ SELECT date, refresh
				FROM exp_super_search_refresh_rules
				WHERE ( refresh != 0 OR template_id != 0 OR channel_id != 0 OR category_group_id != 0 ) AND site_id = ".ee()->db->escape_str( $site_id );
		
		$query = ee()->db->query( $sql );
        
        if ( $query->num_rows() == 0 )
        {
			$this->cached['get_refresh_by_site_id'][$cache_hash]		= 0;
			$this->cached['get_refresh_date_by_site_id'][$cache_hash]	= 0;
        	return FALSE;
        }
        
        if ( $query->row('refresh') == 0 )
        {
			$this->cached['get_refresh_by_site_id'][$cache_hash]		= 0;
			$this->cached['get_refresh_date_by_site_id'][$cache_hash]	= 0;
        	return FALSE;
        }
 		
 		// --------------------------------------------
        //  Set refresh for later just in case. Any of the returned rows will work since date and refresh are the same for all rows in a site.
        // --------------------------------------------
        
        $this->cached['get_refresh_by_site_id'][$cache_hash]		= $query->row('refresh');
        $this->cached['get_refresh_date_by_site_id'][$cache_hash]	= $query->row('date');
 		
 		// --------------------------------------------
        //	If we made it this far, caching is enabled.
        // --------------------------------------------
        
		$this->cached[$cache_name][$cache_hash] = TRUE;
        
 		// --------------------------------------------
        //  Return Data
        // --------------------------------------------
 		
 		return $this->cached[$cache_name][$cache_hash];	
    }
    
    //	End caching is enabled
    
	// --------------------------------------------------------------------
	
	/**
	 * Get category groups
	 *
	 * @access	public
	 * @return	array
	 */
    
	function get_category_groups()
    {
 		// --------------------------------------------
        //  Prep Cache, Return if Set
        // --------------------------------------------
 		
 		$cache_name = __FUNCTION__;
 		$cache_hash = $this->_imploder(func_get_args());
 		
 		if (isset($this->cached[$cache_name][$cache_hash]))
 		{
 			return $this->cached[$cache_name][$cache_hash];
 		}
 		
 		$this->cached[$cache_name][$cache_hash] = array();
 		
 		// --------------------------------------------
        //  Grab category groups
        // --------------------------------------------
		
		$sql = "/* Super Search data.super_search.php get_category_groups() */ SELECT group_id, group_name
				FROM exp_category_groups
				WHERE site_id = ".ee()->db->escape_str( ee()->config->item('site_id') )."
				ORDER BY group_name ASC";
		
		$query = ee()->db->query( $sql );
		
		foreach($query->result_array() as $row)
		{
			$this->cached[$cache_name][$cache_hash][$row['group_id']] = $row['group_name'];
		}
        
 		// --------------------------------------------
        //  Return Data
        // --------------------------------------------
 		
 		return $this->cached[$cache_name][$cache_hash];	
    }
    
    //	End get category groups

	// --------------------------------------------------------------------
	
	/**
	 * Get channels
	 *
	 * @access	public
	 * @return	array
	 */
    
	function get_channels()
    { 		
 		// --------------------------------------------
        //  Prep Cache, Return if Set
        // --------------------------------------------
 		
 		$cache_name = __FUNCTION__;
 		$cache_hash = $this->_imploder(func_get_args());
 		
 		if (isset($this->cached[$cache_name][$cache_hash]))
 		{
 			return $this->cached[$cache_name][$cache_hash];
 		}
 		
 		$this->cached[$cache_name][$cache_hash] = array();
 		
 		// --------------------------------------------
        //  Grab channels
        // --------------------------------------------
		
		$sql	= "/* Super Search data.super_search.php get_channels() */
			SELECT
				site_id,
				channel_id,
				channel_name,
				channel_title,
				cat_group,
				field_group,
				channel_html_formatting,
				channel_allow_img_urls,
				channel_auto_link_urls,
				comment_url,
				channel_url,
				search_results_url,
				search_excerpt
			FROM exp_channels
			ORDER BY channel_title ASC";
		
		$query = ee()->db->query( $sql );
		
		foreach($query->result_array() as $row)
		{
			$this->cached[$cache_name][$cache_hash][$row['channel_id']] = $row;
		}
        
 		// --------------------------------------------
        //  Return Data
        // --------------------------------------------
 		
 		return $this->cached[$cache_name][$cache_hash];	
    }
    
    //	End get channels

	// --------------------------------------------------------------------

	/**
	 * Get channels by site id
	 *
	 * @access	public
	 * @return	array
	 */

	function get_channels_by_site_id( $site_id = '' )
    {
 		// --------------------------------------------
        //  Set site id
        // --------------------------------------------
        
        $site_id	= ( is_string( $site_id ) === TRUE AND $site_id == '' ) ? ee()->config->item('site_id'): $site_id;
 		
 		// --------------------------------------------
        //  Prep Cache, Return if Set
        // --------------------------------------------
 		
 		$cache_name = __FUNCTION__;
 		$cache_hash = $this->_imploder(func_get_args());
 		
 		if (isset($this->cached[$cache_name][$cache_hash]))
 		{
 			return $this->cached[$cache_name][$cache_hash];
 		}
 		
 		$this->cached[$cache_name][$cache_hash] = array();
 		
 		// --------------------------------------------
        //  Site ids
        // --------------------------------------------
        
        $site_ids	= array();
        
        if ( is_array( $site_id ) === TRUE AND ! empty( $site_id ) )
        {
        	$site_ids	= $site_id;
        }
        elseif ( is_string( $site_id ) === TRUE AND $site_id != '' )
        {
        	$site_ids[]	= $site_id;
        }
 		
 		// --------------------------------------------
        //  Grab channels
        // --------------------------------------------
		
		foreach ( $this->get_channels() as $row )
		{
			if ( ! count( $site_ids ) AND in_array( $row['site_id'], $site_ids ) === FALSE ) continue;

			$this->cached[$cache_name][$cache_hash][$row['channel_id']] = $row;
			$this->cached['get_channels_by_site_id_keyed_to_name'][$cache_hash][$row['channel_name']] = $row;
		}

		// --------------------------------------------
		//  Return Data
		// --------------------------------------------

		return $this->cached[$cache_name][$cache_hash];
	}

	//	End get channels by site id

	// --------------------------------------------------------------------

	/**
	 * Get channels by site id and channel id
	 *
	 * @access	public
	 * @return	array
	 */

	function get_channels_by_site_id_and_channel_id( $site_id = '', $channel_id = '' )
    {
 		// --------------------------------------------
        //  Prep Cache, Return if Set
        // --------------------------------------------
 		
 		$cache_name = __FUNCTION__;
 		$cache_hash = $this->_imploder(func_get_args());
 		
 		if (isset($this->cached[$cache_name][$cache_hash]))
 		{
 			return $this->cached[$cache_name][$cache_hash];
 		}
 		
 		$this->cached[$cache_name][$cache_hash] = array();
 		
 		// --------------------------------------------
        //  Site ids
        // --------------------------------------------
        
        $site_ids	= array();
        
        if ( is_array( $site_id ) === TRUE AND ! empty( $site_id ) )
        {
        	$site_ids	= $site_id;
        }
        elseif ( is_string( $site_id ) === TRUE AND $site_id != '' )
        {
        	$site_ids[]	= $site_id;
        }
 		
 		// --------------------------------------------
        //  Channel ids
        // --------------------------------------------
        
        $channel_ids	= array();
        
        if ( is_array( $channel_id ) === TRUE AND ! empty( $channel_id ) )
        {
        	$channel_ids	= $channel_id;
        }
        elseif ( is_string( $channel_id ) === TRUE AND $channel_id != '' )
        {
        	$channel_ids[]	= $channel_id;
        }
 		
 		// --------------------------------------------
        //  Grab channels
        // --------------------------------------------
		
		foreach ( $this->get_channels() as $row )
		{
			if ( ! empty( $site_ids ) AND in_array( $row['site_id'], $site_ids ) === FALSE ) continue;

			if ( ! empty( $channel_ids ) AND in_array( $row['channel_id'], $channel_ids ) === FALSE ) continue;

			$this->cached[$cache_name][$cache_hash][$row['channel_id']] = $row;
		}

		// --------------------------------------------
		//  Return Data
		// --------------------------------------------

		return $this->cached[$cache_name][$cache_hash];
	}

	//	End get channels by site id and channel id

	// --------------------------------------------------------------------

	/**
	 * Get channels by site id keyed to name
	 *
	 * @access	public
	 * @return	array
	 */

	function get_channels_by_site_id_keyed_to_name( $site_id = '' )
    { 		
 		// --------------------------------------------
        //  Set site id
        // --------------------------------------------
        
        $site_id	= ( is_string( $site_id ) === TRUE AND $site_id == '' ) ? ee()->config->item('site_id'): $site_id;
 		
 		// --------------------------------------------
        //  Prep Cache, Return if Set
        // --------------------------------------------
 		
 		$cache_name = __FUNCTION__;
 		$cache_hash = $this->_imploder(func_get_args());
 		
 		if (isset($this->cached[$cache_name][$cache_hash]))
 		{
 			return $this->cached[$cache_name][$cache_hash];
 		}
 		
 		$this->cached[$cache_name][$cache_hash] = array();
 		
 		// --------------------------------------------
        //  Site ids
        // --------------------------------------------
        
        $site_ids	= array();
        
        if ( is_array( $site_id ) === TRUE AND ! empty( $site_id ) )
        {
        	$site_ids	= $site_id;
        }
        elseif ( is_string( $site_id ) === TRUE AND $site_id != '' )
        {
        	$site_ids[]	= $site_id;
        }
 		
 		// --------------------------------------------
        //  Grab channels
        // --------------------------------------------
		
		foreach ( $this->get_channels() as $row )
		{
			if ( ! empty( $site_ids ) AND in_array( $row['site_id'], $site_ids ) === FALSE ) continue;

			$this->cached[$cache_name][$cache_hash][$row['channel_name']] = $row;
			$this->cached['get_channels_by_site_id'][$cache_hash][$row['channel_id']] = $row;
		}

		// --------------------------------------------
		//  Return Data
		// --------------------------------------------

		return $this->cached[$cache_name][$cache_hash];
	}

	//	End get channels by site id keyed to name

	// --------------------------------------------------------------------

	/**
	 * Get channel titles
	 *
	 * @access	public
	 * @return	array
	 */

	function get_channel_titles( $site_id = '' )
	{
		// --------------------------------------------
		//  Prep Cache, Return if Set
		// --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = array();

		// --------------------------------------------
		//  Site ids
		// --------------------------------------------

		$site_ids	= array();

		if ( is_array( $site_id ) === TRUE AND ! empty( $site_id ) )
		{
			$site_ids	= $site_id;
		}
		else
		{
			$site_ids[]	= $site_id;
		}

		// --------------------------------------------
		//  Grab channels
		// --------------------------------------------

		foreach ( $this->get_channels() as $row )
		{
			if ( ! empty( $site_ids ) AND in_array( $row['site_id'], $site_ids ) === FALSE ) continue;

			$this->cached[$cache_name][$cache_hash][$row['channel_id']] = $row['channel_title'];
		}

		// --------------------------------------------
		//  Return Data
		// --------------------------------------------

		return $this->cached[$cache_name][$cache_hash];
	}

	//	End get channel titles

	// --------------------------------------------------------------------

	/**
	 * Get refresh rule by template id
	 *
	 * @access	public
	 * @return	array
	 */

	function get_refresh_rule_by_template_id( $template_id = 0 )
	{
		// --------------------------------------------
		//  Prep Cache, Return if Set
		// --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = FALSE;

		// --------------------------------------------
		//  Grab cache ids
		// --------------------------------------------

		if ( $template_id === 0 OR is_numeric( $template_id ) === FALSE ) return $this->cached[$cache_name][$cache_hash];

		$sql = "/* Super Search data.super_search.php get_refresh_rule_by_template_id() */ SELECT COUNT(*) AS count
				FROM exp_super_search_refresh_rules
				WHERE template_id = ".ee()->db->escape_str( $template_id );
		
		$query = ee()->db->query( $sql );
		
		if ( $query->num_rows() > 0 AND $query->row('count') > 0 )
		{
			$this->cached[$cache_name][$cache_hash] = TRUE;
		}

		// --------------------------------------------
		//  Return Data
		// --------------------------------------------

		return $this->cached[$cache_name][$cache_hash];
	}

	//	End get refresh rule by template id

	// --------------------------------------------------------------------

	/**
	 * Get refresh rule by channel id
	 *
	 * @access	public
	 * @return	array
	 */

	function get_refresh_rule_by_channel_id( $channel_id = 0 )
	{
		// --------------------------------------------
		//  Prep Cache, Return if Set
		// --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = FALSE;

		// --------------------------------------------
		//  Grab cache ids
		// --------------------------------------------

		if ( $channel_id === 0 OR is_numeric( $channel_id ) === FALSE ) return $this->cached[$cache_name][$cache_hash];

		$sql = "/* Super Search data.super_search.php get_refresh_rule_by_channel_id() */ SELECT COUNT(*) AS count
				FROM exp_super_search_refresh_rules
				WHERE channel_id = ".ee()->db->escape_str( $channel_id );
		
		$query = ee()->db->query( $sql );
		
		if ( $query->num_rows() > 0 AND $query->row('count') > 0 )
		{
			$this->cached[$cache_name][$cache_hash] = TRUE;
		}

		// --------------------------------------------
		//  Return Data
		// --------------------------------------------

		return $this->cached[$cache_name][$cache_hash];
	}

	//	End get refresh rule by channel id

	// --------------------------------------------------------------------

	/**
	 * Get refresh rule by category group id
	 *
	 * @access	public
	 * @return	array
	 */

	function get_refresh_rule_by_category_group_id( $group_id = 0 )
	{
		// --------------------------------------------
		//  Prep Cache, Return if Set
		// --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = FALSE;

		// --------------------------------------------
		//  Grab cache ids
		// --------------------------------------------

		if ( $group_id === 0 OR is_numeric( $group_id ) === FALSE ) return $this->cached[$cache_name][$cache_hash];

		$sql = "/* Super Search data.super_search.php get_refresh_rule_by_category_group_id() */ SELECT COUNT(*) AS count
				FROM exp_super_search_refresh_rules
				WHERE category_group_id = ".ee()->db->escape_str( $group_id );
		
		$query = ee()->db->query( $sql );
		
		if ( $query->num_rows() > 0 AND $query->row('count') > 0 )
		{
			$this->cached[$cache_name][$cache_hash] = TRUE;
		}

		// --------------------------------------------
		//  Return Data
		// --------------------------------------------

		return $this->cached[$cache_name][$cache_hash];
	}

	//	End get refresh rule by category group id

	// --------------------------------------------------------------------

	/**
	 * Get refresh by site id
	 *
	 * @access	public
	 * @return	integer
	 */

	function get_refresh_by_site_id( $site_id = '' )
    {
 		// --------------------------------------------
        //  Set site id
        // --------------------------------------------
        
        $site_id	= ( $site_id == '' ) ? ee()->config->item('site_id'): $site_id;
 		
 		// --------------------------------------------
        //  Prep Cache, Return if Set
        // --------------------------------------------
 		
 		$cache_name = __FUNCTION__;
 		$cache_hash = $this->_imploder( array( $site_id ) );
 		
 		if (isset($this->cached[$cache_name][$cache_hash]))
 		{
 			return $this->cached[$cache_name][$cache_hash];
 		}
 		
 		$this->cached[$cache_name][$cache_hash] = 0;
 		
 		// --------------------------------------------
        //  Grab refresh rule
        // --------------------------------------------
		
		$sql = "/* Super Search data.super_search.php get_refresh_by_site_id() */ SELECT date, refresh
				FROM exp_super_search_refresh_rules
				WHERE site_id = ".ee()->db->escape_str( $site_id )."
				LIMIT 1";
		
		$query = ee()->db->query( $sql );
		
		if ( $query->num_rows() == 0 OR $query->row('refresh') === FALSE )
		{
			$this->cached['get_refresh_date_by_site_id'][$cache_hash]	= 0;
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached['get_refresh_date_by_site_id'][$cache_hash]	= $query->row('date');
		$this->cached[$cache_name][$cache_hash] 					= $query->row('refresh');

		// --------------------------------------------
		//  Return Data
		// --------------------------------------------

		return $this->cached[$cache_name][$cache_hash];
	}

	//	End get refresh by site id

	// --------------------------------------------------------------------

	/**
	 * Get refresh date by site id
	 *
	 * @access	public
	 * @return	integer
	 */

	function get_refresh_date_by_site_id( $site_id = '' )
    {
 		// --------------------------------------------
        //  Set site id
        // --------------------------------------------
        
        $site_id	= ( $site_id == '' ) ? ee()->config->item('site_id'): $site_id;
 		
 		// --------------------------------------------
        //  Prep Cache, Return if Set
        // --------------------------------------------
 		
 		$cache_name = __FUNCTION__;
 		$cache_hash = $this->_imploder( array( $site_id ) );
 		
 		if (isset($this->cached[$cache_name][$cache_hash]))
 		{
 			return $this->cached[$cache_name][$cache_hash];
 		}
 		
 		$this->cached[$cache_name][$cache_hash] = 0;
 		
 		// --------------------------------------------
        //  Grab refresh rule
        // --------------------------------------------
		
		$sql = "/* Super Search data.super_search.php get_refresh_date_by_site_id() */ SELECT date, refresh
				FROM exp_super_search_refresh_rules
				WHERE site_id = ".ee()->db->escape_str( $site_id )."
				LIMIT 1";
		
		$query = ee()->db->query( $sql );
		
		if ( $query->num_rows() == 0 OR $query->row('date') === FALSE )
		{
			$this->cached['get_refresh_by_site_id'][$cache_hash]	= 0;
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached['get_refresh_by_site_id'][$cache_hash]	= $query->row('refresh');
		$this->cached[$cache_name][$cache_hash] 				= $query->row('date');

		// --------------------------------------------
		//  Return Data
		// --------------------------------------------

		return $this->cached[$cache_name][$cache_hash];
	}

	//	End get refresh date by site id

	// --------------------------------------------------------------------

	/**
	 * Get selected category groups by site id
	 *
	 * @access	public
	 * @return	array
	 */

	function get_selected_category_groups_by_site_id( $site_id = '' )
	{
		// --------------------------------------------
		//  Prep Cache, Return if Set
		// --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = array();

		// --------------------------------------------
		//  Grab channels
		// --------------------------------------------

		if ( $site_id === 0 OR is_numeric( $site_id ) === FALSE ) return $this->cached[$cache_name][$cache_hash];

		$sql = "/* Super Search data.super_search.php get_selected_category_groups_by_site_id() */ SELECT category_group_id
				FROM exp_super_search_refresh_rules
				WHERE category_group_id != 0
				AND site_id = ".ee()->db->escape_str( $site_id );
		
		$query = ee()->db->query( $sql );
		
		foreach($query->result_array() as $row)
		{
			$this->cached[$cache_name][$cache_hash][] = $row['category_group_id'];
		}

		// --------------------------------------------
		//  Return Data
		// --------------------------------------------

		return $this->cached[$cache_name][$cache_hash];
	}

	//	End selected category groups by site id

	// --------------------------------------------------------------------

	/**
	 * Get selected template groups by site id
	 *
	 * @access	public
	 * @return	array
	 */

	function get_selected_template_groups_by_site_id( $site_id = 0 )
	{
		// --------------------------------------------
		//  Prep Cache, Return if Set
		// --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = array();

		// --------------------------------------------
		//  Grab channels
		// --------------------------------------------

		if ( $site_id === 0 OR is_numeric( $site_id ) === FALSE ) return $this->cached[$cache_name][$cache_hash];

		$sql = "/* Super Search data.super_search.php get_selected_template_groups_by_site_id() */ SELECT group_id
				FROM exp_templates
				WHERE template_id IN (
				SELECT template_id
				FROM exp_super_search_refresh_rules
				WHERE template_id != 0
				AND site_id = ".ee()->db->escape_str( $site_id ).")";
		
		$query = ee()->db->query( $sql );
		
		foreach($query->result_array() as $row)
		{
			$this->cached[$cache_name][$cache_hash][] = $row['group_id'];
		}

		// --------------------------------------------
		//  Return Data
		// --------------------------------------------

		return $this->cached[$cache_name][$cache_hash];
	}

	//	End selected template groups by site id

	// --------------------------------------------------------------------

	/**
	 * Get selected templates by site id
	 *
	 * @access	public
	 * @return	array
	 */

	function get_selected_templates_by_site_id( $site_id = 0 )
	{
		// --------------------------------------------
		//  Prep Cache, Return if Set
		// --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = array();

		// --------------------------------------------
		//  Grab channels
		// --------------------------------------------

		if ( $site_id === 0 OR is_numeric( $site_id ) === FALSE ) return $this->cached[$cache_name][$cache_hash];

		$sql = "/* Super Search data.super_search.php get_selected_templates_by_site_id() */ SELECT template_id
				FROM exp_super_search_refresh_rules
				WHERE template_id != 0
				AND site_id = ".ee()->db->escape_str( $site_id );
		
		$query = ee()->db->query( $sql );
		
		foreach($query->result_array() as $row)
		{
			$this->cached[$cache_name][$cache_hash][] = $row['template_id'];
		}

		// --------------------------------------------
		//  Return Data
		// --------------------------------------------

		return $this->cached[$cache_name][$cache_hash];
	}

	//	End selected templates by site id

	// --------------------------------------------------------------------

	/**
	 * Get selected channels by site id
	 *
	 * @access	public
	 * @return	array
	 */

	function get_selected_channels_by_site_id( $site_id = 0 )
	{
		// --------------------------------------------
		//  Prep Cache, Return if Set
		// --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = array();

		// --------------------------------------------
		//  Grab channels
		// --------------------------------------------

		if ( $site_id === 0 OR is_numeric( $site_id ) === FALSE ) return $this->cached[$cache_name][$cache_hash];

		$sql = "/* Super Search data.super_search.php get_selected_channels_by_site_id() */ SELECT channel_id
				FROM exp_super_search_refresh_rules
				WHERE channel_id != 0
				AND site_id = ".ee()->db->escape_str( $site_id );
		
		$query = ee()->db->query( $sql );
		
		foreach($query->result_array() as $row)
		{
			$this->cached[$cache_name][$cache_hash][] = $row['channel_id'];
		}

		// --------------------------------------------
		//  Return Data
		// --------------------------------------------

		return $this->cached[$cache_name][$cache_hash];
	}

	//	End selected channels by site id

	// --------------------------------------------------------------------

	/**
	 * Get template groups
	 *
	 * @access	public
	 * @return	array
	 */

	function get_template_groups()
	{
		// --------------------------------------------
		//  Prep Cache, Return if Set
		// --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = array();

		// --------------------------------------------
		//  Grab channels
		// --------------------------------------------

		$sql = "/* Super Search data.super_search.php get_template_groups() */ SELECT tg.group_id, tg.group_name, t.template_id, t.template_name
				FROM exp_template_groups tg
				LEFT JOIN exp_templates t ON t.group_id = tg.group_id
				WHERE tg.site_id = ".ee()->db->escape_str( ee()->config->item('site_id') )."
				ORDER BY tg.group_order, t.template_name ASC";
		
		$query = ee()->db->query( $sql );
		
		foreach($query->result_array() as $row)
		{
			$this->cached[ $cache_name ][ $cache_hash ][ $row['group_id'] ]	= $row['group_name'];
		}

		// --------------------------------------------
		//  Return Data
		// --------------------------------------------

		return $this->cached[$cache_name][$cache_hash];
	}

	//	End get template groups

	// --------------------------------------------------------------------

	/**
	 * Get templates by group id
	 *
	 * @access	public
	 * @return	array
	 */

	function get_templates_by_group_id( $group_id = 0 )
	{
		// --------------------------------------------
		//  Prep Cache, Return if Set
		// --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = array();

		// --------------------------------------------
		//  Grab channels
		// --------------------------------------------

		if ( $group_id === 0 OR is_numeric( $group_id ) === FALSE ) return $this->cached[$cache_name][$cache_hash];

		$sql = "/* Super Search data.super_search.php get_templates_by_group_id() */ SELECT t.template_id, t.template_name
				FROM exp_templates t
				WHERE t.group_id = ".ee()->db->escape_str( $group_id )."
				ORDER BY t.template_name ASC";
		
		$query = ee()->db->query( $sql );
		
		foreach($query->result_array() as $row)
		{
			$this->cached[ $cache_name ][ $cache_hash ][ $row['template_id'] ]	= $row['template_name'];
		}
        
 		// --------------------------------------------
        //  Return Data
        // --------------------------------------------
 		
 		return $this->cached[$cache_name][$cache_hash];	
    }
    
    //	End get templates by group id
	
	// --------------------------------------------------------------------
	
	/**
	 * Playa is installed
	 *
	 * @access	public
	 * @return	boolean
	 */
    
	function playa_is_installed()
    {        
        return FALSE;
 	}
 	
 	//	End playa is installed

	// --------------------------------------------------------------------
	
	/**
	 * Set default cache prefs
	 *
	 * @access	public
	 * @return	null
	 */
    
	function set_default_cache_prefs()
    { 		
 		$sites	= $this->get_sites();
        
 		// --------------------------------------------
        //	Get existing prefs
        // --------------------------------------------
        
        $sql	= "SELECT site_id FROM exp_super_search_refresh_rules";
        
        $query	= ee()->db->query( $sql );
        
        $prefs	= array();
        
        foreach ( $query->result_array() as $row )
        {
        	$prefs[]	= $row['site_id'];
        }
        
 		// --------------------------------------------
        //	Loop and load
        // --------------------------------------------
        
        $refresh		= 10;
        $next_refresh	= ee()->localize->now + ( $refresh * 60 );
        
        foreach ( array_keys( $sites ) as $site )
        {
        	if ( in_array( $site, $prefs ) === TRUE ) continue;
        	
        	$sql	= ee()->db->insert_string( 'exp_super_search_refresh_rules', array( 'site_id' => $site, 'refresh' => $refresh, 'date' => $next_refresh, 'member_id' => ee()->session->userdata('member_id') ) );
        	
        	ee()->db->query( $sql );
        }
 	}
 	
 	//	End set default cache prefs

	// --------------------------------------------------------------------
	
	/**
	 * Set default cache prefs
	 *
	 * @access	public
	 * @return	null
	 */
    
	function set_default_search_preferences()
    { 		
 		// --------------------------------------------
        //	Prep
        // --------------------------------------------
        
        $defaults = array( 
        	'use_ignore_word_list'	=> 'y', 
			'ignore_word_list'		=> 'a||and||of||or||the',
			'enable_search_log'		=> 'n'
		);
		
 		// --------------------------------------------
        //	Loop
        // --------------------------------------------
        
        foreach ( $this->get_sites() as $site_id => $site_name )
        {
			// --------------------------------------------
			//	Load
			// --------------------------------------------
        
        	$this->set_preference( $defaults, $site_id );
        }
        
		// --------------------------------------------
		//	Return
		// --------------------------------------------
		
		return TRUE;
 	}
 	
 	//	End set default cache prefs

	// --------------------------------------------------------------------
	
	/**
	 * Set new refresh date
	 *
	 * @access	public
	 * @return	boolean
	 */
    
	function set_new_refresh_date( $site_id = '' )
    { 		
 		// --------------------------------------------
        //  Set site id
        // --------------------------------------------
        
        $site_id	= ( $site_id == '' ) ? ee()->config->item('site_id'): $site_id;
 		
 		// --------------------------------------------
        //  Update
        // --------------------------------------------
        
        $refresh	= $this->get_refresh_by_site_id( $site_id );
        
        $sql	= ee()->db->update_string( 'exp_super_search_refresh_rules', array( 'date' => ee()->localize->now + ( $refresh * 60 ) ), array( 'site_id' => $site_id ) );
        
        ee()->db->query( $sql );
 	}
 	
 	//	End set new refresh date
	
	// --------------------------------------------------------------------
	
	/**
	 * Set preference
	 *
	 * @access	public
	 * @return	array
	 */

	function set_preference( $preferences = array(), $site_id = '' )
    {    
 		// --------------------------------------------
        //  Prep Cache, Return if Set
        // --------------------------------------------
 		
 		$cache_name = __FUNCTION__;
 		$cache_hash = $this->_imploder(func_get_args());
 		
 		if (isset($this->cached[$cache_name][$cache_hash]))
 		{
 			return $this->cached[$cache_name][$cache_hash];
 		}
 		
 		$this->cached[$cache_name][$cache_hash] = array();
    
 		// --------------------------------------------
        //	Grab prefs from DB
        // --------------------------------------------
        
        if( $site_id == '' ) $site_id = ee()->config->item('site_id');

        $sql	= "SELECT site_system_preferences
					FROM exp_sites
					WHERE site_id = " . ee()->db->escape_str( $site_id );
        	
        $query	= ee()->db->query( $sql );

        if ( $query->num_rows() == 0 ) return FALSE;
        
        ee()->load->helper('string');
        
		$this->cached[$cache_name][$cache_hash] = unserialize( base64_decode( $query->row('site_system_preferences') ) );

 		// --------------------------------------------
        //	Add our prefs
        // --------------------------------------------
        
        $prefs	= array();
        
        foreach ( explode( "|", SUPER_SEARCH_PREFERENCES ) as $val )
        {
        	if ( isset( $preferences[$val] ) === TRUE )
        	{
        		$this->cached[$cache_name][$cache_hash][$val]	= $preferences[$val];
        	}
        }
			

		$prefs = '';
		
		$prefs = base64_encode( serialize( $this->cached[$cache_name][$cache_hash] ) );

		ee()->db->query( ee()->db->update_string(
					'exp_sites',
					array(
						'site_system_preferences' => $prefs
					),
					array(
						'site_id'	=> ee()->db->escape_str( $site_id )
					)
				)
			);

		return TRUE;
	}

	//	End set preference

	// --------------------------------------------------------------------

	/**
	 * Repload Preferences
	 *
	 * @access	public
	 * @return	array
	 */

	function reload_preferences()
    {    
 		// --------------------------------------------
        //  Prep Cache, Return if Set
        // --------------------------------------------
 		
 		$cache_name = __FUNCTION__;
 		$cache_hash = $this->_imploder(func_get_args());
 		
 		if (isset($this->cached[$cache_name][$cache_hash]))
 		{
 			return $this->cached[$cache_name][$cache_hash];
 		}
 		
 		$this->cached[$cache_name][$cache_hash] = array();
    
 		// --------------------------------------------
        //	Grab prefs from DB
        // --------------------------------------------

        $sql	= "SELECT site_system_preferences
					FROM exp_sites
					WHERE site_id = " . ee()->db->escape_str( ee()->config->item('site_id') );
        	
        $query	= ee()->db->query( $sql );

        if ( $query->num_rows() == 0 ) return FALSE;
        
        ee()->load->helper('string');
        
		$this->cached[$cache_name][$cache_hash] = unserialize( base64_decode( $query->row('site_system_preferences') ) );

 		return $this->cached[$cache_name][$cache_hash];
	}

	//	End set preference

	// --------------------------------------------------------------------

	/**
	 * Time to refresh cache
	 *
	 * @access	public
	 * @return	boolean
	 */

	function time_to_refresh_cache( $site_id = '' )
    { 		
 		// --------------------------------------------
        //  Set site id
        // --------------------------------------------
        
        $site_id	= ( $site_id == '' ) ? ee()->config->item('site_id'): $site_id;
 		
 		// --------------------------------------------
        //  Prep Cache, Return if Set
        // --------------------------------------------
 		
 		$cache_name = __FUNCTION__;
 		$cache_hash = $this->_imploder( array( $site_id ) );
 		
 		if (isset($this->cached[$cache_name][$cache_hash]))
 		{
 			return $this->cached[$cache_name][$cache_hash];
 		}
 		
 		$this->cached[$cache_name][$cache_hash] = FALSE;
 		
 		// --------------------------------------------
        //  Should we refresh?
        // --------------------------------------------
        
        if ( $this->get_refresh_date_by_site_id( $site_id ) > 0 AND $this->get_refresh_date_by_site_id( $site_id ) <= ee()->localize->now )
        {
			$this->cached[$cache_name][$cache_hash] = TRUE;
		}

		return $this->cached[$cache_name][$cache_hash];
	}

	//	End time to refresh cache

	// --------------------------------------------------------------------

	/**
	 * Save to search log
	 *
	 * @access	public
	 * @return	boolean
	 */

	function log_search( $uri = array(), $results = 0)
    {
 		// --------------------------------------------
        //  Prep
        // --------------------------------------------
 		
 		$site_id	= ( isset( $uri['uri']['site'] ) ) ? $uri['uri']['site'] : ee()->config->item('site_id');

		$keywords	= ( isset( $uri['uri']['keywords'] ) ) ? $uri['uri']['keywords'] : '';
		
		$uri		= ( isset( $uri['uri'] ) ) ? $uri['uri']: array();
		
 		// --------------------------------------------
        //  We don't log searches by default. They gobble DB.
        // --------------------------------------------
		
		if ( ee()->config->item('enable_search_log') != 'n' )
		{
			$arr = array(
					'site_id' 		=> $site_id,
					'results'		=> $results,
					'search_date'	=> ee()->localize->now,
					'term'			=> ( $keywords ),
					'query' 		=> base64_encode( serialize( $uri )));
	
			$sql	= ee()->db->insert_string( 'exp_super_search_log', $arr );
			
			ee()->db->insert('exp_super_search_log',$arr);
			
			// ee()->db->query( $sql );
		}
		
 		// --------------------------------------------
        //  Log terms
        // --------------------------------------------

        $this->log_terms( $keywords );

        return TRUE;
 	}
 	
 	//	End save search to log

	// --------------------------------------------------------------------

	/**
	 * Save to search terms
	 *
	 * @access	public
	 * @return	boolean
	 */
    
	function log_terms( $keywords = '' )
    {
        if( $keywords == '' ) return TRUE;

 		// --------------------------------------------
        //  Prep the terms, update if set
        // --------------------------------------------

        $sql = " INSERT INTO exp_super_search_terms ( term, term_soundex, site_id, first_seen, last_seen, count )
        			VALUES ";

        $sql_block = array();

       	$keywords = ( explode( ' ', $keywords ));

       	if( count( $keywords ) > 0 ) 
       	{
       		foreach( $keywords AS $term )
       		{       			
       			$sql_block[] = "( '" . ee()->db->escape_str( $term ) . "', soundex('" . ee()->db->escape_str( $term ) . "'), " . ee()->db->escape_str( ee()->config->item('site_id') ) . ", '" . ee()->db->escape_str( ee()->localize->now ) . "', '". ee()->db->escape_str( ee()->localize->now ) . "', 1 )";
       		}
       	}

       	$sql .= implode( ', ', $sql_block );

       	$sql .= " ON DUPLICATE KEY UPDATE last_seen = '" . ee()->db->escape_str( ee()->localize->now ) . "', count = count + 1 , first_seen = IF( first_seen = 0, '". ee()->db->escape_str( ee()->localize->now ) . "', first_seen ) ";

       	ee()->db->query( $sql );

        return TRUE;
 	}
 	
 	//	End save search to terms

	// --------------------------------------------------------------------

	/**
	 * Build index single
	 *
	 * @access	private
	 * @param 	string
	 * @param 	int
	 * @param 	int
	 * @return	bool
	 */
	 
	function _build_index_single( $entry_id = 0 )
	{
		if( $entry_id <= 0 OR ! is_numeric( $entry_id ) ) return false;

		$data = $this->sess['lexicon']['entries'][ $entry_id ];

		$terms = array();
		
		ee()->load->library('api');
		ee()->api->instantiate('channel_fields');
		ee()->api_channel_fields->fetch_all_fieldtypes();

		foreach( $data as $key => $row )
		{
			if( $key == 'entry_id' ) continue;

			if (isset($this->sess['index']['fields'][$key]))
			{
				$id	= str_replace('field_id_', '', $key);
				$meta	= $this->sess['index']['fields'][$key];
				ee()->api_channel_fields->set_settings(
					$id,
					array(
						'field_id'		=> $id,
						'field_type'	=> $meta['name']
					)
				);
				ee()->api_channel_fields->setup_handler($id);
					
				if (ee()->api_channel_fields->check_method_exists('third_party_search_index'))
				{
					$terms[$key]	= ee()->db->escape_str(ee()->api_channel_fields->apply('third_party_search_index', array($row)));
				}
			}
		}

		$insert = array(
			'entry_id'      => $entry_id,
			'site_id'       => ee()->config->item('site_id'),
			'index_date'    => ee()->localize->now
		);

		$insert = array_merge( $insert, $terms );

		$sql = ee()->db->insert_string('exp_super_search_indexes', $insert );

		// --------------------------------------
		// Change insert to replace to update existing entry
		// --------------------------------------

		ee()->db->query( preg_replace('/^INSERT/', 'REPLACE', $sql) );

		return true;
	}

	//	END Build Index Single

	// --------------------------------------------------------------------

	/**
	 * Build lexicon
	 *
	 * @access	private
	 * @param 	string
	 * @param 	int
	 * @param 	int
	 * @param 	int
	 * @return	array
	 */

	function build_lexicon( $type = 'single', $entry_id = 0, $batch = 0, $total = 50 )
	{
		// Make a reasonable batch size based on the $total value
		// Smaller will seem quiker to users, but take longer
		// larger will be quicker but gives the ui nothing to feedback with

		$batch_size = 25;

		if( $total < 50 )
		{
			$batch_size = 1;
		}
		elseif( $total < 250 )
		{
			$batch_size = 10;
		}
		elseif( $total < 1000 )
		{
			$batch_size = 25;
		}
		elseif( $total < 5000 )
		{
			$batch_size = 100;
		}
		else
		{
			$batch_size = 250;
		}

		$ret = array();

		$ret['done'] = FALSE;

		$ret['state'] = 'in_progress';

		if( $type == 'build' AND $batch == 0 )
		{
			// This is a full rebuild, first zero out all the counts
			ee()->db->query( 'UPDATE exp_super_search_terms SET entry_count = 0');
		}

		// This is the build lexicon
		// The lexicon keeps a record of every individual term across our sites
		// this later can get matched against misspellings, phonetic variants
		// and corrections

		// The first time the lexicon is built, it has to rip through all the entries in
		// our sites and split up every field into it's individual word
		// This is an expensive operation and we'll do it in batches, and only once
		// After we've done it the first time we only need to update it on single
		// entry creation/edits. If an entry is deleted or editied to remove terms
		// we leave them in. To clear those orphaned terms out we have to rebuild completely

		// What are we doing?

		if( $type == 'build' OR $type == 'rebuild' )
		{
			// Full build - CP only, batches

			// Rebuild - CP only, batches

			// Get our entry_ids

			$sql = " SELECT entry_id FROM exp_channel_titles ORDER BY entry_id ASC ";

			$sql .= " LIMIT " . ee()->db->escape_str( $batch ) . ", " . ee()->db->escape_str( $batch_size );

			$query = ee()->db->query( $sql );

			$ret['batch'] 	= $batch + $batch_size;

			if( $query->num_rows > 0 )
			{
				foreach( $query->result_array() as $row )
				{
					$this->sess['lexicon']['entry_ids'][] = $row['entry_id'];
				}
			}
			else
			{
				$ret['state'] 	= 'complete';
				$ret['done'] 	= TRUE;

				return $ret;
			}
		}
		elseif( $type == 'single' AND $entry_id != 0 )
		{
			// Partial update - entry_id required, single entry at a time

			$this->sess['lexicon']['entry_ids'][] = $entry_id;
		}
		else
		{
			// Shenigins!
			return false;
		}

		// Get field type data + cache the hell out of it.
		$this->_load_fieldtypes();

		$this->_load_entry_data();

		$entries_str = '0';

		// Call the build function with entry_id, one at a time --> GO!		
		foreach( $this->sess['lexicon']['entries'] as $entry_id => $row )
		{
			$this->_build_index_single( $entry_id );

			if( $this->_build_lexicon_single( $entry_id ) ) $entries_str .= '||'.$entry_id;
		}

		// Mark as batch/entry processed in log

		//TODO MEMBER_ID hardcoded
		ee()->db->query(" INSERT INTO exp_super_search_lexicon_log ( type, entry_ids, member_id, origin, action_date ) 
					VALUES ( 'build', '" . ee()->db->escape_str( $entries_str ) . "', 0, 'manual', " . ee()->db->escape_str( ee()->localize->now ) . " ) ");

		// Cleanup - optimize that babby
		ee()->db->query( " OPTIMIZE TABLE exp_super_search_terms ");

		return $ret;
	}

	//	END Build Lexicon

	// --------------------------------------------------------------------

	/**
	 * Build Spelling
	 *
	 * @access	private
	 * @param 	string
	 * @param 	int
	 * @param 	int
	 * @param 	int
	 * @return	array
	 */

	function build_spelling( $type = 'single', $term_id = 0, $batch = 0, $total = 50 )
	{

		// Make a reasonable batch size based on the $total value
		// Smaller will seem quiker to users, but take longer
		// larger will be quicker but gives the ui nothing to feedback with

		$batch_size = 25;

		if( $total < 50 )
		{
			$batch_size = 1;
		}
		elseif( $total < 250 )
		{
			$batch_size = 10;
		}
		elseif( $total < 1000 )
		{
			$batch_size = 25;
		}
		elseif( $total < 5000 )
		{
			$batch_size = 100;
		}
		else
		{
			$batch_size = 250;
		}

		$ret = array();

		$ret['done'] = FALSE;

		$ret['state'] = 'in_progress';


		// What are we doing?

		if( $type == 'build' OR $type == 'rebuild' )
		{
			// Full build - CP only, batches

			// Rebuild - CP only, batches

			// Get our entry_ids

			$sql = " SELECT term_id FROM exp_super_search_terms WHERE count > 0 AND entry_count = 0 AND suggestions = '' ORDER BY term_id ASC ";

			$sql .= " LIMIT " . ee()->db->escape_str( $batch ) . ", " . ee()->db->escape_str( $batch_size );

			$query = ee()->db->query( $sql );

			$ret['batch'] 	= $batch + $batch_size;

			if( $query->num_rows > 0 )
			{
				foreach( $query->result_array() as $row )
				{
					$this->sess['spelling']['term_ids'][] = $row['term_ids'];
				}
			}
			else
			{
				$ret['state'] 	= 'complete';
				$ret['done'] 	= TRUE;

				return $ret;
			}

		}
		elseif( $type == 'single' AND $entry_id != 0 )
		{
			// Partial update - entry_id required, single entry at a time

			$this->sess['spelling']['term_ids'][] = $term_id;
		}
		else
		{
			// Shenigins!
			return false;
		}

		// Call the build function with entry_id, one at a time --> GO!		
		foreach( $this->sess['spelling']['terms'] as $term_id => $row )
		{
			$this->_suggest_spelling( $entry_id );
		}

		return $ret;
	}

	//	END Build Lexicon

	// --------------------------------------------------------------------

	/**
	 * Build lexicon single
	 *
	 * @access	private
	 * @param 	string
	 * @param 	int
	 * @param 	int
	 * @return	bool
	 */

	function _build_lexicon_single( $entry_id = 0 )
	{
		if ( $entry_id <= 0 OR ! is_numeric( $entry_id ) ) return false;

		$data = $this->sess['lexicon']['entries'][ $entry_id ];

		$terms = array();

		// Gather our strings
		foreach ( $data as $key => $row )
		{
			if( $key == 'entry_id' ) continue;

			// Split it up

			$row = strtolower($row);

			$matches = preg_match_all("/\w+/", $row, $output);

			$terms = array_merge( $terms, $output[0] );
		}

		// Make it unique
		$terms = array_unique( $terms );

		// Now we have our terms, drop them into the lexicon

		foreach ( $terms as $term )
		{
			$term	= trim( $term );

			if ( empty( $term ) ) continue;

			$sql = " INSERT INTO exp_super_search_terms
				( term, term_soundex, term_length, entry_count ) 
				VALUES ( '" . ee()->db->escape_str( $term ) . "', soundex('" . ee()->db->escape_str( $term ) . "'), " . strlen( ee()->db->escape_str( $term ) ) . ", 1 )
				ON DUPLICATE KEY UPDATE entry_count = entry_count + 1, term_soundex = soundex(term), term_length = " . strlen( ee()->db->escape_str( $term ) );
	
			$query = ee()->db->query( $sql );
		}

		return true;
	}

	//	END Build Lexicon Single

	// --------------------------------------------------------------------

	/**
	 * Load fieldtypes
	 *
	 * @access	private
	 * @return	bool
	 */

	function _load_entry_data()
	{
		// Build out our basic data

		$sql = " SELECT t.entry_id, t.title AS title ";

		if( isset( $this->sess['lexicon']['fields']['searchable'] ) )
		{
			$sql .= ", " . implode( ', ', $this->sess['lexicon']['fields']['searchable'] );
		}

		$sql .= " FROM exp_channel_data AS d 
					LEFT JOIN exp_channel_titles t ON t.entry_id = d.entry_id 
				 	WHERE d.entry_id IN ( '" . implode("','", ee()->db->escape_str( $this->sess['lexicon']['entry_ids'] ) ) . "') ";
				
		$query = ee()->db->query( $sql );

		foreach( $query->result_array() as $row )
		{
			$this->sess['lexicon']['entries'][ $row['entry_id'] ] = $row;
		}

		return true;

	}

	//	END Load Fieldtypes

	// --------------------------------------------------------------------

	/**
	 * Load fieldtypes
	 *
	 * @access	private
	 * @return	bool
	 */

	function _load_fieldtypes()
	{
		// We should add some checks here so we're only doing whats needed
		// if this is only rebuilding for a single entry
		// +EE1 gypsy support?

		if( ! isset( $this->sess['lexicon']['fields'] ) )
		{
			// Initiate fieldtypes var
			static $fieldtypes;

			$this->sess['lexicon']['fields'] = array();

			// We only need to do this for searchable fields
			$columns	= array(
				'cf.field_id',
				'cf.field_name',
				'cf.field_search',
				'cf.field_type',
				'cf.field_text_direction'
			);

			// Load the necessary stuff
			ee()->load->library('addons');

			// Include EE Fieldtype class
			if ( ! class_exists('EE_Fieldtype'))
			{
				include_once (APPPATH.'fieldtypes/EE_Fieldtype'.EXT);
			}

			// Init text
			$text = array();

			// Set fieldtypes
			if ($fieldtypes === NULL)
			{
				$fieldtypes = ee()->addons->get_installed('fieldtypes');
			}

			// --------------------------------------------
			//	Begin SQL
			// --------------------------------------------

			$sql	= "/* Super Search get fields */ SELECT " . implode( ',', $columns ) . "
						FROM exp_channel_fields cf
						WHERE field_search = 'y' ";

			$query = ee()->db->query( $sql );

			$third_party_fields = FALSE;

			if( $query->num_rows() > 0 )
			{
				foreach( $query->result_array() as $row )
				{
					$this->sess['lexicon']['fields']['raw'][] = $row;

					$this->sess['lexicon']['fields']['searchable'][] = 'field_id_' . $row['field_id'];

					// Include the file if it doesn't yet exist
					if ( ! class_exists($fieldtypes[$row['field_type']]['class']))
					{
						require $fieldtypes[$row['field_type']]['path'].$fieldtypes[$row['field_type']]['file'];
					}

					// Only initiate the fieldtypes that have the necessary method
					if (method_exists($fieldtypes[$row['field_type']]['class'],'low_search_index')
						OR method_exists($fieldtypes[$row['field_type']]['class'],'third_party_search_index'))
					{
						$this->sess['index']['fields']['field_id_'.$row['field_id']] = $fieldtypes[$row['field_type']];

						$third_party_fields = TRUE;
					}
				}
			}

			if( $third_party_fields )
			{
				// Make sure we have all the needed columns ready in our exp_super_search_indexes table
				$query = ee()->db->query( " SHOW COLUMNS FROM exp_super_search_indexes WHERE Field IN( '" . implode( array_keys( ee()->db->escape_str( $this->sess['index']['fields'] ) ), "','" ) . " ') ");

				$sql = array();

				$pref_cols = array();

				foreach( $this->sess['index']['fields'] AS $field_id => $meta )
				{
					$col_exists = FALSE;

					foreach( $query->result_array() AS $key => $row )
					{
						if( $row['Field'] == $field_id ) $col_exists = TRUE;
					}

					if( !$col_exists )
					{
						// This column doesnt exist in our _indexes table, create it
						$sql[] = " ALTER TABLE  exp_super_search_indexes ADD COLUMN " . ee()->db->escape_str( $field_id ) . " longtext ";

						//Update this site's prefs array to allow us to hook onto these new columns during the main search loop w/out
						// incuring any additional costs for sites w/out any 3rd party field indexes

						$pref_cols[] = $field_id;
					}
				}

				if( count( $sql ) > 0 )
				{
					// We have missing cols. Exectue the querys to add them
					foreach( $sql AS $s )
					{
						ee()->db->query( $s );
					}
				}

				if( count( $pref_cols ) > 0 )
				{
					// We have new third_party_search_index columns to add to our preferences array
					// Grab the current contents and append our new ones

					$prefs = $this->reload_preferences();

					if( isset( $prefs['third_party_search_indexes'] ) )
					{
						$prefs['third_party_search_indexes'] .= "|" . implode( $pref_cols, "|" );
					}
					else
					{
						$prefs['third_party_search_indexes'] =  implode( $pref_cols, "|" );
					}

					// Re-sync them to the db

					$this->set_preference( $prefs, ee()->config->item('site_id') );
				}
			}
		}
	}

	//	END Load Fieldtypes

	// --------------------------------------------------------------------

	/**
	 * Pluralize
	 *
	 * @access	private
	 * @param 	string
	 * @return	string
	 */

	function get_plural( $word )
    {

    	// Don't bother if we've only got a 2 character string
    	if( strlen( $word ) < 3 AND $word != 'ox' )
    		return $word;


        // save some time in the case that singular and plural are the same
        if ( in_array( strtolower( $word ), $this->uncountable ) )
            return $word;

        // check for irregular singular forms
        foreach ( $this->irregular as $pattern => $result )
        {
            $pattern = '/' . $pattern . '$/i';

            if ( preg_match( $pattern, $word ) )
                return preg_replace( $pattern, $result, $word);
        }

        // check for matches using regular expressions
        foreach ( $this->plural as $pattern => $result )
        {
            if ( preg_match( $pattern, $word ) )
                return preg_replace( $pattern, $result, $word );
        }

        return $word;
    }
    
	//	END Pluralize

	// --------------------------------------------------------------------

	/**
	 * Singularize
	 *
	 * @access	private
	 * @param 	string
	 * @return	string
	 */

    function get_singular( $word )
    {
    	// Don't bother if we've only got a 3 character string
    	if( strlen( $word ) < 4 )
    		return $word;

        // save some time in the case that singular and plural are the same
        if ( in_array( strtolower( $word ), $this->uncountable ) )
            return $word;

        // check for irregular plural forms
        foreach ( $this->irregular as $result => $pattern )
        {
            $pattern = '/' . $pattern . '$/i';

            if ( preg_match( $pattern, $word ) )
                return preg_replace( $pattern, $result, $word);
        }

        // check for matches using regular expressions
        foreach ( $this->singular as $pattern => $result )
        {
            if ( preg_match( $pattern, $word ) )
                return preg_replace( $pattern, $result, $word );
        }

        return $word;
    }
    
    //	END Singularize

	// --------------------------------------------------------------------

	/**
	 * Define mysql levenshtein function
	 *
	 * Defines a helper function for spelling corrections
	 * the specific db permissions may snarl this up
	 *
	 * @access	public
	 * @param 	string
	 * @return	string
	 */

	function define_levenshtein( )
	{
		$sql = "
			CREATE FUNCTION LEVENSHTEIN (s1 VARCHAR(255), s2 VARCHAR(255))
			RETURNS INT
			DETERMINISTIC
			BEGIN
			  DECLARE s1_len, s2_len, i, j, c, c_temp, cost INT;
			  DECLARE s1_char CHAR;
			  DECLARE cv0, cv1 VARBINARY(256);
			  SET s1_len = CHAR_LENGTH(s1), s2_len = CHAR_LENGTH(s2), cv1 = 0x00, j = 1, i = 1, c = 0;
			  IF s1 = s2 THEN
				RETURN 0;
			  ELSEIF s1_len = 0 THEN
				RETURN s2_len;
			  ELSEIF s2_len = 0 THEN
				RETURN s1_len;
			  ELSE
				WHILE j <= s2_len DO
				  SET cv1 = CONCAT(cv1, UNHEX(HEX(j))), j = j + 1;
				END WHILE;
				WHILE i <= s1_len DO
				  SET s1_char = SUBSTRING(s1, i, 1), c = i, cv0 = UNHEX(HEX(i)), j = 1;
				  WHILE j <= s2_len DO
					SET c = c + 1;
					IF s1_char = SUBSTRING(s2, j, 1) THEN SET cost = 0; ELSE SET cost = 1; END IF;
					SET c_temp = CONV(HEX(SUBSTRING(cv1, j, 1)), 16, 10) + cost;
					IF c > c_temp THEN SET c = c_temp; END IF;
					SET c_temp = CONV(HEX(SUBSTRING(cv1, j+1, 1)), 16, 10) + 1;
					IF c > c_temp THEN SET c = c_temp; END IF;
					SET cv0 = CONCAT(cv0, UNHEX(HEX(c))), j = j + 1;
				  END WHILE;
				  SET cv1 = cv0, i = i + 1;
				END WHILE;
			  END IF;
			  RETURN c;
			END; ";

		return $sql;
	}

	//	END Singularize

	// --------------------------------------------------------------------

	/**
	 * Figure out a the state of a logical block
	 *
	 * Helper function for controlling global settings that have per-field overrides
	 * in both ways, for both disabling and enabling
	 *
	 * @access	public
	 * @param 	string
	 * @return	string
	 */

	function flag_state( $flag = FALSE, $overrides = array(), $field = '', $field_map = array() )
	{
		if ( empty( $flag ) OR empty( $field ) ) return FALSE;

		$state = FALSE;

		// -------------------------------------
		//	We'll hit this routine many time as we loop through search arguments. Let's cache our first time through since we need to convert and prep some data in the $field_map from array( 'body' => '1' ) to array( 'cd.field_id_1' => 'body' ).
		// -------------------------------------

		if ( isset( $this->cache['flag_state_field_map'] ) === TRUE )
		{
			//	We already have flag_state_field_map
		}
		else
		{
			foreach ( $field_map as $key => $val )
			{
				if (is_numeric($val))
				{
					$this->cache['flag_state_field_map']['field_id_'.$val]	= $key;
				}
				elseif (is_string($val))
				{
					$this->cache['flag_state_field_map'][$val]	= $key;
				}
				elseif (is_array($val) AND $key == 'supersearch_msm_duplicate_fields')
				{
					// We may have our msm duplicate fields array here, in which case we need to add duplicated field names into our search, ouch
					foreach ($val as $name => $msm)
					{
						foreach ($msm as $id)
						{
							if (is_numeric($id) AND ! in_array('field_id_' . $id, $this->cache['flag_state_field_map']))
							{
								$this->cache['flag_state_field_map']['field_id_'.$id]	= $name;
							}
						}
					}
				}
			}
		}

		// -------------------------------------
		//	What's the name of the incoming $field as far as a user might name it? title, body instead of t.title, cd.field_id_1?
		// -------------------------------------

		if ( strpos( $field, '.' ) !== FALSE )
		{
			$field	= str_replace( array( 't.', 'cd.' ), '', $field );
		}

		// -------------------------------------
		//	Is our field found in our searchable field map?
		// -------------------------------------

		if ( isset( $this->cache['flag_state_field_map'][$field] ) === TRUE )
		{
			$field	= $this->cache['flag_state_field_map'][$field];
		}
		else
		{
			return FALSE;
		}

		// -------------------------------------
		//	Test or / and
		// -------------------------------------
		//	If we can find our field in either 'or' or 'and', we can set state to true since we want to search on this field.
		// -------------------------------------

		if ( $overrides == 'all' )
		{
			$state	= TRUE;
		}
		elseif ( ! empty( $overrides['or'] ) OR ! empty( $overrides['and'] ) )
		{
			if ( in_array( $field, $overrides['or'] ) === TRUE )
			{
				$state = TRUE;
			}

			if ( in_array( $field, $overrides['and'] ) === TRUE )
			{
				$state = TRUE;
			}
		}

		// -------------------------------------
		//	Test not
		// -------------------------------------
		//	If we can find our field in 'not', then we want to return false. However, if we have a 'not' array, and our field is not in that array, then it passes our test and is considered searchable, all provided that 'or' and 'and' are empty.
		// -------------------------------------

		if ( isset( $overrides['not'] ) === TRUE AND empty( $overrides['or'] ) AND empty( $overrides['and'] ) )
		{
			if ( count( $overrides['not'] ) > 0 )
			{
				if ( in_array( $field, $overrides['not'] ) === TRUE )
				{
					$state = FALSE;
				}
				else
				{
					// Not in the 'not' field array
					$state = TRUE;
				}
			}
			else
			{
				// no entries in the 'not' field array
				$state = TRUE;
			}
		}

		return $state;
	}

	//	END flag_state

	// --------------------------------------------------------------------

	/**
	 * Checks a potentially invalid bit of sql, for validity before running unprotected
	 *
	 * Helper function to check if a sql string is bad before running it.
	 * This was added in SuperSearch 2.0 for the magical feature where we 
	 * allow regex searching on fields. 
	 * 
	 * We don't trust users, so lets be safe about it.
	 *
	 * @access	public
	 * @param 	string
	 * @return	string
	 */
	 
    function check_sql( $sql )
    {
    	if( $sql == '' ) return FALSE;

    	$db_error_state = ee()->db->db_debug; 

		ee()->db->trans_start( TRUE );

		ee()->db->db_debug = FALSE;

    	$query = ee()->db->query( $sql );

		ee()->db->trans_complete();

		if (ee()->db->trans_status() === FALSE)
		{
		    ee()->db->trans_rollback();
			// Revert the db_error state 
			ee()->db->db_debug = $db_error_state;

			return FALSE;
		}
		else
		{
			// Revert the db_error state 
			ee()->db->db_debug = $db_error_state;

			return $query;
		}
	}

	//	END check_sql

	// --------------------------------------------------------------------

	/**
	 * Spelling suggestions
	 *
	 * @access	public
	 * @param 	string
	 * @return	string
	 */
	 
    function spelling_suggestions( $words = array(), $fuzzy_distance = 2, $build = TRUE )
    {
		if( count($words) < 1 ) return;

		$possible = array();
		$new_suggestions = array();

		// Check our caches
		$sql = " SELECT term, suggestions, entry_count FROM exp_super_search_terms
					WHERE term IN ('" . implode( "','", ee()->db->escape_str( $words ) ) . "') ";

	    $query = $this->check_sql( $sql );
	    	
    	if( $query === FALSE ) return;

		if( $query->num_rows > 0 )
		{
			foreach( $query->result_array() as $row )
			{
				if( $row['entry_count'] > 0 )
				{
					// This is a valid term. dont try and suggest anything
					unset( $words[ $row['term'] ] );
				}
				elseif( $row['suggestions'] != '' )
				{
					// This is an invalid term, but we have cached suggestions for it
					// Decode our suggestions array
					$suggestion = unserialize( $row['suggestions'] );
					
					foreach ( $suggestion as $key => $val )
					{
						if ( $val['distance'] > $fuzzy_distance ) continue;
	
						$possible[ $row['term'] ] = $val[ 'term' ];
					}
					
				}
			}
		}

		if ( $build === TRUE )
		{
			$this->_suggest_spelling( $words );
		}

		return $possible;
	}

	//	end spelling suggestions

	// --------------------------------------------------------------------

	/**
	 * 
	 *
	 * @access	public
	 * @param 	string
	 * @return	string
	 */
	 
    function _suggest_spelling( $words = array() )
    {
		if( count($words) < 1 ) return;

		foreach( $words as $word )
		{
			$word = strtolower( $word );

			$l = strlen( $word );

			ee()->TMPL->log_item( 'Super Search: fuzzy spelling. Running full levenshtein calculation for the term : \'' . $word . '\'');

			$sql = " SELECT term, entry_count, LEVENSHTEIN( term, '" . ee()->db->escape_str( $word ) . "' ) distance 
					FROM exp_super_search_terms 
							WHERE entry_count > 0 
							AND length(term) > 2
							AND term_length >= " . ( ee()->db->escape_str( $l-1 ) ) . "
							AND term_length <= " . ( ee()->db->escape_str( $l+2 ) ) . "
							AND term != '" . ee()->db->escape_str( $word ). "'
					ORDER BY distance, entry_count DESC
					LIMIT 5 ";

			$query = $this->check_sql( $sql );

			if( $query === FALSE ) return;

			if( $query->num_rows > 0 )
			{
				$potentials = array();

				foreach( $query->result_array() as $row )
				{
					// Evaluate the fitness of our suggestions

					// Test the fitness of subsequent candidates
					// we may have a worse fit that is significantly
					// more common in our corpus.

					// Our fitness function assumes that suggestions
					// that are increasingly of higher distance
					// need to be an order of magnitude more common
					// to be considered and takes the distance as a
					// weighted against the percentage of term length

					// We define our basic fitness function as :
					//  ( ( l * ( 1/d^2 ) / 100 ) * c
					//  where 	- l = length of the parent term
					//		 	- d = levenshtein distance for this suggestion
					//			- c = entry_count, ie. how common this suggestion is
					//  then we'll order our suggestions by this fitness function

					// Edge case test just to be really sure
					if( $row['distance'] == 0 ) continue;

					$l = strlen( $word );
					$d = $row['distance'];
					$c = $row['entry_count'];

					$fitness = ( ( $l * ( 1 / ($d * $d) ) ) / 100 ) * $c;

					$row['fitness'] = $fitness;

					if( count( $potentials ) < 1 )
					{
						$potentials = $row;
					}
					elseif( $potentials['fitness'] < $row['fitness'] )
					{
						$potentials = $row;
					}
				}

				$possible[ $word ] = $potentials['term'];

				$new_suggestions[ $word ][] = $potentials;

			}
		}

		if ( ! empty( $new_suggestions ) )
		{
			foreach( $new_suggestions AS $key => $term )
			{
				$arr = array(
					'suggestions' 	=> serialize( $term )
				);

				$where = array(
					'term' 	=> ee()->db->escape_str( $key )
				);

				$sql	= ee()->db->update_string( 'exp_super_search_terms', $arr, $where );
				
				ee()->db->query( $sql );
       		}
		}

		return;
	}

	//	End _suggest spelling
}

// END CLASS Super search data
