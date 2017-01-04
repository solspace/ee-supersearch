<?php

use Solspace\Addons\SuperSearch\Library\AddonBuilder;

class Super_search_upd extends AddonBuilder
{
	public $module_actions	= array();
    public $hooks			= array();

	// --------------------------------------------------------------------

	/**
	 * Contructor
	 *
	 * @access	public
	 * @return	null
	 */

	public function __construct()
	{
		parent::__construct('module');

		// --------------------------------------------
		//  Module Actions
		// --------------------------------------------

		$this->module_actions = array('save_search');

		// --------------------------------------------
		//  Extension Hooks
		// --------------------------------------------

		$default = array(
			'class'			=> $this->extension_name,
			'settings'		=> '',
			'priority'		=> 4,
			'version'		=> $this->version,
			'enabled'		=> 'y'
		);

		$this->hooks = array(
			array_merge(
				$default,
				array(
					'method'	=> 'after_channel_entry_save',
					'hook'		=> 'after_channel_entry_save'
				)
			),
			array_merge(
				$default,
				array(
					'method'	=> 'channel_entries_query_result',
					'hook'		=> 'channel_entries_query_result'
				)
			),
			array_merge(
				$default,
				array(
					'method'	=> 'sessions_end_processor',
					'hook'		=> 'sessions_end'
				)
			),
			array_merge(
				$default,
				array(
					'method'	=> 'super_search_alter_search_check_group',
					'hook'		=> 'super_search_alter_search',
					'priority'	=> 5
				)
			),
			array_merge(
				$default,
				array(
					'method'	=> 'super_search_alter_search_multiselect',
					'hook'		=> 'super_search_alter_search',
					'priority'	=> 6
				)
			),
			array_merge(
				$default,
				array(
					'method'	=> 'super_search_alter_search_playa',
					'hook'		=> 'super_search_alter_search',
					'priority'	=> 7
				)
			),
			array_merge(
				$default,
				array(
					'method'	=> 'super_search_alter_search_relationship',
					'hook'		=> 'super_search_alter_search',
					'priority'	=> 4
				)
			),
			array_merge(
				$default,
				array(
					'method'	=> 'super_search_extra_basic_fields_tag',
					'hook'		=> 'super_search_extra_basic_fields',
					'priority'	=> 5
				)
			),
			array_merge(
				$default,
				array(
					'method'	=> 'super_search_alter_ids_tag',
					'hook'		=> 'super_search_alter_ids',
					'priority'	=> 5
				)
			),
			array_merge(
				$default,
				array(
					'method'	=> 'super_search_do_search_and_array_playa',
					'hook'		=> 'super_search_do_search_and_array',
					'priority'	=> 5
				)
			),
			array_merge(
				$default,
				array(
					'method'	=> 'super_search_do_search_and_array_rating',
					'hook'		=> 'super_search_do_search_and_array',
					'priority'	=> 6
				)
			),
			array_merge(
				$default,
				array(
					'method'	=> 'super_search_prep_order',
					'hook'		=> 'super_search_prep_order',
					'priority'	=> 5
				)
			),
		);
	}
	// END __construct


	// --------------------------------------------------------------------

	/**
	 * Module Installer
	 *
	 * @access	public
	 * @return	bool
	 */

	public function install()
	{
		// Already installed, let's not install again.
		if ($this->database_version() !== FALSE)
		{
			return FALSE;
		}

		// --------------------------------------------
		//  Our Default Install
		// --------------------------------------------

		if ($this->default_module_install() == FALSE)
		{
			return FALSE;
		}

		// --------------------------------------------
		//  Set default cache prefs
		// --------------------------------------------

		$this->model('Data')->set_default_cache_prefs();

		// --------------------------------------------
		//  Default Preferences - Per Site
		// --------------------------------------------

        $defaults = array(
        	'use_ignore_word_list'			=> 'y',
			'ignore_word_list'				=> 'a||and||of||or||the',
			'enable_search_log'				=> 'n',
			'third_party_search_indexes'	=> ''
		);

		$squery = ee()->db->select("site_id")->get("exp_sites");

		foreach($squery->result_array() as $row)
		{
			foreach($defaults as $key => $value)
			{
				$new = $this->make('Preference');
				$new->preference_name = $key;
				$new->preference_value = $value;
				$new->site_id = $row['site_id'];
				$new->save();
			}
		}

		// --------------------------------------------
		//  Module Install
		// --------------------------------------------

		$data = array(
			'module_name'			=> $this->class_name,
			'module_version'		=> $this->version,
			'has_publish_fields'	=> 'n',
			'has_cp_backend'		=> 'y'
		);

		ee()->db->insert('exp_modules', $data);

		// --------------------------------------------
		// Create the levenshtein mysql level function
		// --------------------------------------------

		if (ee()->db->insert_id())
		{
			//be safe
			$sql = "DROP FUNCTION IF EXISTS LEVENSHTEIN;";
			ee()->db->query($sql);

			$sql = $this->model('Data')->define_levenshtein();
			ee()->db->query($sql);
		}

		// --------------------------------------------
        //	Return
        // --------------------------------------------

        return TRUE;
    }

