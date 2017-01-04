<?php

use Solspace\Addons\SuperSearch\Library\AddonBuilder;

class Super_search_ext extends AddonBuilder
{
	var $settings		= array();

	var $name			= '';
	var $version		= '';
	var $description	= '';
	var $settings_exist	= 'n';
	var $spaces			= '+';
	var $docs_url		= '';
	var $sess			= FALSE;

	public $required_by 	= array('module');

	// -------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	null
	 */

	public function __construct($settings = array())
	{
		parent::__construct('extension');

		// -------------------------------------
		//  Settings
		// -------------------------------------

		$this->settings = $settings;

		// -------------------------------------
		// Prepare for $this->EE->session->cache
		// -------------------------------------

		/*
		//	mk is hiding this during conversion to EE 3 since EE 3 seems to choke on it.
		//have to check for session here because if it doesn't exist, we set off a clobbering commotion
		if ( isset( ee()->session->cache ) )
		{
			if ( isset( ee()->session->cache['extensions']['super_search'] ) === FALSE )
			{
				ee()->session->cache['extensions']['super_search']	= array();
			}

			$this->sess	=& ee()->session->cache['extensions']['super_search'];
		}
		*/
	}

	// END Super_search_extension()

	// -------------------------------------------------------------

	/**
	 * Channel entries query result
	 *
	 * This helps us perform a little trick when we use the channel module in our own modules to parse templates. We get to manipulate the query object this way.
	 *
	 * @access	private
	 * @return	null
	 */

	function channel_entries_query_result($ths, $query_result)
    {
		$query_result = (ee()->extensions->last_call !== FALSE) ? ee()->extensions->last_call : $query_result;

		// -------------------------------------
		//	If we have any data AND we are in the context of a $$ instance of the $channel object, proceed. Otherwise we will end up overwriting future calls to exp:channel:entries within the same session call.
		// -------------------------------------

		if (isset(ee()->session->cache['modules']['super_search']['channel_query_object']) === TRUE AND isset($ths->is_super_search))
		{
			$query_result	= ee()->session->cache['modules']['super_search']['channel_query_object']->result;
		}

		return $query_result;
	}

	//	End channel entries query result

	// -------------------------------------------------------------

	/**
	 * Field Frame exists
	 *
	 * Quick test to make sure that the Field Frame extension even exists.
	 *
	 * @access	public
	 * @return	null
	 */

	function _field_frame_exists()
	{
		return FALSE;	// As of $$ 2.0 and EE 2.4, let's just bail out of this
	}

	// End field frame exists

	// -------------------------------------------------------------

	/**
	 * Field frame multi field helper
	 *
	 * Checkbox group and multiselect fields behave pretty much the same, so we homogenize the code.
	 *
	 * @access	private
	 * @return	null
	 */

	function _field_frame_multi_field_helper($ths, $type)
    {
    	// -------------------------------------
        //	Access the Super Search session cache for convenience
        // -------------------------------------

    	$sess	=& ee()->session->cache['modules']['super_search'];

    	// -------------------------------------
        //	Get field data
        // -------------------------------------

        if ( ( $fields = $this->_get_additional_ff_field_data( $type ) ) === FALSE )
        {
        	return FALSE;
        }

    	// -------------------------------------
        //	Handle exact field searching
        // -------------------------------------

        if ( ! empty( $sess['search']['q']['exactfield'] ) )
        {
        	$handy	=& $sess['search']['q']['exactfield'];

			// -------------------------------------
			//	For each correct FF field type, let's change the query to be a serialized array so that we can get a perfect DB match.
			// -------------------------------------

        	foreach ( $fields as $key => $val )
        	{
				// -------------------------------------
				//	If we're searching a single value, we can serialize and be ready for FF. If the 'or' array has more than one term, we can't bother trying to test all of the serialized permutations of the terms. We skip that case.
				// -------------------------------------

				if ( ! empty( $handy[$key]['or'] ) AND count( $handy[$key]['or'] ) == 1 )
				{
					$handy[$key]['or'][0]	= serialize( array( $handy[$key]['or'][0] ) );
				}

				// -------------------------------------
				//	Conjoined / inclusive searching
				// -------------------------------------

				if ( ! empty( $handy[$key]['and'] ) )
				{
					$handy[$key]['or']	= array( serialize( $handy[$key]['and'] ) );
				}
			}
		}
	}

	// End field frame multi field helper

	// -------------------------------------------------------------

	/**
	 * Get additional ff field data
	 *
	 * FieldFrame fields have additional data stored in the column ff_settings in exp_weblog_fields. We'll need this data to work with FF fields.
	 *
	 * @access	public
	 * @return	null
	 */

	function _get_additional_ff_field_data( $type = '' )
	{
		// -------------------------------------
		//	Validate
		// -------------------------------------

		if ( $type == '' ) return FALSE;

		// -------------------------------------
		//	Data already available?
		// -------------------------------------

		if ( ! empty( $this->sess['ff_fields'][$type] ) )
		{
			return $this->sess['ff_fields'][$type];
		}

		// -------------------------------------
		//	Get data
		// -------------------------------------

		if ( empty( $this->sess['ff_fields_query'] ) )
		{
			$sql	= "/* Super Search Extension FieldFrame / Playa code */ SELECT cf.field_id, cf.field_name, cf.field_settings, cf.site_id, cf.group_id
				FROM exp_channel_fields cf
				WHERE cf.field_type = '" . ee()->db->escape_str( $type ) . "'";

			$query	= $this->EE->db->query( $sql );

			$this->sess['ff_fields_query']	= $query;
		}
		else
		{
			$query	= $this->sess['ff_fields_query'];
		}

		foreach ( $query->result_array() as $row )
		{
			$this->sess['ff_fields'][$type][$row['field_name']]	= $row;

			if ( ! empty( $row['field_settings'] ) )
			{
				$this->sess['ff_fields'][$type][$row['field_name']]['ff_settings']	= unserialize( base64_decode( $row['field_settings'] ) );
			}
		}


		// -------------------------------------
		//	Return
		// -------------------------------------

		if ( ! empty( $this->sess['ff_fields'][$type] ) )
		{
			return $this->sess['ff_fields'][$type];
		}

		return FALSE;
	}

	//	End get additional ff field data

	// -------------------------------------------------------------

	/**
	 * Only numeric
	 *
	 * Returns an array containing only numeric values
	 *
	 * @access		private
	 * @return		array
	 */

	function _only_numeric( $array )
	{
		if ( empty( $array ) === TRUE ) return array();

		if ( is_array( $array ) === FALSE )
		{
			$array	= array( $array );
		}

		foreach ( $array as $key => $val )
		{
			if ( preg_match( '/[^0-9]/', $val ) != 0 ) unset( $array[$key] );
		}

		if ( empty( $array ) === TRUE ) return array();

		return $array;
	}

