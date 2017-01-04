<?php

/**
 * Code Pack - Library
 *
 * Handles how code packs are read and installed
 *
 * @package		Solspace:Code Pack
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2016, Solspace, Inc.
 * @link		https://solspace.com/expressionengine/super-search
 * @license		https://solspace.com/software/license-agreement
 * @version		2.0
 * @filesource	super_search/Library/CodePack.php
 */

namespace Solspace\Addons\SuperSearch\Library;

use FilesystemIterator;
use Exception;

class CodePack
{
	/**
 	 * Automatically set local system lang files?
 	 *
 	 * @var boolean
 	 */
	public $autoSetLang = false;

	/**
 	 * Language Item Backup
 	 * These get reset for each type if available
 	 *
 	 * @var array
 	 */
	public $langItems = array(
		//errors
		'ee_not_running'				=> 'ExpressionEngine 2.x does not appear to be running.',
		'invalid_code_pack_path'		=> 'Invalid Code Pack Path',
		'invalid_code_pack_path_exp'	=> 'No valid codepack found at \'%path%\'.',
		'missing_code_pack'				=> 'Code Pack missing',
		'missing_code_pack_exp'			=> 'You have chosen no code pack to install.',
		'missing_prefix'				=> 'Prefix needed',
		'missing_prefix_exp'			=> 'Please provide a prefix for the sample templates and data that will be created.',
		'invalid_prefix'				=> 'Invalid prefix',
		'invalid_prefix_exp'			=> 'The prefix you provided was not valid.',
		'missing_theme_html'			=> 'Missing folder',
		'missing_theme_html_exp'		=> 'There should be a folder called \'html\' inside your site\'s \'/themes/solspace_themes/code_pack/%code_pack_name%\' folder. Make sure that it is in place and that it contains additional folders that represent the template groups that will be created by this code pack.',
		'missing_codepack_legacy'		=> 'Missing the CodePackLegacy library needed to install this legacy codepack.',

		//@deprecated
		'missing_code_pack_theme'		=> 'Code Pack Theme missing',
		'missing_code_pack_theme_exp'	=> 'There should be at least one theme folder inside the folder \'%code_pack_name%\' located inside \'/themes/code_pack/\'. A theme is required to proceed.',

		//conflicts
		'conflicting_group_names'		=> 'Conflicting template group names',
		'conflicting_group_names_exp'	=> 'The following template group names already exist. Please choose a different prefix in order to avoid conflicts. %conflicting_groups%',
		'conflicting_global_var_names'	=> 'Conflicting global variable names.',
		'conflicting_global_var_names_exp' => 'There were conflicts between global variables on your site and global variables in this code pack. Consider changing your prefix to resolve the following conflicts. %conflicting_global_vars%',

		//success messages
		'global_vars_added'				=> 'Global variables added',
		'global_vars_added_exp'			=> 'The following global template variables were successfully added. %global_vars%',
		'templates_added'				=> 'Templates were added',
		'templates_added_exp'			=> '%template_count% templates were successfully added to your site as part of this code pack.',
		"home_page"						=>"Home Page",
		"home_page_exp"					=> "View the home page for this code pack here: %link%",
	);
	//END $langItems

	/**
	 * What system are we running? EE, blocks, wordpress, etc?
	 *
	 * @var string
	 * @see detectSystem
	 */
	public $system = '';

	/**
	 * System Addons folder
	 *
	 * @var string
	 * @see setAddonsFolder;
	 */
	public $addonsFolder =  '';

	/**
	 * Conditional Tagpairs for cross platform templates
	 *
	 * @var array
	 */
	public $supported_tagpairs = array(
		'ee'
	);

	/**
	 * Accepted types of templates
	 *
	 * @var array
	 */
	public $types = array(
		'js',
		'json',
		'static',
		'css',
		'xml',
		'xslt',
		'rss',
		'atom',
		'feed',
		'html',
		'txt'
	);

	/**
	 * Solspace Legacy Code Packs
	 *
	 * @var array
	 * @see getCodePacks
	 * @see removeLegacyExtensions
	 */
	public $solspaceLegacyPacks = array(
		'calendar_code_pack',
		'fbc_code_pack',
		'friends_code_pack',
		'rating_code_pack',
		'super_search_code_pack',
		'user_code_pack',
	);

