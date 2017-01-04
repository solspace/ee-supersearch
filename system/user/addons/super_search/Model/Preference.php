<?php

namespace Solspace\Addons\SuperSearch\Model;

class Preference extends BaseModel
{

	protected static $_primary_key	= 'preference_id';
	protected static $_table_name	= 'super_search_preferences';

	protected $preference_id;
	protected $preference_name;
	protected $preference_value;
	protected $site_id;

	protected $_default_prefs = array(
		'use_ignore_word_list'			=> array(
			'type'		=> 'yes_no',
			'default'	=> 'y',
			'validate'	=> 'enum[y,n]'
		),
		'ignore_word_list'				=> array(
			'type'		=> 'text',
			'default'	=> '',
		),
		'enable_search_log'				=> array(
			'type'		=> 'yes_no',
			'default'	=> 'y',
			'validate'	=> 'enum[y,n]'
		),
		'enable_smart_excerpt'			=> array(
			'type'		=> 'yes_no',
			'default'	=> 'y',
			'validate'	=> 'enum[y,n]'
		),
		'enable_fuzzy_searching'		=> array(
			'type'		=> 'yes_no',
			'default'	=> 'y',
			'validate'	=> 'enum[y,n]'
		),
		'enable_fuzzy_searching_plurals'	=> array(
			'type'		=> 'yes_no',
			'default'	=> 'y',
			'validate'	=> 'enum[y,n]'
		),
		'enable_fuzzy_searching_phonetics'	=> array(
			'type'		=> 'yes_no',
			'default'	=> 'y',
			'validate'	=> 'enum[y,n]'
		),
		'enable_fuzzy_searching_spelling'	=> array(
			'type'		=> 'yes_no',
			'default'	=> 'y',
			'validate'	=> 'enum[y,n]'
		),
		'third_party_search_indexes'		=> array(
			'type'		=> 'text',
			'default'	=> '',
		),
	);

	// --------------------------------------------------------------------

	/**
	 * Validate Default Prefs
	 *
	 * Since we often use multiple prefs, lets validate default prefs
	 * and leave alone the ones not meant to be in the prefs page
	 *
	 * @access	public
	 * @param	array	$inputs		incoming inputs to validate
	 * @param	array	$required	array of names of required items
	 * @return	object				instance of validator result
	 */

	public function validateDefaultPrefs($inputs = array(), $required = array())
	{
		//not a typo, see get__default_prefs
		$prefsData = $this->default_prefs;

		$rules = array();

		foreach ($prefsData as $name => $data)
		{
			if (isset($data['validate']))
			{
				$r = (in_array($name, $required)) ? 'required|' : '';

				$rules[$name] = $r . $data['validate'];
			}
		}

		return ee('Validation')->make($rules)->validate($inputs);
	}

	//END validateDefaultPrefs

	// --------------------------------------------------------------------

	/**
	 * Getter: default_prefs
	 *
	 * loads items with lang lines and choices before sending off.
	 * (Requires ee('Model')->make() to access.)
	 *
	 * @access	public
	 * @return	array		key->value array of pref names and defaults
	 */

	public function get__default_prefs()
	{
		//just in case this gets removed in the future.
		if (isset(ee()->lang) && method_exists(ee()->lang, 'loadfile'))
		{
			ee()->lang->loadfile('super_search');
		}

		$prefs = $this->_default_prefs;

		return $prefs;
	}
	//END get__default_prefs
}
//END Preference