	//	End only numeric

	// -------------------------------------------------------------

	/**
	 * Prep sql
	 *
	 * @access	private
	 * @return	string
	 */

	function _prep_sql( $type = 'or', $field = '', $keywords = array(), $exact = 'notexact', $field_id = '', $field_name = '' )
	{
		// -------------------------------------
		//	Go!
		// -------------------------------------

		$arr	= array();

		// -------------------------------------
		//	Prep conjunction
		// -------------------------------------

		if ( $type == 'and' AND empty( $keywords['and'] ) === FALSE )
		{
			$temp	= array();

			foreach ( $keywords['and'] as $val )
			{
				if ( $val == '' ) continue;

				if ( strpos( $val, $this->spaces ) !== FALSE )
				{
					$val	= str_replace( $this->spaces, ' ', $val );
				}

				if ( $exact != 'exact' )
				{
					$temp[]	= $field." LIKE '%".ee()->db->escape_str( $val )."%'";
					// $temp[]	= $field." REGEXP '[[:<:]]".ee()->db->escape_str( $val )."'";
				}
				else
				{
					$temp[]	= $field." = '".ee()->db->escape_str( $val )."'";
				}
			}

			if ( count( $temp ) > 0 )
			{
				$arr[]	= '('.implode( ' OR ', $temp ).')';
			}
		}

		// -------------------------------------
		//	Prep exclusion
		// -------------------------------------

		if ( $type == 'not' AND empty( $keywords['not'] ) === FALSE )
		{
			$temp	= array();

			foreach ( $keywords['not'] as $val )
			{
				if ( $val == '' ) continue;

				if ( strpos( $val, $this->spaces ) !== FALSE )
				{
					$val	= str_replace( $this->spaces, ' ', $val );
				}

				if ( $exact != 'exact' )
				{
					$temp[]	= $field." LIKE '%".ee()->db->escape_str( $val )."%'";
					// $temp[]	= $field." NOT REGEXP '[[:<:]]".ee()->db->escape_str( $val )."'";
				}
				else
				{
					$temp[]	= $field." = '".ee()->db->escape_str( $val )."'";
				}
			}

			if ( count( $temp ) > 0 )
			{
				$arr[]	= '('.implode( ' OR ', $temp ).')';
			}
		}

		// -------------------------------------
		//	Prep inclusion
		// -------------------------------------

		if ( $type == 'or' AND empty( $keywords['or'] ) === FALSE )
		{
			$temp	= array();

			foreach ( $keywords['or'] as $val )
			{
				if ( $val == '' ) continue;

				if ( strpos( $val, $this->spaces ) !== FALSE )
				{
					$val	= str_replace( $this->spaces, ' ', $val );
				}

				if ( $exact != 'exact' )
				{
					$temp[]	= $field." LIKE '%".ee()->db->escape_str( $val )."%'";
					// $temp[]	= $field." REGEXP '[[:<:]]".ee()->db->escape_str( $val )."'";
				}
				else
				{
					$temp[]	= $field." = '".ee()->db->escape_str( $val )."'";
				}
			}

			if ( count( $temp ) > 0 )
			{
				$arr[]	= '('.implode( ' OR ', $temp ).')';
			}
		}

        // -------------------------------------
		//	Glue
		// -------------------------------------

		if ( empty( $arr ) === TRUE ) return FALSE;

		if ( $type == 'not' )
		{
			return '(' . implode( ' AND ', $arr ) . ')';
		}

		return implode( ' AND ', $arr );
	}

	//	End prep sql

	// -------------------------------------------------------------

	/**
	 * after_channel_entry_save
	 *
	 * Regreshes our lexicon with the new terms from the entry just published
	 *
	 * @access public
	 * @param int
	 * @param array
	 * @param array
	 * @return null
	 */

	function after_channel_entry_save( $ths, $vals )
	{
		$this->model('Data')->build_lexicon('single', $vals['entry_id']);

    	// -------------------------------------
        //  Get morsels to be refreshed
        // -------------------------------------

    	if ($this->model('Data')->get_refresh_rule_by_channel_id($vals['channel_id']) === FALSE) return FALSE;

    	// -------------------------------------
        //  Refresh
        // -------------------------------------

        $this->model('Data')->clear_cache();
	}

	//	End after_channel_entry_save()

	// -------------------------------------------------------------

	/**
	 * Sessions end processor
	 *
	 * Handles various actions triggered by the sessions_end extension hook.
	 *
	 * @access	public
	 * @return	null
	 */

	function sessions_end_processor( $object )
	{
		// -------------------------------------
		//	Are we editing a category in the CP?
		// -------------------------------------

		if (! empty($_POST) AND ! empty($_SERVER['QUERY_STRING']) AND strpos($_SERVER['QUERY_STRING'], 'cat/') !== FALSE)
		{
			$q	= array_reverse(explode('/', $_SERVER['QUERY_STRING']));

			if (empty($q)) return FALSE;
			if (! is_numeric($q[1])) return FALSE;

			// -------------------------------------
			//  Should we refresh?
			// -------------------------------------

			if ( $this->model('Data')->get_refresh_rule_by_category_group_id($q[1]) !== FALSE )
			{
				// -------------------------------------
				//  Refresh
				// -------------------------------------

				$this->model('Data')->clear_cache();
			}

        	return TRUE;
        }

    	// -------------------------------------
        //	Are we editing a template in the CP?
        // -------------------------------------

        if (! empty($_POST) AND ! empty($_SERVER['QUERY_STRING']) AND strpos($_SERVER['QUERY_STRING'], 'template') !== FALSE AND is_numeric(end(explode('/', $_SERVER['QUERY_STRING']))))
        {
			// -------------------------------------
			//  Are we editing a template? We may in the future execute an action even when someone is creating a new template.
			// -------------------------------------

			$template_id	= end(explode('/', $_SERVER['QUERY_STRING']));

			// -------------------------------------
			//  Get morsels to be refreshed
			// -------------------------------------

			if ($this->model('Data')->get_refresh_rule_by_template_id($template_id) !== FALSE)
			{
				// -------------------------------------
				//  Refresh
				// -------------------------------------

				$this->model('Data')->clear_cache();
			}

        	return TRUE;
        }
    }

    //	End sessions end processor

	// -------------------------------------------------------------

	/**
	 * Do search and array playa
	 *
	 * We will not permit playa fields to be keyword searched.
	 *
	 * @access	public
	 * @return	null
	 */