	/**
	 * Is this inside of an addon?
	 *
	 * @var boolean
	 * @see __construct
	 */
	public $internalCodePack = false;

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access	public
	 */

	public function __construct($init = true)
	{
		$this->detectInternalCodePack();

		if ($init)
		{
			$this->detectSystem();
			$this->detectSupport();
			$this->setAddonsFolder();
		}
	}
	//END __construct


	// --------------------------------------------------------------------

	/**
	 * Detect if this is an internal codepack library and not the
	 * one from the codepack lib itself
	 *
	 * @access	public
	 * @return	boolean		is internal code pack lib?
	 */
	public function detectInternalCodePack()
	{
		$this->internalCodePack	= ! (
			get_class($this) === 'CodePack' &&
			//why the need for this? Well sometimes PHP was doing
			// (get_class($this) === 'CodePack') === true where
			// the class was 'AddonCodePack'. WTF?
			// I suspect its some odd parsing in the eval testing
			// it was doing, but this will work for sure in all
			// cases and lets my testing work too. w/e.
			strlen(get_class($this)) === 8
		);

		return $this->internalCodePack;
	}
	//END detectInternalCodePack


	// --------------------------------------------------------------------

	/**
	 * Detect System we are running on
	 *
	 * @access	public
	 * @return	void
	 */

	public function detectSystem()
	{
		//allow someone to set these before firing __construct
		if ($this->system !== '')
		{
			return $this->system;
		}

		$this->system = 'ee';

		return $this->system;
	}
	//END detectSystem


	// --------------------------------------------------------------------

	/**
	 * Detect system support for the code pack lib
	 *
	 * @access	public
	 * @return	array	array of tech that is or is not supported for platform
	 */

	public function detectSupport()
	{
		$supported = array(
			'zip'	=> class_exists('ZipArchive'),
			'php'	=> version_compare(PHP_VERSION, '5.3.10', '>=')
		);

		return $this->supported = $supported;
	}
	//ENd detectSupport


	// --------------------------------------------------------------------

	/**
	 * Set Addons Folder
	 *
	 * @access public
	 * @return string addon folder path with trailing slash
	 */
	public function setAddonsFolder()
	{
		if ($this->addonsFolder !== '')
		{
			return $this->addonsFolder;
		}

		//just in case some fool runs
		//this function before detectSystem
		$this->detectSystem();

		//because i hate swtich/case setups
		if ($this->system == 'ee')
		{
			$this->addonsFolder = PATH_THIRD;
		}

		return $this->addonsFolder;
	}
	//END setAddonsFolder


	// --------------------------------------------------------------------

	/**
	 * Rudementary language getter that can be extended for each system
	 *
	 * @access	public
	 * @param	string $line	language key to find
	 * @return	string			lang line or line itself if not found
	 */

	public function lang($line = '')
	{
		$line = (string) $line;
		return (isset($this->langItems[$line]) ? $this->langItems[$line] : $line);
	}
	//END lang


	// --------------------------------------------------------------------

	/**
	 * Sets language items to the local system lang
	 *
	 * @access	public
	 * @param	mixed		$function	array with class, function or anon function
	 * @param	string		$prefix		key prefix if any
	 */

	public function setLangItems($function, $prefix = '')
	{
		if (! is_callable($function))
		{
			return false;
		}

		// is this an `array($object, 'method')` setup?
		if (is_array($function))
		{
			foreach ($this->langItems as $key => $value)
			{
				$this->langItems[$key] = $function[0]->$function[1]($prefix.$key);
			}
		}
		//must be a string name for a global method
		else
		{
			foreach ($this->langItems as $key => $value)
			{
				$this->langItems[$key] = $function($prefix.$key);
			}
		}

		return true;
	}
	//END setLangItems


	// --------------------------------------------------------------------

	/**
	 * get Addon Code Packs
	 *
	 * @access public
	 * @return array	array of codepacks found for addons
	 */

