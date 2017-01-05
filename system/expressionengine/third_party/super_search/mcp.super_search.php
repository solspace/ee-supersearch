<?php if ( ! defined('EXT')) exit('No direct script access allowed');

/**
 * Super Search - Control Panel
 *
 * @package		Solspace:Super Search
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2009-2016, Solspace, Inc.
 * @link		https://solspace.com/expressionengine/super-search
 * @license		https://solspace.com/software/license-agreement
 * @version		2.2.4
 * @filesource	super_search/mcp.super_search.php
 */

require_once 'addon_builder/module_builder.php';

class Super_search_mcp extends Module_builder_super_search
{
	public $row_limit			= 30;
	public $sess				= array();

	// -----------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	bool		Enable calling of methods based on URI string
	 * @return	string
	 */
    
    function __construct( $switch = TRUE )
    {
		parent::__construct();
        
        if ( (bool) $switch === FALSE ) return;	// We're in install / uninstall mode
     
		$this->sess	=&	ee()->session->cache['modules']['super_search'];
		
		//	----------------------------------------
		//	 UTF-8
		//	----------------------------------------

		if (function_exists( 'mb_internal_encoding'))
		{
			mb_internal_encoding('UTF-8');
		}
		
		// -------------------------------------
        //  Module Menu Items
        // -------------------------------------
        
        $menu	= array(
        	'module_home'	=> array(
        		'name'	=> 'homepage',
        		'link'  => $this->base, 
				'title' => lang('homepage')
			),
			'module_search_log'	=> array(
				'name'	=> 'search_log',
				'link'  => $this->base.'&method=search_log',
				'title' => lang('search_log')
			),
			'module_fields'	=> array(
				'name'	=> 'fields',
				'link'  => $this->base.'&method=fields',
				'title' => lang('fields')
			)
		);

		$search_log_path = BASE . '&C=tools_logs&M=view_search_log';

		$menu['module_search_utils']	= array(
			'name'	=> 'search_utils',
			'link'  => $this->base.'&method=search_utils',
			'title' => lang('search_utils')
		);

		$menu['module_search_options']	= array(
			'name'	=> 'search_options',
			'link'  => $this->base.'&method=search_options',
			'title' => lang('preferences')
		);

		$menu['module_demo_templates']	= array(
			'name'	=> 'demo_templates',
			'link'	=> $this->base.'&method=code_pack',
			'title'	=> lang('demo_templates'),
		);

		$menu['module_documentation']	= array(
			'name'	=> 'documentation',
			'link'  => SUPER_SEARCH_DOCS_URL,
			'title' => lang('online_documentation'),
			'new_window' => TRUE
		);

		$this->cached_vars['lang_module_version'] 	= lang('super_search_module_version');
		$this->cached_vars['module_version'] 		= SUPER_SEARCH_VERSION;
		$this->cached_vars['module_menu_highlight']	= 'module_home';
		$this->cached_vars['module_menu']			= $menu;
		//needed for header.html file views
		$this->cached_vars['js_magic_checkboxes']	= $this->js_magic_checkboxes();

		// -------------------------------------
		//  EE 1.x Styles & Scripts
		// -------------------------------------

		$this->_add_styles( TRUE );

		// -------------------------------------
		//  Sites
		// -------------------------------------

		$this->cached_vars['sites']	= array();

		foreach($this->data->get_sites() as $site_id => $site_label)
		{
			$this->cached_vars['sites'][$site_id] = $site_label;
		}
	}

	//	END constructor

	// -----------------------------------------------------------------

	/**
	 * Clear cache
	 *
	 * @access	private
	 * @return	boolean
	 */

	function _clear_cache()
	{
		if ( empty( $_POST ) === TRUE ) return FALSE;

		if ( isset( $_POST['cache'] ) === TRUE AND $_POST['cache'] == 'clear' )
		{
			do
			{
				ee()->db->query(
					"DELETE FROM exp_super_search_cache
					WHERE site_id = ".ee()->db->escape_str( ee()->config->item('site_id') )."
					LIMIT 1000"
				);
			}
			while ( ee()->db->affected_rows() > 0 );

			do
			{
				ee()->db->query(
					"DELETE FROM exp_super_search_history
					WHERE saved = 'n'
					AND cache_id NOT IN (
						SELECT cache_id
						FROM exp_super_search_cache
						WHERE site_id = ".ee()->db->escape_str( ee()->config->item('site_id') )."
					)
					LIMIT 1000"
				);
			}
			while ( ee()->db->affected_rows() > 0 );
		}

		return TRUE;
	}

	//	End clear cache

	// -----------------------------------------------------------------

	/**
	 * Edit field
	 *
	 * @access	public
	 * @param	message
	 * @return	string
	 */

	function edit_field()
	{
		// -------------------------------------
		//	Field id
		// -------------------------------------

		$field_id	= ee()->input->get_post('field_id');

		// -------------------------------------
		//	SQL
		// -------------------------------------

		if ( ! empty( $_POST['sql'] ) )
		{
			$sql	= base64_decode( $_POST['sql'] );

			ee()->db->query( $sql );
		}

		// -------------------------------------
		//	Load fields page
		// -------------------------------------

		ee()->functions->redirect( $this->cached_vars['base_uri'] . AMP . 'method=fields' . AMP . 'msg=field_edited_successfully' );
	}

	//	End edit field

	// -----------------------------------------------------------------

	/**
	 * Edit field confirm
	 *
	 * @access	public
	 * @param	message
	 * @return	string
	 */

	function edit_field_confirm()
	{
		if ( empty($_POST['type']) ) return FALSE;

		// -------------------------------------
		//	Field id
		// -------------------------------------

		$field_id	= ee()->input->get_post('field_id');

		// -------------------------------------
		//	Field length tests
		// -------------------------------------

		$error	= array();
		$sql	= "";

		switch ( $_POST['type'] )
		{
			case 'character':

				if ( empty( $_POST['length'] ) OR ! is_numeric($_POST['length']) )
				{
					$error[]	= lang('field_length_required');
				}
				elseif ( $_POST['length'] < 1 OR $_POST['length'] > 255 )
				{
					$error[]	= lang('char_length_incorrect');
				}

				$check_truncation	= TRUE;
				$sql	.= " CHAR(".round(ee()->db->escape_str($_POST['length'])).") NOT NULL";

			break;
			case 'varchar':

				if ( empty( $_POST['length'] ) OR ! is_numeric($_POST['length']) )
				{
					$error[]	= lang('field_length_required');
				}
				elseif ( $_POST['length'] < 1 OR $_POST['length'] > 255 )
				{
					$error[]	= lang('varchar_length_incorrect');
				}

				$check_truncation	= TRUE;
				$sql	.= " VARCHAR(".round(ee()->db->escape_str($_POST['length'])).") NOT NULL";

			break;
			case 'decimal':

				$check_truncation	= TRUE;
				$parens	= ( is_numeric($_POST['length']) === TRUE AND is_numeric($_POST['precision']) === TRUE ) ? "(".round(ee()->db->escape_str($_POST['length'])).",".round(ee()->db->escape_str($_POST['precision'])).")": '';
				$sql	.= " DECIMAL" . $parens . " NOT NULL";

			break;
			case 'float':

				$check_truncation	= TRUE;
				$parens	= ( is_numeric($_POST['length']) === TRUE AND is_numeric($_POST['precision']) === TRUE ) ? "(".round(ee()->db->escape_str($_POST['length'])).",".round(ee()->db->escape_str($_POST['precision'])).")": '';
				$sql	.= " FLOAT" . $parens . " NOT NULL";

			break;
			case 'integer':

				$_POST['length']	= 10;

				$check_truncation	= TRUE;
				$sql	.= " INT(".round(ee()->db->escape_str($_POST['length'])).") unsigned NOT NULL";
			break;
			case 'small integer':

				$_POST['length']	= 5;

				$check_truncation	= TRUE;
				$sql	.= " SMALLINT(".round(ee()->db->escape_str($_POST['length'])).") unsigned NOT NULL";

			break;
			case 'tiny integer':

				$_POST['length']	= 3;

				$check_truncation	= TRUE;
				$sql	.= " TINYINT(".round(ee()->db->escape_str($_POST['length'])).") unsigned NOT NULL";

			break;
			default:
				$sql	.= " TEXT NOT NULL";
			break;
		}

		$sql	= "ALTER TABLE `%table%` CHANGE `%field%` `%field%`".$sql;

		$sql	= str_replace( array( '%table%', '%field%' ), array( 'exp_channel_data', 'field_id_'.$field_id ), $sql );

		// -------------------------------------
		//	Any errors?
		// -------------------------------------

		if ( count( $error ) > 0 )
		{
			return $this->show_error($error);
		}

		// -------------------------------------
		//	Prep vars
		// -------------------------------------

		$this->cached_vars['field_id']	= $field_id;
		$this->cached_vars['sql']		= base64_encode( $sql );
		$this->cached_vars['method']	= 'edit_field';

		// -------------------------------------
		//	Will this change truncate data?
		// -------------------------------------

		$this->cached_vars['question']	= lang('edit_field_question');

		if ( isset( $check_truncation ) === TRUE )
		{
			$this->cached_vars['question']	= lang('edit_field_question_truncate');
		}

		// -------------------------------------
		//	Prep message
		// -------------------------------------

		$this->_prep_message();

		// -------------------------------------
		//	Title and Crumbs
		// -------------------------------------

		$this->add_crumb( lang( 'edit_field' ) );
		$this->cached_vars['module_menu_highlight'] = 'module_fields';

		// -------------------------------------
		//	Load Homepage
		// -------------------------------------

		return $this->ee_cp_view('edit_field_confirm.html');
	}

