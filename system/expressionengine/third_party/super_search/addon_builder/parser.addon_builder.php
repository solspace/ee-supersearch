<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Addon Builder - Abstracted Template Parser
 *
 * Augments EE templating capability. Does not replace it.
 * Portions of this code are derived from core.template.php.
 * They are used with the permission of EllisLab, Inc.
 *
 *
 * @package		Solspace:Addon Builder
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2014, Solspace, Inc.
 * @link		http://solspace.com/docs/
 * @license		http://www.solspace.com/license_agreement/
 * @version		1.5.8
 * @filesource 	addon_builder/parser.addon_builder.php
 */

get_instance()->load->library('template');

if ( ! class_exists('Addon_builder_super_search'))
{
	require_once 'addon_builder.php';
}

class Addon_builder_parser_super_search extends EE_Template
{
	private $old_get = '';

	/**
	 * Addon builder instance for helping
	 *
	 * @var object
	 */
	protected $aob;
	public static $global_cache;

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	null
	 */

	public function __construct()
	{

		parent::__construct();

		$this->EE =& get_instance();
		$this->aob = new Addon_builder_super_search();

		// -------------------------------------
		//	global cache
		// -------------------------------------

		if ( ! isset(self::$global_cache))
		{
			if ( ! isset(ee()->session->cache['modules']['morsel']['template']))
			{
				ee()->session->cache['modules']['morsel']['template'] = array();
			}

			self::$global_cache =& ee()->session->cache['modules']['morsel']['template'];
		}

		// --------------------------------------------
		//  ExpressionEngine only loads snippets
		//  on PAGE and ACTION requests
		// --------------------------------------------

		$this->load_snippets();

		//Fix for template stuff starting in EE 2.8.0+
		//We cant count on them not adding this later as
		//public/private/protected so adding it here manually so we don't get
		//variable access mismatch errors.
		if ( ! isset($this->layout_conditionals))
		{
			$this->layout_conditionals = array();
		}
	}
	/* END constructor() */


	// --------------------------------------------------------------------

	/**
	 * Load Snippets for CP as ExpressionEngine only loads snippets
	 * on PAGE and ACTION requests
	 *
	 * @access	public
	 * @return	void
	 */

	public function load_snippets()
	{
		//this is done automatically for action and page requests
		if (REQ != 'CP' || (
				isset(self::$global_cache['snippets_loaded']) &&
				self::$global_cache['snippets_loaded']
			)
		)
		{
			return;
		}

		// load up any Snippets
		ee()->db->select('snippet_name, snippet_contents');
		ee()->db->where(
			'(site_id = ' . ee()->config->item('site_id').' OR site_id = 0)'
		);
		$fresh = ee()->db->get('snippets');

		if ($fresh->num_rows() > 0)
		{
			$snippets = array();

			foreach ($fresh->result() as $var)
			{
				$snippets[$var->snippet_name] = $var->snippet_contents;
			}

			$var_keys = array();

			foreach (ee()->config->_global_vars as $k => $v)
			{
				$var_keys[] = LD.$k.RD;
			}

			foreach ($snippets as $name => $content)
			{
				$snippets[$name] = str_replace(
					$var_keys,
					array_values(ee()->config->_global_vars),
					$content
				);
			}

			ee()->config->_global_vars = array_merge(
				ee()->config->_global_vars,
				$snippets
			);

			unset($var_keys);
		}

		unset($snippets);
		unset($fresh);

		self::$global_cache['snippets_loaded'] = true;
	}
	//END load_snippets


	// --------------------------------------------------------------------

	/**
	 * Process Template
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @param	bool
	 * @param	string|integer
	 * @return	null
	 */

	public function process_string_as_template($str)
	{
		// --------------------------------------------
		//  Solves the problem of redirect links (?URL=)
		//  being added by Typography in a CP request
		// --------------------------------------------

		if (REQ == 'CP')
		{
			$this->old_get = (isset($_GET['M'])) ? $_GET['M'] : '';
			$_GET['M'] = 'send_email';
		}

		// standardize newlines
		$str	= preg_replace("/(\015\012)|(\015)|(\012)/", "\n", $str);

		ee()->load->helper('text');

		// convert high ascii
		$str	= (
			ee()->config->item('auto_convert_high_ascii') == 'y'
		) ? ascii_to_entities($str): $str;

		// -------------------------------------
		//  Prepare for Processing
		// -------------------------------------

		//need to make sure this isn't run as static or cached
		$this->template_type	= 'webpage';
		$this->cache_status		= 'NO_CACHE';

		//restore_xml_declaration gets calls in parse_globals
		$this->template			= $this->convert_xml_declaration(
			$this->remove_ee_comments($str)
		);

		$this->log_item("Template Type: ".$this->template_type);

		// -------------------------------------`
		//	add our globals to global vars
		// -------------------------------------

		$this->log_item(
			"Solspace globals added (Keys): " .
			implode('|', array_keys($this->global_vars))
		);
		$this->log_item(
			"Solspace globals added (Values): " .
			trim(implode('|', $this->global_vars))
		);

		ee()->config->_global_vars = array_merge(
			ee()->config->_global_vars,
			$this->global_vars
		);

		$this->parse($str, false, ee()->config->item('site_id'));


		if (REQ == 'CP')
		{
			$_GET['M'] = $this->old_get;
		}

		// -------------------------------------------
		// 'template_post_parse' hook.
		//  - Modify template after tag parsing
		//
		if (ee()->extensions->active_hook('template_post_parse') === TRUE)
		{
			$this->final_template = ee()->extensions->call(
				'template_post_parse',
				$this->final_template,
				false, // $is_partial
				ee()->config->item('site_id')
			);
		}
		//
		// -------------------------------------------

		// --------------------------------------------
		//  Finish with Global Vars and Return!
		// --------------------------------------------

		return $this->parse_globals($this->final_template);
	 }
	// END process_string_as_template


	// --------------------------------------------------------------------

	/**
	 *	Fetch Add-Ons for Instllation
	 *	This caches parent lists
	 *
	 *	@access		public
	 *	@return		null
	 */

	public function fetch_addons()
	{
		//no res
		if (count($this->modules) > 0 && count($this->plugins) > 0)
		{
			return;
		}

		if ( isset(self::$global_cache['fetch_modules']) &&
			isset(self::$global_cache['fetch_plugins']))
		{
			$this->modules = self::$global_cache['fetch_modules'];
			$this->plugins = self::$global_cache['fetch_plugins'];
			return;
		}

		parent::fetch_addons();

		self::$global_cache['fetch_modules'] = array_unique($this->modules);
		self::$global_cache['fetch_plugins'] = array_unique($this->plugins);

	}
	// END fetch_addons
}
// END parser