	public function getAddonCodePacks()
	{
		$packs = array();

		// -------------------------------------
		//	non legacy stuff
		// -------------------------------------

		//$folders = $this->getFolders($this->addonsFolder);

		if ( ! empty($folders))
		{
			foreach ($folders as $folder)
			{
				$details = $this->getCodePackDetails(
					$this->addonsFolder . $folder . '/code_pack/'
				);

				if (is_array($details))
				{
					$pack[] = $details;
				}
			}
		}

		return $packs;
	}
	//END getCodePacks


	// --------------------------------------------------------------------

	/**
	 * Get Code Pack Details
	 *
	 * @access	public
	 * @param	string	$codePackFolder	path to codepack folder
	 * @return	mixed					boolen false if not available else array
	 */

	public function getCodePackDetails($codePackFolder = '', $name = '')
	{
		$codePackFolder = rtrim((string) $codePackFolder, '/') . '/';
		$manifest		= $codePackFolder . 'manifest.json';

		if ( ! is_dir($codePackFolder) || ! is_file($manifest))
		{
			return false;
		}

		$manifest	= file_get_contents($manifest);
		$details	= json_decode($manifest, true);

		if ( ! $manifest)
		{
			return false;
		}

		if ( ! isset($details['name']))
		{
			$details['name'] = $this->detectNameFromPath($codePackFolder);
		}

		return array(
			'code_pack_name'			=> $details['name'],
			'code_pack_label'			=> (
				isset($details['label']) ?
					$details['label'] :
					$details['name']
			),
			'code_pack_description'			=> (
				isset($details['description']) ?
					$details['description'] :
					''
			),
			'homePage'			=> (
				isset($details['homePage']) ?
					$details['homePage'] :
					''
			),
		);
	}
	//END getCodePackDetails


	// --------------------------------------------------------------------

	/**
	 * Detect Code Pack name from path
	 *
	 * This isn't the best method, but it tries to get a name from
	 * the url the codepack lives at if possible. So a url like:
	 * /some/path/my_sweet_addon/code_pack/ would return: my_sweet_addon
	 *
	 * @access	public
	 * @param	string	$path	path to attempt to gather name from
	 * @return	string			detected name or blank string
	 */

	public function detectNameFromPath($path = '')
	{
		$segs	= preg_split("/\//", $path, -1, PREG_SPLIT_NO_EMPTY);
		$return	= '';

		if (empty($segs))
		{
			return $return;
		}

		$i = count($segs);

		while ($i--)
		{
			if (trim($segs[$i]) == 'code_pack' AND $i !== 0)
			{
				$return = strtolower(trim($segs[$i - 1]));
			}
		}

		return $return;
	}
	//END detectNameFromPath


	// --------------------------------------------------------------------

	/**
	 * Code pack install
	 *
	 * This method installs sample data into sites.
	 * It has the facility to read arrays from a data.php file and
	 * create content in a site's database. For this purpose,
	 * $db is a reserved variable.
	 *
	 * (This is passed to a system specific install)
	 *
	 * @access	public
	 * @param	string	$variables['code_pack_name']
	 * @param	string	$variables['code_pack_theme']
	 * @param	string	$variables['prefix']
	 * @param	string	$variables['theme_path']
	 * @param	string	$variables['theme_url']
	 * @return	string
	 */
	public function installCodePack($variables = array())
	{
		$func = $this->system . 'InstallCodePack';
		return $this->$func($variables);
	}
	//END installCodePack


	// --------------------------------------------------------------------

	/**
	 * EE Code pack install
	 *
	 * This method installs sample data into EE sites.
	 * It has the facility to read arrays from a data.php file and
	 * create content in a site's database. For this purpose,
	 * $db is a reserved variable.
	 *
	 * @access	public
	 * @param	string	$variables['code_pack_name']
	 * @param	string	$variables['code_pack_path']
	 * @param	string	$variables['prefix']
	 * @param	string	$variables['theme_path']
	 * @param	string	$variables['theme_url']
	 * @param	string	$variables['global_variables']
	 * @param	boolean	$variables['legacy']
	 * @return	string
	 */