	function super_search_do_search_and_array_playa( $ths, $arr = array() )
    {
    	$arr	= ( is_array( ee()->extensions->last_call ) === TRUE ) ? ee()->extensions->last_call: $arr;

    	if ( $this->_field_frame_exists() === FALSE ) return $arr;

    	// -------------------------------------
        //	Get field data
        // -------------------------------------

        if ( ( $fields = $this->_get_additional_ff_field_data( 'playa' ) ) === FALSE )
        {
        	return $arr;
        }

    	// -------------------------------------
        //	Access the Super Search session cache for convenience
        // -------------------------------------

    	$sess	=& ee()->session->cache['modules']['super_search'];

    	// -------------------------------------
        //	Kill Playa fields when we can detect keyword or simple field searching
        // -------------------------------------

        if ( empty( $sess['search']['q']['keywords'] ) AND empty( $sess['search']['q']['field'] ) ) return $arr;

        if ( ! empty( $sess['search']['q']['keywords'] ) AND ee()->config->item( 'allow_keyword_search_on_playa_fields' ) !== FALSE AND ee()->config->item( 'allow_keyword_search_on_playa_fields' ) == 'y' )
        {
        	return $arr;
        }

    	// -------------------------------------
        //	Loop and slay fields
        // -------------------------------------

        foreach ( $fields as $field_name => $field_data )
        {
        	if ( isset( $sess['search']['q']['field'][$field_name] ) === TRUE ) continue;

        	$test	= 'wd.field_id_' . $field_data['field_id'];

        	if ( isset( $arr['and'] ) === TRUE )
        	{
        		foreach ( $arr['and'] as $k => $v )
        		{
        			if ( strpos( $v, $test . ' LIKE' ) !== FALSE )
        			{
        				unset( $arr['and'][$k] );
        			}
        		}
        	}

        	if ( isset( $arr['or'] ) === TRUE )
        	{
        		foreach ( $arr['or'] as $k => $v )
        		{
        			if ( strpos( $v, $test . ' LIKE' ) !== FALSE )
        			{
        				unset( $arr['or'][$k] );
        			}
        		}
        	}

        	if ( isset( $arr['not'] ) === TRUE )
        	{
        		foreach ( $arr['not'] as $k => $v )
        		{
        			if ( strpos( $v, $test . ' NOT LIKE' ) !== FALSE )
        			{
        				unset( $arr['not'][$k] );
        			}
        		}
        	}
        }

    	// -------------------------------------
        //	Return
        // -------------------------------------

        return $arr;
    }

    //	End do search and array playa

	// -------------------------------------------------------------

	/**
	 * Super Search do search and array rating
	 * @access	public
	 * @return	null
	 */

	function super_search_do_search_and_array_rating($ths, $arr)
    {
    	// -------------------------------------
        //	Been here before?
        // -------------------------------------

    	$arr	= ( is_array( ee()->extensions->last_call ) === TRUE ) ? ee()->extensions->last_call: $arr;

    	// -------------------------------------
        //	Rating module running?
        // -------------------------------------

        if ( ! empty( ee()->TMPL->module_data['Rating'] ) AND isset( $ths->sess['search']['q']['order'] ) AND strpos( $ths->sess['search']['q']['order'], 'r.entry_id' ) !== FALSE)
        {
        	$arr['from'][]	= "LEFT JOIN exp_rating_stats r ON r.entry_id = t.entry_id";
        }

    	// -------------------------------------
        //	Return
        // -------------------------------------

       return $arr;
    }

    //	End super_search_do_search_and_array_rating()

	// -------------------------------------------------------------

	/**
	 * Super Search prep order
	 *
	 * @access	public
	 * @return	null
	 */

	function super_search_prep_order($ths, $arr, $ord)
    {
    	// -------------------------------------
        //	Been here before?
        // -------------------------------------

    	$arr	= ( is_array( ee()->extensions->last_call ) === TRUE ) ? ee()->extensions->last_call: $arr;

    	// -------------------------------------
        //	Rating module running?
        // -------------------------------------

        if ( empty( ee()->TMPL->module_data['Rating'] )) return $arr;

    	// -------------------------------------
        //	Are we being called?
        // -------------------------------------

        if ( strpos( $ord[0], 'rating' ) === FALSE ) return $arr;

    	// -------------------------------------
        //	Spinny uppy
        // -------------------------------------

		if ( class_exists('Rating') === FALSE )
		{
			require PATH_THIRD.'/rating/mod.rating'.EXT;
		}

		$rating = new Rating;

    	// -------------------------------------
        //	Which field?
        // -------------------------------------

        if ( isset( $ord[0] ) AND ( $ord[0] == 'rating' OR $ord[0] == 'ratings' ))
        {
        	$field	= 'bayesian:overall';
        }
        elseif ( isset( $ord[0] ) AND strpos( $ord[0], 'rating_field' . $ths->modifier_separator ) !== FALSE )
        {
        	$ord[0]	= str_replace( 'rating_field' . $ths->modifier_separator, '', $ord[0] );

        	$field	= $ord[0];
        }

    	// -------------------------------------
        //	And take it away Paul...
        // -------------------------------------

		$bayesian	= $rating->bayesian_rating_field( $field, " AND r.status != 'closed'" );

		if ( ! empty( $bayesian ))
		{
			//	Our default is to sort entries by descending Bayesian ranking, but you can flip it if you must.
			if ( ! empty( $ord[1] ) AND $ord[1] == 'asc' )
			{
				if ( preg_match_all( '/(\d+)/s', $bayesian, $match ))
				{
					$bayesian	= 'FIELD(r.entry_id,' . implode( ',', ee()->db->escape_str( array_reverse( $match[0] ))) . ')';
				}
			}

			$arr[]	= "CASE WHEN r.entry_id IS null THEN 1 else 0 END, " . $bayesian;
		}

    	// -------------------------------------
        //	Return
        // -------------------------------------

       // print_r( $arr );

       return $arr;
    }

    //	End super search prep order

	// -------------------------------------------------------------

	/**
	 * Super Search extra basic fields tag
	 *
	 * @access	public
	 * @return	null
	 */

	function super_search_extra_basic_fields_tag($ths)
    {
		$arr[]	= 'tags';
		$arr[]	= 'tags-like';

        // -------------------------------------
		// Add tags to $this->basic
		// -------------------------------------

		foreach ( $arr as $val )
		{
			$ths->basic[]	= $val;
		}

        // -------------------------------------
		// Return
		// -------------------------------------

		$basic	= ( ! empty( ee()->extensions->last_call ) ) ? ee()->extensions->last_call: array();

		return array_merge( $basic, $arr );
	}

	//	End super search extra basic fields tag

	// -------------------------------------------------------------

	/**
	 * Alter Super Search ids array for the Tag module
	 *
	 * @access	public
	 * @return	null
	 */