	// END install()

	// --------------------------------------------------------------------

	/**
	 * Module Uninstaller
	 *
	 * @access	public
	 * @return	bool
	 */

	public function uninstall()
	{
		// Cannot uninstall what does not exist, right?
		if ($this->database_version() === FALSE)
		{
			return FALSE;
		}

		// --------------------------------------------
		//  Default Module Uninstall
		// --------------------------------------------

		if ($this->default_module_uninstall() == FALSE)
		{
			return FALSE;
		}

		// --------------------------------------------
		//	Return
		// --------------------------------------------

        return TRUE;
    }

    //	END uninstall

	// --------------------------------------------------------------------

	/**
	 * Module Updater
	 *
	 * @access	public
	 * @return	bool
	 */

	public function update($current = '')
	{
		if ($current == $this->version)
		{
			return FALSE;
		}

		// --------------------------------------------
		//  Do DB work
		// --------------------------------------------

		if (! file_exists($this->addon_path.'db.'.strtolower($this->lower_name).'.sql'))
		{
			return FALSE;
		}

		$sql = preg_split("/;;\s*(\n+|$)/", file_get_contents($this->addon_path.'db.'.strtolower($this->lower_name).'.sql'), -1, PREG_SPLIT_NO_EMPTY);

		if (count($sql) == 0)
		{
			return FALSE;
		}

		foreach($sql as $i => $query)
		{
			$sql[] = trim($query);
		}

        // --------------------------------------------
        //  Super Search History needs a query field
        // --------------------------------------------

		if (! ee()->db->field_exists('query', 'exp_super_search_history', FALSE))
		{
        	$sql[]	= "ALTER TABLE exp_super_search_history ADD `query` mediumtext NOT NULL";
        }

        // --------------------------------------------
        //  Super Search History needs a query field
        // --------------------------------------------

		if (! ee()->db->field_exists('hash', 'exp_super_search_history', FALSE))
        {
        	$sql[]	= "ALTER TABLE exp_super_search_history ADD `hash` varchar(32) NOT NULL";
        }

        // --------------------------------------------
        //  Super Search History needs a unique key for some insert magic
        // --------------------------------------------

        if (ee()->db->table_exists('exp_super_search_history') === TRUE)
        {
        	$query	= ee()->db->query( "SHOW indexes FROM exp_super_search_history WHERE Key_name = 'search_key'" );

        	if ($query->num_rows() == 0)
        	{
        		$sql[]	= "ALTER TABLE `exp_super_search_history` ADD UNIQUE KEY `search_key` (`member_id`,`cookie_id`,`site_id`,`search_name`,`saved`)";
        	}
        }

        // --------------------------------------------
        //	Change the name of the weblog_id field to channel_id
        // --------------------------------------------

		if (version_compare($this->database_version(), '1.1.0.b3', '<'))
        {
        	$sql[]	= "ALTER TABLE exp_super_search_refresh_rules CHANGE `weblog_id` `channel_id` int(10) unsigned NOT NULL default '0'";
        }

        // --------------------------------------------
        //	Change the name of the weblog_id field to channel_id
        // --------------------------------------------

        if (ee()->db->table_exists('exp_super_search_preferences') === FALSE)
		{
			$module_install_sql = file_get_contents($this->addon_path.'db.'.strtolower($this->lower_name).'.sql');

			//gets JUST the prefs table from the sql

			$prefs_table = stristr(
				$module_install_sql,
				"CREATE TABLE IF NOT EXISTS `exp_super_search_preferences`"
			);

			$prefs_table = substr($prefs_table, 0, stripos($prefs_table, ';;'));

			//install it
			$sql[] = $prefs_table;
		}

        // --------------------------------------------
        // Create the log table if it doesn't exist
        // --------------------------------------------

        if (ee()->db->table_exists('exp_super_search_log') === FALSE)
		{
			$module_install_sql = file_get_contents($this->addon_path.'db.'.strtolower($this->lower_name).'.sql');

			//gets JUST the prefs table from the sql

			$prefs_table = stristr(
				$module_install_sql,
				"CREATE TABLE IF NOT EXISTS `exp_super_search_log`"
			);

			$prefs_table = substr($prefs_table, 0, stripos($prefs_table, ';;'));

			//install it
			$sql[] = $prefs_table;
		}

		// --------------------------------------------
        // Create the terms table if it doesn't exist
        // --------------------------------------------

        if (ee()->db->table_exists('exp_super_search_terms') === FALSE)
		{
			$module_install_sql = file_get_contents($this->addon_path.'db.'.strtolower($this->lower_name).'.sql');

			//gets JUST the prefs table from the sql

			$prefs_table = stristr(
				$module_install_sql,
				"CREATE TABLE IF NOT EXISTS `exp_super_search_terms`"
			);

			$prefs_table = substr($prefs_table, 0, stripos($prefs_table, ';;'));

			//install it
			$sql[] = $prefs_table;

			// --------------------------------------------
			// Create the levenshtein mysql level function
			// --------------------------------------------

			//be safe
			$sql[] = "DROP FUNCTION IF EXISTS LEVENSHTEIN;";

			//install it
			$sql[] = $this->model('Data')->define_levenshtein();

			// Set our inital word list contents
			$this->model('Data')->set_preference(array('ignore_word_list' => 'a||and||of||or||the'));
		}

        // --------------------------------------------
        // Add the term_length col to the search lexicon table
        // --------------------------------------------

		if (version_compare($this->database_version(), '2.0.0.b3', '<'))
        {
        	if (ee()->db->table_exists('exp_super_search_terms') === TRUE)
        	{
				if (! ee()->db->field_exists('term_length', 'exp_super_search_terms', FALSE))
				{
					$sql[]	= "ALTER TABLE exp_super_search_terms ADD `term_length` int(10) unsigned NOT NULL default '0'";
				}
        	}
        }

        // --------------------------------------------
        // Fix for table collation and foreign characters
        // --------------------------------------------

		if (version_compare($this->database_version(), '2.1.0', '<'))
        {
        	// Undo primary key on exp_super_search_terms temporarily
        	$query	= ee()->db->query("SHOW KEYS FROM exp_super_search_terms");

        	if ($query->num_rows > 0)
        	{
        		foreach ($query->result_array() as $row)
        		{
        			if (! empty($row['Key_name']) AND $row['Key_name'] == 'PRIMARY')
        			{
        				$key_exists	= TRUE;
        			}
        		}
        	}

        	if (isset($key_exists))
        	{
				ee()->db->query("ALTER TABLE exp_super_search_terms DROP PRIMARY KEY");
        	}

			// make sure STRICT MODEs aren't in use
			@mysql_query("SET SESSION sql_mode=''", ee()->db->conn_id);

			$tables = array('exp_super_search_cache', 'exp_super_search_history', 'exp_super_search_log', 'exp_super_search_terms', 'exp_super_search_lexicon_log');

			foreach ($tables as $table)
			{
				$query = ee()->db->query("SHOW COLUMNS FROM `{$table}`");

				foreach($query->result_array() as $row)
				{
					$field = $row['Field'];
					ee()->db->query("UPDATE `{$table}` SET `{$field}` = CONVERT(CONVERT(`{$field}` USING binary) USING utf8)");
				}

				ee()->db->query("ALTER TABLE `{$table}` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci");
			}

        	// Re-establish primary key on exp_super_search_terms
        	if (isset($key_exists))
        	{
				ee()->db->query("ALTER TABLE exp_super_search_terms ADD KEY `term` (term)");
        	}
        }

        // --------------------------------------------
        //	Move preferences from exp_sites into exp_super_search_preferences
        // --------------------------------------------

		if (version_compare($this->database_version(), '3.0.0', '<'))
        {
			// --------------------------------------------
			//	Grab prefs from exp_sites
			// --------------------------------------------

			$sqla	= "SELECT site_system_preferences,
						site_id
						FROM exp_sites";

			$query	= ee()->db->query($sqla);

			if ($query->num_rows() > 0)
			{
				ee()->load->helper('string');

				$prefs	= array();

				foreach ($query->result_array() as $row)
				{
					$p	= unserialize(base64_decode($row['site_system_preferences']));

					$prefs[$row['site_id']]	= $p;
				}
			}

			// --------------------------------------------
			//	Loop and update
			// --------------------------------------------

			$prefs = $this->make('Preference');

			$default_prefs = $prefs->default_prefs;

			foreach ($prefs as $site_id => $p)
			{
				foreach ($default_prefs as $key => $val)
				{
					if (! isset($p[$key])) continue;

					$new = $this->make('Preference');
					$new->preference_name = $key;
					$new->preference_value = $p[$key];
					$new->site_id = $site_id;
					$new->save();
				}
			}

			// --------------------------------------------
			//	Add curated_count to terms table
			// --------------------------------------------

			ee()->db->query("ALTER TABLE exp_super_search_terms ADD `curated_count` int(10) unsigned NOT NULL default '0'");

			// --------------------------------------------
			//	Set has_publish_fields to 'n' since EE chokes when you don't have those.
			// --------------------------------------------

			ee()->db->query(
				ee()->db->update_string(
					'exp_modules',
					array(
						'has_publish_fields'	=> 'n'
					),
					array(
						'module_name'	=> $this->class_name
					)
				)
			);
        }

		// --------------------------------------------
        //  Run module SQL - dependent on CREATE TABLE IF NOT EXISTS syntax
        // --------------------------------------------

		if (! empty($sql))
		{
			foreach ($sql as $query)
			{
				if ($query != '') ee()->db->query($query);
			}
		}

    	// --------------------------------------------
        //  Default Module Update
        // --------------------------------------------

    	$this->default_module_update();

        // --------------------------------------------
        //  Version Number Update - LAST!
        // --------------------------------------------

    	ee()->db->query(
    		ee()->db->update_string(
				'exp_modules',
				array('module_version'	=> $this->version),
				array('module_name'		=> $this->class_name)
			)
		);

		return TRUE;
    }

    //	END update()
}
//END Super_search_upd