	public function eeInstallCodePack($variables = array())
	{
		if ( ! $this->system == 'ee')
		{
			trigger_error($this->lang('ee_not_running'), E_USER_NOTICE);
		}

		$site_id = ee()->config->item('site_id');

		if ($this->autoSetLang)
		{
			$this->setLangItems('lang');
		}

		// -------------------------------------
		//	legacy?
		// -------------------------------------

		if ( ! isset($variables['legacy']))
		{
			$variables['legacy'] = false;
		}

		// -------------------------------------
		//	return vars
		// -------------------------------------

		$return						= array();
		$return['errors']			= array();
		$return['success']			= array();
		$return['global_vars']		= array();
		$return['template_count']	= 0;

		// -------------------------------------
		//	Set reserved names
		// -------------------------------------

		$return['reserved_names']	= $this->getReservedSystemNames();

		// -------------------------------------
		//	validate variables
		// -------------------------------------

		$return = $this->validateVariables($variables, $return);

		// --------------------------------------------
		//	Do we have errors?
		// --------------------------------------------

		if (! empty($return['errors']))
		{
			return $return;
		}

		// --------------------------------------------
		//	Prepare vars for later
		// --------------------------------------------

		$details = $this->getCodePackDetails($variables['code_pack_path']);

		$return['conflicting_groups']		= array();
		$return['conflicting_global_vars']	= array();

		// -------------------------------------
		//	Get list of template groups
		// -------------------------------------

		$template_path = rtrim($variables['code_pack_path'], '/') . '/templates/';

		$return['template_groups'] = $this->getFolders($template_path);

		// -------------------------------------
		//	Prepare arrays
		// -------------------------------------

		if (count($return['template_groups']) == 0)
		{
			$return['errors'][]	= array(
				'label'			=> $this->lang('missing_theme_html'),
				'description'	=> str_replace(
					'%code_pack_name%',
					$template_path,
					$this->lang('missing_theme_html_exp')
				)
			);
		}

		// -------------------------------------
		//	group prefixes
		// -------------------------------------

		$return['prefixed_template_groups']	= array();

		foreach ($return['template_groups'] as $key => $val)
		{
			$return['prefixed_template_groups'][]	= $variables['prefix'].$val;
		}

		// -------------------------------------
		//	Check for template group name conflicts
		// -------------------------------------

		if (count($return['template_groups']) > 0 AND
			! empty($variables['prefix']))
		{
			$query = ee()->db->select('group_name')
							->where('site_id', $site_id)
							->where_in('group_name', $return['prefixed_template_groups'])
							->get('template_groups');

			if ($query->num_rows() > 0)
			{
				$return['conflicting_groups']	= array();

				foreach ($query->result_array() as $row)
				{
					$return['conflicting_groups'][]	= $row['group_name'];
				}

				$return['errors'][]	= array(
					'label'			=> $this->lang('conflicting_group_names'),
					'description'	=> $this->lang('conflicting_group_names_exp')
				);
			}
		}

		// -------------------------------------
		//	Check for global variable conflicts
		// -------------------------------------

		$global_vars	= array();

		if (isset($variables['global_vars']) &&
			is_array($variables['global_vars']))
		{
			$global_vars = $variables['global_vars'];
		}

		if ( ! empty($global_vars))
		{
			$query = ee()->db
						->select('variable_name')
						->where('site_id', $site_id)
						->where_in('variable_name', array_keys($global_vars))
						->get('global_variables');

			if ($query->num_rows() > 0)
			{
				foreach ($query->result_array() as $row)
				{
					$return['conflicting_global_vars'][]	= $row['variable_name'];
				}

				$return['errors'][]	= array(
					'label'			=> $this->lang('conflicting_global_var_names'),
					'description'	=> $this->lang('conflicting_global_var_names_exp')
				);
			}
		}

		if ( ! empty($return['errors']))
		{
			return $return;
		}

		// -------------------------------------
		//	Does this have an installer?
		// -------------------------------------

		$className		= ucfirst($variables['code_pack_name']) . '_installer';

		$iPath			= $variables['code_pack_path'] . '/' .
								$variables['code_pack_name'] . '_installer.php';

		//installer isn't required
		if (is_file($iPath))
		{
			if (! class_exists($className))
			{
				require_once $iPath;
			}

			$instance = new $className();

			$options = array(
				'variables' => $variables,
				'return'	=> $return
			);

			$options['caller'] =& $this;

			$return	= $instance->install($options);
		}


		// --------------------------------------------
		//	Do we have errors?
		// --------------------------------------------

		if ( ! empty($return['errors']))
		{
			return $return;
		}

		// -------------------------------------
		//	Create global variables
		// -------------------------------------

		if ( ! empty($global_vars) && empty($return['conflicting_global_vars']))
		{
			foreach ($global_vars as $key => $val)
			{
				ee()->db->insert(
					'global_variables',
					array(
						'site_id'		=> $site_id,
						'variable_name'	=> $key,
						'variable_data'	=> $val
					)
				);

				$return['global_vars'][] = $key;
			}
		}

		if (count($return['global_vars']) > 0)
		{
			$return['success'][]	= array(
				'label'			=> $this->lang('global_vars_added'),
				'description'	=> $this->lang('global_vars_added_exp')
			);
		}

		// -------------------------------------
		//	Install templates
		// -------------------------------------

		if (empty($return['errors']) AND
			! empty($return['template_groups']))
		{
			//get template group number
			$tg_query =	ee()->db
							->select_max('group_order')
							->get('template_groups');

			$group_order = 0;

			if ($tg_query->num_rows() > 0)
			{
				$group_order = $tg_query->row('group_order');
			}

			foreach ($return['template_groups'] as $group)
			{
				$files	= $this->getFiles(
					$template_path . $group . '/'
				);

				if (in_array('index.txt', $files) === FALSE AND
					 in_array('index.html', $files) === FALSE)
				{
					$files[]	= 'index.html';
				}

				// -------------------------------------
				//	Install group
				// -------------------------------------

				//reserved even with group?
				if (in_array(
						$variables['prefix'] . $group,
						$return['reserved_names']
					))
				{
					continue;
				}

				ee()->db->insert(
					'template_groups',
					array(
						'site_id'		=> $site_id,
						'group_name'	=> $variables['prefix'] . $group,
						'group_order'	=> ++$group_order
					)
				);

				$group_id = ee()->db->insert_id();

				// -------------------------------------
				//	Add templates
				// -------------------------------------

				foreach ($files as $val)
				{
					//get filetype for storing properly
					$ext 			= substr(strrchr($val, '.'), 1);

					$types = array(
						'css'		=> 'css',
						'scss'		=> 'css',

						'js'		=> 'js',
						'json'		=> 'js',

						'rss'		=> 'feed',
						'atom'		=> 'feed',
						'feed'		=> 'feed',

						'static'	=> 'static',

						'xml'		=> 'xml',
						'xslt'		=> 'xml'
					);

					$template_type = isset($types[$ext]) ? $types[$ext] : 'webpage';

					//just want the name itself
					$name = preg_replace('/(\.' . $ext . ')$/s', '', $val);

					if (in_array($name, $return['reserved_names']) === TRUE)
					{
						continue;
					}

					// -------------------------------------
					//	Parse prefix in template
					// -------------------------------------

					$contents	= str_replace(
						'%prefix%',
						$variables['prefix'],
						file_get_contents($template_path . $group . '/' . $val)
					);

					// -------------------------------------
					//	legacy tag pair remove
					// -------------------------------------

					//remove ee 1.x content
					$contents = preg_replace(
						'/%ee1%(.*?)%\/ee1%/s',
						"" ,
						$contents
					);

					//remove ee2.x tags
					$contents = preg_replace(
						'/%ee2%(.*?)%\/ee2%/s',
						"$1" ,
						$contents
					);

					// -------------------------------------
					//	clean tagpairs
					// -------------------------------------

					foreach ($this->supported_tagpairs as $tagpair)
					{
						if ($tagpair !== 'ee')
						{
							//remove depending on version
							$contents = preg_replace(
								'/%'. $tagpair . '%(.*?)%\/' . $tagpair . '%/s',
								"" ,
								$contents
							);
						}
					}

					//remove depending on version
					$contents = preg_replace(
						'/%ee%(.*?)%\/ee%/s',
						"$1" ,
						$contents
					);

					// -------------------------------------
					//	Detect PHP
					// -------------------------------------

					$php_parsing_on		= 'n';
					$php_parse_location	= 'o';

					if (preg_match('/<\?php/is', $contents))
					{
						$php_parsing_on	= 'y';
					}

					if (preg_match('/<\?php\s\/\/\sinput/is', $contents))
					{
						$php_parse_location	= 'i';
					}

					// -------------------------------------
					//	Prepare insert
					// -------------------------------------

					ee()->db->insert(
						'templates',
						array(
							'site_id'				=> $site_id,
							'group_id'				=> $group_id,
							'template_type'			=> $template_type,
							'edit_date'				=> time(),
							'template_name'			=> $name,
							'template_data'			=> $contents,
							'allow_php'				=> $php_parsing_on,
							'php_parse_location'	=> $php_parse_location,
						)
					);

					$return['template_count']++;
				}
			}
		}

		if ($return['template_count'] > 0)
		{
			$return['success'][]	= array(
				'label'			=> $this->lang('templates_added'),
				'description'	=> str_replace(
					'%templates_added%',
					$return['template_count'],
					$this->lang('templates_added_exp')
				)
			);

			$home_url	= ee()->functions->create_url(
				str_replace('%prefix%', $variables['prefix'], $details['homePage'])
			);

			$return['success'][]	= array(
				'label'			=> $this->lang('home_page'),
				'link'			=> $home_url,
				'description'	=> str_replace(
					'%link%',
					'<a href="' . $home_url . '" onclick="window.open(this.href); return false;">' .
						$this->lang('home_page') .
						'</a>',
					$this->lang('home_page_exp')
				)
			);

		}

		// --------------------------------------------
		//	Return
		// --------------------------------------------

		return $return;
	}
	//END ee_install_pack