	function super_search_alter_ids_tag($ids, $ths)
    {
        // -------------------------------------
		// Respect other calls on this hook
		// -------------------------------------

		if ( ! empty( ee()->extensions->last_call ) AND is_array( ee()->extensions->last_call ) === TRUE )
		{
			$ids	= ee()->extensions->last_call;
		}

    	// -------------------------------------
        //	Is Tag running?
        // -------------------------------------

        if ( empty( ee()->TMPL->module_data['Tag'] ) ) return $ids;

    	// -------------------------------------
        //	Save a clean version for ordering
        // -------------------------------------

        $ids_order	= $ids;

        // -------------------------------------
		// Prepare for ee()->session->cache
		// -------------------------------------

		if ( isset( ee()->session->cache ) === TRUE )
		{
			if ( isset( ee()->session->cache['modules']['super_search'] ) === FALSE )
			{
				ee()->session->cache['modules']['super_search']	= array();
			}

			$sess	=& ee()->session->cache['modules']['super_search'];
		}

		if ( isset( $sess ) === FALSE ) return $ids;

    	// -------------------------------------
        //	Keyword search?
        // -------------------------------------

        $and	= array();
        $or		= array();
        $not	= array();

        /*

    	We are no longer allowing this form of keyword tag search. Tags are loaded into the exp_channel_data table and are searchable as keywords there. So that's enough. As long as a field is marked as searchable.

        if ( ! empty( $sess['search']['q']['keywords'] ) AND $this->check_yes( ee()->TMPL->fetch_param('ignore_tags_in_keywords') ) !== TRUE )
        {
			// -------------------------------------
			//	Prep tag for keyword search
			// -------------------------------------
			//	Add a fourth argument 'exact' to match exact words.
			// -------------------------------------

			$exact	= '';

			if ( ( $temp = $this->_prep_sql( 'not', 't.tag_name', $sess['search']['q']['keywords'], $exact ) ) !== FALSE )
			{
				$not[]	= $temp;
			}

			if ( ( $temp = $this->_prep_sql( 'or', 't.tag_name', $sess['search']['q']['keywords'], $exact ) ) !== FALSE )
			{
				$or[]	= $temp;
			}
        }

        */

    	// -------------------------------------
        //	Build SQL
        // -------------------------------------

		$sql	= "/* Super Search Extension: " . __FUNCTION__ . "*/
					SELECT e.entry_id, e.tag_id
					FROM exp_tag_entries e
					LEFT JOIN exp_tag_tags t ON t.tag_id = e.tag_id
					WHERE e.type = 'channel'";

		$funkql	= "/* Super Search Extension: " . __FUNCTION__ . "*/
					SELECT COUNT(*) AS count FROM exp_tag_tags t WHERE";

    	// -------------------------------------
        //	Add filters
        // -------------------------------------

		if ( ! empty( $sess['search']['q']['channel_ids'] ) )
		{
			$sql	.= " AND e.channel_id IN (" . implode( ",", ee()->db->escape_str( $sess['search']['q']['channel_ids'] ) ) . ")";
		}

	  	// -------------------------------------
		//	Are we working with categories?
		// -------------------------------------

		if ( ! empty( $sess['search']['q']['category'] ) )
		{
			$super_search = new Super_search();

			if ( ( $subids = $super_search->_get_ids_by_category( $sess['search']['q']['category'] ) ) !== FALSE )
			{
				$sql .= " AND e.entry_id IN (". implode(',', ee()->db->escape_str( $subids )) .")";
			}
		}

		// -------------------------------------
		//	Are we looking for authors?
		// -------------------------------------

		if ( empty( $sess['search']['q']['author'] ) === FALSE )
		{
			$super_search = new Super_search();

			if ( ( $author = $super_search->_prep_author( $sess['search']['q']['author'] ) ) !== FALSE )
			{
				$sql	.= ' AND t.author_id IN ('.implode( ',', ee()->db->escape_str( $author )).')';
			}
			else
			{
				// For an inclusive author search, if we have none, stop here
				return $ids;
			}
		}

    	// -------------------------------------
        //	If we're excluding a tag
    	// -------------------------------------
    	//	We want to find entries that have the tag and then we exclude those from the grand list of $ids.
        // -------------------------------------

        if ( ! empty( $not ) )
        {
        	$notsql	= $sql . " AND (" . implode( ' OR ', ee()->db->escape_str( $not )) . ")";

        	$query	= ee()->db->query( $notsql );

        	if ( $query->num_rows() > 0 )
        	{
				$notids	= $this->prepare_keyed_result( $query, 'entry_id', 'entry_id' );

				$ids	= array_diff( $ids, $notids );
        	}
        }

    	// -------------------------------------
        //	If we're including a tag
    	// -------------------------------------
    	//	We want to find entries that have the tag and then we exclude those from the grand list of $ids.
        // -------------------------------------

        if ( ! empty( $or ) )
        {
        	$orsql	= $sql . " AND (" . implode( ' OR ', ee()->db->escape_str( $or )) . ")";

        	$query	= ee()->db->query( $orsql );

        	if ( $query->num_rows() > 0 )
        	{
				$orids	= $this->prepare_keyed_result( $query, 'entry_id', 'entry_id' );

				$ids	= array_merge( $ids, $orids );
        	}
        }

    	// -------------------------------------
        //	Exact tags search?
        // -------------------------------------

        $and	= array();
        $or		= array();
        $not	= array();

        if ( ! empty( $sess['search']['q']['tags'] ) )
        {
			$exact	= 'exact';

			if ( ( $temp = $this->_prep_sql( 'not', 't.tag_name', $sess['search']['q']['tags'], $exact ) ) !== FALSE )
			{
				$not[]	= $temp;
			}

			if ( ( $temp = $this->_prep_sql( 'or', 't.tag_name', $sess['search']['q']['tags'], $exact ) ) !== FALSE )
			{
				$or[]	= $temp;
			}
        }

    	// -------------------------------------
        //	If we're excluding a tag
    	// -------------------------------------
    	//	We want to find entries that have the tag and then we exclude those from the grand list of $ids.
        // -------------------------------------

        if ( ! empty( $not ) )
        {
        	$notsql	= $sql . " AND (" . implode(' OR ', $not) . ")";

        	$query	= ee()->db->query( $notsql );

        	if ( $query->num_rows() > 0 )
        	{
				$notids	= $this->prepare_keyed_result( $query, 'entry_id', 'entry_id' );

				$ids	= array_diff( $ids, $notids );
        	}
        }

    	// -------------------------------------
        //	If we're including a tag
    	// -------------------------------------
    	//	We want to find entries that have the tag and then we exclude those from the grand list of $ids.
        // -------------------------------------

        if ( ! empty( $or ) )
        {
        	$orsql	= $sql . " AND (" . implode( ' OR ', $or) . ")";

        	if ( count( $ids ) > 0 )
        	{
        		$orsql	.= " AND e.entry_id IN (" . implode( ",", ee()->db->escape_str( $ids )) . ")";
        	}

        	$query	= ee()->db->query( $orsql );

        	if ( $query->num_rows() > 0 )
        	{
				$ids	= $this->prepare_keyed_result( $query, 'entry_id', 'entry_id' );
        	}
        	else
        	{
				ee()->extensions->end_script	= TRUE;
				return FALSE;
        	}
        }

    	// -------------------------------------
        //	Conjoined search
        // -------------------------------------

        if ( ! empty( $sess['search']['q']['tags']['and'] ) )
        {
			$exact	= '';

			if ( ( $temp = $this->_prep_sql( 'and', 't.tag_name', $sess['search']['q']['tags'], $exact ) ) !== FALSE )
			{
				$and[]	= $temp;
			}

			// -------------------------------------
			//	If there are no such tags, we fail completely.
			// -------------------------------------

			$query	= ee()->db->query( $funkql . " (" . implode(' OR ', $and) . ")" );

			if ( $query->row('count') == 0 )
			{
				ee()->extensions->end_script	= TRUE;
				return FALSE;
			}

        	$andsql	= $sql . " AND (" . implode( ' OR ', $and) . ")";

        	if ( count( $ids ) > 0 )
        	{
        		$andsql	.= " AND e.entry_id IN (" . implode( ",", ee()->db->escape_str( $ids )) . ")";
        	}

			$arr	= array();

        	$query	= ee()->db->query( $andsql );

        	if ( $query->num_rows() == 0 )
        	{
        		return $arr;
        	}

			foreach ( $query->result_array() as $row )
			{
				$arr[ $row['tag_id'] ][]	= $row['entry_id'];
			}

			if ( count( $arr ) < 2 )
			{
				$chosen	= array_shift( $arr );
			}
			else
			{
				$chosen = call_user_func_array('array_intersect', $arr);
			}

			if ( count( $chosen ) == 0 )
			{
				return array();
			}

			$ids	= $chosen;
        }

    	// -------------------------------------
        //	Tags like search?
        // -------------------------------------

        $and	= array();
        $or		= array();
        $not	= array();

        if ( ! empty( $sess['search']['q']['tags-like'] ) )
        {
			$exact	= '';

			if ( ( $temp = $this->_prep_sql( 'not', 't.tag_name', $sess['search']['q']['tags-like'], $exact ) ) !== FALSE )
			{
				$not[]	= $temp;
			}

			if ( ( $temp = $this->_prep_sql( 'or', 't.tag_name', $sess['search']['q']['tags-like'], $exact ) ) !== FALSE )
			{
				$or[]	= $temp;
			}
        }

    	// -------------------------------------
        //	If we're excluding a tag
    	// -------------------------------------
    	//	We want to find entries that have the tag and then we exclude those from the grand list of $ids.
        // -------------------------------------

        if ( ! empty( $not ) )
        {
        	$notsql	= $sql . " AND (" . implode( ' OR ', $not) . ")";

        	$query	= ee()->db->query( $notsql );

        	if ( $query->num_rows() > 0 )
        	{
				$notids	= $this->prepare_keyed_result( $query, 'entry_id', 'entry_id' );

				$ids	= array_diff( $ids, $notids );
        	}
        }

    	// -------------------------------------
        //	If we're including a tag
    	// -------------------------------------
    	//	We want to find entries that have the tag and then we exclude those from the grand list of $ids.
        // -------------------------------------

        if ( ! empty( $or ) )
        {
        	$orsql	= $sql . " AND (" . implode( ' OR ', $or) . ")";

        	if ( count( $ids ) > 0 )
        	{
        		$orsql	.= " AND e.entry_id IN (" . implode( ",", ee()->db->escape_str( $ids )) . ")";
        	}

        	$query	= ee()->db->query( $orsql );

        	if ( $query->num_rows() > 0 )
        	{
				$ids	= $this->prepare_keyed_result( $query, 'entry_id', 'entry_id' );
        	}
        }

    	// -------------------------------------
        //	Conjoined tags-like search
        // -------------------------------------

        //	Even though this one makes intuitive sense, it's actually pretty hard to code for. So we'll punt.

    	// -------------------------------------
        //	Let's fit this new $ids array against the initial $ids array so that we can preserve ordering.
        // -------------------------------------

        $ids	= array_unique( array_intersect( $ids_order, $ids ));

    	// -------------------------------------
        //	Return
        // -------------------------------------

		return $ids;
    }