	//	End edit field confirm

	// -----------------------------------------------------------------

	/**
	 * Edit field form

	 * @access	public
	 * @param	message
	 * @return	string
	 */

	function edit_field_form( $message = '' )
	{
		$this->cached_vars['field_id']			= ee()->input->get_post('field_id');
		$this->cached_vars['field_name']		= '';
		$this->cached_vars['field_label']		= '';
		$this->cached_vars['type']				= '';
		$this->cached_vars['length']			= '';
		$this->cached_vars['precision']			= '';

		// -------------------------------------
		//	Query
		// -------------------------------------

		if ( ee()->input->get_post('field_id') !== FALSE )
		{
			$sql	= "SELECT field_id, field_name, field_label FROM exp_channel_fields WHERE field_id = '".ee()->db->escape_str( ee()->input->get_post('field_id') )."' LIMIT 1";

			$query				= ee()->db->query($sql);

			$this->cached_vars['field_id']			= $query->row('field_id');
			$this->cached_vars['field_name']		= $query->row('field_name');
			$this->cached_vars['field_label']		= $query->row('field_label');
			$this->cached_vars	= array_merge( $this->cached_vars, $this->_get_field_attributes( $query->row('field_id') ) );
		}

		// -------------------------------------
		//	Prep message
		// -------------------------------------

		$this->_prep_message( $message );

		// -------------------------------------
		//	Title and Crumbs
		// -------------------------------------

		$this->add_crumb( lang( 'edit_field' ) );
		$this->cached_vars['module_menu_highlight'] = 'module_fields';

		// -------------------------------------
		//	Load Homepage
		// -------------------------------------

		return $this->ee_cp_view('field.html');
	}

	//	End edit field form

	// -----------------------------------------------------------------

	/**
	 * Fields

	 * @access	public
	 * @param	message
	 * @return	string
	 */

	function fields($message = '')
	{
		$paginate		= '';
		$row_count		= 0;

		// -------------------------------------
		//	 Custom Field Groups by Site ID
		// -------------------------------------

		$sql	= "SELECT group_id, group_name, site_id FROM exp_field_groups ORDER BY site_id, group_name";

		$query = ee()->db->query($sql);

		$this->cached_vars['field_groups']	= array();

		foreach ( $query->result_array() as $row )
		{
			$group_name = $row['group_name'];

			if( count( $this->cached_vars['sites'] ) > 1 )
			{
				$group_name = $this->cached_vars['sites'][$row['site_id']] . ' :: ' . $group_name;
			}

			$this->cached_vars['field_groups'][$row['group_id']] = $group_name;
		}

		if ($query->num_rows() > 0)
		{
			$this->cached_vars['default_group_id'] = $query->row('group_id');
		}

		// -------------------------------------
		//	 Custom Fields by Group ID
		// -------------------------------------

		$sql	= "SELECT field_id, field_label, group_id, site_id FROM exp_channel_fields ORDER BY site_id, group_id, field_label";

		$query = ee()->db->query($sql);

		$this->cached_vars['fields']	= array();

		foreach ( $query->result_array() as $row )
		{
			$row	= array_merge( $row, $this->_get_field_attributes( $row['field_id'] ) );
			$this->cached_vars['fields'][ $row['group_id']][$row['field_id']]	= $row;
		}

		// -------------------------------------
		//	Prep message
		// -------------------------------------

		$this->_prep_message( $message );

		// -------------------------------------
		//	Title and Crumbs
		// -------------------------------------

		$this->add_crumb( lang('fields') );
		$this->cached_vars['module_menu_highlight'] = 'module_fields';

		// -------------------------------------
		//	Load page
		// -------------------------------------

		return $this->ee_cp_view('fields.html');
	}

	//	End fields

	// -----------------------------------------------------------------

	/**
	 * Get field attributes

	 * @access	private
	 * @param	message
	 * @return	array
	 */

	function _get_field_attributes( $field )
	{
		if ( $field == '' ) return FALSE;

		if ( isset( $this->sess['fields'][$field] ) === FALSE )
		{
			$sql	= "DESCRIBE `" . $this->sc->db->channel_data . "`";

			$query	= ee()->db->query( $sql );

			$fields	= array();
			
			foreach ( $query->result_array() as $row )
			{
				if ( strpos( $row['Field'], 'field_id_' ) === FALSE ) continue;
				$id	= str_replace( 'field_id_', '', $row['Field'] );
				$fields[ $id ]['type']		= $row['Type'];
				$fields[ $id ]['length']	= '';
				$fields[ $id ]['precision']	= '';
				$fields[ $id ]['default']	= $row['Default'];

				// -------------------------------------
				//  Char
				// -------------------------------------

				if ( strpos( $row['Type'], 'char' ) !== FALSE )
				{
					$fields[ $id ]['type']	= 'character';
					$arr	= explode( ",", str_replace( array( 'char(', ')' ), '', $row['Type'] ) );
					$fields[ $id ]['length']	= $arr[0];
				}

				// -------------------------------------
				//  Decimal
				// -------------------------------------

				if ( strpos( $row['Type'], 'decimal' ) !== FALSE )
				{
					$fields[ $id ]['type']	= 'decimal';
					$arr	= explode( ",", str_replace( array( 'decimal', '(', ')' ), '', $row['Type'] ) );
					$fields[ $id ]['length']	= ( isset( $arr[0] ) === TRUE ) ? $arr[0]: '';
					$fields[ $id ]['precision']	= ( isset( $arr[1] ) === TRUE ) ? $arr[1]: '';
				}

				// -------------------------------------
				//  Float
				// -------------------------------------

				if ( strpos( $row['Type'], 'float' ) !== FALSE )
				{
					$fields[ $id ]['type']	= 'float';
					$arr	= explode( ",", str_replace( array( 'float', '(', ')' ), '', $row['Type'] ) );
					$fields[ $id ]['length']	= ( isset( $arr[0] ) === TRUE ) ? $arr[0]: '';
					$fields[ $id ]['precision']	= ( isset( $arr[1] ) === TRUE ) ? $arr[1]: '';
				}

				// -------------------------------------
				//  Integer
				// -------------------------------------

				if ( strpos( $row['Type'], 'int' ) !== FALSE )
				{
					$fields[ $id ]['type']	= 'integer';
					$arr	= explode( ",", str_replace( array( 'int', '(', ')', 'unsigned' ), '', $row['Type'] ) );
					$fields[ $id ]['length']	= trim( $arr[0] );
				}

				// -------------------------------------
				//  Small integer
				// -------------------------------------

				if ( strpos( $row['Type'], 'smallint' ) !== FALSE )
				{
					$fields[ $id ]['type']	= 'small integer';
					$arr	= explode( ",", str_replace( array( 'smallint', '(', ')', 'unsigned' ), '', $row['Type'] ) );
					$fields[ $id ]['length']	= trim( $arr[0] );
				}

				// -------------------------------------
				//  Tiny integer
				// -------------------------------------

				if ( strpos( $row['Type'], 'tinyint' ) !== FALSE )
				{
					$fields[ $id ]['type']	= 'tiny integer';
					$arr	= explode( ",", str_replace( array( 'tinyint(', ')', 'unsigned' ), '', $row['Type'] ) );
					$fields[ $id ]['length']	= trim( $arr[0] );
				}

				// -------------------------------------
				//  Var char
				// -------------------------------------

				if ( strpos( $row['Type'], 'varchar' ) !== FALSE )
				{
					$fields[ $id ]['type']	= 'varchar';
					$arr	= explode( ",", str_replace( array( 'varchar(', ')' ), '', $row['Type'] ) );
					$fields[ $id ]['length']	= $arr[0];
				}
			}

			$this->sess['fields']	= $fields;
		}

		if ( isset( $this->sess['fields'][$field] ) === TRUE )
		{
			return $this->sess['fields'][$field];
		}
		else
		{
			return FALSE;
		}
	}

	//	End get field attributes

	// -----------------------------------------------------------------


	/**
	 * Module's Home Page

	 * @access	public
	 * @param	string
	 * @return	null
	 */