	// --------------------------------------------------------------------

	/**
	 * Validate passed in variables
	 *
	 * @access public
	 * @param  array	$variables	variable array to validate
	 * @param  array	$return		return array to add errors to
	 * @return array				array with any errors added to ['error']
	 */

	public function validateVariables($variables = array(), $return = array())
	{
		// -------------------------------------
		//	Check for code pack name
		// -------------------------------------

		if (empty($variables['code_pack_name']))
		{
			$return['errors'][]	= array(
				'label'			=> $this->lang('missing_code_pack'),
				'description'	=> $this->lang('missing_code_pack_exp')
			);
		}

		// -------------------------------------
		//	Check for code pack path
		// -------------------------------------

		if (empty($variables['code_pack_path']) ||
			! is_dir($variables['code_pack_path']))
		{
			$return['errors'][]	= array(
				'label'			=> $this->lang('invalid_code_pack_path'),
				'description'	=> $this->lang('invalid_code_pack_path_exp')
			);
		}

		// -------------------------------------
		//	Check for template prefix
		// -------------------------------------

		if (empty($variables['prefix']))
		{
			$return['errors'][]	= array(
				'label'			=> $this->lang('missing_prefix'),
				'description'	=> $this->lang('missing_prefix_exp')
			);
		}
		elseif (preg_match("/[^a-zA-Z0-9\_\-]/", $variables['prefix']))
		{
			$return['errors'][]	= array(
				'label'			=> $this->lang('invalid_prefix'),
				'description'	=> $this->lang('invalid_prefix_exp')
			);
		}

		// -------------------------------------
		//	legacy
		// -------------------------------------

		if ($variables['legacy'])
		{
			// -------------------------------------
			//	Check for code pack theme
			// -------------------------------------

			if (empty($variables['code_pack_theme']))
			{
				$return['errors'][]	= array(
					'label'			=> $this->lang('missing_code_pack_theme'),
					'description'	=> str_replace(
						'%code_pack_name%',
						$variables['code_pack_name'],
						$this->lang('missing_code_pack_theme_exp')
					)
				);
			}

			// -------------------------------------
			//	Check for code pack theme path
			//	(only legacy strictly requires)
			// -------------------------------------

			if (empty($variables['theme_path']))
			{
				$return['errors'][]	= array(
					'label'			=> $this->lang('missing_code_pack_theme'),
					'description'	=> str_replace(
						'%code_pack_name%',
						$variables['code_pack_name'],
						$this->lang('missing_code_pack_theme_exp')
					)
				);
			}

			// -------------------------------------
			//	Check for code pack theme url
			//	(only legacy strictly requires)
			// -------------------------------------

			if (empty($variables['theme_url']))
			{
				$return['errors'][]	= array(
					'label'			=> $this->lang('missing_code_pack_theme'),
					'description'	=> str_replace(
						'%code_pack_name%',
						$variables['code_pack_name'],
						$this->lang('missing_code_pack_theme_exp')
					)
				);
			}
		}
		//END 	if ($variables['legacy'])

		return $return;
	}
	//END validateVariables


