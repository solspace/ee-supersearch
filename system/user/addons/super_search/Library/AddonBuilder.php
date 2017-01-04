<?php

/**
 * Addon Builder - Base Class
 *
 * A class that helps with the building of ExpressionEngine Add-Ons
 * Supports EE 3.0.0+
 *
 * @package		Solspace:Addon Builder
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2016, Solspace, Inc.
 * @link		https://solspace.com/expressionengine
 * @license		https://solspace.com/software/license-agreement
 * @version		2.0.1
 * @filesource	super_search/Library/AddonBuilder.php
 */

namespace Solspace\Addons\SuperSearch\Library;

class AddonBuilder
{
	/**
	 * AOB version
	 * $this->version is already used by EE for modules.
	 *
	 * @var string
	 */
	static $class_version		= '2.0.1';

	/**
	 * Current EE version
	 * @var string
	 * @see __construct()
	 */
	public $ee_version			= '3';

	/**
	 * Internal cache
	 *
	 * @var array
	 * @see set_cache
	 */
	public $cache				= array();

	/**
	 * Object level for views
	 *
	 * @var	integer
	 * @see	view
	 */
	public $ob_level			= 0;

	/**
	 * cached output variables for view
	 *
	 * @var array
	 * @see view
	 */
	public $cached_vars			= array();

	/**
	 * cached item switching
	 *
	 * @var array
	 * @see cycle
	 */
	public $switches			= array();

	/**
	 * The general class name (ucfirst with underscores)
	 * used in database and class instantiation
	 *
	 * @var string
	 * @see __construct
	 */
	public $class_name			= '';

	/**
	 * The lowercased class name
	 * used for referencing module files and in URLs
	 *
	 * @var string
	 * @see __construct
	 */
	public $lower_name			= '';

	/**
	 * uppercase class name
	 *
	 * @var string
	 * @see __construct
	 */
	public $upper_name			= '';

	/**
	 * The name that we put into the Extensions DB table
	 *
	 * @var string
	 * @see __construct
	 */
	public $extension_name		= '';

	/**
	 * Module disabled? Typically used when an update is in progress.
	 *
	 * @deprecated
	 * @var boolean
	 */
	public $disabled			= false;

	/**
	 * Path to all user addons
	 *
	 * @var string
	 * @see __construct
	 */
	public $user_addons_path	= '';

	/**
	 * Path to expressionengine folder
	 *
	 * @var string
	 * @see __construct
	 */
	public $ee_path				= '';

	/**
	 * Path to all addons
	 *
	 * @var string
	 * @see __construct
	 */
	public $ci_path				= '';

	/**
	 * Path to addon using AOB
	 *
	 * @var string
	 * @see __construct
	 */
	public $addon_path			= '';

	/**
	 * EE module version number
	 *
	 * @var string
	 * @see __construct
	 */
	public $version				= '';

	/**
	 * Crumbs array for CP
	 *
	 * @var array
	 * @see add_crumb
	 * @see build_crumb
	 */
	public $crumbs				= array();

	/**
	 * Object instance for data file instance
	 *
	 * @var mixed
	 * @see data
	 */
	public $data				= false;

	/**
	 * object instance for actions file instance
	 *
	 * @var boolean
	 * @see actions
	 */
	public $actions				= false;

	/**
	 * cache for module prefernece
	 *
	 * @var array
	 * @see get_prefernece
	 */
	public $module_preferences	= array();

	/**
	 * Remote data holder for fetch_url
	 *
	 * @var string
	 * @see fetch_url
	 */
	public $remote_data			= '';

	/**
	 * Updater object instance
	 *
	 * @var object
	 * @see udpater
	 */
	public $updater;

	/**
	 * AOB path to this folder
	 *
	 * @var string
	 * @see __construct
	 */
	public $aob_path			= '';

	/**
	 * Automatically create class variables
	 * commonly used with pagination
	 *
	 * This lets universal_pagination and
	 * parse_pagination easily work together
	 *
	 * @var boolean
	 * @see universal_pagination
	 * @see parse_pagination
	 */
	public $auto_paginate 		= false;

	/**
	 * Local language array cache for items
	 * that manually load lang lines
	 *
	 * @var array
	 * @see fetch_language_file
	 * @see line
	 */
	public $language			= array();

	/**
	 * Local is_loaded cache for lang files
	 *
	 * @var array
	 * @see fetch_language_file
	 * @see line
	 */
	public $is_loaded			= array();

	/**
	 * Settings array for extensions
	 *
	 * @var array
	 */
	public $settings			= array();

	/**
	 * Unit test mode bool for alternating
	 * to test objects instead of helper functions
	 *
	 * @var boolean
	 */
	protected $unit_test_mode	= false;

	/**
	 * Unit test dummy object for helpers
	 * that cannot be mocked
	 *
	 * @var object
	 * @see test_object
	 */
	protected $test_mock;

	/**
	 * Sites array in site_id => site_label form
	 *
	 * @var array
	 * @see get_sites
	 */
	protected $sites;

	/**
	 * loaded addon.setup.php array
	 *
	 * @var array
	 * @see _construct
	 */
	protected $addon_info;

	/**
	 * Has Incoming message for user
	 *
	 * @var boolean
	 * @see prep_message
	 * @see mcp_view
	 */
	protected $has_message = false;

	/**
	 * Theme Url for this addon
	 *
	 * @var string
	 * @see __construct
	 */
	public $theme_url	= array();

	/**
	 * Theme Path for this addon
	 *
	 * @var string
	 * @see __construct
	 */
	public $theme_path	= array();

	// -------------------------------------
	//	module params
	// -------------------------------------

	/**
	 * Module Actions to insert on install
	 *
	 * @var array
	 * @see default_module_install
	 */
	public $module_actions		= array();

	/**
	 * Hooks
	 *
	 * @var array
	 * @see has_hooks
	 * @see update_extension_hooks
	 * @see remove_extension_hooks
	 */
	public $hooks				= array();

	// Defaults for the exp_extensions fields

	/**
	 * Default extension hooks
	 *
	 * @var array
	 * @see update_extension_hooks
	 * @see init_extension_builder
	 */
	public $extension_defaults	= array();

	/**
	 * Base url for CP
	 *
	 * @var string
	 * @see init_module_builder
	 * @see init_extension_builder
	 */
	public $base				= '';

	/**
	 * Nav Item array for native EE CP sidebar
	 *
	 * @access	protected
	 * @var		array
	 * @see		set_nav
	 */
	protected $nav_items		= array();

	/**
	 * Nav Objects for native EE CP sidebar
	 *
	 * @access	protected
	 * @var		array
	 * @see		set_nav
	 */
	protected $sidebar_items		= array();

	// -------------------------------------
	//	extension params
	// -------------------------------------

	/**
	 * Addon Description
	 *
	 * @var string
	 * @see _constructor
	 */
	public $description			= '';

	/**
	 * Addon Docs Url
	 *
	 * @var string
	 * @see _constructor
	 */
	public $docs_url			= '';

	/**
	 * Settings Exist?
	 *
	 * @var string
	 * @see init_extension_builder
	 */
	public $settings_exist		= 'n';

	/**
	 * The 'settings' field default
	 *
	 * @var array
	 * @see init_extension_builder
	 */
	public $default_settings	= array();



	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	null
	 */

	public function __construct($init_type = '')
	{
		// -------------------------------------
		//	ee version
		// -------------------------------------

		//normal
		if (defined('APP_VER'))
		{
			$this->ee_version = APP_VER;
		}
		//install wizard
		else if (isset(ee()->version))
		{
			$this->ee_version = ee()->version;
		}

		// -------------------------------------
		//	constants overrides
		// -------------------------------------

		//this changes between installer and regular :/
		$this->ee_path			= defined('EE_APPPATH') ? EE_APPPATH : APPPATH;
		$this->ci_path			= SYSPATH . 'ee/legacy/';
		$this->user_addons_path	= PATH_THIRD;

		// -------------------------------------
		//	because this is not always loaded
		// -------------------------------------

		if ( ! function_exists('lang'))
		{
			ee()->load->helper('language');
		}

		// -------------------------------------
		//	standard paths
		// -------------------------------------

		$instance = get_instance();
		$this->EE =& $instance;

		//--------------------------------------------
		// Auto-Detect Name
		//--------------------------------------------

		$ns_set = explode('\\', __NAMESPACE__);

		//we want [ADDON_NAME] from
		//Solspace\Addons\[ADDON_NAME]\Library
		$this->namespace_name	= $ns_set[2];

		//This assumes we are in addon_name/Library/AddonBuilder.php
		$folderName        = preg_split(
			'/' . preg_quote(DIRECTORY_SEPARATOR, '/') . '/',
			dirname(dirname(__FILE__)),
			-1,
			PREG_SPLIT_NO_EMPTY
		);
		$this->folder_name = end(
			$folderName
		);

		//--------------------------------------------
		// Important Class Vars
		//--------------------------------------------

		$this->lower_name		= strtolower(
			ee()->security->sanitize_filename($this->folder_name)
		);
		$this->upper_name		= strtoupper($this->lower_name);
		$this->class_name		= ucfirst($this->lower_name);
		$this->extension_name	= $this->class_name . '_ext';

		$this->theme_url		= URL_THIRD_THEMES . $this->lower_name . '/';
		$this->theme_path		= PATH_THIRD_THEMES . $this->lower_name . '/';

		// -------------------------------------
		//	set cache
		// -------------------------------------

		$this->set_cache();

		//--------------------------------------------
		// Add-On Path
		//--------------------------------------------

		$this->addon_path = $this->user_addons_path . $this->lower_name . '/';

		// -------------------------------------
		//	package path loaded?
		// -------------------------------------
		//	Sometimes our package path isn't
		//	auto loaded in the Install Wiz weeeee
		// -------------------------------------

		$paths = ee()->load->get_package_paths();

		if ( ! in_array($this->addon_path, $paths))
		{
			ee()->load->add_package_path($this->addon_path);
		}

		//--------------------------------------------
		// Language auto load
		//--------------------------------------------

		//EE autoloads lang for us, so lets check that first
		if (
			//install wizrd doesn't set EE lang
			! isset(ee()->lang->is_loaded) OR
			(
			! in_array($this->lower_name . '_lang.php', ee()->lang->is_loaded) AND
			! in_array('lang.' . $this->lower_name . '.php', ee()->lang->is_loaded)
			)
		)
		{
			if (isset(ee()->lang) AND
				is_object(ee()->lang) AND
				//loading before userdata is set can screw up per user lang
				//settings because the is_loaded cache does not flush
				//after sessions load :/
				isset(ee()->session) AND
				isset(ee()->session->userdata['language'])
			)
			{
				ee()->lang->loadfile($this->lower_name);
			}
			else
			{
				//add our lang file to the EE->lang->langauge array
				//without adding it to EE->lang->is_loaded
				//list so once we get our user's session lang setting,
				//it will override it properly
				$this->fetch_language_file($this->lower_name);
			}
		}

		//--------------------------------------------
		// Module Info
		//--------------------------------------------

		$this->addon_info	= require $this->addon_path . 'addon.setup.php';

		$this->version		= $this->addon_info['version'];
		$this->docs_url		= $this->addon_info['docs_url'];
		$this->description	= $this->addon_info['description'];

		// -------------------------------------
		//	CP items
		// -------------------------------------

		//bad hack with session because it complains about
		//calling this function without session object attached
		if (REQ == 'CP' && isset(ee()->session))
		{
			$this->base = ee('CP/URL', 'addons/settings/' . $this->lower_name);
		}

		//--------------------------------------------
		// Important Cached Vars - Used in Both Extensions and Modules
		//--------------------------------------------

		$this->cached_vars['CSRF_TOKEN']		= $this->get_csrf_token();
		$this->cached_vars['csrf_hidden_name']	= 'csrf_token';
		$this->cached_vars['csrf_js_name']		= 'CSRF_TOKEN';
		$this->cached_vars['page_crumb']		= '';
		$this->cached_vars['page_title']		= '';
		$this->cached_vars['text_direction']	= 'ltr';
		$this->cached_vars['message']			= '';
		$this->cached_vars['caller'] 			=& $this;
		$this->cached_vars['theme_url']			= $this->theme_url;
		$this->cached_vars['addon_theme_url']	= $this->theme_url;

		//--------------------------------------------
		// Determine View Path for Add-On
		//--------------------------------------------

		if ( isset($this->cache['view_path']))
		{
			$this->view_path = $this->cache['view_path'];
		}
		else
		{
			$possible_paths = array(
				$this->addon_path . 'views/'
			);

			foreach(array_unique($possible_paths) as $path)
			{
				if (is_dir($path))
				{
					$this->view_path = $path;
					$this->cache['view_path'] = $path;
					break;
				}
			}
		}

		// -------------------------------------
		//	initialize builder?
		// -------------------------------------

		$possible_init = 'init_' . $init_type . '_builder';

		if (is_callable(array($this, $possible_init)))
		{
			$this->$possible_init();
		}
	}
	// END __construct()


	// --------------------------------------------------------------------

	/**
	 * Sets cache link to class
	 *
	 * @access public
	 * @return object	cache instance
	 */