    //	End alter super search ids array for the tag module

	// -------------------------------------------------------------

	/**
	 * Alter Search for FF check groups
	 *
	 * We want Super Search to be compatible with Brandon Kelly's FieldFrame, but we don't want to make the support totally native. We'll use extension methods so that we can eventually let people turn support on and off per field type.
	 *
	 * @access	public
	 * @return	null
	 */

	function super_search_alter_search_check_group($ths)
    {
    	if ( $this->_field_frame_exists() === FALSE ) return FALSE;

    	// -------------------------------------
        //	Just use the multi field helper
        // -------------------------------------

        if ( $this->_field_frame_multi_field_helper( $ths, 'ff_checkbox_group' ) )
        {
        	return FALSE;
        }
    }

    //	End alter search for FF check groups

	// -------------------------------------------------------------

	/**
	 * Alter Search for FF multiselect
	 *
	 * We want Super Search to be compatible with Brandon Kelly's FieldFrame, but we don't want to make the support totally native. We'll use extension methods so that we can eventually let people turn support on and off per field type.
	 *
	 * @access	public
	 * @return	null
	 */

	function super_search_alter_search_multiselect($ths)
    {
    	if ( $this->_field_frame_exists() === FALSE ) return FALSE;

    	// -------------------------------------
        //	Just use the multi field helper
        // -------------------------------------

        if ( $this->_field_frame_multi_field_helper( $ths, 'ff_multiselect' ) )
        {
        	return FALSE;
        }
    }

    //	End alter search for FF multiselect

	// -------------------------------------------------------------

	/**
	 * Alter Search for FF playa
	 *
	 * We want Super Search to be compatible with Brandon Kelly's FieldFrame, but we don't want to make the support totally native. We'll use extension methods so that we can eventually let people turn support on and off per field type.
	 *
	 * @access	public
	 * @return	null
	 */