	// --------------------------------------------------------------------

	/**
	 * get Folders for a path
	 *
	 * @access	public
	 * @param	string		$path	Absolute server path to theme directory
	 * @return	array				array of folder names to choose
	 */

	public function getFolders($path)
	{
		$folders = array();

		$path = rtrim(realpath($path), '/') . '/';

		// -------------------------------------
		//	legit?
		// -------------------------------------

		if (! is_dir($path))
		{
			return $folders;
		}

		$iterator = new FilesystemIterator(
			$path,
			FilesystemIterator::SKIP_DOTS |
			FilesystemIterator::UNIX_PATHS |
			FilesystemIterator::FOLLOW_SYMLINKS
		);

		foreach (iterator_to_array($iterator) as $fileinfo)
		{
			if ($fileinfo->isDir() AND
				substr($fileinfo->getFilename(), 0, 1) != '.')
			{
				$folders[] = $fileinfo->getFilename();
			}
		}

		sort($folders);

		return $folders;
	}
	// END getFolders()


	// --------------------------------------------------------------------

	/**
	 * get Files for a path
	 *
	 * @access	public
	 * @param	string		$path	Absolute server path to theme directory
	 * @return	array				array of folder names to choose
	 */

	public function getFiles($path, $filter = array(), $includeHidden = true)
	{
		$files = array();

		$path = rtrim(realpath($path), '/') . '/';

		$blackList = array('thumbs.db', '.ds_store', '.gitignore');

		// -------------------------------------
		//	legit?
		// -------------------------------------

		if (! is_dir($path))
		{
			return $files;
		}

		$iterator = new FilesystemIterator(
			$path,
			FilesystemIterator::SKIP_DOTS |
			FilesystemIterator::UNIX_PATHS |
			FilesystemIterator::FOLLOW_SYMLINKS
		);

		foreach (iterator_to_array($iterator) as $fileinfo)
		{
			if ($fileinfo->isFile() AND
				(
					$includeHidden OR
					substr($fileinfo->getFilename(), 0, 1) != '.'
				) AND
				(
					empty($filter) OR
					in_array($fileinfo->getExtension(), $filter)
				) AND
				! in_array(strtolower($fileinfo->getFilename()), $blackList)
			)
			{
				$files[] = $fileinfo->getFilename();
			}
		}

		sort($files);

		return $files;
	}
	// END getFiles