	public function set_cache()
	{
		//helps clean this ugly ugly code
		$s = 'solspace';
		$l = $this->lower_name;
		$g = 'global';
		$c = 'cache';
		$b = 'addon_builder';
		$a = 'addon';

		//no sessions? lets use global until we get here again
		if ( ! isset(ee()->session) OR
			! is_object(ee()->session))
		{
			if ( ! isset($GLOBALS[$s][$c][$b][$a][$l]))
			{
				$GLOBALS[$s][$c][$b][$a][$l] = array();
			}

			$this->cache =& $GLOBALS[$s][$c][$b][$a][$l];

			if ( ! isset($GLOBALS[$s][$c][$b][$g]) )
			{
				$GLOBALS[$s][$c][$b][$g] = array();
			}

			$this->global_cache =& $GLOBALS[$s][$c][$b][$g];
		}
		//sessions?
		else
		{
			//been here before?
			if ( ! isset(ee()->session->cache[$s][$b][$a][$l]))
			{
				//grab pre-session globals, and only unset the ones for this addon
				if ( isset($GLOBALS[$s][$c][$b][$a][$l]))
				{
					ee()->session->cache[$s][$b][$a][$l] = $GLOBALS[$s][$c][$b][$a][$l];

					//cleanup, isle 5
					unset($GLOBALS[$s][$c][$b][$a][$l]);
				}
				else
				{
					ee()->session->cache[$s][$b][$a][$l] = array();
				}
			}

			//check for solspace-wide globals
			if ( ! isset(ee()->session->cache[$s][$b][$g]) )
			{
				if (isset($GLOBALS[$s][$c][$b][$g]))
				{
					ee()->session->cache[$s][$b][$g] = $GLOBALS[$s][$c][$b][$g];

					unset($GLOBALS[$s][$c][$b][$g]);
				}
				else
				{
					ee()->session->cache[$s][$b][$g] = array();
				}
			}

			$this->global_cache =& ee()->session->cache[$s][$b][$g];
			$this->cache 		=& ee()->session->cache[$s][$b][$a][$l];
		}

		return $this->cache;
	}
	//END set_cache


	// --------------------------------------------------------------------

	/**
	 * Load Session
	 *
	 * Loads session if not present, for items like cp_js_end
	 *
	 * @access	public
	 * @return	mixed	sessions object or boolean false
	 */

	public function load_session()
	{
		if ( ! isset(ee()->session) OR ! is_object(ee()->session))
		{
			//needs to stay APPPATH so it can switch during
			//legacy installer
			if (file_exists(APPPATH . 'libraries/Session.php') ||
				file_exists($this->ci_path . 'libraries/Session.php'))
			{
				ee()->load->library('session');
			}
		}

		return (isset(ee()->session) AND is_object(ee()->session)) ?
					ee()->session :
					false;
	}
	//END load_session


	// --------------------------------------------------------------------

	/**
	 * Database Version
	 *
	 * Returns the version of the module in the database
	 *
	 * @access	public
	 * @param 	bool 	ignore all caches and get version from database
	 * @return	string
	 */

	public function database_version($ignore_cache = false)
	{
		if ( ! $ignore_cache AND
			 isset($this->cache['database_version']))
		{
			return $this->cache['database_version'];
		}

		//	----------------------------------------
		//	 Use Template object variable, if available
		// ----------------------------------------

		if ( ! $ignore_cache AND
			 isset(ee()->TMPL) AND
			 is_object(ee()->TMPL) AND
			 count(ee()->TMPL->module_data) > 0)
		{
			if ( ! isset(ee()->TMPL->module_data[$this->class_name]))
			{
				$this->cache['database_version'] = false;
			}
			else
			{
				$this->cache['database_version'] = ee()->TMPL->module_data[$this->class_name]['version'];
			}
		}
		//global cache
		elseif ( ! $ignore_cache AND
			isset($this->global_cache['module_data']) AND
			isset($this->global_cache['module_data'][$this->lower_name]['database_version']))
		{
			$this->cache['database_version'] = $this->global_cache['module_data'][$this->lower_name]['database_version'];
		}
		//fill global with last resort
		else
		{
			//	----------------------------------------
			//	 Retrieve all Module Versions from the Database
			//	  - By retrieving all of them at once,
			//   we can limit it to a max of one query per
			//   page load for all Bridge Add-Ons
			// ----------------------------------------

			$query = ee()->db
						->select('module_version, module_name')
						->get('modules');

			foreach($query->result_array() as $row)
			{
				if ( isset(ee()->session) AND is_object(ee()->session))
				{
					$this->global_cache['module_data'][
						strtolower($row['module_name'])
					]['database_version'] = $row['module_version'];
				}

				if ($this->class_name == $row['module_name'])
				{
					$this->cache['database_version'] = $row['module_version'];
				}
			}
		}

		//did get anything?
		return isset($this->cache['database_version']) ?
				$this->cache['database_version'] :
				false;
	}
	// END database_version()


	// --------------------------------------------------------------------

	/**
	 * Checks to see if extensions are allowed
	 *
	 *
	 * @access	public
	 * @return	bool	Whether the extensions are allowed
	 */

	public function extensions_allowed()
	{
		return $this->check_yes(ee()->config->item('allow_extensions'));
	}
	//END extensions_allowed


	// --------------------------------------------------------------------

	/**
	 * Takes a nav array and sets the sidebar with the nav
	 *
	 * @example
	 *
	 * 	$this->set_nav(array(
	 *		'home'			=> array(		//starts header link on right nav
	 *			'link'			=> $this->base,
	 *			'title'			=> lang('homepage'),
	 *			'sub_list'		=> array(	//starts a sub list under the header
	 *				'first' => array(
	 *					'title' => lang('test'),
	 *					'link'	=> 'http://google.com'
	 *				)
	 *			)
	 *		),
	 *		'preferences'	=> array(
	 *			'link'			=> $this->base.'&method=preferences',
	 *			'title'			=> lang('preferences')
	 *		),
	 *		'documentation'	=> array(
	 *			'link'			=> $this->docs_url,
	 *			'title'			=> lang('online_documentation'),
	 *			'external'		=> true	//opens in new window
	 *		)
	 *	));
	 *
	 * @access	public
	 * @param	array	$nav_items	$k=>$v array of items to add to nav
	 */

	public function set_nav($nav_items = array())
	{
		if (empty($nav_items))
		{
			return;
		}

		$this->nav_items = $nav_items;
		$this->sidebar_items['_main'] = $main = ee('CP/Sidebar')->make();

		$first		= false;
		$default	= false;

		foreach ($nav_items as $name => $item_data)
		{
			if ( ! $first)
			{
				$first = $name;
			}

			if (isset($item_data['default']))
			{
				$default = $name;
			}

			// -------------------------------------
			//	header
			// -------------------------------------

			$header = $this->sidebar_items[$name] = $main->addHeader(
				$item_data['title'],
				isset($item_data['link']) ? $item_data['link'] : ''
			);

			// -------------------------------------
			//	external link?
			// -------------------------------------

			if (isset($item_data['external']))
			{
				$header->urlIsExternal(
					$item_data['external']
				);
			}

			// -------------------------------------
			//	side button on url?
			// -------------------------------------

			if (isset($item_data['nav_button']))
			{
				$header->withButton(
					$item_data['nav_button']['title'],
					$item_data['nav_button']['link']
				);
			}

			// -------------------------------------
			//	sub list?
			// -------------------------------------

			if (isset($item_data['sub_list']))
			{
				$list = $header->addBasicList();

				$this->sidebar_items[$name . '_sub_list'] = array();

				foreach ($item_data['sub_list'] as $key => $list_data)
				{
					$item = $list->addItem(
						$list_data['title'],
						isset($list_data['link']) ? $list_data['link'] : ''
					);

					$this->sidebar_items[$name . '_sub_list'][$key] = $item;

					if ( ! empty($list_data['external']))
					{
						$item->urlIsExternal($list_data['external']);
					}
				}
			}
			//END if (isset($item_data['sub_list']))
		}
		//end foreach ($nav_items as $name => $item_data)
	}
	//end set_nav


	// --------------------------------------------------------------------

	/**
	 * MCP view with options
	 *
	 * @access	protected
	 * @param	array  $options input options for view
	 * @return	string			html output
	 */

	protected function mcp_view($options = array())
	{
		$defaults = array(
			'file'			=> '',
			'pkg_js'		=> array(),
			'pkg_css'		=> array(),
			'highlight'		=> '',
			'crumbs'		=> array(),
			'errors'		=> array(),
			'header_array'	=> array(),
			'show_message'	=> true
		);

		$options = array_merge($defaults, $options);

		$output = '';

		// -------------------------------------
		//	Crumbs?
		// -------------------------------------

		if ( ! empty($options['crumbs']))
		{
			foreach ($options['crumbs'] as $args)
			{
				call_user_func_array(array($this, 'add_crumb'), $args);
			}
		}

		// -------------------------------------
		//	optional js and css
		// -------------------------------------

		if ( ! empty($options['pkg_js']))
		{
			foreach ($options['pkg_js'] as $js)
			{
				ee()->cp->load_package_js(
					preg_replace('/\.js$/is', '', $js)
				);
			}
		}

		if ( ! empty($options['pkg_css']))
		{
			foreach ($options['pkg_css'] as $css)
			{
				ee()->cp->load_package_css(
					preg_replace('/\.css$/is', '', $css)
				);
			}
		}

		// -------------------------------------
		//	build header
		// -------------------------------------

		$header_name = $this->addon_info['name'];

		$header_title = 'Solspace ' . $header_name . '
				<span class="version-number">'
					. lang('version') . ': ' . $this->version .'</span>';

		if (isset($options['highlight']))
		{
			//sublist item?
			if (stristr($options['highlight'], '/'))
			{
				$hls = explode('/', $options['highlight']);

				if (isset($this->sidebar_items[$hls[0] . '_sub_list'][$hls[1]]))
				{
					$this->sidebar_items[$hls[0] . '_sub_list'][$hls[1]]->isActive();
					$header_title .= ': ' . $this->nav_items[$hls[0]]['sub_list'][$hls[1]]['title'];
				}
			}
			else if (isset($this->sidebar_items[$options['highlight']]))
			{
				$this->sidebar_items[$options['highlight']]->isActive();
				$header_title .= ': ' . $this->nav_items[$options['highlight']]['title'];
			}
		}
		else if (isset($this->nav_items[$options['file']]))
		{
			$header_title .= ': ' . $this->nav_items[$options['file']]['title'];
		}

		// -------------------------------------
		//	set page header
		// -------------------------------------

		ee()->view->header = array_merge(array(
			'title' => $header_title,
		), $options['header_array']);

		//--------------------------------------------
		// Load View Path, Call View File
		//--------------------------------------------

		//i hate having views inside of code
		//but this needs to be aob stored.
		if ($options['show_message'] && $this->has_message)
		{
			$output .= '<div class="inc-message">' .
				ee('CP/Alert')->getAllInlines() .
			'</div>';
		}

		$ret =  array(
			//breadcrumb heading
			'heading'		=> $header_title,
			'body'			=> $output . $this->view($options['file'], array(), true),
		);

		if (isset($this->sidebar_items['_main']))
		{
			$ret['sidebar'] = $this->sidebar_items['_main'];
		}

		if ( ! empty($this->crumbs))
		{
			//force copy
			$crumbs = array_merge(array(), $this->crumbs);

			//heading happens if an empty crumb is sent
			if (isset($crumbs['__heading__']))
			{
				$ret['heading'] = $crumbs['__heading__'];
				unset($crumbs['__heading__']);
			}

			$ret['breadcrumb'] = $crumbs;
		}

		return $ret;
	}
	//END mcp_view


	// --------------------------------------------------------------------

	/**
	 * Modal Confirm Dialog
	 *
	 * sets everything up for an MCP modal confirm dialog
	 * form view must also contain the proper html items
	 * see documentation.
	 * https://ellislab.com/expressionengine/user-guide/development/services/modal.html
	 *
	 * @access	protected
	 * @param	array	$options	 options array
	 * @return	void
	 */

	protected function mcp_modal_confirm($options)
	{
		// -------------------------------------
		//	options
		// -------------------------------------

		$defaults = array(
			'form_url'		=> '',
			'name'			=> '',
			'kind'			=> '',
			'plural'		=> '',
			'modal_vars'	=> array('desc' => '')
		);

		foreach ($defaults as $key => $value)
		{
			$$key = isset($options[$key]) ? $options[$key] : $value;
		}

		// -------------------------------------
		//	Vars
		// -------------------------------------

		$modal_vars = array_merge(array(
			'name'		=> 'modal-confirm-remove',
			'form_url'	=> $form_url,
			'checklist' => array(
				array(
					'kind' => $kind,
				)
			)
		), $modal_vars);

		// -------------------------------------
		//	add modal
		// -------------------------------------

		ee('CP/Modal')->addModal(
			$name,
			ee('View')->make('_shared/modal_confirm_remove')->render($modal_vars)
		);

		ee()->javascript->set_global(
			'lang.remove_confirm',
			$kind . ': <b>### ' . $plural . '</b>'
		);

		// -------------------------------------
		//	load JS
		// -------------------------------------

		ee()->cp->add_js_script(array(
			'file' => array('cp/confirm_remove'),
		));
	}
	//ENd mcp_modal_confirm