	function index( $message = '' )
	{
		// -------------------------------------
		//	Top Terms
		// -------------------------------------

		$sql = " SELECT * FROM exp_super_search_terms WHERE count > 0 AND term != '' ORDER BY count DESC LIMIT 20 ";

		$query = ee()->db->query( $sql );

		if( $query->num_rows > 0 )
		{
			foreach( $query->result_array() as $row )
			{
				$this->cached_vars['top_terms'][] = $row;
			}
		}
		else
		{
			$this->cached_vars['top_terms'] = 'No Searches';
		}

		$this->cached_vars['all_terms_link'] = $this->base.'&method=search_log';

		$this->cached_vars['all_log_link'] = $this->base.'&method=search_log';

		$this->cached_vars['term_link_base'] = $this->base.'&method=search_log&term_id=';


		// is logging already enabled?
		// show a different message depending on the state

		if( $this->check_yes( ee()->config->item('enable_search_log') ) )
		{
			$this->cached_vars['lang_no_searches_yet'] = lang('no_searches_yet');
		}
		else
		{
			$this->cached_vars['lang_no_searches_yet'] = str_replace( '%enable_link%', $this->base.'&method=search_options', lang('no_searches_yet_long') );
		}

		// -------------------------------------
		//	Recent Searches
		// -------------------------------------

		if( $query->num_rows > 0 )
		{
			$ret = $this->_get_searches_grouped(0, 0, FALSE);

			$this->cached_vars['searches_grouped'] = $ret['searches_grouped'];

			$this->cached_vars['top_log_id'] = $ret['top_log_id'];

			$this->cached_vars['top_log_time'] = $ret['top_log_time'];

			if( isset( $ret['recent_searches'] ) !== FALSE )
			{
				$this->cached_vars['recent_searches'] = $ret['recent_searches'];
			}
		}
		else
		{
			$this->cached_vars['recent_searches'] = 'No Searches';

			$this->cached_vars['no_results'] = TRUE;
		}
		
		// -------------------------------------
		// Lexicon State
		// -------------------------------------
		$this->cached_vars['lexicon_built'] = 'y';

		$this->cached_vars['lexicon_url'] = $this->base.'&method=lexicon';

		$query = ee()->db->query( " SELECT * FROM exp_super_search_lexicon_log 
								WHERE origin = 'manual' ORDER BY action_date DESC LIMIT 1 ");

		if( $query->num_rows() < 1 )
		{

			// We have an unbuilt lexicon, BUT there may be no current entries to build anyway.
			// check that before carrying on, so that on clean installs we don't
			// show the build option

			$equery = ee()->db->query( " SELECT COUNT(*) as count FROM exp_channel_titles " );

			if( $equery->row('count') > 0 )
			{
				$this->cached_vars['lexicon_built'] = 'n';	
			}
		}

		// -------------------------------------
		//	Title and Crumbs
		// -------------------------------------

		$this->cached_vars['loading_gif_url'] = $this->theme_folder_url() . "images/indicator_dark.gif";

		$crumb	= lang( 'Homepage' );

		$this->add_crumb( $crumb );

		$this->cached_vars['module_menu_highlight'] = 'module_home';

		// -------------------------------------
		//	Prep message
		// -------------------------------------

		$this->_prep_message( $message );
	
		// -------------------------------------
        //  Load Homepage
        // -------------------------------------
        
		$this->_add_styles();

		return $this->ee_cp_view('index.html');
	}

	//	End index

	// -----------------------------------------------------------------

	/**
	 * Module's Lexicon Page

	 * @access	public
	 * @param	string
	 * @return	null
	 */

	function lexicon( $message = '' )
	{
		$this->cached_vars['lexicon_action'] = '';

		$this->cached_vars['just_lexicon'] = TRUE;

		if( ee()->input->get_post('build') == 'yes' )
		{
			if( ee()->input->get_post('ajax') == 'yes' )
			{
				$type = 'build';

				$batch = 0;

				$total = 50;

				// We're on an ajax call
				if( ee()->input->get_post('batch') > 0 )
				{
					$batch = ee()->input->get_post('batch');
				}

				// We're on an ajax call
				if( ee()->input->get_post('total') > 0 )
				{
					$total = ee()->input->get_post('total');
				}

				$this->cached_vars['response'] = $this->data->build_lexicon( $type, 0 , $batch, $total);

				exit($this->json_encode($this->cached_vars['response'] ) );

			}
			else
			{
				$this->cached_vars['lexicon_action'] = $this->data->build_lexicon('build');
			}
		}

		$query = ee()->db->query( " SELECT COUNT(entry_id) AS total_entries FROM exp_channel_titles ");

		$this->cached_vars['lexicon_progress_count_total'] = $query->row('total_entries');

		$queryb = ee()->db->query( " SELECT * FROM exp_super_search_lexicon_log
								WHERE origin = 'manual' ORDER BY action_date DESC LIMIT 1 ");

		if( $queryb->num_rows() > 0 )
		{
			$last_build = $this->human_time( $queryb->row('action_date') );

			$this->cached_vars['lexicon_build_lang'] = lang('lexicon_rebuild');

			$this->cached_vars['lexicon_last_build'] = lang('lexicon_last_built') . $last_build;// has never been built.';
		}
		else
		{
			$this->cached_vars['lexicon_build_lang'] = lang('lexicon_build');

			$this->cached_vars['lexicon_last_build'] =  lang('lexicon_never_built');
		}

		$this->cached_vars['lexicon_build_url'] = $this->base.'&method=lexicon&build=yes&total='.$query->row('total_entries').'&batch=0';

		$this->cached_vars['success_png_url'] = $this->theme_folder_url() . "images/success.png";

		ee()->cp->add_js_script(
		  array('ui' => array(
				'core', 'progressbar')));

		ee()->javascript->compile();

		// -------------------------------------
		//	Title and Crumbs
		// -------------------------------------

		$crumb	= lang( 'manage_lexicon' );

		$this->add_crumb( $crumb );

		$this->cached_vars['module_menu_highlight'] = 'none';

		// -------------------------------------
		//	Prep message
		// -------------------------------------

		$this->_prep_message( $message );

		$this->_add_styles();

		// -------------------------------------
		//  Load Homepage
		// -------------------------------------

		return $this->ee_cp_view('lexicon.html');
	}

	//	End lexicon

	// -----------------------------------------------------------------

	/**
	 * Module's Spelling Page

	 * @access	public
	 * @param	string
	 * @return	null
	 */

	function spelling( $message = '' )
	{
		if( ee()->input->get_post('build') == 'yes' )
		{
			// Build or rebuild!

			if( ee()->input->get_post('ajax') == 'yes' )
			{

				$batch = 0;

				$total = 50;

				// We're on an ajax call
				if( ee()->input->get_post('batch') > 0 )
				{
					$batch = ee()->input->get_post('batch');
				}

				// We're on an ajax call
				if( ee()->input->get_post('total') > 0 )
				{
					$total = ee()->input->get_post('total');
				}

				$this->cached_vars['response'] = $this->data->build_spelling('build', 0 , $batch, $total);

				exit($this->json_encode( $this->cached_vars['response'] ) );

			}
			else
			{
				$this->cached_vars['lexicon_action'] = $this->data->build_lexicon('build');
			}
		}

		// Get our spelling data
		$this->_spelling_data();

		ee()->cp->add_js_script(
		  array('ui' => array(
				'core', 'progressbar')));

		ee()->javascript->compile();

		// -------------------------------------
		//	Title and Crumbs
		// -------------------------------------

		$crumb	= lang( 'manage_spelling' );

		$this->add_crumb( $crumb );

		$this->cached_vars['module_menu_highlight'] = 'none';

		// -------------------------------------
		//	Prep message
		// -------------------------------------

		$this->_prep_message( $message );

		$this->_add_styles();

		// -------------------------------------
		//  Load Homepage
		// -------------------------------------

		return $this->ee_cp_view('spelling.html');
	}

	//	End lexicon

	// -----------------------------------------------------------------

	/**
	 * Clear search cache

	 * @access	public
	 * @param	string
	 * @return	null
	 */

	function clear_search_cache()
	{
		// -------------------------------------
		//	Clear cache?
		// -------------------------------------

		if ( ee()->input->get_post('msg') == 'cache_cleared' )
		{
			$this->_clear_cache();
		}

		return ee()->functions->redirect( $this->base . '&method=search_options&msg=cache_cleared');
	}

	//	End clear search cache
	
	/**
	 * Module's Search Options
	 
	 * @access	public
	 * @param	string
	 * @return	null
	 */
    
	function search_options( $message = '' )
    {
		// -------------------------------------
		//	Are we updating / inserting?
		// -------------------------------------
		
		if ( ee()->input->post('use_ignore_word_list') !== FALSE )
		{
			// -------------------------------------
			//	Prep vars
			// -------------------------------------

			$allow_keyword_search_on_playa_fields = ( $this->check_yes( ee()->db->escape_str( ee()->input->post('allow_keyword_search_on_playa_fields') ) ) ) ? 'y' : 'n';
			
			$use_ignore_word_list = ( $this->check_yes( ee()->db->escape_str( ee()->input->post('use_ignore_word_list') ) ) ) ? 'y' : 'n';

			$enable_search_log = ( $this->check_yes( ee()->db->escape_str( ee()->input->post('enable_search_log') ) ) ) ? 'y' : 'n';
			
			$enable_smart_excerpt = ( $this->check_yes( ee()->db->escape_str( ee()->input->post('enable_smart_excerpt') ) ) ) ? 'y' : 'n';

			$enable_fuzzy_searching = ( $this->check_yes( ee()->db->escape_str( ee()->input->post('enable_fuzzy_searching') ) ) ) ? 'y' : 'n';

			$enable_fuzzy_searching_plurals = ( $this->check_yes( ee()->db->escape_str( ee()->input->post('enable_fuzzy_searching_plurals') ) ) ) ? 'y' : 'n';

			$enable_fuzzy_searching_phonetics = ( $this->check_yes( ee()->db->escape_str( ee()->input->post('enable_fuzzy_searching_phonetics') ) ) ) ? 'y' : 'n';

			$enable_fuzzy_searching_spelling = ( $this->check_yes( ee()->db->escape_str( ee()->input->post('enable_fuzzy_searching_spelling') ) ) ) ? 'y' : 'n';

			// now collapse our word_list into a nice useable format

			$words = '';

			if( ee()->input->post('word_hidden') !== FALSE ) 
			{
				foreach( ee()->input->post('word_hidden') AS $word ) 
				{
					// clean the word to be safe
					$words[] = ee()->db->escape_str( $word );
				}

				asort( $words );

				$words = implode( '||', $words );
			}

			// -------------------------------------
			//	Check DB for insert / update
			// -------------------------------------
			
			$this->cached_vars['prefs']['allow_keyword_search_on_playa_fields']	= $allow_keyword_search_on_playa_fields;
			$this->cached_vars['prefs']['ignore_word_list'] 					= $words;
			$this->cached_vars['prefs']['use_ignore_word_list'] 				= $use_ignore_word_list;
			$this->cached_vars['prefs']['enable_search_log']					= $enable_search_log;
			$this->cached_vars['prefs']['enable_smart_excerpt']					= $enable_smart_excerpt;
			$this->cached_vars['prefs']['enable_fuzzy_searching']				= $enable_fuzzy_searching;
			$this->cached_vars['prefs']['enable_fuzzy_searching_plurals']		= $enable_fuzzy_searching_plurals;
			$this->cached_vars['prefs']['enable_fuzzy_searching_phonetics']		= $enable_fuzzy_searching_phonetics;
			$this->cached_vars['prefs']['enable_fuzzy_searching_spelling']		= $enable_fuzzy_searching_spelling;

			//This is a global setting, so to keep the consistency, we'll set this on all the sites

			$query = ee()->db->query(" SELECT site_id FROM exp_sites ");

			foreach( $query->result_array() AS $row )
			{
				if( $row['site_id'] != '' )
				{
					if ( $this->data->set_preference( $this->cached_vars['prefs'], $row['site_id'] ) !== FALSE )
					{
						$message	= lang( 'preferences_updated' );
					}
				}
			}

			$message	= lang( 'preferences_updated' );

			// Now we'll need to repload our preferences as they've changed, but we dont know about it yet
			// We could just use the passed data, but this is better for maintaining data consistency
			$new_prefs = $this->data->reload_preferences();




			// Also we've merged in the cache options, so handle their posts too

			// -------------------------------------
			//	Validate
			// -------------------------------------

			$errors	= array();

			if ( is_numeric( ee()->input->get_post('refresh') ) === FALSE )
			{
				$errors[]	= lang('numeric_refresh');
			}

			if ( count( $errors ) > 0 )
			{
				return $this->show_error($errors);
			}

			// -------------------------------------
			//	Update or Create
			// -------------------------------------
			
			$query		= ee()->db->query( "SELECT COUNT(*) AS count FROM exp_super_search_refresh_rules WHERE site_id = ".ee()->db->escape_str( ee()->config->item('site_id')));
			
			if ( $query->num_rows() > 0 AND $query->row('count') > 0 )
			{
				$update	= TRUE;
			}
			
			$refresh	= ( ee()->input->get_post('refresh') != '' ) ? ee()->input->get_post('refresh'): 0;
			$date		= ( $refresh == 0 ) ? 0: ( ee()->localize->now + ( $refresh * 60 ) );
			
			if ( $update === TRUE )
			{
				ee()->db->query(
					ee()->db->update_string(
						'exp_super_search_refresh_rules',
						array(
							'refresh'		=> $refresh,
							'date'			=> $date,
							'member_id'		=> ee()->session->userdata('member_id')
							),
						array(
							'site_id'		=> ee()->db->escape_str( ee()->config->item('site_id'))
							)
						)
					);
			}
			else
			{
				ee()->db->query(
					ee()->db->insert_string(
						'exp_super_search_refresh_rules',
						array(
							'refresh'		=> $refresh,
							'date'			=> $date,
							'site_id'		=> ee()->config->item('site_id'),
							'member_id'		=> ee()->session->userdata('member_id')
							)
						)
					);
			}
	        
			// -------------------------------------
			//	Category group ids?
			// -------------------------------------
			
			$category_group_ids	= array();
			
			if ( empty( $_POST['category_group_id'] ) === FALSE )
			{
				foreach ( $_POST['category_group_id'] as $val )
				{
					$category_group_ids[]	= $val;
				}
			}
	        
			// -------------------------------------
			//	Template ids?
			// -------------------------------------
			
			$template_ids	= array();
			
			if ( empty( $_POST['template_id'] ) === FALSE )
			{
				foreach ( $_POST['template_id'] as $val )
				{
					$template_ids[]	= $val;
				}
			}
	        
			// -------------------------------------
			//	Channel ids?
			// -------------------------------------
			
			$channel_ids	= array();
			
			if ( empty( $_POST['channel_id'] ) === FALSE )
			{
				foreach ( $_POST['channel_id'] as $val )
				{
					$channel_ids[]	= $val;
				}
			}

			// -------------------------------------
			//	Update refresh rules
			// -------------------------------------
			
			$sql	= array();
			
			$sql[]	= "DELETE FROM exp_super_search_refresh_rules
						WHERE category_group_id != 0
						AND site_id = ".ee()->db->escape_str( ee()->config->item('site_id') );
			
			$sql[]	= "DELETE FROM exp_super_search_refresh_rules
						WHERE channel_id != 0
						AND site_id = ".ee()->db->escape_str( ee()->config->item('site_id') );
			
			$sql[]	= "DELETE FROM exp_super_search_refresh_rules
						WHERE template_id != 0
						AND site_id = ".ee()->db->escape_str( ee()->config->item('site_id') );
						
			foreach ( $sql as $q )
			{					
				ee()->db->query( $q );
			}

			foreach ( $category_group_ids as $val )
			{
				$sql	= ee()->db->insert_string('exp_super_search_refresh_rules',
											array(
												'site_id'			=> ee()->config->item('site_id'),
												'refresh'			=> $refresh,
												'date'				=> $date,
												'category_group_id'	=> $val
											)
				);
						
				ee()->db->query( $sql );
			}

			foreach ( $channel_ids as $val )
			{
				$sql	= ee()->db->insert_string('exp_super_search_refresh_rules',
											array(
												'site_id'			=> ee()->config->item('site_id'),
												'refresh'			=> $refresh,
												'date'				=> $date,
												'channel_id'		=> $val
											)
				);
						
				ee()->db->query( $sql );
			}
			
			foreach ( $template_ids as $val )
			{
				$sql	= ee()->db->insert_string('exp_super_search_refresh_rules',
											array(
												'site_id'			=> ee()->config->item('site_id'),
												'refresh'			=> $refresh,
												'date'				=> $date,
												'template_id'	=> $val
											)
				);
						
				ee()->db->query( $sql );
			}

			// End cache post hanlding
		}

		// -------------------------------------
		//	Title and Crumbs
		// -------------------------------------

		$crumb	= lang( 'manage_search_options' );

		$this->add_crumb( $crumb );
		
		$this->cached_vars['module_menu_highlight'] = 'module_search_options';

		$this->cached_vars['form_url'] = $this->base . '&method=search_options';

		$this->cached_vars['theme_path'] = $this->theme_folder_url();
        
		// -------------------------------------
		//	Grab all our settings
		// -------------------------------------
		
		$this->cached_vars['prefs']	= array(
			'allow_keyword_search_on_playa_fields' 	=> 'n',
			'ignore_word_list'						=> array(),
			'use_ignore_word_list'					=> 'n',
			'enable_search_log'						=> 'y',
			'enable_smart_excerpt'					=> 'y',
			'enable_fuzzy_searching'				=> 'y',	
			'enable_fuzzy_searching_plurals'		=> 'y',
			'enable_fuzzy_searching_phonetics'		=> 'n',
			'enable_fuzzy_searching_spelling'		=> 'n'
		);

		// -------------------------------------
		//	Set vars
		// -------------------------------------

		foreach ( $this->cached_vars['prefs'] as $key => $val )
		{
			if( isset( $new_prefs[ $key ] ) )
			{
				if ( $new_prefs[ $key ] !== FALSE )
				{
					if( $key == 'ignore_word_list')
					{
						if( $new_prefs[ $key ] !== '' )
						{
							$this->cached_vars['prefs'][$key]	= explode( '||', $new_prefs[ $key ] );
						}
					}
					else
					{
						$this->cached_vars['prefs'][$key]	= $new_prefs[ $key ];
					}
				}
			}
			else
			{
				if ( ee()->config->item( $key ) !== FALSE )
				{
					if( $key == 'ignore_word_list')
					{
						if( ee()->config->item( $key ) !== '' )
						{
							$this->cached_vars['prefs'][$key]	= explode( '||', ee()->config->item( $key ) );
						}
					}
					else
					{
						$this->cached_vars['prefs'][$key]	= ee()->config->item( $key );
					}
				}
			}
		}

		$this->data->set_default_cache_prefs();

		// -------------------------------------
		//	Clear cache?
		// -------------------------------------

		if ( ee()->input->get_post('msg') == 'cache_cleared' )
		{
			$this->_clear_cache();
		}

		// -------------------------------------
		//	Prep vars
		// -------------------------------------

		$edit_mode							= 'new';
        $this->cached_vars['refresh']		= '0';
        $this->cached_vars['date']			= '';
        $this->cached_vars['next_refresh']	= '&nbsp;';
        
		$this->cached_vars['playa_installed'] = FALSE;

		// -------------------------------------
		//	Query
		// -------------------------------------
		
		$sql	=  "SELECT date, refresh FROM exp_super_search_refresh_rules
					WHERE site_id = '".ee()->db->escape_str( ee()->config->item('site_id') )."'
					LIMIT 1";
		
		$query	= ee()->db->query($sql);
		
		if ( $query->num_rows() > 0 )
		{
			$edit_mode	= 'edit';
			$this->cached_vars['refresh']	= $query->row('refresh');
			$this->cached_vars['date']		= $query->row('date');
			
			if ( $query->row('refresh') > 0 )
			{
				$this->cached_vars['next_refresh']		= str_replace( '%n%', $this->human_time( $query->row('date')), lang('next_refresh') );
			}
		}

		// -------------------------------------
		//  Template Refresh
		// -------------------------------------
		
		$attributes = array('class'		=> 'select',
							'name'		=> 'template_id[]',
							'id'		=> 'template_id',
							'multiple'	=> 'multiple',
							'size'		=> 10);
		
		$template_refresh_data = array('attributes' => $attributes, 'data' => array() );
		
		foreach($this->data->get_template_groups() AS $group_id => $group_name)
		{
			$optgroup = array('label' => $group_name, 'group_id' => $group_id, 'data' => array(), 'class' => 'optgroup' );
			
			foreach($this->data->get_templates_by_group_id( $group_id ) AS $value => $text)
			{	
				$option = array('value' => $value, 'text' => $text );
				
				if ($edit_mode == 'edit' && in_array( $value, $this->data->get_selected_templates_by_site_id( ee()->config->item('site_id') ) ) )
				{
					$option['selected'] = TRUE;
				}

				$optgroup['data'][] = $option;
			}
			
			$template_refresh_data['data'][] = $optgroup;
		}

		$this->cached_vars['template_id_field'] = $this->view( 'document_optgroup.html', $template_refresh_data, TRUE );

		// -------------------------------------
		//	Channel Refresh
		// -------------------------------------

		$attributes = array('class'		=> 'select',
							'name'		=> 'channel_id[]',
							'id'		=> 'channel_id',
							'multiple'	=> 'multiple',
							'size'		=> 5);

		$channel_refresh_data = array('attributes' => $attributes, 'data' => array() );

		foreach($this->data->get_channel_titles( ee()->config->item('site_id') ) AS $value => $text)
		{
			$option = array('value' => $value, 'text' => $text );

			if ($edit_mode == 'edit' && in_array( $value, $this->data->get_selected_channels_by_site_id( ee()->config->item('site_id') ) ) )
			{
				$option['selected'] = TRUE;
			}

			$channel_refresh_data['data'][] = $option;
		}

		$this->cached_vars['channel_id_field'] = $this->view( 'document_optgroup.html', $channel_refresh_data, TRUE );

		// -------------------------------------
		//  Category Refresh
		// -------------------------------------

		$attributes = array('class'		=> 'select',
							'name'		=> 'category_group_id[]',
							'id'		=> 'category_group_id',
							'multiple'	=> 'multiple',
							'size'		=> 5);

		$category_refresh_data = array('attributes' => $attributes, 'data' => array() );

		foreach($this->data->get_category_groups() AS $value => $text)
		{
			$option = array('value' => $value, 'text' => $text );

			if ($edit_mode == 'edit' && in_array( $value, $this->data->get_selected_category_groups_by_site_id( ee()->config->item('site_id') ) ) )
			{
				$option['selected'] = TRUE;
			}

			$category_refresh_data['data'][] = $option;
		}

		$this->cached_vars['category_group_id_field'] = $this->view( 'document_optgroup.html', $category_refresh_data, TRUE );
		
		// -------------------------------------
		// Lexicon
		// -------------------------------------

		$query = ee()->db->query( " SELECT COUNT(entry_id) AS total_entries FROM exp_channel_titles ");

		$this->cached_vars['lexicon_progress_count_total'] = $query->row('total_entries');
		
		$queryb = ee()->db->query( " SELECT * FROM exp_super_search_lexicon_log 
								WHERE origin = 'manual' ORDER BY action_date DESC LIMIT 1 ");

		if( $queryb->num_rows() > 0 )
		{
			$last_build = $this->human_time( $queryb->row('action_date') );

			$this->cached_vars['lexicon_build_lang'] = lang('lexicon_rebuild');

			$this->cached_vars['lexicon_last_build'] = lang('lexicon_last_built') . $last_build;// has never been built.';
		}
		else
		{
			$this->cached_vars['lexicon_build_lang'] = lang('lexicon_build');

			$this->cached_vars['lexicon_last_build'] =  lang('lexicon_never_built');
		}

		$this->cached_vars['lexicon_build_url'] = $this->base.'&method=lexicon&build=yes&total='.$query->row('total_entries').'&batch=0';


		$this->cached_vars['success_png_url'] = $this->theme_folder_url() . "images/success.png";

		ee()->cp->add_js_script(
		  array('ui' => array(
				'core', 'progressbar'))); 

		ee()->javascript->compile();

		$this->cached_vars['just_lexicon'] = FALSE;

		// -------------------------------------
		// Spelling
		// -------------------------------------

		$this->_spelling_data();

		// -------------------------------------
		//	Prep message
		// -------------------------------------

		$this->_prep_message( $message );

		$this->_add_styles( TRUE );

		// -------------------------------------
		//  Load Homepage
		// -------------------------------------

		return $this->ee_cp_view('options.html');
	}

	//	End search options

	// -----------------------------------------------------------------

	/**
	 * Module's Search Options

	 * @access	public
	 * @param	string
	 * @return	null
	 */
    
	function search_utils( $message = '' )
    {
		// -------------------------------------
		//	Title and Crumbs
		// -------------------------------------

		$crumb	= lang( 'manage_search_utils' );

		$this->add_crumb( $crumb );

		$this->cached_vars['module_menu_highlight'] = 'module_search_utils';

		$this->cached_vars['form_url'] = $this->base . '&method=search_utils';

		$this->cached_vars['theme_path'] = $this->theme_folder_url();
		
		// -------------------------------------
		// Lexicon 
		// -------------------------------------

		$query = ee()->db->query( " SELECT COUNT(entry_id) AS total_entries FROM exp_channel_titles ");

		$this->cached_vars['lexicon_progress_count_total'] = $query->row('total_entries');
		
		$queryb = ee()->db->query( " SELECT * FROM exp_super_search_lexicon_log 
								WHERE origin = 'manual' ORDER BY action_date DESC LIMIT 1 ");

		if( $queryb->num_rows() > 0 )
		{
			$last_build = $this->human_time( $queryb->row('action_date') );

			$this->cached_vars['lexicon_build_lang'] = lang('lexicon_rebuild');

			$this->cached_vars['lexicon_last_build'] = lang('lexicon_last_built') . $last_build;// has never been built.';
		}
		else
		{
			$this->cached_vars['lexicon_build_lang'] = lang('lexicon_build');

			$this->cached_vars['lexicon_last_build'] =  lang('lexicon_never_built');
		}

		$this->cached_vars['lexicon_build_url'] = $this->base.'&method=lexicon&build=yes&total='.$query->row('total_entries').'&batch=0';

		$this->cached_vars['success_png_url'] = $this->theme_folder_url() . "images/success.png";

		ee()->cp->add_js_script(
		  array('ui' => array(
				'core', 'progressbar'))); 

		ee()->javascript->compile(); 

		$this->cached_vars['just_lexicon'] = FALSE; 

		// -------------------------------------
		// Spelling
		// -------------------------------------

		$this->_spelling_data();

		// -------------------------------------
		//	Prep message
		// -------------------------------------
		
		$this->_prep_message( $message );
		
		$this->_add_styles( TRUE );

		// -------------------------------------
		//  Load Homepage
		// -------------------------------------

		return $this->ee_cp_view('utilities.html');
	}
	
	//	End search options

	// -----------------------------------------------------------------

	/**
	 * Module's Search Log
	 
	 * @access	public
	 * @param	string
	 * @return	null
	 */
    
	function search_log( $message = '' )
    {
		// -------------------------------------
		//	Title and Crumbs
		// -------------------------------------

		$crumb	= lang( 'search_log' );

		$this->add_crumb( $crumb );
		
		$this->cached_vars['module_menu_highlight'] = 'module_search_log';

		// -------------------------------------
		//	Prep message
		// -------------------------------------

		$this->_prep_message( $message );

		// -------------------------------------
		//  Figure out what we're doing
		// -------------------------------------

		$this->cached_vars['multiple_sites_enabled'] = ee()->config->item('multiple_sites_enabled');


		// is logging already enabled?
		// show a different message depending on the state

		if( $this->check_yes( ee()->config->item('enable_search_log') ) )
		{
			$this->cached_vars['no_searches_recorded'] = lang('no_searches_recorded');
        
			// -------------------------------------
			//	Clearing the log?
			// -------------------------------------
			
			if ( ee()->input->post('clear_log') !== FALSE AND $this->check_yes( ee()->input->post('clear_log') ))
			{
				ee()->db->query( "DELETE FROM exp_super_search_log WHERE site_id = " . ee()->config->item('site_id') );
				
				$message = $this->cached_vars['no_searches_recorded'] = lang('search_log_cleared');
			}
		}
		else
		{
			$this->cached_vars['no_searches_recorded'] = str_replace( '%enable_link%', $this->base.'&method=search_options', lang('no_searches_yet_long') );
		}

        $type = 'log';

        $terms = array();

        $fuzzy_terms = array();
		
		if( ee()->input->get_post('term_id') !== FALSE 
			OR ee()->input->get_post('term') !== FALSE
			OR ee()->input->get_post('search_term') !== FALSE )
		{
			// We have (at least one) term to display
			$type = 'term_id';

			if( ee()->input->get_post('search_term')!== FALSE )
			{
				$type = 'search';

				$terms = explode( ' ', ee()->db->escape_str( ee()->input->get_post('search_term')));
			}
			elseif( ee()->input->get_post('term') !== FALSE )
			{
				$type = 'terms';

    			$terms = explode( ' ', base64_decode( ee()->db->escape_str( ee()->input->get_post('term') ) ) );
			}

			// -------------------------------------
			//	The Term(s) details
			// -------------------------------------
			
			$sql = " SELECT * FROM exp_super_search_terms 
						WHERE ( ";

			if( count( $terms ) > 0 )
			{
	    		foreach( $terms as $term ) 
	    		{
	    			if( trim($term) != '' )
		    		{
		    			$sql .= " term LIKE '%" . $term . "%' OR ";

						$this->cached_vars['search_terms'][] = $term;
					}
	    		}

		    	$sql .= " 1=0 ";
	    	}
	    	else
	    	{
	    		$sql .= " term_id = '" . ee()->db->escape_str( ee()->input->get_post('term_id') ) . "' ";
	    	}

	    	$sql .= " ) AND count > 0 ORDER BY count DESC ";
			
			$query = ee()->db->query( $sql );

			if( $query->num_rows() < 1 )
			{
				// We have no matching terms
				$this->cached_vars['terms'] = FALSE;

				$this->cached_vars['no_results'] = TRUE;
			}
			else
			{
				foreach( $query->result_array() as $row )
				{
					if( $row['first_seen'] == '0' )
					{
						$row['first_seen_formatted'] = '-';

						$row['first_seen_wordy'] = '-';
					}
					else
					{
						$row['first_seen_formatted'] = $this->human_time($row['first_seen']);

						$row['first_seen_wordy'] = $this->_time_elapsed_string( ee()->localize->now - $row['first_seen'] );
					}

					if( $row['last_seen'] == '0' )
					{
						$row['last_seen_formatted'] = '-';

						$row['last_seen_wordy'] = '-';
					}
					else
					{
						$row['last_seen_formatted'] = $this->human_time($row['last_seen']);

						$row['last_seen_wordy'] = $this->_time_elapsed_string( ee()->localize->now - $row['last_seen'] );
					}

					$sparkline = $this->_get_sparkline( $row['term'] );

					if( $row['suggestions'] != '' )
					{
						$suggestions = unserialize( $row['suggestions'] );

						if( is_array( $suggestions ) AND count( $suggestions ) > 0 )
						{
							$fuzzy_terms = $suggestions;
						}
					}

					$row['sparkline'] = $sparkline;

					$this->cached_vars['terms'][] = $row;

					$term = $row['term'];


					if( $type == 'term_id' )
					{
						$this->cached_vars['search_single_term'] = $row['term'];
					}
				}
			}

			if( count( $fuzzy_terms ) > 0 )
			{
				// Grab any suggested terms also
				$sql = " SELECT * FROM exp_super_search_terms WHERE count > 0 AND ";

	    		foreach( $fuzzy_terms as $term ) 
	    		{
	    			if( trim($term['term']) != '' )
		    		{
		    			$sql .= " term LIKE '%" . ee()->db->escape_str( $term['term'] ) . "%' OR ";

						$this->cached_vars['search_terms'][] = $term['term'];
					}
	    		}

		    	$sql .= " 1=0 ";

		    	$query = ee()->db->query( $sql );

		    	if( $query->num_rows > 0 )
		    	{
		    		foreach( $query->result_array() as $row )
				    {
				    			
			    		if( $row['first_seen'] == '0' )
						{
							$row['first_seen_formatted'] = '-';			
						
							$row['first_seen_wordy'] = '-';
						}
						else
						{				
							$row['first_seen_formatted'] = $this->human_time($row['first_seen']);

							$row['first_seen_wordy'] = $this->_time_elapsed_string( ee()->localize->now - $row['first_seen'] );
						}

						if( $row['last_seen'] == '0' )
						{
							$row['last_seen_formatted'] = '-';

							$row['last_seen_wordy'] = '-';
						}
						else
						{
							$row['last_seen_formatted'] = $this->human_time($row['last_seen']);

							$row['last_seen_wordy'] = $this->_time_elapsed_string( ee()->localize->now - $row['last_seen'] );
						}

						$sparkline = $this->_get_sparkline( $row['term'] );

						$row['sparkline'] = $sparkline;

						$this->cached_vars['terms'][] = $row;

						$term = $row['term'];


						if( $type == 'term_id' )
						{
							$this->cached_vars['search_single_term'] = $row['term'];
						}
		    		}
		    	}
			}

			$this->cached_vars['all_terms_link'] = $this->base.'&method=search_log';

			$this->cached_vars['term_link_base'] = $this->base.'&method=search_log&term_id=';

			$this->cached_vars['i'] = 1;

		}

		$this->cached_vars['type'] = $type;

		// -------------------------------------
		//	Search Log items
		// -------------------------------------

		$sql = " SELECT %q FROM exp_super_search_log ";

		if( $type != 'log' )
		{
			if( $this->cached_vars['terms'] === FALSE )
			{
				$sql .= " WHERE 1=0 ";
			}
			elseif( count( $this->cached_vars['terms'] ) > 0 )
			{
				$sql .= " WHERE ";

				foreach( $this->cached_vars['terms'] AS $single )
				{
					$sql .= " term LIKE '%" . ee()->db->escape_str( $single['term'] ) . "%' OR ";
				}

				$sql .= " 1=0 ";

			}
		}

		$sql .= " ORDER BY search_date DESC ";

		// -------------------------------------
        //  Get our precious precious data
        // -------------------------------------

        $searches = array();
		
		$query	= ee()->db->query( str_replace( '%q', 'COUNT(*) AS count', $sql ) );

		// -------------------------------------
		//	Paginate
		// -------------------------------------
    
        $paginate							= '';

        $row_count 							= 0;

        $total_results 						= $query->row('count');

		if ( $total_results > $this->row_limit )
		{
			$row_count		= ( ! ee()->input->get_post('row')) ? 0 : ee()->input->get_post('row');

			$url			= $this->base . AMP . 'method=search_log';

			if( ee()->input->get_post('search_term') !== FALSE )
			{
				$this->cached_vars['search_term'] = ee()->db->escape_str( ee()->input->get_post('search_term') );

				$url .= '&search_term=' . ee()->input->get_post('search_term');
			}
			elseif( ee()->input->get_post('term_id') !== FALSE )
			{
				$url .= '&term_id=' . ee()->input->get_post('term_id');
			}
			elseif( ee()->input->get_post('term') !== FALSE )
			{
				$url .= '&term=' . ee()->input->get_post('term');
			}

			//get pagination info
			$pagination_data 	= $this->universal_pagination(array(
				'sql'					=> $sql, 
				'total_results'			=> $total_results, 
				'limit'					=> $this->row_limit,
				'current_page'			=> $row_count,
				'pagination_config'		=> array('base_url' => $url),
				'query_string_segment'	=> 'row'
			));
				

			$sql				= $pagination_data['sql'];
			$paginate 			= $pagination_data['pagination_links'];
		}

		$this->cached_vars['pagination'] = $paginate;

		$this->cached_vars['total_results'] = $total_results;

		$this->cached_vars['logging_state'] = ee()->config->item('enable_search_log');

		$query	= ee()->db->query( str_replace( '%q', '*', $sql ) );

		$search_data = $query->result_array();

		$last_row = array();

        foreach( $search_data as $row )
        {
        	$row['search_date_wordy'] = $this->_time_elapsed_string( time() - $row['search_date'] );

			$row['search_date_formatted'] = $this->human_time($row['search_date']);
			
			$query	= ( strpos( $row['query'], '{' ) === FALSE ) ? base64_decode( $row['query'] ): $row['query'];
        	
   			$query = unserialize( $query );

        	$row['extra'] = $query;

        	$row['term_view_url'] = $this->base . "&method=search_log&term=" . base64_encode( $row['term'] );

        	if( count( $last_row ) > 0 )
        	{
        		$row['last_term'] = $last_row['term'];
        	}
        	else
        	{
        		$row['last_term'] = '';
        	}

        	if( strpos( $row['site_id'], '|' ) )
        	{
        		$row['site_id'] = str_replace( '|', ' & ', $row['site_id'] );
        	}

        	$last_row = $row;

        	$searches[] = $row;
        }
        
        $this->cached_vars['log'] = $searches;	

		$this->cached_vars['i'] = 1;

		$this->cached_vars['form_url'] = $this->base . "&method=search_log";
			
		// -------------------------------------
		//  Load Search Log
		// -------------------------------------

		$this->_add_styles();

		return $this->ee_cp_view('search_term.html');
	}
	
	//	End search options

	// -----------------------------------------------------------------

	/**
	 * Code pack installer page
	 *
	 * @access public
	 * @param	string	$message	lang line for update message
	 * @return	string				html output
	 */

	public function code_pack($message = '')
	{
		//--------------------------------------------
		//	message
		//--------------------------------------------

		if ($message == '' AND ee()->input->get_post('msg') !== FALSE)
		{
			$message = lang(ee()->input->get_post('msg'));
		}

		$this->cached_vars['message'] = $message;

		// -------------------------------------
		//	load vars from code pack lib
		// -------------------------------------

		$lib_name = str_replace('_', '', $this->lower_name) . 'codepack';
		$load_name = str_replace(' ', '', ucwords(str_replace('_', ' ', $this->lower_name))) . 'CodePack';

		ee()->load->library($load_name, $lib_name);
		ee()->$lib_name->autoSetLang = true;

		$cpt = ee()->$lib_name->getTemplateDirectoryArray(
			$this->addon_path . 'code_pack/'
		);

		$screenshot = ee()->$lib_name->getCodePackImage(
			$this->sc->addon_theme_path . 'code_pack/',
			$this->sc->addon_theme_url . 'code_pack/'
		);

		$this->cached_vars['screenshot'] = $screenshot;

		$this->cached_vars['prefix'] = $this->lower_name . '_';

		$this->cached_vars['code_pack_templates'] = $cpt;

		$this->cached_vars['form_url'] = $this->base . '&method=code_pack_install';

		//--------------------------------------
		//  menus and page content
		//--------------------------------------

		$this->cached_vars['module_menu_highlight'] = 'module_demo_templates';

		$this->add_crumb(lang('demo_templates'));

		//$this->cached_vars['current_page'] = $this->view('code_pack.html', NULL, TRUE);

		//---------------------------------------------
		//  Load Homepage
		//---------------------------------------------

		return $this->ee_cp_view('code_pack.html');
	}
	//END code_pack


	// --------------------------------------------------------------------

	/**
	 * Code Pack Install
	 *
	 * @access public
	 * @param	string	$message	lang line for update message
	 * @return	string				html output
	 */

	public function code_pack_install()
	{
		$prefix = trim((string) ee()->input->get_post('prefix'));

		if ($prefix === '')
		{
			ee()->functions->redirect($this->base . '&method=code_pack');
		}

		// -------------------------------------
		//	load lib
		// -------------------------------------

		$lib_name = str_replace('_', '', $this->lower_name) . 'codepack';
		$load_name = str_replace(' ', '', ucwords(str_replace('_', ' ', $this->lower_name))) . 'CodePack';

		ee()->load->library($load_name, $lib_name);
		ee()->$lib_name->autoSetLang = true;

		// -------------------------------------
		//	¡Las Variables en vivo! ¡Que divertido!
		// -------------------------------------

		$variables = array();

		$variables['code_pack_name']	= $this->lower_name . '_code_pack';
		$variables['code_pack_path']	= $this->addon_path . 'code_pack/';
		$variables['prefix']			= $prefix;

		// -------------------------------------
		//	install
		// -------------------------------------

		$details = ee()->$lib_name->getCodePackDetails($this->addon_path . 'code_pack/');

		$this->cached_vars['code_pack_name'] = $details['code_pack_name'];
		$this->cached_vars['code_pack_label'] = $details['code_pack_label'];

		$return = ee()->$lib_name->installCodePack($variables);

		$this->cached_vars = array_merge($this->cached_vars, $return);

		//--------------------------------------
		//  menus and page content
		//--------------------------------------

		$this->cached_vars['module_menu_highlight'] = 'module_demo_templates';

		$this->add_crumb(lang('demo_templates'), $this->base . '&method=code_pack');
		$this->add_crumb(lang('install_demo_templates'));

		//$this->cached_vars['current_page'] = $this->view('code_pack_install.html', NULL, TRUE);

		//---------------------------------------------
		//  Load Homepage
		//---------------------------------------------

		return $this->ee_cp_view('code_pack_install.html');
	}
	//END code_pack_install


	// -----------------------------------------------------------------

	/**
	 * Prep message

	 * @access	private
	 * @param	message
	 * @return	boolean
	 */

	function _prep_message( $message = '' )
	{
		if ( $message == '' AND isset( $_GET['msg'] ) )
		{
			$message = lang( $_GET['msg'] );
		}

		$this->cached_vars['message']	= $message;

		return TRUE;
	}

	//	End prep message


	// -----------------------------------------------------------------

	/**
	 * Module Upgrading
	 *
	 * This function is not required by the 1.x branch of ExpressionEngine by default.  However,
	 * as the install and deinstall ones are, we are just going to keep the habit and include it
	 * anyhow.
	 *		- Originally, the $current variable was going to be passed via parameter, but as there might
	 *		  be a further use for such a variable throughout the module at a later date we made it
	 *		  a class variable.
	 *
	 *
	 * @access	public
	 * @return	bool
	 */

	function super_search_module_update()
	{
		if ( ! isset($_POST['run_update']) OR $_POST['run_update'] != 'y' )
		{
			$this->add_crumb(lang('update_super_search'));
			$this->cached_vars['form_url'] = $this->base . '&method=super_search_module_update';
			return $this->ee_cp_view('update_module.html');
		}

		require_once $this->addon_path.'upd.super_search.php';

		$U = new Super_search_upd();

		if ($U->update() !== TRUE)
		{
			return ee()->functions->redirect($this->base . AMP . 'msg=update_failure');
		}
		else
		{
			return ee()->functions->redirect($this->base . AMP . 'msg=update_successful');
		}
	}

	//	END super_search_module_update()

	// -----------------------------------------------------------------

	/**
	 * Module Styles
	 *
	 * Adds all the styles we need for extra SuperSearch prettiness
	 *
	 *
	 * @access	private
	 * @return	null
	 */

	private function _add_styles( $include_all = FALSE, $cache_vars = FALSE )
	{
		 $ss_cache    =& ee()->sessions->cache['solspace'];

		//prevent double loading of assets
		if ( ! isset($ss_cache['assets']['super_search']) )
		{
			ee()->cp->add_to_head( '<link rel="stylesheet" type="text/css" href="' . $this->theme_folder_url() . 'css/super_search.css" />');

			if( $include_all )
			{
				ee()->cp->add_to_foot( '<script type="text/javascript" src="' . $this->theme_folder_url() . 'js/jquery.animate-shadow-min.js"></script>');
			}

			ee()->cp->add_to_foot( '<script type="text/javascript" src="' . $this->theme_folder_url() . 'js/super_search.js"></script>');

			$ss_cache['assets']['super_search'] = TRUE;
		}
	}

	//	End _add_styles

	// -----------------------------------------------------------------

	/**
	 * Theme folder url
	 *
	 * Returns the theme path string
	 *
	 * @access	private
	 * @return	string
	 */

	private function theme_folder_url()
	{
		return $this->sc->addon_theme_url;
	}

	//	End theme_folder_url

	// -----------------------------------------------------------------

	/**
	 * Get Searches Grouped
	 *
	 * Returns the searches in time groups
	 *
	 * @access	private
	 * @return	string
	 */

	private function _get_searches_grouped( $last_log_id = 0, $top_log_time = 0, $partial = TRUE, $period = 60 )
	{
		$ret = array();

		$top_ceil =  ceil( ee()->localize->now / 60 ) * 60;		
		$top_floor =  floor( ee()->localize->now / 60 ) * 60;

		// get the floor for this minute
		$current_top = $top_ceil;
		// 1 minute for now

		$current_bottom = $top_floor;
	
		$top_log_id = 0;

		$top_log_time_ceil = ceil( $top_log_time / 60 ) * 60;
		$top_log_time_floor = floor( $top_log_time / 60 ) * 60;

		$searches_grouped = array();
		$searches_fragment = array();

		$sql = " SELECT * FROM exp_super_search_log 
					WHERE search_date < " . ee()->db->escape_str( $top_ceil );

		if( $last_log_id > 0 ) 
		{
			$sql .= " AND log_id > ". ee()->db->escape_str( $last_log_id );
		}

		$sql .= " ORDER BY search_date DESC LIMIT 200 ";

		$query = ee()->db->query( $sql );

		$i = 0;

		foreach( $query->result_array() as $row ) 
		{
			if( $top_log_id <= 0 ) $top_log_id = $row['log_id'];
			if( $top_log_time <= 0 ) $top_log_time = $row['search_date'];

			while( $row['search_date'] < $current_bottom )
			{
				$current_top = $current_top - $period;

				$current_bottom = $current_top - $period;
			}
			
			if(	$top_log_time_floor <= $row['search_date'] AND 
					$row['search_date'] < $top_log_time_ceil )
			{

				$row['search_date_formatted'] = $this->human_time( $row['search_date'] );
				
				$row['term_view_url'] = $this->base . "&method=search_log&term=" . base64_encode( $row['term'] );

				if( $partial === TRUE )
				{
					$searches_fragment[] = $row;
				}
				else
				{
					if( isset( $searches_grouped[ $row['search_date_formatted'] ] ) === FALSE ) $searches_grouped[ $row['search_date_formatted'] ] = array();

					$searches_grouped[ $row['search_date_formatted'] ][] = $row;
					
				}
			}
			elseif( $current_bottom <= $row['search_date'] AND $row['search_date'] <=$current_top )
			{
				$row['search_date_formatted'] = $this->human_time( $row['search_date'] );

				if( isset( $searches_grouped[ $row['search_date_formatted'] ] ) === FALSE ) $searches_grouped[ $row['search_date_formatted'] ] = array();
				
				$row['term_view_url'] = $this->base . "&method=search_log&term=" . base64_encode( $row['term'] );

				$searches_grouped[ $row['search_date_formatted'] ][] = $row;
			}
    							
			$row['term_view_url'] = $this->base . "&method=search_log&term=" . base64_encode( $row['term'] );
			
			if( $top_log_time < $row['search_date'] ) $top_log_time = $row['search_date'];

			$ret['recent_searches'][] = $row;
		}

		$ret['top_log_id'] = $top_log_id;
		$ret['top_log_time'] = $top_log_time;

		$ret['searches_grouped'] = $searches_grouped;

		$ret['searches_fragment'] = $searches_fragment;

		return $ret;
	}

	//	End Searches Grouped

	// -----------------------------------------------------------------

	/**
	 * Get Sparkline
	 *
	 * Returns the sparkline for this term
	 *
	 * @access	private
	 * @return	string
	 */

	private function _get_sparkline( $term = '' )
	{
		if( $term != '' )
		{
			$midnight = ceil( ee()->localize->now / ( 24 * 60 * 60 ) ) * ( 24 * 60 * 60 );
		
			// 90 days is 90 * 24 * 60 * 60 seconds
			$period = $midnight - ( 90 * 24 * 60 * 60 );

			$sql = " SELECT FROM_UNIXTIME( search_date, '%Y%m%d') AS date, search_date, COUNT(*) AS cnt
										FROM exp_super_search_log 
										WHERE term LIKE '%" . ee()->db->escape_str( $term ) . "%' 
										AND search_date > " . ee()->db->escape_str( $period ) . "
										GROUP BY date ";

			$query = ee()->db->query( $sql );

			if( $query->num_rows() > 0 )
			{
				$data = array();
				$clean = array();

				foreach( $query->result_array() AS $row )
				{
					$data[ $row['search_date'] ] = $row['cnt'];
				}

				// Work backwards
				$top = $midnight;
				$bottom = $top - ( 24 * 60 * 60 );

				$i = 90;

				while( $top >= $period )
				{
					foreach( $data as $key => $val )
					{
						if( $bottom <= $key AND $key <= $top )
						{
							$clean[ $i ] = $val;
						}
					}

					if( isset( $clean[ $i ] ) === FALSE )
					{
						$clean[ $i ] = 0;
					}

						// Move our markers
					$top = $top - ( 24 * 60 * 60 );					
					$bottom = $top - ( 24 * 60 * 60 );
					$i--;

				}

				// Normalize these puppies
				$min_val = min( $clean );

				$max_val = max( $clean );


				if( $max_val < 1 )
				{
					return '';
				}

				foreach( $clean AS $key => $val )
				{
					$clean[ $key ] = round( ( $val / $max_val ) * 100, 1);
				}

				$clean = array_reverse( $clean );

				// Now collapse our array into a string
				return implode(',', $clean);
			}
			else
			{
				return '';
			}
		}

		return '';
	}

	//	End get sparkline

	// -----------------------------------------------------------------

	/**
	 * Time String
	 *
	 * Returns the relative time in a nicer wordy fashion
	 *
	 * @access	private
	 * @return	string
	 */

	private function _time_elapsed_string($ptime) 
	{
		$year 		= lang('period_year');
		$month 		= lang('period_month');
		$day 		= lang('period_day');
		$hour 		= lang('period_hour');
		$min 		= lang('period_min');
		$sec 		= lang('period_sec');
		$postfix 	= lang('period_postfix');
		$ago 		= lang('period_ago');

		$etime = $ptime;

		if ($etime < 1)  return lang('period_now');


		$a = array( 12 * 30 * 24 * 60 * 60  	=>  $year,
					   30 * 24 * 60 * 60        =>  $month,
					   24 * 60 * 60             =>  $day,
					   60 * 60                  =>  $hour,
					   60                       =>  $min,
					   1                        =>  $sec );

		 foreach ($a as $secs => $str)
		 {
			 $d = $etime / $secs;

			 if ($d >= 1)
			 {
				if($secs == 60)
				{
					$str = $min;
				}

				$r = round($d);
				return $r . ' ' . $str . ($r > 1 ? $postfix : ' ') . ' ' . $ago;
			}
		 }
	}

	//	End time elapsed string

	// -----------------------------------------------------------------

	/**
	 * Spelling data helpers (as we have multiple views on the same data)
	 *
	 *
	 * @access	private
	 * @return	null
	 */

	private function _spelling_data() 
	{
		$query = ee()->db->query( " SELECT COUNT(term_id) AS total_terms FROM exp_super_search_terms WHERE count > 0 AND entry_count = 0 AND suggestions = '' ");

		$this->cached_vars['spelling_unknown_count'] = $query->row('total_terms');
		
		$queryb = ee()->db->query( " SELECT COUNT(term_id) AS total_terms FROM exp_super_search_terms WHERE count > 0 AND entry_count = 0 AND suggestions != '' ");

		$this->cached_vars['spelling_known_count'] = $queryb->row('total_terms');

		// -------------------------------------
		// Spelling
		// -------------------------------------

		$this->cached_vars['spelling_build_lang'] = lang('lexicon_build');

		$this->cached_vars['spelling_state_lang'] = '<strong>34</strong> Terms with suggestions recorded.<br/> <strong>1</strong> unknown term to find suggestions.';

		$plural = ( $this->cached_vars['spelling_unknown_count'] > 1 ? 's' : '' );

		$this->cached_vars['suggestions_unknown_lang'] = str_replace( array('%i%', '%s%'), array( $this->cached_vars['spelling_unknown_count'], $plural ) , lang('spelling_unknown_line') );


		$plural = ( $this->cached_vars['spelling_known_count'] > 1 ? 's' : '' );

		$this->cached_vars['suggestions_known_lang'] = str_replace( array('%i%', '%s%'), array( $this->cached_vars['spelling_known_count'], $plural ) , lang('spelling_known_line') );

		$this->cached_vars['spelling_build_url'] = $this->base.'&method=spelling&build=yes&total='.$query->row('total_terms').'&batch=0';

		$this->cached_vars['success_png_url'] = $this->theme_folder_url() . "images/success.png";

		$this->cached_vars['just_spelling'] = FALSE; 

		return;
    }
    
    //	End spelling data
}

// END CLASS Super Search