	// --------------------------------------------------------------------

	/**
	 * get Files for a path
	 *
	 * @access	public
	 * @param	string		$path	Absolute server path to theme directory
	 * @return	array				array of folder names to choose
	 */

	public function getTemplateDirectoryArray($path)
	{
		$folders = array();

		$path = rtrim(realpath($path), '/') . '/';

		//code pack folder must have template folder to look in
		if ( ! preg_match('/templates\/$/i', $path))
		{
			$path .= 'templates/';
		}

		// -------------------------------------
		//	legit?
		// -------------------------------------

		if (! is_dir($path))
		{
			return $folders;
		}

		$iterator = new FilesystemIterator(
			$path,
			FilesystemIterator::SKIP_DOTS |
			FilesystemIterator::UNIX_PATHS |
			FilesystemIterator::FOLLOW_SYMLINKS
		);

		foreach (iterator_to_array($iterator) as $fileinfo)
		{
			if ($fileinfo->isDir() AND
				substr($fileinfo->getFilename(), 0, 1) != '.')
			{
				$folders[$fileinfo->getFilename()] = $this->getFiles(
					$path . $fileinfo->getFilename()
				);
			}
		}

		//sort($folders);

		return $folders;
	}
	//END getTemplateDirectoryArray


	// --------------------------------------------------------------------