	// --------------------------------------------------------------------

	/**
	 * View File Loader
	 *
	 * forwarder to ee()->load->view with adding $this->cached_vars
	 *
	 *
	 * @access		public
	 * @param		string	$view				The view file to be located
	 * @param		array	$vars				vars to send to template
	 * @param		bool	$include_cached		include $this->cached_vars?
	 * @return		string						completed template
	 */

	public function view($view, $vars = array(), $include_cached = true)
	{
		$outvars = ($include_cached) ? $this->cached_vars : array();

		if (is_array($vars))
		{
			$outvars = array_merge($outvars, $vars);
		}

		return ee('View')->make($this->lower_name . ':' . $view)->render($outvars);
	}
	// END view()


	// --------------------------------------------------------------------

	/**
	 * Add Array of Breadcrumbs for a Page
	 *
	 * @access	public
	 * @param	array
	 * @return	null
	 */

	public function add_crumbs($array)
	{
		if ( is_array($array))
		{
			foreach($array as $value)
			{
				if ( is_array($value))
				{
					$this->add_crumb($value[0], $value[1]);
				}
				else
				{
					$this->add_crumb($value);
				}
			}
		}
	}
	// END add_crumbs


	// --------------------------------------------------------------------

	/**
	 * Add Single Crumb to List of Breadcrumbs
	 *
	 * @access	public
	 * @param	string		Text of breacrumb
	 * @param	string		Link, if any for breadcrumb
	 * @return	null
	 */

	public function add_crumb($text, $link = '__heading__')
	{
		$this->crumbs[(string) $link] = $text;
	}
	// END add_crumb


	// --------------------------------------------------------------------

	/**
	 * Prep Message
	 *
	 * @access	public
	 * @param	string	$message		incoming message or langline
	 * @param	boolean	$success		message is a success message (default yes)
	 * @param	boolean	$shared_form	is this going to a shared form?
	 * @return	void
	 */

	public function prep_message($message = '', $success = true, $shared_form = false)
	{
		if ( $message == '' AND isset($_GET['msg']) )
		{
			$message = lang(urldecode($_GET['msg']));
		}

		if ($message)
		{
			$inline = ($shared_form) ? 'shared-form' : 'inc-message';

			$alert = ee('CP/Alert')->makeInline($inline);

			if ( ! $success)
			{
				$alert->withTitle($message)->now();
			}
			else
			{
				$alert->asSuccess()->withTitle($message)->now();
			}

			$alert->canClose();

			$this->has_message = ! $shared_form;
		}
	}
	// End prep message


	// --------------------------------------------------------------------

	/**
	 * Field Output Prep for arrays and strings
	 *
	 *
	 * @access	public
	 * @param	string|array	The item that needs to be prepped for output
	 * @return	string|array
	 */

	public function output($item)
	{
		if (is_array($item))
		{
			$array = array();

			foreach($item as $key => $value)
			{
				$array[$this->output($key)] = $this->output($value);
			}

			return $array;
		}
		else if (is_string($item))
		{
			return htmlspecialchars($item, ENT_QUOTES);
		}
		else
		{
			return $item;
		}
	}
	// END output


	// --------------------------------------------------------------------

	/**
	 * Cycles Between Values
	 *
	 * Takes a list of arguments and cycles through them on each call
	 *
	 * @access	public
	 * @param	string|array	The items that need to be cycled through
	 * @return	string|array
	 */

	public function cycle($items)
	{
		if ( ! is_array($items))
		{
			$items = func_get_args();
		}

		$hash = md5(implode('|', $items));

		if ( ! isset($this->switches[$hash]) OR
			! isset($items[$this->switches[$hash] + 1]))
		{
			$this->switches[$hash] = 0;
		}
		else
		{
			$this->switches[$hash]++;
		}

		return $items[$this->switches[$hash]];
	}
	// END cycle


	// --------------------------------------------------------------------

	/**
	 * Fetch the Data for a URL
	 *
	 * @access public
	 * @param  string  $url			The URI that we are fetching
	 * @param  array   $post		The POST array we are sending
	 * @param  boolean $username	Possible username required
	 * @param  boolean $password	Password to go with the username
	 * @return string				url data
	 */
	public function fetch_url($url, $post = array(), $username = false, $password = false)
	{
		$data = '';

		$user_agent = ini_get('user_agent');

		if ( empty($user_agent))
		{
			$user_agent = $this->class_name.'/1.0';
		}

		// --------------------------------------------
		//  file_get_contents()
		// --------------------------------------------

		if ((bool) @ini_get('allow_url_fopen') !== false &&
			empty($post) && $username == false)
		{
			$opts = array(
				'http'	=> array('header' => "User-Agent:".$user_agent."\r\n"),
				'https'	=> array('header' => "User-Agent:".$user_agent."\r\n")
			);

			$context = stream_context_create($opts);

			if ($data = @file_get_contents($url, false, $context))
			{
				return $data;
			}
		}

		// --------------------------------------------
		//  cURL
		// --------------------------------------------

		if (function_exists('curl_init') === true AND
			($ch = @curl_init()) !== false)
		{
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);

			// prevent a PHP warning on certain servers
			if (! ini_get('safe_mode') AND ! ini_get('open_basedir'))
			{
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			}

			//	Are we posting?
			if ( ! empty( $post ) )
			{
				$str	= '';

				foreach ( $post as $key => $val )
				{
					$str	.= urlencode( $key ) . "=" . urlencode( $val ) . "&";
				}

				$str	= substr( $str, 0, -1 );

				curl_setopt( $ch, CURLOPT_POST, true );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $str );
			}

			curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($ch, CURLOPT_TIMEOUT, 15);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