	function super_search_alter_search_playa($ths)
    {
        // -------------------------------------
		// Prepare for ee()->session->cache
		// -------------------------------------
		 
		if ( isset( ee()->session->cache ) === TRUE )
		{
			if ( isset( ee()->session->cache['modules']['super_search'] ) === FALSE )
			{
				ee()->session->cache['modules']['super_search']	= array();
			}
			
			$sess	=& ee()->session->cache['modules']['super_search'];
		}

		if ( isset( $sess ) === FALSE ) return FALSE;
    	
    	// -------------------------------------
        //	Handle exact field searching
        // -------------------------------------
        
        if ( ! empty( $sess['search']['q']['field'] ) )
        {
        	$handy	=& $sess['search']['q']['field'];
        	
        	$fields	= array();
        	
        	foreach ( $sess['general_field_data']['searchable'] as $field_name => $field_data )
        	{
        		if ( isset( $handy[$field_name] ) === TRUE AND ! empty( $field_data['field_type'] ) AND $field_data['field_type'] == 'playa' )
        		{
        			$fields[$field_name]	= $handy[$field_name];
        		}
        	}
        
			// -------------------------------------
			//	For each correct field type, let's determine how the relationship is being provided to us. We accept entry id's or url_titles.
			// -------------------------------------
        
        	foreach ( $fields as $key => $val )
        	{
        		$related	= array();
        	
				// -------------------------------------
				//	Super Search stores the exact field queries in the 'or' array, just to follow the overall data model. If 'or' is not empty and it's element count is = 1 we'll search for that value.
				// -------------------------------------

				if ( ! empty( $handy[$key]['or'] ) )
				{
					if ( count( $handy[$key]['or'] ) == 1 )
					{
						$related	= array( $handy[$key]['or'][0] );
					}
					else
					{
						$related	= $handy[$key]['or'];
					}

					unset( $handy[$key]['or'] );
				}
			}

			// -------------------------------------
			//	Are fields even being invoked?
			// -------------------------------------

			if (! empty($related))
			{
				// -------------------------------------
				//	Prepare arrays
				// -------------------------------------

				$entry_ids	= array();
				$titles		= array();

				foreach ( $related as $val )
				{
					if ( is_numeric( $val ) === TRUE )
					{
						$entry_ids[]	= $val;
					}
					else
					{
						$titles[]	= $val;
					}
				}

				// -------------------------------------
				//	We need to fail if the supplied related entries are bunk
				// -------------------------------------

				if ( empty( $entry_ids ) AND empty( $titles ) )
				{
					ee()->extensions->end_script	= TRUE;
					return FALSE;
				}

				// -------------------------------------
				//	Get pure entry ids
				// -------------------------------------

				$sql	= "SELECT entry_id
					FROM exp_channel_titles
					WHERE channel_id != ''
					AND (";

				if ( ! empty( $entry_ids ) )
				{
					$sql	.= "entry_id IN (" . implode( ',', ee()->db->escape_str( $entry_ids )) . ")";

					if ( ! empty( $titles ) )
					{
						$sql	.= " OR (";
					}
				}

				if ( ! empty( $titles ) )
				{
					foreach ( $titles as $t )
					{
						$sql	.= " url_title LIKE '%" . ee()->db->escape_str( str_replace( $ths->spaces, ' ', $t ) ) . "%'";
						$sql	.= " OR title LIKE '%" . ee()->db->escape_str( str_replace( $ths->spaces, ' ', $t ) ) . "%' OR";
					}

					$sql	= rtrim( $sql, 'OR' );
				}

				$sql	.= ")";
			
				$query	= ee()->db->query( $sql );
			
				$related_entry_ids	= array();

				foreach ( $query->result_array() as $row )
				{
					$related_entry_ids[]	= $row['entry_id'];
				}

				// -------------------------------------
				//	If we are testing for related , we need to fail if the supplied related entries are not valid
				// -------------------------------------

				if ( ! empty( $related ) AND empty( $related_entry_ids ) )
				{
					ee()->extensions->end_script	= TRUE;
					return FALSE;
				}

				// -------------------------------------
				//	After validation, do we still have related entries?
				// -------------------------------------

				if ( ! empty( $related_entry_ids ) AND ! empty( $sess['fields']['searchable'][$key] ) )
				{
					// -------------------------------------
					//	Get parent entries
					// -------------------------------------

					$sql	= "SELECT r.parent_entry_id, r.child_entry_id
						FROM exp_playa_relationships r
						LEFT JOIN exp_channel_data cd ON cd.entry_id = r.parent_entry_id
						WHERE r.child_entry_id IN (" . implode( ',', ee()->db->escape_str( $related_entry_ids )) . ")
						AND r.parent_field_id = " . ee()->db->escape_str( $sess['fields']['searchable'][$key] );
					
					$query	= ee()->db->query( $sql );
		
					// -------------------------------------
					//	We need to fail if no related parents were found
					// -------------------------------------

					if ( $query->num_rows() == 0 )
					{
						ee()->extensions->end_script	= TRUE;
						return FALSE;
					}

					foreach ( $query->result_array() as $row )
					{
						$sess['search']['q']['include_entry_ids'][$row['parent_entry_id']]	= $row['parent_entry_id'];
					}				
				}
			}
        }
    	
    	// -------------------------------------
        //	Handle exact field searching
        // -------------------------------------
        
        if ( ! empty( $sess['search']['q']['exactfield'] ) )
        {        
        	$handy	=& $sess['search']['q']['exactfield'];
        	
        	$fields	= array();
        	
        	foreach ( $sess['general_field_data']['searchable'] as $field_name => $field_data )
        	{
        		if ( isset( $handy[$field_name] ) === TRUE AND ! empty( $field_data['field_type'] ) AND $field_data['field_type'] == 'playa' )
        		{
        			$fields[$field_name]	= $handy[$field_name];
        		}
        	}
        
			// -------------------------------------
			//	For each correct Playa field type, let's determine how the relationship is being provided to us. We accept entry id's or url_titles.
			// -------------------------------------
        
        	foreach ( $fields as $key => $val )
        	{
        		$related	= array();
        	
				// -------------------------------------
				//	Super Search stores the exact field queries in the 'or' array, just to follow the overall data model. If 'or' is not empty and it's element count is = 1 we'll search for that value.
				// -------------------------------------

				if ( ! empty( $handy[$key]['or'] ) )
				{
					if ( count( $handy[$key]['or'] ) == 1 )
					{
						$related	= array( $handy[$key]['or'][0] );
					}
					else
					{
						$related	= $handy[$key]['or'];
					}

					unset( $handy[$key]['or'] );
				}
			}

			// -------------------------------------
			//	Are Playa fields even being invoked?
			// -------------------------------------

			if (! empty($related))
			{
				// -------------------------------------
				//	Prepare arrays
				// -------------------------------------

				$entry_ids	= array();
				$titles		= array();

				foreach ( $related as $val )
				{
					if ( is_numeric( $val ) === TRUE )
					{
						$entry_ids[]	= $val;
					}
					else
					{
						$titles[]	= $val;
					}
				}

				// -------------------------------------
				//	We need to fail if the supplied related entries are bunk
				// -------------------------------------

				if ( empty( $entry_ids ) AND empty( $titles ) )
				{
					ee()->extensions->end_script	= TRUE;
					return FALSE;
				}

				// -------------------------------------
				//	Get pure entry ids
				// -------------------------------------

				$sql	= "SELECT entry_id
					FROM exp_channel_titles
					WHERE channel_id != ''
					AND (";

				if ( ! empty( $entry_ids ) )
				{
					$sql	.= "entry_id IN (" . implode( ',', ee()->db->escape_str( $entry_ids )) . ")";

					if ( ! empty( $titles ) )
					{
						$sql	.= " OR (";
					}
				}

				if ( ! empty( $titles ) )
				{
					$sql	.= "url_title IN ('" . str_replace( $ths->spaces, ' ', implode( "','", ee()->db->escape_str( $titles ))) . "')";
					$sql	.= " OR title IN ('" . str_replace( $ths->spaces, ' ', implode( "','", ee()->db->escape_str( $titles ))) . "')";
				}

				$sql	.= ")";
			
				$query	= ee()->db->query( $sql );
			
				$related_entry_ids	= array();

				foreach ( $query->result_array() as $row )
				{
					$related_entry_ids[]	= $row['entry_id'];
				}
			
				// print_r( $sql );
		
				// -------------------------------------
				//	If we are testing for related , we need to fail if the supplied related entries are not valid
				// -------------------------------------

				if ( ! empty( $related ) AND empty( $related_entry_ids ) )
				{
					ee()->extensions->end_script	= TRUE;
					return FALSE;
				}

				// -------------------------------------
				//	After validation, do we still have related entries?
				// -------------------------------------

				if ( ! empty( $related_entry_ids ) AND ! empty( $sess['fields']['searchable'][$key] ) )
				{
					// -------------------------------------
					//	Get parent entries
					// -------------------------------------

					$sql	= "SELECT r.parent_entry_id, r.child_entry_id
						FROM exp_playa_relationships r
						LEFT JOIN exp_channel_data cd ON cd.entry_id = r.parent_entry_id
						WHERE r.child_entry_id IN (" . implode( ',', ee()->db->escape_str( $related_entry_ids )) . ")
						AND r.parent_field_id = " . ee()->db->escape_str( $sess['fields']['searchable'][$key] );
					
					$query	= ee()->db->query( $sql );
		
					// -------------------------------------
					//	We need to fail if no related parents were found
					// -------------------------------------

					if ( $query->num_rows() == 0 )
					{
						ee()->extensions->end_script	= TRUE;
						return FALSE;
					}

					foreach ( $query->result_array() as $row )
					{
						$sess['search']['q']['include_entry_ids'][$row['parent_entry_id']]	= $row['parent_entry_id'];
					}
				}
			}
		}
	}

    //	End alter search for FF playa

	// -------------------------------------------------------------

	/**
	 * Alter Search for EE relationship fields
	 *
	 * @access	public
	 * @return	null
	 */

	function super_search_alter_search_relationship($ths)
    {
        // -------------------------------------
		// Prepare for ee()->session->cache
		// -------------------------------------

		if ( isset( ee()->session->cache ) === TRUE )
		{
			if ( isset( ee()->session->cache['modules']['super_search'] ) === FALSE )
			{
				ee()->session->cache['modules']['super_search']	= array();
			}

			$sess	=& ee()->session->cache['modules']['super_search'];
		}

		if ( isset( $sess ) === FALSE ) return FALSE;

    	// -------------------------------------
        //	Handle exact field searching
        // -------------------------------------

        if ( ! empty( $sess['search']['q']['field'] ) )
        {
        	$handy	=& $sess['search']['q']['field'];

        	$fields	= array();

        	foreach ( $sess['general_field_data']['searchable'] as $field_name => $field_data )
        	{
        		if ( isset( $handy[$field_name] ) === TRUE AND ! empty( $field_data['field_type'] ) AND $field_data['field_type'] == 'relationship' )
        		{
        			$fields[$field_name]	= $handy[$field_name];
        		}
        	}

			// -------------------------------------
			//	For each correct field type, let's determine how the relationship is being provided to us. We accept entry id's or url_titles.
			// -------------------------------------

        	foreach ( $fields as $key => $val )
        	{
        		$related	= array();

				// -------------------------------------
				//	Super Search stores the exact field queries in the 'or' array, just to follow the overall data model. If 'or' is not empty and it's element count is = 1 we'll search for that value.
				// -------------------------------------

				if ( ! empty( $handy[$key]['or'] ) )
				{
					if ( count( $handy[$key]['or'] ) == 1 )
					{
						$related	= array( $handy[$key]['or'][0] );
					}
					else
					{
						$related	= $handy[$key]['or'];
					}

					unset( $handy[$key]['or'] );
				}
			}

			// -------------------------------------
			//	Are fields even being invoked?
			// -------------------------------------

			if (! empty( $related ) )
			{
				// -------------------------------------
				//	Prepare arrays
				// -------------------------------------

				$entry_ids	= array();
				$titles		= array();

				foreach ( $related as $val )
				{
					if ( is_numeric( $val ) === TRUE )
					{
						$entry_ids[]	= $val;
					}
					else
					{
						$titles[]	= $val;
					}
				}

				// -------------------------------------
				//	We need to fail if the supplied related entries are bunk
				// -------------------------------------

				if ( empty( $entry_ids ) AND empty( $titles ) )
				{
					ee()->extensions->end_script	= TRUE;
					return FALSE;
				}

				// -------------------------------------
				//	Get pure entry ids
				// -------------------------------------

				$sql	= "SELECT entry_id
					FROM exp_channel_titles
					WHERE channel_id != ''
					AND (";

				if ( ! empty( $entry_ids ) )
				{
					$sql	.= "entry_id IN (" . implode( ',', ee()->db->escape_str( $entry_ids )) . ")";

					if ( ! empty( $titles ) )
					{
						$sql	.= " OR (";
					}
				}

				if ( ! empty( $titles ) )
				{
					foreach ( $titles as $t )
					{
						$sql	.= " url_title LIKE '%" . ee()->db->escape_str( str_replace( $ths->spaces, ' ', $t ) ) . "%'";
						$sql	.= " OR title LIKE '%" . ee()->db->escape_str( str_replace( $ths->spaces, ' ', $t ) ) . "%' OR";
					}

					$sql	= rtrim( $sql, 'OR' );
				}

				$sql	.= ")";

				$query	= ee()->db->query( $sql );

				$related_entry_ids	= array();

				foreach ( $query->result_array() as $row )
				{
					$related_entry_ids[]	= $row['entry_id'];
				}

				// -------------------------------------
				//	If we are testing for related , we need to fail if the supplied related entries are not valid
				// -------------------------------------

				if ( ! empty( $related ) AND empty( $related_entry_ids ) )
				{
					ee()->extensions->end_script	= TRUE;
					return FALSE;
				}

				// -------------------------------------
				//	After validation, do we still have related entries?
				// -------------------------------------

				if ( ! empty( $related_entry_ids ) AND ! empty( $sess['fields']['searchable'][$key] ) )
				{
					// -------------------------------------
					//	Get parent entries
					// -------------------------------------

					$sql	= "SELECT r.parent_id, r.child_id
						FROM exp_relationships r
						LEFT JOIN exp_channel_data cd ON cd.entry_id = r.parent_id
						WHERE r.child_id IN (" . implode( ',', ee()->db->escape_str( $related_entry_ids )) . ")
						AND r.field_id = " . ee()->db->escape_str( $sess['fields']['searchable'][$key] );

					$query	= ee()->db->query( $sql );

					// -------------------------------------
					//	We need to fail if no related parents were found
					// -------------------------------------

					if ( $query->num_rows() == 0 )
					{
						ee()->extensions->end_script	= TRUE;
						return FALSE;
					}

					foreach ( $query->result_array() as $row )
					{
						$sess['search']['q']['include_entry_ids'][$row['parent_id']]	= $row['parent_id'];
					}
				}

			}
        }

    	// -------------------------------------
        //	Handle exact field searching
        // -------------------------------------

        if ( ! empty( $sess['search']['q']['exactfield'] ) )
        {
        	$handy	=& $sess['search']['q']['exactfield'];

        	$fields	= array();

        	foreach ( $sess['general_field_data']['searchable'] as $field_name => $field_data )
        	{
        		if ( isset( $handy[$field_name] ) === TRUE AND ! empty( $field_data['field_type'] ) AND $field_data['field_type'] == 'relationship' )
        		{
        			$fields[$field_name]	= $handy[$field_name];
        		}
        	}

			// -------------------------------------
			//	For each correct Playa field type, let's determine how the relationship is being provided to us. We accept entry id's or url_titles.
			// -------------------------------------

        	foreach ( $fields as $key => $val )
        	{
        		$related	= array();

				// -------------------------------------
				//	Super Search stores the exact field queries in the 'or' array, just to follow the overall data model. If 'or' is not empty and it's element count is = 1 we'll search for that value.
				// -------------------------------------

				if ( ! empty( $handy[$key]['or'] ) )
				{
					if ( count( $handy[$key]['or'] ) == 1 )
					{
						$related	= array( $handy[$key]['or'][0] );
					}
					else
					{
						$related	= $handy[$key]['or'];
					}

					unset( $handy[$key]['or'] );
				}
			}

			// -------------------------------------
			//	Are Playa fields even being invoked?
			// -------------------------------------

			if (! empty( $related ) )
			{
				// -------------------------------------
				//	Prepare arrays
				// -------------------------------------

				$entry_ids	= array();
				$titles		= array();

				foreach ( $related as $val )
				{
					if ( is_numeric( $val ) === TRUE )
					{
						$entry_ids[]	= $val;
					}
					else
					{
						$titles[]	= $val;
					}
				}

				// -------------------------------------
				//	We need to fail if the supplied related entries are bunk
				// -------------------------------------

				if ( empty( $entry_ids ) AND empty( $titles ) )
				{
					ee()->extensions->end_script	= TRUE;
					return FALSE;
				}

				// -------------------------------------
				//	Get pure entry ids
				// -------------------------------------

				$sql	= "SELECT entry_id
					FROM exp_channel_titles
					WHERE channel_id != ''
					AND (";

				if ( ! empty( $entry_ids ) )
				{
					$sql	.= "entry_id IN (" . implode( ',', ee()->db->escape_str( $entry_ids )) . ")";

					if ( ! empty( $titles ) )
					{
						$sql	.= " OR (";
					}
				}

				if ( ! empty( $titles ) )
				{
					$sql	.= "url_title IN ('" . str_replace( $ths->spaces, ' ', implode( "','", ee()->db->escape_str( $titles ))) . "')";
					$sql	.= " OR title IN ('" . str_replace( $ths->spaces, ' ', implode( "','", ee()->db->escape_str( $titles ))) . "')";
				}

				$sql	.= ")";

				$query	= ee()->db->query( $sql );

				$related_entry_ids	= array();

				foreach ( $query->result_array() as $row )
				{
					$related_entry_ids[]	= $row['entry_id'];
				}

				// print_r( $sql );

				// -------------------------------------
				//	If we are testing for related , we need to fail if the supplied related entries are not valid
				// -------------------------------------

				if ( ! empty( $related ) AND empty( $related_entry_ids ) )
				{
					ee()->extensions->end_script	= TRUE;
					return FALSE;
				}

				// -------------------------------------
				//	After validation, do we still have related entries?
				// -------------------------------------

				if ( ! empty( $related_entry_ids ) AND ! empty( $sess['fields']['searchable'][$key] ) )
				{
					// -------------------------------------
					//	Get parent entries
					// -------------------------------------

					$sql	= "SELECT r.parent_id, r.child_id
						FROM exp_relationships r
						LEFT JOIN exp_channel_data cd ON cd.entry_id = r.parent_id
						WHERE r.child_id IN (" . implode( ',', ee()->db->escape_str( $related_entry_ids )) . ")
						AND r.field_id = " . ee()->db->escape_str( $sess['fields']['searchable'][$key] );

					$query	= ee()->db->query( $sql );

					// -------------------------------------
					//	We need to fail if no related parents were found
					// -------------------------------------

					if ( $query->num_rows() == 0 )
					{
						ee()->extensions->end_script	= TRUE;
						return FALSE;
					}

					foreach ( $query->result_array() as $row )
					{
						$sess['search']['q']['include_entry_ids'][$row['parent_id']]	= $row['parent_id'];
					}
				}

			}
		}
	}

	//	End alter search for EE relationship fields

	// -------------------------------------------------------------

	/**
	 *	required functions for extensions
	 */
	public function activate_extension(){}
	public function disable_extension(){}
	public function update_extension(){}

	// -------------------------------------------------------------

	/**
	 * Error Page
	 *
	 * @access	public
	 * @param	string	$error	Error message to display
	 * @return	null
	 */

	function error_page($error = '')
	{
		$this->cached_vars['error_message'] = $error;

		$this->cached_vars['page_title'] = lang('error');

		// -------------------------------------
		//  Output
		// -------------------------------------

		$this->ee_cp_view('error_page.html');
	}

	//	END error_page()

	// -------------------------------------------------------------

	/**
	 * Allowed Ability for Group
	 *
	 * @access	public
	 * @param	string	$which	Name of permission
	 * @return	bool
	 */

	function allowed_group($which = '')
	{
		if ($which == '')
		{
			return FALSE;
		}
		// Super Admins always have access

		if ($this->sess->userdata['group_id'] == 1)
		{
			return TRUE;
		}

		if ( !isset($this->sess->userdata[$which]) OR $this->sess->userdata[$which] !== 'y')
			return FALSE;
		else
			return TRUE;
	}

	// END allowed_group()
}

//	END Class Super_search_extension