	/**
	 * Get code pack full description
	 *
	 * @access	public
	 * @return	array
	 */

	function getCodePackFullDescription($path = '')
	{
		$description = '';

		$file_path = $path . '/meta/description.html';

		if ($path == '' || ! file_exists($file_path))
		{
			return $description;
		}

		// --------------------------------------------
		//  Get description file contents
		// --------------------------------------------

		$description = file_get_contents($file_path);

		return $description;
	}
	//	End get code pack full description


	// --------------------------------------------------------------------

	/**
	 * Get code pack image
	 *
	 * @access	public
	 * @return	array
	 */

	function getCodePackImage($path = '', $url = '')
	{
		$image	= '';

		// --------------------------------------------
		//  Validate
		// --------------------------------------------

		if ($url == '')
		{
			return $image;
		}

		// --------------------------------------------
		//  Does file exist?
		// --------------------------------------------

		//no trust for you
		$path = rtrim($path, '/') . '/';
		$url = rtrim($url, '/') . '/';

		//because crappy non-unix systems just CANT handle glob() *SIIIIGHHHH*
		$results = array();

		foreach($this->getFolders($path) as $folder)
		{
			$f_path = $path . $folder . '/';

			if (in_array($folder, array('meta','resources','img','images')))
			{
				$files = $this->getFiles($f_path, array('jpg','jpeg','png','gif'));

				foreach ($files as $filename)
				{
					if (preg_match('/^screenshot\./', $filename))
					{
						$results[] = $f_path . $filename;
					}
				}
			}
		}

		if (! empty($results))
		{
			$image	= array_pop($results);
		}

		if ($image !== '')
		{
			$image = str_replace($path, $url, $image);
		}

		return $image;
	}
	//	End get code pack image


	// --------------------------------------------------------------------

	/**
	 * Get Reserved System Names
	 *
	 * @access public
	 * @return array array of reserved system names for current system
	 */
	public function getReservedSystemNames()
	{
		$return = array();

		if ($this->system == 'ee')
		{
			if (ee()->config->item("use_category_name") == 'y' AND
			ee()->config->item("reserved_category_word") != '')
			{
				$return[] = ee()->config->item("reserved_category_word");
			}

			if (ee()->config->item("forum_is_installed") == 'y' AND
				ee()->config->item("forum_trigger") != '')
			{
				$return[] = ee()->config->item("forum_trigger");
			}

			if (ee()->config->item("profile_trigger") != '')
			{
				$return[] = ee()->config->item("profile_trigger");
			}

			// -------------------------------------
			//	pages?
			// -------------------------------------

			if (ee()->db
					->where_in('module_name', array('Structure', 'Pages'))
					->count_all_results('modules') > 0)
			{
				$query = ee()->db->select('site_pages')->get('sites');

				$new_pages = array();

				if ($query->num_rows() > 0)
				{
					foreach($query->result_array() as $row)
					{
						try
						{
							$site_pages = unserialize(base64_decode(
								$row['site_pages']
							));

							if (is_array($site_pages))
							{
								$return += $site_pages;
							}
						}
						catch (Exception $e)
						{
							continue;
						}
					}
				}
			}
		}

		return $return;
	}
	//END getReservedSystemNames


	// --------------------------------------------------------------------

	/**
	 * Remove EE extensions for legacy code packs
	 *
	 * @access public
	 * @param  string	$addon	specfic addon?
	 * @return boolean			success
	 */
	public function removeLegacyExtensions($addon = '')
	{
		if ($this->system !== 'ee')
		{
			return false;
		}

		$remove = array();

		foreach ($this->solspaceLegacyPacks as $item)
		{
			//looking for just one addon?
			if ($addon !== '' && ! stristr($item, $addon))
			{
				continue;
			}

			//normal class name (just in case)
			$remove[] = $item;

			//only thing that should really be there
			$remove[] = $item . '_ext';

			//some error happening in later versions
			//of 1.x code packs. Cleanup.
			$remove[] = preg_replace('/_pack$/is', '', $item);
		}

		if (! empty($remove))
		{
			//delort
			ee()->db
				->where_in('class', $remove)
				->delete('extensions');
		}

		return true;
	}
	//END removeLegacyExtensions
}
 //END class CodePack