			if ($username != false)
			{
				curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");

				if (defined('CURLOPT_HTTPAUTH'))
				{
					curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC | CURLAUTH_DIGEST);
				}
			}

			$data = curl_exec($ch);
			curl_close($ch);

			if ($data !== false)
			{
				return $data;
			}
		}

		// --------------------------------------------
		//  fsockopen() - Last but only slightly least...
		// --------------------------------------------

		$parts	= parse_url($url);
		$host	= $parts['host'];
		$path	= (!isset($parts['path'])) ? '/' : $parts['path'];
		$port	= ($parts['scheme'] == "https") ? '443' : '80';
		$ssl	= ($parts['scheme'] == "https") ? 'ssl://' : '';

		if (isset($parts['query']) AND $parts['query'] != '')
		{
			$path .= '?'.$parts['query'];
		}

		$data = '';

		$fp = @fsockopen($ssl.$host, $port, $error_num, $error_str, 7);

		if (is_resource($fp))
		{
			$getpost	= ( ! empty( $post ) ) ? 'POST ': 'GET ';

			fputs($fp, $getpost.$path." HTTP/1.0\r\n" );
			fputs($fp, "Host: ".$host . "\r\n" );

			if ( ! empty( $post ) )
			{
				$str	= '';

				foreach ( $post as $key => $val )
				{
					$str	.= urlencode( $key ) . "=" . urlencode( $val ) . "&";
				}

				$str	= substr( $str, 0, -1 );

				fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
				fputs($fp, "Content-length: " . strlen( $str ) . "\r\n");
			}

			fputs($fp,  "User-Agent: ".$user_agent."r\n");

			if ($username != false)
			{
				fputs ($fp, "Authorization: Basic ".base64_encode($username.':'.$password)."\r\n");
			}

			fputs($fp, "Connection: close\r\n\r\n");

			if ( ! empty( $post ) )
			{
				fputs($fp, $str . "\r\n\r\n");
			}

			// ------------------------------
			//  This error suppression has to do with a PHP bug involving
			//  SSL connections: http://bugs.php.net/bug.php?id=23220
			// ------------------------------

			$old_level = error_reporting(0);

			$headers = '';

			while ( ! feof($fp))
			{
				$bit = fgets($fp, 128);

				$headers .= $bit;

				if(preg_match("/^\r?\n$/", $bit)) break;
			}

			while ( ! feof($fp))
			{
				$data .= fgets($fp, 128);
			}

			error_reporting($old_level);

			fclose($fp);
		}

		return trim($data);
	}
	// END fetch_url


	// --------------------------------------------------------------------

	/**
	 * Write File
	 *
	 * @access	public
	 * @param	$file	Full location of final file
	 * @param	$data	Data to put into file
	 * @return	bool
	 */

	public function write_file($file, $data)
	{
		$temp_file = $file.'.tmp';

		if ( ! file_exists($temp_file))
		{
			// Remove old cache file, prevents rename problem on Windows
			// http://bugs.php.net/bug.php?id=44805

			@unlink($file);

			if (file_exists($file))
			{
				$this->errors[] = "Unable to Delete Old Cache File: ".$file;
				return false;
			}

			if ( ! $fp = @fopen($temp_file, 'wb'))
			{
				$this->errors[] = "Unable to Write Temporary Cache File: ".$temp_file;
				return false;
			}

			if ( ! flock($fp, LOCK_EX | LOCK_NB))
			{
				$this->errors[] = "Locking Error when Writing Cache File";
				return false;
			}

			fwrite($fp, $data);
			flock($fp, LOCK_UN);
			fclose($fp);

			// Write, then rename...
			@rename($temp_file, $file);

			// Double check permissions
			@chmod($file, 0777);

			// Just in case the rename did not work
			@unlink($temp_file);
		}

		return true;
	}
	// END write_file()


	// --------------------------------------------------------------------

	/**
	 * Check that File is Really Writable, Even on Windows
	 *
	 * is_writable() returns true on Windows servers when you really cannot write to the file
	 * as the OS reports to PHP as false only if the read-only attribute is marked.  Ugh!
	 *
	 * Oh, and there is some silly thing with
	 *
	 * @access	public
	 * @param	string		$path	- Path to be written to.
	 * @param	bool		$remove	- If writing a file, remove it after testing?
	 * @return	bool
	 */

	public static function is_really_writable($file, $remove = false)
	{
		// is_writable() returns true on Windows servers
		// when you really can't write to the file
		// as the OS reports to PHP as false only if the
		// read-only attribute is marked.  Ugh?

		if (substr($file, -1) == '/' OR is_dir($file))
		{
			return self::is_really_writable(rtrim($file, '/').'/'.uniqid(mt_rand()), true);
		}

		if (($fp = @fopen($file, 'ab')) === false)
		{
			return false;
		}
		else
		{
			if ($remove === true)
			{
				@unlink($file);
			}

			fclose($fp);
			return true;
		}
	}
	// END is_really_writable()


	// --------------------------------------------------------------------

	/**
	 *	Check Captcha
	 *
	 *	If Captcha is required by a module, we simply do all the work
	 *
	 *	@access		public
	 *	@return		bool
	 */

	public function check_captcha()
	{
		if ( ee()->config->item('captcha_require_members') == 'y'  OR
			(ee()->config->item('captcha_require_members') == 'n' AND
			 ee()->session->userdata['member_id'] == 0))
		{
			if ( ! ee()->input->post('captcha'))
			{
				return false;
			}
			else
			{
				ee()->db->where('word', ee()->input->post('captcha', true));
				ee()->db->where('ip_address', ee()->input->ip_address());
				ee()->db->where('date > ', '(UNIX_TIMESTAMP()-7200)', false);

				if ( ! ee()->db->count_all_results('captcha'))
				{
					return false;
				}

				ee()->db->where('word', ee()->input->post('captcha', true));
				ee()->db->where('ip_address', ee()->input->ip_address());
				ee()->db->where('date < ', '(UNIX_TIMESTAMP()-7200)', false);

				ee()->db->delete('captcha');
			}
		}

		return true;
	}
	// END check_captcha()


	// --------------------------------------------------------------------

	/**
	 * Global Error Message Routine
	 *
	 * @access	public
	 * @param	mixed	$message	error string or array of error strings
	 * @param	bool	$restore	optional restore XID on error
	 * @return	mixed				void if not unit test
	 */

	public function show_error($message = '', $restore = true)
	{
		if ($this->unit_test_mode)
		{
			return $this->test_method(
				__FUNCTION__,
				'show_error',
				array($message)
			);
		}
		//EL is wanting to deprecate output->show_user_error for CP
		//removed deprecation in EE 2.7, but its coming back i suppose
		else if (REQ == 'CP')
		{
			return show_error($message);
		}
		else
		{
			$type = ( ! empty($_POST)) ? 'submission' : 'general';

			return ee()->output->show_user_error($type, $message);
		}
	}
	// END show_error()


	// --------------------------------------------------------------------

	/**
	 *	Check if Submitted String is a Yes value
	 *
	 *	If the value is 'y', 'yes', 'true', or 'on', then returns true, otherwise false
	 *
	 *	@access		public
	 *	@param		string
	 *	@return		bool
	 */

	function check_yes($which)
	{
		if (is_string($which))
		{
			$which = strtolower(trim($which));
		}

		return in_array($which, array('yes', 'y', 'true', 'on'), true);
	}
	// END check_yes()


	// --------------------------------------------------------------------

	/**
	 *	Check if Submitted String is a No value
	 *
	 *	If the value is 'n', 'no', 'false', or 'off', then returns true, otherwise false
	 *
	 *	@access		public
	 *	@param		string
	 *	@return		bool
	 */

	function check_no($which)
	{
		if (is_string($which))
		{
			$which = strtolower(trim($which));
		}

		return in_array($which, array('no', 'n', 'false', 'off'), true);
	}
	// END check_no()


	// --------------------------------------------------------------------

	/**
	 *	Pagination for all versions front-end and back
	 *
	 *	* = optional
	 *	$input_data = array(
	 *		'sql'					=> '',
	 *		'total_results'			=> '',
	 *		*'url_suffix' 			=> '',
	 *		'tagdata'				=> ee()->TMPL->tagdata,
	 *		'limit'					=> '',
	 *		*'offset'				=> ee()->TMPL->fetch_param('offset'),
	 *		*'query_string_segment'	=> 'P',
	 *		'uri_string'			=> ee()->uri->uri_string,
	 *		*'current_page'			=> 0
	 *		*'pagination_config'	=> array()
	 *	);
	 *
	 *	@access		public
	 *	@param		array
	 *	@return		array
	 */

	public function universal_pagination($input_data)
	{
		// -------------------------------------
		//	prep input data
		// -------------------------------------

		//set defaults for optional items
		$input_defaults	= array(
			'url_suffix' 			=> '',
			'query_string_segment' 	=> 'P',
			'offset'				=> 0,
			'pagination_page'		=> 0,
			'pagination_config'		=> array(),
			'sql'					=> '',
			'tagdata'				=> '',
			'uri_string'			=> '',
			'paginate_prefix'		=> '',
			'prefix'				=> '',
			'total_results'			=> 0,
			'request'				=> REQ,
			'auto_paginate'			=> false
		);

		//array2 overwrites any duplicate key from array1
		$input_data 					= array_merge($input_defaults, $input_data);

		// -------------------------------------
		//	using the prefix? well, lets use it like the old. Stupid legacy
		// -------------------------------------

		if (trim($input_data['prefix']) !== '')
		{
			//allowing ':' in a prefix
			if (substr($input_data['prefix'], -1, 1) !== ':')
			{
				$input_data['prefix'] = rtrim($input_data['prefix'], '_') . '_';
			}

			$input_data['paginate_prefix'] = $input_data['prefix'];
		}

		//using query strings?
		//technically, ACT is the same here, but ACT is not for templates :p
		$use_query_strings 				= (
			REQ == 'CP' OR
			$input_data['request'] == 'CP' OR
			ee()->config->item('enable_query_strings')
		);

		//make sure there is are surrounding slashes.
		$input_data['uri_string']		= '/' . trim($input_data['uri_string'], '/') . '/';

		//shortcuts
		$config							= $input_data['pagination_config'];
		$p								= $input_data['query_string_segment'];
		$config['query_string_segment'] = $input_data['query_string_segment'];
		$config['page_query_string']	= $use_query_strings;

		//need the prefix so our segments are like /segment/segment/P10
		//instead of like /segment/segment/10
		//this only works in EE 2.x because CI 1.x didn't have the prefix
		//a hack later in the code makes this work for EE 1.x
		if (REQ == 'PAGE')
		{
			$config['prefix'] = $config['query_string_segment'];
		}

		ee()->load->helper('string');

		//current page
		if ( ! $use_query_strings AND preg_match("/$p(\d+)/s", $input_data['uri_string'], $match) )
		{
			if ( $input_data['pagination_page'] == 0 AND is_numeric($match[1]) )
			{
				$input_data['pagination_page'] 	= $match[1];

				//remove page from uri string, query_string, and uri_segments
				$input_data['uri_string'] 		= reduce_double_slashes(
					str_replace($p . $match[1] , '', $input_data['uri_string'] )
				);
			}
		}
		else if ( $use_query_strings === false)
		{
			if ( ! is_numeric($input_data['pagination_page']) )
			{
				$input_data['pagination_page'] = 0;
			}
		}
		else if ( ! in_array(
			ee()->input->get_post($input_data['query_string_segment']),
			array(false, '')
		))
		{
			$input_data['pagination_page'] = ee()->input->get_post($input_data['query_string_segment']);
		}

		// --------------------------------------------
		//  Automatic Total Results
		// --------------------------------------------

		if ( empty($input_data['total_results']) AND
			 ! empty($input_data['sql'])
		)
		{
			$query = ee()->db->query(
				preg_replace(
					"/SELECT(.*?)\s+FROM\s+/is",
					'SELECT COUNT(*) AS count FROM ',
					$input_data['sql'],
					1
				)
			);

			$input_data['total_results'] = $query->row('count');
		}

		//this prevents the CI pagination class from
		//trying to find the number itself...
		$config['uri_segment'] = 0;

		// -------------------------------------
		//	prep return data
		// -------------------------------------

		$return_data 	= array(
			'paginate'				=> false,
			'paginate_tagpair_data'	=> '',
			'current_page'			=> 0,
			'total_pages'			=> 0,
			'total_results'			=> $input_data['total_results'],
			'page_count'			=> '',
			'pagination_links'		=> '',
			'pagination_array'		=> '', //2.3.0+
			'base_url'				=> '',
			'page_next'				=> '',
			'page_previous'			=> '',
			'pagination_page'		=> $input_data['pagination_page'],
			'tagdata'				=> $input_data['tagdata'],
			'sql'					=> $input_data['sql'],
		);

		// -------------------------------------
		//	Begin pagination check
		// -------------------------------------

		if (REQ == 'CP' OR
			$input_data['request'] == 'CP' OR
			(
				strpos(
					$return_data['tagdata'],
					LD . $input_data['paginate_prefix'] . 'paginate'
				) !== false
				OR
				strpos(
					$return_data['tagdata'],
					LD . 'paginate'
				) !== false
			)
		)
		{
			$return_data['paginate'] = true;

			// -------------------------------------
			//	If we have prefixed pagination tags,
			//	lets do those first
			// -------------------------------------

			if ($input_data['paginate_prefix'] != '' AND preg_match(
					"/" . LD . $input_data['paginate_prefix'] . "paginate" . RD .
						"(.+?)" .
					LD . preg_quote('/', '/') .
						$input_data['paginate_prefix'] . "paginate" .
					RD . "/s",
					$return_data['tagdata'],
					$match
				))
			{
				$return_data['paginate_tagpair_data']	= $match[1];
				$return_data['tagdata'] 				= str_replace(
					$match[0],
					'',
					$return_data['tagdata']
				);
			}
			//else lets check for normal pagination tags
			else if (preg_match(
					"/" . LD . "paginate" . RD .
						"(.+?)" .
					LD . preg_quote('/', '/') . "paginate" . RD . "/s",
					$return_data['tagdata'],
					$match
				))
			{
				$return_data['paginate_tagpair_data']	= $match[1];
				$return_data['tagdata'] 				= str_replace(
					$match[0],
					'',
					$return_data['tagdata']
				);
			}

			// ----------------------------------------
			//  Calculate total number of pages
			// ----------------------------------------

			$return_data['current_page'] 	= floor(
				$input_data['pagination_page'] / $input_data['limit']
			) + 1;

			$return_data['total_pages']		= ceil(
				($input_data['total_results'] - $input_data['offset']) / $input_data['limit']
			);

			$return_data['page_count'] 		= lang('page') 		. ' ' .
											  $return_data['current_page'] 	. ' ' .
											  lang('of') 		. ' ' .
											  $return_data['total_pages'];

			// ----------------------------------------
			//  Do we need pagination?
			// ----------------------------------------

			if ( ($input_data['total_results'] - $input_data['offset']) > $input_data['limit'] )
			{
				if ( ! isset( $config['base_url'] )  )
				{
					$config['base_url']			= ee()->functions->create_url(
						$input_data['uri_string'] . $input_data['url_suffix'],
						false,
						0
					);
				}

				$config['total_rows'] 	= ($input_data['total_results'] - $input_data['offset']);
				$config['per_page']		= $input_data['limit'];
				$config['cur_page']		= $input_data['pagination_page'];
				$config['first_link'] 	= lang('pag_first_link');
				$config['last_link'] 	= lang('pag_last_link');

				ee()->load->library('pagination');

				ee()->pagination->initialize($config);

				$return_data['pagination_links'] = ee()->pagination->create_links();

				//this has to be reset for some stupid reason or the links
				//will always think they are on page one. Wat.
				ee()->pagination->initialize($config);
				$return_data['pagination_array'] = ee()->pagination->create_link_array();

				$return_data['base_url'] = ee()->pagination->base_url;

				// ----------------------------------------
				//  Prepare next_page and previous_page variables
				// ----------------------------------------

				//next page?
				if ( (($return_data['total_pages'] * $input_data['limit']) - $input_data['limit']) >
					 $return_data['pagination_page'])
				{
					$return_data['page_next'] = $return_data['base_url'] .
						($use_query_strings ? '' : $p) .
						($input_data['pagination_page'] + $input_data['limit']) . '/';
				}

				//previous page?
				if (($return_data['pagination_page'] - $input_data['limit'] ) >= 0)
				{
					$return_data['page_previous'] = $return_data['base_url'] .
						($use_query_strings ? '' : $p) .
						($input_data['pagination_page'] - $input_data['limit']) . '/';
				}
			}
		}

		//move current page to offset
		//$return_data['current_page'] += $input_data['offset'];

		//add limit to passed in sql
		$return_data['sql'] .= 	' LIMIT ' .
			($return_data['pagination_page'] + $input_data['offset']) .
			', ' . $input_data['limit'];

		//if we are automatically making magic, lets add all of the class vars
		if ($input_data['auto_paginate'] === true)
		{
			$this->auto_paginate	= true;
			$this->paginate			= $return_data['paginate'];
			$this->page_next		= $return_data['page_next'];
			$this->page_previous	= $return_data['page_previous'];
			$this->p_page			= $return_data['pagination_page'];
			$this->current_page  	= $return_data['current_page'];
			$this->pagination_links	= $return_data['pagination_links'];
			$this->pagination_array	= $return_data['pagination_array'];
			$this->basepath			= $return_data['base_url'];
			$this->total_pages		= $return_data['total_pages'];
			$this->paginate_data	= $return_data['paginate_tagpair_data'];
			$this->page_count		= $return_data['page_count'];
			//ee()->TMPL->tagdata	= $return_data['tagdata'];
		}

		return $return_data;
	}
	//	End universal_pagination


	// --------------------------------------------------------------------

	/**
	 * Universal Parse Pagination
	 *
	 * This creates a new XID hash in the DB for usage.
	 *
	 * @access	public
	 * @param 	array
	 * @return	tagdata
	 */

	public function parse_pagination($options = array())
	{
		// -------------------------------------
		//	prep input data
		// -------------------------------------

		//set defaults for optional items
		$defaults	= array(
			'prefix'			=> '',
			'tagdata'			=> ((isset(ee()->TMPL) and is_object(ee()->TMPL)) ?
									ee()->TMPL->tagdata : ''),
			'paginate'			=> false,
			'page_next'			=> '',
			'page_previous'		=> '',
			'p_page'			=> 0,
			'current_page'		=> 0,
			'pagination_links'	=> '',
			'pagination_array'	=> '',
			'basepath'			=> '',
			'total_pages'		=> '',
			'paginate_data'		=> '',
			'page_count'		=> '',
			'auto_paginate'		=> $this->auto_paginate
		);

		//array2 overwrites any duplicate key from array1
		$options = array_merge($defaults, $options);

		// -------------------------------------
		//	auto paginate?
		// -------------------------------------

		if ($options['auto_paginate'])
		{
			$options = array_merge($options, array(
				'paginate'			=> $this->paginate,
				'page_next'			=> $this->page_next,
				'page_previous'		=> $this->page_previous,
				'p_page'			=> $this->p_page,
				'current_page'		=> $this->current_page,
				'pagination_links'	=> $this->pagination_links,
				'pagination_array'	=> $this->pagination_array,
				'basepath'			=> $this->basepath,
				'total_pages'		=> $this->total_pages,
				'paginate_data'		=> $this->paginate_data,
				'page_count'		=> $this->page_count,
			));
		}

		// -------------------------------------
		//	prefixed items?
		// -------------------------------------

		$prefix = '';

		if (trim($options['prefix']) != '')
		{
			//allowing ':' in a prefix
			if (substr($options['prefix'], -1, 1) !== ':')
			{
				$options['prefix'] = rtrim($options['prefix'], '_') . '_';
			}

			$prefix = $options['prefix'];
		}

		$tag_paginate			= $prefix . 'paginate';
		$tag_pagination_links	= $prefix . 'pagination_links';
		$tag_current_page		= $prefix . 'current_page';
		$tag_total_pages		= $prefix . 'total_pages';
		$tag_page_count			= $prefix . 'page_count';
		$tag_previous_page		= $prefix . 'previous_page';
		$tag_next_page			= $prefix . 'next_page';
		$tag_auto_path			= $prefix . 'auto_path';
		$tag_path				= $prefix . 'path';

		// -------------------------------------
		//	TO VARIABLES!
		// -------------------------------------

		extract($options);

		// ----------------------------------------
		//	no paginate? :(
		// ----------------------------------------

		if ( $paginate === false )
		{
			$tagdata = ee()->functions->prep_conditionals(
				$tagdata,
				array($tag_paginate => false)
			);

			return ee()->TMPL->parse_variables(
				$tagdata,
				array(array($tag_paginate => array()))
			);
		}

		// -------------------------------------
		//	replace {if (prefix_)paginate} blocks
		// -------------------------------------

		$tagdata = ee()->functions->prep_conditionals(
			$tagdata,
			array($tag_paginate => true)
		);

		// -------------------------------------
		//	count and link conditionals
		// -------------------------------------

		$pagination_items = array(
			$tag_pagination_links	=> $pagination_links,
			$tag_current_page		=> $current_page,
			$tag_total_pages		=> $total_pages,
			$tag_page_count			=> $page_count
		);

		// -------------------------------------
		//	ee 2.3 pagination array?
		// -------------------------------------

		if ( ! empty($pagination_array))
		{

			//remove first or last page links where appropriate
			if ($current_page == 1)
			{
				$pagination_array['first_page'] = array();
			}

			if ($total_pages == $current_page)
			{
				$pagination_array['last_page'] = array();
			}

			//if we don't do this first, parse_pagination
			//will attempt to convert the array to a string to compare
			$paginate_data	= ee()->functions->prep_conditionals(
				$paginate_data,
				array(
					$tag_pagination_links => true
				)
			);

			// Check to see if pagination_links is being used as a single
			// variable or as a variable pair
			if (preg_match_all(
					"/" . LD . $tag_pagination_links . RD .
						"(.+?)" .
					LD . '\/' . $tag_pagination_links . RD . "/s",
					$paginate_data,
					$matches
				))
			{
				// Parse current_page and total_pages
				$paginate_data = ee()->TMPL->parse_variables(
					$paginate_data,
					array(array($tag_pagination_links => array($pagination_array)))
				);
			}
		}
		//need blanks if there is no data
		//this helps tag pairs return blank
		else
		{
			$pagination_array = array(
				'first_page'	=> array(),
				'previous_page'	=> array(),
				'page'			=> array(),
				'next_page'		=> array(),
				'last_page'		=> array(),
			);

			//if we don't do this first, parse_pagination
			//will attempt to convert the array to a string to compare
			$paginate_data	= ee()->functions->prep_conditionals(
				$paginate_data,
				array(
					$tag_pagination_links => false
				)
			);

			$paginate_data = ee()->TMPL->parse_variables(
				$paginate_data,
				array(array($tag_pagination_links => array($pagination_array)))
			);
		}


		// -------------------------------------
		//	parse everything left
		// -------------------------------------

		$paginate_data	= ee()->functions->prep_conditionals(
			$paginate_data,
			$pagination_items
		);

		// -------------------------------------
		//	if this is EE 2.3+, we need to parse the pagination
		//	tag pair before we str_replace
		// -------------------------------------

		foreach ( $pagination_items as $key => $val )
		{
			$paginate_data	= str_replace(
				LD . $key . RD,
				$val,
				$paginate_data
			);
		}

		// ----------------------------------------
		//	Previous link
		// ----------------------------------------

		if (preg_match(
				"/" . LD . "if " . $tag_previous_page . RD .
					"(.+?)" .
				 LD . preg_quote('/', '/') . "if" . RD . "/s",
				 $paginate_data,
				 $match
			))
		{
			if ($page_previous == '')
			{
				 $paginate_data = preg_replace(
					"/" . LD . "if " . $tag_previous_page . RD .
						".+?" .
					LD . preg_quote('/', '/') . "if" . RD . "/s",
					'',
					$paginate_data
				);
			}
			else
			{
				$match['1'] 	= preg_replace(
					"/" . LD . $tag_path . '.*?' . RD . "/",
					$page_previous,
					$match['1']
				);

				$match['1'] 	= preg_replace(
					"/" . LD . $tag_auto_path . RD . "/",
					$page_previous,
					$match['1']
				);

				$paginate_data 	= str_replace(
					$match['0'],
					$match['1'],
					$paginate_data
				);
			}
		}

		// ----------------------------------------
		//	Next link
		// ----------------------------------------

		if (preg_match(
				"/" . LD . "if " . $tag_next_page . RD .
					"(.+?)" .
				LD . preg_quote('/', '/') . "if" . RD . "/s",
				$paginate_data,
				$match
			))
		{
			if ($page_next == '')
			{
				$paginate_data = preg_replace(
					"/" . LD . "if " . $tag_next_page . RD .
						".+?" .
					LD . preg_quote('/', '/') . "if" . RD . "/s",
					'',
					$paginate_data
				);
			}
			else
			{
				$match['1'] 	= preg_replace(
					"/" . LD . $tag_path . '.*?' . RD . "/",
					$page_next,
					$match['1']
				);

				$match['1'] 	= preg_replace(
					"/" . LD . $tag_auto_path . RD . "/",
					$page_next,
					$match['1']
				);

				$paginate_data 	= str_replace(
					$match['0'],
					$match['1'],
					$paginate_data
				);
			}
		}

		// ----------------------------------------
		//	Add pagination
		// ----------------------------------------

		if ( ee()->TMPL->fetch_param('paginate') == 'both' )
		{
			$tagdata	= $paginate_data . $tagdata . $paginate_data;
		}
		elseif ( ee()->TMPL->fetch_param('paginate') == 'top' )
		{
			$tagdata	= $paginate_data . $tagdata;
		}
		else
		{
			$tagdata	= $tagdata . $paginate_data;
		}

		// ----------------------------------------
		//	Return
		// ----------------------------------------

		return $tagdata;
	}
	//END parse_pagination


	// --------------------------------------------------------------------

	/**
	 * pagination_prefix_replace
	 * gets the tag group id from a number of places and sets it to the
	 * instance default param
	 *
	 * @access 	public
	 * @param 	string 	prefix for tag
	 * @param 	string 	tagdata
	 * @param 	bool 	reverse, are we removing the preixes we did before?
	 * @return	string 	tag data with prefix changed out
	 */

	public function pagination_prefix_replace($prefix = '', $tagdata = '', $reverse = false)
	{
		if ($prefix == '')
		{
			return $tagdata;
		}

		//allowing ':' in a prefix
		if (substr($prefix, -1, 1) !== ':')
		{
			$prefix = rtrim($prefix, '_') . '_';
		}

		//if there is nothing prefixed, we don't want to do anything datastardly
		if ( ! $reverse AND
			strpos($tagdata, LD.$prefix . 'paginate'.RD) === false)
		{
			return $tagdata;
		}

		// -------------------------------------
		//	Fix subtag previous and next pages
		//	nested in addon_pagination_links
		// -------------------------------------

		$tpl = $prefix . 'pagination_links';

		if (preg_match(
				"/" . LD . $tpl . RD .
					"(.*)?" .
				LD . preg_quote('/', '/') . $tpl . RD . "/ims",
				$tagdata,
				$matches
			)
		)
		{
			$fix_pp_np = preg_replace(
				array(
					"/\bnext_page\b/is",
					"/\bprevious_page\b/is"
				),
				array(
					$prefix . "next_page",
					$prefix . "previous_page"
				),
				$matches[1]
			);

			$tagdata = str_replace($matches[1], $fix_pp_np, $tagdata);
		}

		// -------------------------------------
		//	prefix and replace
		// -------------------------------------

		$hash 	= 'e2c518d61874f2d4a14bbfb9087a7c2d';

		$items 	= array(
			'paginate',
			'pagination_links',
			'current_page',
			'total_pages',
			'page_count',
			'previous_page',
			'next_page',
			'auto_path',
			'path'
		);

		$find 			= array();
		$hash_replace 	= array();
		$prefix_replace = array();

		$length = count($items);

		foreach ($items as $key => $item)
		{
			$nkey = $key + $length;

			//this is terse, but it ensures that we
			//find any an all tag pairs if they occur
			$find[$key] 			= LD . $item . RD;
			$find[$nkey] 			= LD . '/' .  $item . RD;
			$hash_replace[$key] 	= LD . $hash . $item . RD;
			$hash_replace[$nkey] 	= LD . '/' .  $hash . $item . RD;
			$prefix_replace[$key] 	= LD . $prefix . $item . RD;
			$prefix_replace[$nkey] 	= LD . '/' .  $prefix . $item . RD;
		}

		//prefix standard and replace prefixs
		if ( ! $reverse)
		{
			$tagdata = str_replace($find, $hash_replace, $tagdata);
			$tagdata = str_replace($prefix_replace, $find, $tagdata);
		}
		//we are on the return, fix the hashed ones
		else
		{
			$tagdata = str_replace($hash_replace, $find, $tagdata);
		}

		return $tagdata;
	}
	//END pagination_prefix_replace


	// --------------------------------------------------------------------

	/**
	 * Get CSRF Token (EE 2.8+ only)
	 *
	 * @access	public
	 * @return	string	40 char CSRF hash token
	 */

	public function get_csrf_token()
	{
		if (defined('CSRF_TOKEN'))
		{
			return CSRF_TOKEN;
		}
		//csrf needs a session to work.
		//this is generally only ever hit on ACT
		//or m=Javascript
		else if ($this->session_obj_set())
		{
			ee()->load->library('csrf');
			return ee()->csrf->get_user_token();
		}
		//this means we are in sessions_start or _end so hopefully
		//whatever happens here gets parsed out later. It's this or a
		//blank string which is just as likely to fail if not parsed.
		else
		{
			return '{csrf_token}';
		}
	}
	//END get_csrf_token


	// --------------------------------------------------------------------

	/**
	 * Implodes an Array and Hashes It
	 *
	 * @access	public
	 * @return	string
	 */

	public function _imploder($arguments)
	{
		return md5(serialize($arguments));
	}
	// END


	// --------------------------------------------------------------------

	/**
	 * Prepare keyed result
	 *
	 * Take a query object and return an associative array. If $val is empty,
	 * the entire row per record will become the value attached to the indicated key.
	 *
	 * For example, if you do a query on exp_channel_titles and exp_channel_data
	 * you can use this to quickly create an associative array of channel entry
	 * data keyed to entry id.
	 *
	 * @access	public
	 * @return	mixed
	 */

	public function prepare_keyed_result($query, $key = '', $val = '')
	{
		if ( ! is_object( $query )  OR $key == '' ){ return false; }

		// --------------------------------------------
		//  Loop through query
		// --------------------------------------------

		$data	= array();

		foreach ( $query->result_array() as $row )
		{
			if ( isset( $row[$key] ) === false ){ continue; }

			$data[ $row[$key] ]	= ( $val != '' AND isset($row[$val])) ? $row[$val]: $row;
		}

		return ( empty( $data ) ) ? false : $data;
	}
	// END prepare_keyed_result


	// --------------------------------------------------------------------

	/**
	 * returns the truthy or last arg i
	 *
	 * @access	public
	 * @param	array 	args to be checked against
	 * @param	mixed	bool or array of items to check against
	 * @return	mixed
	 */
	public function either_or_base($args = array(), $test = false)
	{
		foreach ($args as $arg)
		{
			//do we have an array of nots?
			//if so, we need to be test for type
			if ( is_array($test))
			{
				if ( ! in_array($arg, $test, true) ){ return $arg; }
			}
			//is it implicit false?
			elseif ($test)
			{
				if ($arg !== false){ return $arg; }
			}
			//else just test for falsy
			else
			{
				if ($arg){ return $arg; }
			}
		}

		return end($args);
	}
	//END either_or_base


	// --------------------------------------------------------------------

	/**
	 * returns the truthy or last arg
	 *
	 * @access	public
	 * @param	mixed	any number of arguments consisting of variables to be returned false
	 * @return	mixed
	 */

	public function either_or()
	{
		$args = func_get_args();

		return $this->either_or_base($args);
	}
	//END either_or


	// --------------------------------------------------------------------

	/**
	 * returns the non exact bool false or last arg
	 *
	 * @access	public
	 * @param	mixed	any number of arguments consisting of variables to be returned false
	 * @return	mixed
	 */

	public function either_or_strict()
	{
		$args = func_get_args();

		return $this->either_or_base($args, true);
	}
	// END either_or_strict


	//---------------------------------------------------------------------

	/**
	 * add_right_link
	 * @access	public
	 * @param	string	$text	string of link name
	 * @param	string	$link	html link for right link
	 * @return	void
	 */

	public function add_right_link($text, $link)
	{
		//no funny business
		if (REQ != 'CP') return;

		$this->right_links[$text] = $link;
	}
	//end add_right_link


	// --------------------------------------------------------------------

	/**
	 * do we have any hooks?
	 *
	 *
	 * @access	public
	 * @return	bool	Whether the extensions are allowed
	 */

	public function has_hooks()
	{
		//is it there? is it array? is it empty?
		//Such are life's unanswerable questions, until now.
		if ( ! $this->updater() OR
			((! isset($this->updater()->hooks)		OR
				! is_array($this->updater->hooks))	AND
			(! isset($this->hooks)					OR
				! is_array($this->hooks)))			OR
			(empty($this->hooks) AND empty($this->updater->hooks))
		)
		{
			return false;
		}

		return true;
	}
	//end has hooks


	// --------------------------------------------------------------------

	/**
	 * loads updater object and sets it to $this->upd and returns it
	 *
	 *
	 * @access	public
	 * @return	obj		updater object for module
	 */

	public function updater()
	{
		if ( ! is_object($this->updater) )
		{
			$class		= $this->class_name . '_upd';

			$update_file 	= $this->addon_path .
								'upd.' . $this->lower_name . '.php';

			if (! class_exists($class))
			{
				if (is_file($update_file))
				{
					require_once $update_file;
				}
				else
				{
					return false;
				}
			}

			$this->updater	= new $class();
		}

		return $this->updater;
	}
	//end updater


	// --------------------------------------------------------------------

	/**
	 * Checks to see if extensions are enabled for this module
	 *
	 *
	 * @access	public
	 * @param	bool	match exact number of hooks
	 * @return	bool	Whether the extensions are enabled if need be
	 */

	public function extensions_enabled($check_all_enabled = false)
	{
		if ( ! $this->has_hooks() ) return true;
		//we don't want to end on this as it would confuse users
		if ( $this->updater() === false )	return true;

		$num_enabled = 0;

		foreach ($this->updater()->hooks as $hook_data)
		{
			if (isset(ee()->extensions->extensions[$hook_data['hook']]))
			{
				foreach(ee()->extensions->extensions[$hook_data['hook']] as $priority => $hook_array)
				{
					if (isset($hook_array[$this->extension_name]))
					{
						$num_enabled++;
					}
				}
			}
		}

		//we arent going to look for all of the hooks
		//because some could be turned off manually for testing
		return (($check_all_enabled) ?
					($num_enabled == count($this->updater()->hooks) ) :
					($num_enabled > 0) );
	}
	//END extensions_enabled


	// --------------------------------------------------------------------

	/**
	 * AJAX Request
	 *
	 * Tests via headers or GET/POST parameter whether the incoming
	 * request is AJAX in nature
	 * Useful when we want to change the output of a method.
	 *
	 * The major difference here to the build in EE methods is
	 * the TMPL param to override ajax detection.
	 *
	 * @access public
	 * @return boolean
	 */

	public function is_ajax_request()
	{
		// --------------------------------------------
		//  Headers indicate this is an AJAX Request
		//	- They can disable via a parameter or GET/POST
		//	- If not, true
		// --------------------------------------------

		if (ee()->input->is_ajax_request())
		{
			// Check for parameter
			if (isset(ee()->TMPL) AND is_object(ee()->TMPL))
			{
				if (ee()->TMPL->fetch_param('ajax_request') !== false &&
					$this->check_no(ee()->TMPL->fetch_param('ajax_request')))
				{
					return false;
				}
			}

			// Check for GET/POST variable
			if (ee()->input->get_post('ajax_request') !== false &&
				$this->check_no(ee()->input->get_post('ajax_request')))
			{
				return false;
			}

			// Not disabled
			return true;
		}

		// --------------------------------------------
		//  Headers do NOT indicate it is an AJAX Request
		//	- They can force with a parameter OR GET/POST variable
		//	- If not, false
		// --------------------------------------------

		if (isset(ee()->TMPL) AND is_object(ee()->TMPL))
		{
			if ($this->check_yes(ee()->TMPL->fetch_param('ajax_request')))
			{
				return true;
			}
		}

		if ($this->check_yes(ee()->input->get_post('ajax_request')))
		{
			return true;
		}

		return false;
	}
	// END is_ajax_request()


	// --------------------------------------------------------------------

	/**
	 * Send AJAX response
	 *
	 * Outputs and exit either an HTML string or a
	 * JSON array with the Profile disabled and correct
	 * headers sent.
	 *
	 * @access	public
	 * @param	string|array	String is sent as HTML, Array is sent as JSON
	 * @param	bool			Is this an error message?
	 * @param 	bool 			bust cache for JSON?
	 * @return	void
	 */

	public function send_ajax_response($msg, $error = false, $cache_bust = true)
	{
		ee()->output->enable_profiler(false);

		$send_headers = (ee()->config->item('send_headers') == 'y');

		if ($error === true)
		{
			ee()->output->set_status_header(500);
		}

		if ($send_headers)
		{
			if ($cache_bust)
			{
				//cache bust
				@header('Cache-Control: no-cache, must-revalidate');
				@header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			}

			if (is_array($msg) || is_object($msg))
			{
				@header('Content-Type: application/json; charset=UTF-8');
			}
			else
			{
				@header('Content-Type: text/html; charset=UTF-8');
			}
		}

		exit(json_encode($msg));
	}
	//END send_ajax_response()


	// --------------------------------------------------------------------

	/**
	 *	Validate Emails
	 *
	 *	Validates an array or parses a string of emails and then validates
	 *
	 *	@access		public
	 *	@param		string|array
	 *	@return		array  $vars - Contains two keys good/bad of,
	 *								what else, good and bad emails
	 */
	public function validate_emails($emails)
	{
		if ( is_string($emails))
		{
			// Remove all white space and replace with commas
			$email	= trim(
				preg_replace("/\s*(\S+)\s*/s", "\\1,", trim($emails)),
				','
			);

			// Remove duplicate commas
			$email	= str_replace(',,', ',', $email);

			// Explode and make unique
			$emails	= array_unique(explode(",", $email));
		}

		$vars['good']	= array();
		$vars['bad']	= array();

		foreach($emails as $addr)
		{
			if (preg_match('/<(.*)>/', $addr, $match))
			{
				$addr = $match[1];
			}

			if ((bool) filter_var($addr, FILTER_VALIDATE_EMAIL) === FALSE)
			{
				$vars['bad'][] = $addr;
				continue;
			}

			$vars['good'][] = $addr;
		}

		return $vars;
	}
	// END validate_emails();


	// --------------------------------------------------------------------

	/**
	 *	Get Action URL
	 *
	 * 	returns a full URL for an action
	 *
	 *	@access		public
	 *	@param		string method name
	 *	@return		string url for action
	 */

	public function get_action_url($method_name)
	{
		$action_q	= ee()->db->where(array(
			'class'		=> $this->class_name,
			'method'	=> $method_name
		))->get('actions');

		if ($action_q->num_rows() == 0)
		{
			return '';
		}

		$action_id = $action_q->row('action_id');

		//fix for things like transcribe
		ee()->functions->cached_index = array();

		return ee()->functions->fetch_site_index(0, 0) .
					QUERY_MARKER . 'ACT=' . $action_id;
	}
	//END get_action_url


	// --------------------------------------------------------------------

	/**
	 * is_positive_intlike
	 *
	 * return
	 *
	 * (is_positive_entlike would have taken forever)
	 *
	 * @access 	public
	 * @param 	mixed 	num 		number/int/string to check for numeric
	 * @param 	int 	threshold 	lowest number acceptable (default 1)
	 * @return  bool
	 */

	public function is_positive_intlike($num, $threshold = 1)
	{
		//without is_numeric, bools return positive
		//because preg_match auto converts to string
		return (
			is_numeric($num) AND
			preg_match("/^[0-9]+$/", $num) AND
			$num >= $threshold
		);
	}
	//END is_positive_intlike


	// --------------------------------------------------------------------

	/**
	 * get_post_or_zero
	 *
	 * @access	public
	 * @param 	string 	name of GET/POST var to check
	 * @return	int 	returns 0 if the get/post is not present or numeric or above 0
	 */

	public function get_post_or_zero($name)
	{
		$name = ee()->input->get_post($name);
		return ($this->is_positive_intlike($name) ? $name : 0);
	}
	//END get_post_or_zero


	// --------------------------------------------------------------------

	/**
	 * Install/Update Our Extension for Module
	 *
	 * Tells ExpressionEngine what extension hooks
	 * we wish to use for this module.
	 *
	 * @access	public
	 * @return	null
	 */

	public function update_extension_hooks()
	{
		// --------------------------------------------
		//  Determine Existing Methods
		// --------------------------------------------

		$exists	= array();

		$query = ee('Model')->get('Extension')
					->filter('class', $this->extension_name)
					->all();

		foreach ( $query AS $row )
		{
			$exists[] = $row->method;

			if ($this->settings == '' AND ! empty($row->settings))
			{
				$this->settings = $row->settings;
			}
		}

		if ((
				! is_array($this->hooks) OR
				count($this->hooks) == 0
			) &&
			empty($exists)
		)
		{
			return true;
		}

		// --------------------------------------------
		//  Extension Table Defaults
		// --------------------------------------------

		$this->extension_defaults = array(
			'class'			=> $this->extension_name,
			'settings'		=> '',
			'priority'		=> 10,
			'version'		=> $this->version,
			'enabled'		=> 'y'
		);

		// --------------------------------------------
		//  Find Missing and Insert
		// --------------------------------------------

		$current_methods = array();

		foreach($this->hooks as $data)
		{
			// Default exp_extension fields, overwrite with any from array
			$data = array_merge($this->extension_defaults, $data);

			$current_methods[] = $data['method'];

			$e = null;

			if ( ! in_array($data['method'], $exists))
			{
				// Every so often, EE can accidentally send empty
				// $settings argument to the constructor, so
				// our new hooks will not have any settings,
				// so we have to fix that here.

				if ($data['settings'] == '' OR
					$data['settings'] == 's:0:"";')
				{
					//its a string thats already serialized
					if (is_string($this->settings) &&
						preg_match('/^[a-z]{1}:[\d]{1}/ims', trim($this->settings)))
					{
						$data['settings'] = unserialize($this->settings);
					}
					//not a string or not serialized
					else
					{
						$data['settings'] = $this->settings;
					}
				}

				$e = ee('Model')->make('Extension');
			}
			else
			{
				unset($data['settings']);

				$e = ee('Model')->get('Extension')
						->filter('class', $data['class'])
						->filter('method', $data['method'])
						->first();
			}

			if ( ! empty($e))
			{
				foreach ($data as $key => $value)
				{
					$e->$key = $value;
				}

				$e->save();
			}
		}
		//END foreach($this->hooks as $data)

		// --------------------------------------------
		//  Remove Old Hooks
		// --------------------------------------------

		$old_hooks = array_diff($exists, $current_methods);

		if ( ! empty($old_hooks))
		{
			ee('Model')->get('Extension')
				->filter('method', 'IN', $old_hooks)
				->filter('class', $this->extension_name)
				->delete();
		}
		//END if ( ! empty($old_hooks))
	}
	// END update_extension_hooks()


	// --------------------------------------------------------------------

	/**
	 * Remove Extension Hooks
	 *
	 * Removes all of the extension hooks that will be called for this module
	 *
	 * @access	public
	 * @return	null
	 */

	public function remove_extension_hooks()
	{
		ee()->db
			->where('class', $this->extension_name)
			->delete('extensions');

		// --------------------------------------------
		//  Remove from $EE->extensions->extensions array
		// --------------------------------------------

		foreach(ee()->extensions->extensions as $hook => $calls)
		{
			foreach($calls as $priority => $class_data)
			{
				foreach($class_data as $class => $data)
				{
					if ($class == $this->class_name OR
						$class == $this->extension_name)
					{
						unset(
							ee()->extensions
								->extensions[$hook][$priority][$class]
						);
					}
				}
			}
		}
	}
	// END remove_extension_hooks


	// --------------------------------------------------------------------

	/**
	 *	Fetch Language File
	 *
	 *	Two known extensions sessions_end and sessions_start are
	 *	called prior to Language being instantiated, so we wrote
	 *	our own little method here that removes the
	 *	ee()->session->userdata check and still loads the
	 *	language file for the extension, if required...
	 *
	 *	With this method, we can add items to the lang files
	 *	without having to add to the ee()->lang->is_loaded
	 *	array and cause issues with foreign lang files that need to
	 *	be loaded _after_ sessions_end
	 *
	 * @access	public
	 * @param	string  $which	name of langauge file
	 * @param	boolean $object	session object
	 * @param	boolean $add_to_ee_lang	session object
	 * @return	null
	 */

	public function fetch_language_file($which = '', $object = false, $add_to_ee_lang = true)
	{
		if ($which == '')
		{
			return false;
		}

		if ( ! $object AND isset(ee()->session))
		{
			$object =& ee()->session;
		}

		if (is_object($object) AND
			strtolower(get_class($object)) == 'session' AND
			$object->userdata['language'] != '')
		{
			$user_lang = $object->userdata['language'];
		}
		else
		{
			if (ee()->input->cookie('language'))
			{
				$user_lang = ee()->input->cookie('language');
			}
			elseif (ee()->config->item('deft_lang') != '')
			{
				$user_lang = ee()->config->item('deft_lang');
			}
			else
			{
				$user_lang = 'english';
			}
		}

		//no BS
		$user_lang	= ee()->security->sanitize_filename($user_lang);
		$which		= ee()->security->sanitize_filename($which);

		if ( ! in_array($which, $this->is_loaded))
		{
			$options = array(
				$this->addon_path . 'language/'.$user_lang.'/lang.'.$which.'.php',
				$this->addon_path . 'language/'.$user_lang.'/'.$which.'_lang.php',
				$this->addon_path . 'language/english/lang.'.$which.'.php',
				$this->addon_path . 'language/english/'.$which.'_lang.php'
			);

			$success = false;

			foreach($options as $path)
			{
				if ( file_exists($path) AND include $path)
				{
					$success = true;
					break;
				}
			}

			if ($success == false)
			{
				return false;
			}

			if (isset($lang))
			{
				$this->is_loaded[] = $which;

				$this->language = array_merge(
					$this->language,
					$lang
				);

				if ($add_to_ee_lang AND isset(ee()->lang->language))
				{
					ee()->lang->language = array_merge(
						ee()->lang->language,
						$lang
					);
				}

				unset($lang);
			}
		}

		return true;
	}
	// END fetch_language_file


	// --------------------------------------------------------------------

	/**
	 * Line from lang file
	 *
	 * This is not meant to take over EE's lang line, but rather
	 * a way to load and use our own lang lines if EE's isn't available
	 * at the moment, like on some early hook.
	 *
	 * @access	public
	 * @param	string	$which	which lang line you want
	 * @param	string	$label	name of item this is a label for
	 * @return	string			lang line
	 */

	public function line($which = '', $label = '')
	{
		if ($which != '')
		{
			if ( ! isset($this->language[$which]) AND
				( ! function_exists('lang') OR lang($which) == $which)
			)
			{
				$line = $which;
			}
			else
			{
				$line = ( ! isset($this->language[$which])) ?
							lang($which) :
							$this->language[$which];
			}

			if ($label != '')
			{
				$line = '<label for="'.$label.'">'.$line."</label>";
			}

			return stripslashes($line);
		}
		else
		{
			return $which;
		}
	}
	// END Line


	// --------------------------------------------------------------------

	/**
	 * Setup Unit Test Mode
	 *
	 * @access	public
	 * @param	mixed	$test_mock	mock object to send functions to
	 * @return	void
	 */

	public function setup_unit_test_mode($test_mock = false, $do_actions = true)
	{
		$this->unit_test_mode = true;

		if (is_object($test_mock))
		{
			$this->test_mock = $test_mock;
		}
		else if (is_string($test_mock) && class_exists($test_mock))
		{
			$this->test_mock = new $test_mock();
		}
	}
	//END setup_unit_test_mode


	// --------------------------------------------------------------------

	/**
	 * Test Object
	 * Sets the test object with a dummy if not already set
	 *
	 * @access	protected
	 * @return	object		test_object instance
	 */

	protected function test_object()
	{
		if ( ! isset($this->test_mock) ||
			 ! is_object($this->test_mock))
		{
			$dummy_class_name = $this->class_name . '_aob_method_mock';

			if ( ! class_exists($dummy_class_name))
			{
				//dummy for testing if mocks aren't needed
				eval(
					'class ' . $dummy_class_name . '
					{
						//function catch all
						public function __call($method, $args)
						{
							return false;
						}
					}'
				);
			}

			$this->test_mock = new $dummy_class_name();
		}

		return $this->test_mock;
	}
	//END test_object


	// --------------------------------------------------------------------

	/**
	 * Fire Test Method
	 *
	 * @access	protected
	 * @param	string	$caller	calling function
	 * @param	string	$method method desired
	 * @param	array	$args   arguments for function (optional)
	 * @return	mixed			function result or array false
	 */
	protected function test_method($caller, $method, $args = array())
	{
		if (is_callable(array($this->test_object(), $method)))
		{
			$this->test_object()->_test_mode_caller = $caller;

			return call_user_func_array(array($this->test_object(), $method), $args);
		}

		return false;
	}
	//END test_method


	// --------------------------------------------------------------------

	/**
	 * Human time changes functions in EE 2.6
	 *
	 * @access	public
	 * @param	mixed	$time	time to set for human output
	 * @return	string			converted time
	 */
	public function human_time($time)
	{
		return ee()->localize->human_time($time);
	}
	//END human_time


	// --------------------------------------------------------------------

	/**
	 *  Fetch Date Params added due to depracation of Localize one with
	 *  no good reason given *shrug*
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public function fetch_date_params($datestr = '')
	{
		if ($datestr == '')
		{
			return;
		}

		if ( ! preg_match_all("/(%\S)/", $datestr, $matches))
		{
			return;
		}

		return $matches[1];
	}
	//END fetch_date_params


	// --------------------------------------------------------------------

	/**
	 * Convert timestamp legacy support
	 * EE 2.6 moves this to format_date
	 *
	 * @access	public
	 * @param	mixed	$time	timestamp to convert
	 * @return	string			converted timestamp
	 */

	public function convert_timestamp($format = '', $time = '', $localize = true)
	{
		$return_str = false;

		if ( ! is_array($format))
		{
			$return_str = true;
			$format = array($format);
		}

		foreach ($format as $var)
		{
			$out[] = ee()->localize->format_date($var, $time, $localize);
		}

		return ($return_str) ? array_pop($out) : $out;
	}
	//END convert_timestamp


	// --------------------------------------------------------------------

	/**
	 * Get Sites
	 *
	 * @access	public
	 * @return	array	site data
	 */

	protected function get_sites()
	{
		if ( ! empty($this->sites))
		{
			return $this->sites;
		}

		if (isset(ee()->session) AND
			is_object(ee()->session) AND
			isset(ee()->session->userdata['group_id']) AND
			ee()->session->userdata['group_id'] == 1 AND
			isset(ee()->session->userdata['assigned_sites']) AND
			is_array(ee()->session->userdata['assigned_sites']))
		{
			$this->sites = ee()->session->userdata['assigned_sites'];
			return $this->sites;
		}

		//--------------------------------------------
		// Perform the Actual Work
		//--------------------------------------------

		ee()->db
			->select('site_id, site_label')
			->from('exp_sites');

		if (ee()->config->item('multiple_sites_enabled') == 'y')
		{
			ee()->db->order_by('site_label');
		}
		else
		{
			ee()->db->where('site_id', 1);
		}

		$sites_query = ee()->db->get();

		//no need to check here, EE won't even start without
		//these present in the DB
		$this->sites = $this->prepare_keyed_result(
			$sites_query,
			'site_id',
			'site_label'
		);

		return $this->sites;
	}
	//END get_sites


	// --------------------------------------------------------------------

	/**
	 * Class Lib Loader
	 *
	 * Lib loader that cuts down on obnoxious lines like
	 * ee()->load->library('my_addon_name_class_name');
	 * ee()->my_addon_name_class_name->run_long_name();
	 * and turns it to $this->class_lib('lib_name')->method();
	 *
	 * This turns out visually as long as StaticC::autoLoadHelper()
	 * and lets us reuse more code because the class name is auto generated.
	 *
	 * This assumes the root addon name as a prefix and then the name
	 * of what the class is. E,g, Super_Search_notifications would be
	 * $this->lib('notifications')->method();
	 *
	 * @access	public
	 * @param	string $name	singular table name from full model name
	 * @param	boolean	$refresh	refresh cache?
	 * @return	object			model instance from EE instance
	 */

	public function lib($name, $refresh = false)
	{
		return $this->_lib_mod_loader(
			$name,
			array(
				'type'		=>'library',
				'refresh'	=> $refresh
			)
		);
	}
	//END lib


	// --------------------------------------------------------------------

	/**
	 * Model
	 *
	 * Legacy support for EE 2.x AddonBuilder model calling via
	 * Codeigniter
	 *
	 * @deprecated
	 * @access	public
	 * @param	string	$name	singular table name from full model name
	 * @return	object			model instance from EE instance
	 */

	public function model($name = '')
	{
		return $this->_lib_mod_loader($name, array('type' => 'model'));
	}
	//END model


	// --------------------------------------------------------------------

	/**
	 * Conveniance function for new EE 3 models ->make() method for inserts
	 * and using on model methods
	 *
	 * @access	public
	 * @param	string	$name	singular table name from full model name
	 * @return	object			model instance from EE instance
	 */

	public function make($name = '', array $data = array())
	{
		return $this->_lib_mod_loader(
			$name,
			array(
				'make_data'		=> $data,
				'type'			=> 'model',
				'make'			=> true,
				'refresh'		=> true
			)
		);
	}
	//END make


	// --------------------------------------------------------------------

	/**
	 * Conveniance function for new EE 3 models ->get() method for fetching
	 *
	 * @access	public
	 * @param	string	$name	singular table name from full model name
	 * @return	object			model instance from EE instance
	 */

	public function fetch($name = '', $default_ids = NULL)
	{
		return $this->_lib_mod_loader(
			$name,
			array(
				'default_ids'	=> $default_ids,
				'type'			=> 'model',
				'make'			=> false,
				'refresh'		=> true
			)
		);
	}
	//END fetch


	// --------------------------------------------------------------------

	/**
	 * Helper function for library and model loaders
	 *
	 * @access	public
	 * @param	string	$name		singular table name from full model name
	 * @param	string	$type		type of object for EE to load. model or library
	 * @param	boolean	$refresh	refresh cache?
	 * @param	boolean	$make		return an instance rather than query builder
	 * @return	object				model instance from EE instance
	 */

	protected function _lib_mod_loader($object_name, $options = array())
	{
		// -------------------------------------
		//	Default options
		// -------------------------------------

		$defaults = array(
			'type'			=> 'library',
			'refresh'		=> false,
			'make'			=> false,
			'default_ids'	=> null,
			'make_data'		=> array()
		);

		foreach ($defaults as $key => $value)
		{
			$$key = isset($options[$key]) ? $options[$key] : $value;
		}

		// -------------------------------------
		//	refresh
		// -------------------------------------

		if ( ! $refresh &&
			! empty($this->cache['lib_mod'][$type][$object_name])
		)
		{
			return $this->cache['lib_mod'][$type][$object_name];
		}

		$legacy_name = $this->lower_name . '_' . $object_name .
						($type == 'model' ? '_model' : '');

		// -------------------------------------
		//	legacy
		// -------------------------------------

		//if this is traditionally loaded
		//return global cache of it
		if (isset(ee()->$legacy_name))
		{
			return ee()->$legacy_name;
		}

		// -------------------------------------
		//	namespaced?
		// -------------------------------------

		$namespace	= $this->addon_info['namespace'];
		$uc_name	= ucfirst($object_name);

		$folder = ucfirst($type);

		$ns_class = $namespace . '\\' . $folder . '\\' . $uc_name;

		//EE 3.x
		if (class_exists($ns_class))
		{
			//model
			if (isset($this->addon_info['models'][$uc_name]))
			{
				//model does not get cached
				$method = $make ? 'make' : 'get';

				$model_name = $this->lower_name . ':' . $uc_name;

				if ($make)
				{
					return ee('Model')->make($model_name, $make_data);
				}
				else
				{
					return ee('Model')->get($model_name, $default_ids);
				}
			}
			//lib
			else
			{
				//lib gets cached
				$this->cache['lib_mod'][$type][$object_name] = new $ns_class;
				return $this->cache['lib_mod'][$type][$object_name];
			}
		}
		//legacy load
		else
		{
			ee()->load->$type($legacy_name);
		}

		//run isset again in cause load fails but doesn't fire
		//a fatal error
		if (isset(ee()->$legacy_name))
		{
			return ee()->$legacy_name;
		}
		//in theory we never get here due to EE throwing an error
		//on failed ee()->load->model/lib
		else
		{
			throw new \Exception(
				'No ' . ucfirst($type) . ' named "' . $legacy_name . '" or "' .
													$ns_class . '" found'
			);
		}
	}
	//END _lib_mod_loader


	// --------------------------------------------------------------------

	/**
	 * Get Session ID
	 *
	 * @access	public
	 * @return	int		session id, fingerprint, or 0 if not findable
	 */

	public function get_session_id()
	{
		if ( ! $this->session_obj_set())
		{
			$s = 0;
		}
		//EE 2.8+
		else
		{
			$s = ee()->session->session_id();
		}

		return $s;
	}
	//END get_session_id


	// --------------------------------------------------------------------

	/**
	 * Add Pagination Object to Channel object
	 *
	 * @access	public
	 * @param	object	$obj	Incoming channel object
	 * @return	object	$obj	returns object sent due to deprecation of
	 * 							arguments passed by reference.
	 */

	public function add_pag_to_channel($obj)
	{
		ee()->load->library('pagination');

		//this is already done in the contructor of the Channel object
		//in EE 2.8, but it won't hurt to redo it and this might
		//help future proof us as they tend to change this. A lot.
		$obj->pagination = ee()->pagination->create();

		return $obj;
	}
	//END add_pag_to_channel


	// --------------------------------------------------------------------

	/**
	 * Removes Pagination tags from tagdata
	 *
	 * @access	public
	 * @param	object	$obj	Incoming channel object
	 * @return	object	$obj	returns object sent due to deprecation of
	 * 							arguments passed by reference.
	 */

	public function fetch_pagination_data($ojb)
	{
		ee()->TMPL->tagdata = $ojb->pagination->prepare(ee()->TMPL->tagdata);

		return $ojb;
	}
	//END fetch_pagination_data


	// --------------------------------------------------------------------

	/**
	 * Adds rendered pagination back to ending channel return data
	 *
	 * @access	public
	 * @param	object	$obj	Incoming channel object
	 * @return	object	$obj	returns object sent due to deprecation of
	 * 							arguments passed by reference.
	 */

	public function add_pagination_data($obj)
	{
		//this has remained the same since EE 2.4 thusfar
		$obj->return_data = $obj->pagination->render($obj->return_data);

		return $obj;
	}
	//END add_pagination_data


	// --------------------------------------------------------------------

	/**
	 * Set cookie	with legacy support
	 *
	 * @access	public
	 * @param	string	cookie name
	 * @param	string	cookie value
	 * @param	string	expire time
	 * @return	void
	 */

	public function set_cookie($name = '', $value = '', $expire = '')
	{
		return ee()->input->set_cookie($name, $value, $expire);
	}
	//END set_cookie


	// --------------------------------------------------------------------

	/**
	 * Delete Cookie 	with legacy support
	 *
	 * @access	public
	 * @param	string	cookie name
	 * @return	void
	 */

	public function delete_cookie($name = '')
	{
		return ee()->input->delete_cookie($name);
	}
	//END delete_cookie


	// --------------------------------------------------------------------

	/**
	 * CSRF Protection Enabled
	 *
	 * @access	public
	 * @return	boolean
	 */

	public function csrf_enabled()
	{
		//default is n
		return ! $this->check_yes(ee()->config->item('disable_csrf_protection'));
	}
	//END csrf_enabled


	// --------------------------------------------------------------------

	/**
	 * Session Object Set
	 *
	 * @access	public
	 * @return	boolean		is the dang thing set correctly? >_<
	 */

	public function session_obj_set()
	{
		return (
			isset(ee()->session) &&
			is_object(ee()->session) &&
			//Some buttwipe addons initiate session as stdClass by setting
			//session->cache before session is instantiated.
			get_class(ee()->session) != 'stdClass'
		);
	}
	//END session_obj_set

	// --------------------------------------------------------------------
	//	Module Builder methods
	// --------------------------------------------------------------------

	// --------------------------------------------------------------------

	/**
	 * Initialize Module Builder
	 *
	 * @access	public
	 * @return	void
	 */

	public function init_module_builder()
	{
		// --------------------------------------------
		//  Default CP Variables
		// --------------------------------------------

		if (REQ == 'CP')
		{
			$this->cached_vars['page_crumb']			= '';
			$this->cached_vars['page_title']			= '';
			$this->cached_vars['base_uri']				= $this->base;
			$this->cached_vars['module_menu']			= array();
			$this->cached_vars['module_menu_highlight'] = 'module_home';
			$this->cached_vars['module_version'] 		= $this->version;

			// --------------------------------------------
			//  Default Crumbs for Module
			// --------------------------------------------

			if (function_exists('lang'))
			{
				$this->add_crumb(
					lang($this->lower_name.'_module_name'),
					$this->base
				);
			}
		}
		//END if (REQ == 'CP')
	}
	//END init module


	// --------------------------------------------------------------------

	/**
	 *	dd()
	 *
	 *	@access		public
	 *  @param		string
	 *	@return		string
	 */

	public function dd($q)
	{
		print_r($q); exit();
	}

	//	End dd()


	// --------------------------------------------------------------------

	/**
	 * Module Installer
	 *
	 * @access	public
	 * @return	bool
	 */

	public function default_module_install()
	{
		$this->install_module_sql();
		$this->update_module_actions();
		$this->update_extension_hooks();

		return true;
	}
	// END default_module_install()


	// --------------------------------------------------------------------

	/**
	 * Module Uninstaller
	 *
	 * Looks for an db.[module].sql file as well as the old
	 * [module].sql file in the module's folder
	 *
	 * @access	public
	 * @return	bool
	 */

	public function default_module_uninstall()
	{
		//get module id
		$query = ee()->db
					->select('module_id')
					->where('module_name', $this->class_name)
					->get('modules');

		$files = array(
			$this->addon_path . $this->lower_name.'.sql',
			$this->addon_path . 'db.'.$this->lower_name.'.sql'
		);

		ee()->load->dbforge();

		foreach($files as $file)
		{
			if (file_exists($file))
			{
				if (preg_match_all(
					"/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+`([^`]+)`/",
					file_get_contents($file),
					$matches)
				)
				{
					foreach($matches[1] as $table)
					{
						ee()->dbforge->drop_table(
							preg_replace(
								"/^" . preg_quote(ee()->db->dbprefix) . "/ims",
								'',
								trim($table)
							)
						);
					}
				}

				break;
			}
		}

		ee()->db
				->where('module_id', $query->row('module_id'))
				->delete('module_member_groups');

		ee()->db
				->where('module_name', $this->class_name)
				->delete('modules');

		ee()->db
				->where('class', $this->class_name)
				->delete('actions');

		$this->remove_extension_hooks();

		return true;
	}
	// END default_module_uninstall()


	// --------------------------------------------------------------------

	/**
	 * Module Update
	 *
	 * @access	public
	 * @return	bool
	 */

	public function default_module_update()
	{
		$this->update_module_actions();
		$this->update_extension_hooks();

		unset($this->cache['database_version']);

		return true;
	}
	// END default_module_update()


	// --------------------------------------------------------------------

	/**
	 * Install Module SQL
	 *
	 * Looks for an db.[module].sql file as well as the
	 * old [module].sql file in the module's folder
	 *
	 * @access	public
	 * @return	null
	 */

	public function install_module_sql()
	{
		$sql = array();

		// --------------------------------------------
		//  Our Install Queries
		// --------------------------------------------

		$files = array(
			$this->addon_path . $this->lower_name.'.sql',
			$this->addon_path . 'db.'.$this->lower_name.'.sql'
		);

		foreach($files as $file)
		{
			if (file_exists($file))
			{
				$sql = preg_split(
					"/;;\s*(\n+|$)/",
					file_get_contents($file),
					-1,
					PREG_SPLIT_NO_EMPTY
				);

				foreach($sql as $i => $query)
				{
					$sql[$i] = trim($query);
				}

				break;
			}
		}

		// --------------------------------------------
		//  Module Install
		// --------------------------------------------

		foreach ($sql as $query)
		{
			ee()->db->query($query);
		}
	}
	//END install_module_sql()


	// --------------------------------------------------------------------

	/**
	 * Module Actions
	 *
	 * ensures that we have all of the correct
	 * actions in the database for this module
	 *
	 * @access	public
	 * @return	array
	 */

	public function update_module_actions()
	{
		// -------------------------------------
		//	delete actions
		// -------------------------------------

		ee()->db
			->where('class', $this->class_name)
			->delete('actions');

		// --------------------------------------------
		//  Actions of Module Actions
		// --------------------------------------------

		$actions = (
			isset($this->module_actions) &&
			is_array($this->module_actions) &&
			count($this->module_actions) > 0
		) ?
			$this->module_actions :
			array();

		$csrf_exempt_actions = (
			isset($this->csrf_exempt_actions) &&
			is_array($this->csrf_exempt_actions) &&
			count($this->csrf_exempt_actions) > 0
		) ?
			$this->csrf_exempt_actions :
			array();

		// --------------------------------------------
		//  Add Missing Actions
		// --------------------------------------------

		$batch = array();

		foreach($actions as $method)
		{
			$data = array(
				'class'		=> $this->class_name,
				'method'	=> $method
			);

			//is this action xid exempt? (typically for non-essential ajax)
			$data['csrf_exempt'] = in_array(
				$method,
				$csrf_exempt_actions
			) ? 1 : 0;

			$batch[] = $data;
		}

		if ( ! empty($batch))
		{
			ee()->db->insert_batch('actions', $batch);
		}
	}
	// END update_module_actions()


	// --------------------------------------------------------------------

	/**
	 * Module Specific No Results Parsing
	 *
	 * Looks for (your_module)_no_results and uses that,
	 * otherwise it returns the default no_results conditional
	 *
	 *	@access		public
	 *	@return		string
	 */

	public function no_results($custom_condition = '')
	{
		$custom_condition	= (empty($custom_condition)) ? $this->lower_name: $custom_condition;

		if ( preg_match(
				"/".LD."if " .preg_quote($custom_condition)."_no_results" .
					RD."(.*?)".LD.preg_quote('/', '/')."if".RD."/s",
				ee()->TMPL->tagdata,
				$match
			)
		)
		{
			return $match[1];
		}
		else
		{
			return ee()->TMPL->no_results();
		}
	}
	// END no_results()


	// ------------------------------------------------------------------------

	/**
	 * Sanitize Search Terms
	 *
	 * Filters a search string for security
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */

	public function sanitize_search_terms($str)
	{
		ee()->load->helper('search');
		return sanitize_search_terms($str);
	}
	// END sanitize_search_terms()


	// --------------------------------------------------------------------

	/**
	 * mcp_link
	 *
	 * makes cp url out of argument arrays
	 *
	 * @access	public
	 * @param 	mixed 	$vars		key value pair of get vars to add to base, or path string
	 * @param 	bool 	$compile 	return the compiled version of the CP/URL class?
	 * @return	string
	 */

	public function mcp_link($vars = '', $compile = true)
	{
		$method = '';
		$qsVars = array();

		if (is_array($vars))
		{
			if (isset($vars['method']))
			{
				$method = $vars['method'];
				unset($vars['method']);

				$qsVars = $vars;
			}
		}
		elseif (is_string($vars))
		{
			$method = $vars;
		}

		$url = ee(
			'CP/URL',
			'addons/settings/' . $this->lower_name . '/' . $method
		);

		if ( ! empty($qsVars))
		{
			$url->addQueryStringVariables($qsVars);
		}

		return $compile ? $url->compile() : $url;
	}
	//END mcp_link


	// --------------------------------------------------------------------
	// Extension Builder Methods
	// --------------------------------------------------------------------

	// --------------------------------------------------------------------

	/**
	 * Initialize Extension Builder
	 *
	 * @access	public
	 * @return void
	 */

	public function init_extension_builder()
	{
		// --------------------------------------------
		//  Set Required Extension Variables
		// --------------------------------------------

		//lang loader not loaded?
		if ( ! isset(ee()->lang) AND
			! is_object(ee()->lang))
		{
			$this->fetch_language_file($this->lower_name);
		}

		// --------------------------------------------
		//  Extension Table Defaults
		// --------------------------------------------

		$this->extension_defaults = array(
			'class'			=> $this->extension_name,
			'settings'		=> '',
			'priority'		=> 10,
			'version'		=> $this->version,
			'enabled'		=> 'y'
		);

		// --------------------------------------------
		//  Default CP Variables
		// --------------------------------------------

		if (REQ == 'CP')
		{
			$this->cached_vars['page_crumb']	= '';
			$this->cached_vars['page_title']	= '';
			$this->cached_vars['base_uri']		= $this->base;

			$this->cached_vars['extension_menu'] = array();
			$this->cached_vars['extension_menu_highlight'] = '';

			//install wizard doesn't load lang shortcut
			if (function_exists('lang'))
			{
				$this->add_crumb(
					lang($this->lower_name.'_label'),
					$this->cached_vars['base_uri']
				);
			}
		}
		//END if (REQ == 'CP')
	}
	//END init_extension_builder


	// --------------------------------------------------------------------

	/**
	 * Activate Extension
	 * default required method for extensions
	 *
	 * @access	public
	 * @return	boolean		success
	 */

	public function activate_extension()
	{
		if (isset($this->required_by) &&
			in_array('module', $this->required_by))
		{
			return true;
		}

		$this->update_extension_hooks(true);

		return true;
	}
	// END activate_extension


	// --------------------------------------------------------------------

	/**
	 * Disable Extension
	 * default required method for extensions
	 *
	 * @access	public
	 * @return	boolean		success
	 */

	public function disable_extension()
	{
		if (isset($this->required_by) &&
			in_array('module', $this->required_by))
		{
			return true;
		}

		$this->remove_extension_hooks();
	}
	// END disable_extension


	// --------------------------------------------------------------------

	/**
	 * Update Extension
	 * default required method for extensions
	 *
	 * @access	public
	 * @return	boolean		success
	 */

	public function update_extension()
	{
		return $this->activate_extension();
	}
	//END update_extension


	// --------------------------------------------------------------------

	/**
	 * Last Extension Call Variable
	 *
	 * You know that  ee()->extensions->last_call
	 * class variable int he Extensions class for when multiple
	 * extensions call the same hook?
	 * This will take the possible default
	 * parameter and a default value and return whichever is valid.
	 * Examples:
	 *
	 * // Default argument or Last Call?
	 * $argument = $this->last_call($argument);
	 *
	 * // No default argument.  If no Last Call, empty array is default.
	 * $argument = $this->last_call(NULL, array());
	 *
	 *	@access		public
	 *	@param		mixed	$arugument	The default argument sent by ext
	 *	@param		mixed	$default	other default
	 *	@return		mixed
	 */

	public function get_last_call($argument, $default = NULL)
	{
		if (ee()->extensions->last_call !== false)
		{
			return ee()->extensions->last_call;
		}
		elseif ($argument !== NULL)
		{
			return $argument;
		}
		else
		{
			return $default;
		}
	}
	// END get_last_call
}
// END Addon_builder Class
