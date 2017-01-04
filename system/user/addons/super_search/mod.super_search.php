<?php

use Solspace\Addons\SuperSearch\Library\AddonBuilder;
use Solspace\Addons\SuperSearch\Library\Utils;

class Super_Search extends AddonBuilder
{
	public $TYPE;

	public $disable_caching			= FALSE;
	public $disable_history			= FALSE;
	public $cache_overridden		= FALSE;
	public $relevance_count_words_within_words	= FALSE;
	public $allow_wildcards 		= FALSE;
	public $allow_regex	 			= FALSE;

	// Searches on keywords that are too small
	// return too many results. We force a limit
	// on the DB query in those cases. First element
	// is the minimum keyword length, second element
	// is the limit we impose.
	public $minlength				= array(3, 10000);

	//	Same as above except that the limits when
	//	using channel:entries are much lower.
	public $wminlength				= array(3, 1000);

	public $hash					= '';
	public $history_id				= 0;

	public $ampmarker				= '45lkj78fd23klk';
	public $doubleampmarker			= '98lk7854cik3fgd9';
	public $negatemarker			= '87urnegate09u8';
	public $modifier_separator		= '-';
	public $parse_switch			= '';
	public $parser					= '&';
	public $separator				= '=';
	public $grid_field_separator	= ':';
	public $slash					= 'SLASH';
	public $spaces					= '+';
	public $pipes 					= '|';
	public $wildcard 				= '*';

	public $cur_page				= 0;
	public $current_page			= 0;
	public $limit					= 100;
	public $total_pages				= 0;
	public $page_count				= '';
	public $page_next				= '';
	public $page_previous			= '';
	public $pager					= '';
	public $paginate				= FALSE;
	public $paginate_data			= '';

	public $res_page				= '';
	public $urimarker				= 'jhgkjkajkmjksjkrlr3409oiu';
	public $how						= 'any';
	public $inclusive_keywords	 	= TRUE;
	public $inclusive_categories 	= FALSE;
	public $has_regex				= FALSE;
	public $partial_author 			= TRUE;
	public $fuzzy_weight			= 1;
	public $fuzzy_weight_default	= 0.3;
	public $fuzzy_distance			= 2;
	public $relevance_proximity_threshold 	= 999;
	public $relevance_proximity		= 0;
	public $relevance_proximity_default	= 1.3;
	public $relevance_frequency		= 1.3;

	// Enables a URI to contain multiple parameters
	// of the same type in an array manner
	public $arrays					= array();

	// Tests for simple parameters. Note that some are
	// aliases such as limit as an alias of num.
	public $basic					= array(
		'author',
		'category',
		'categorylike',
		'category_like',
		'category-like',
		'dynamic',
		'exclude_entry_ids',
		'group',
		'include_entry_ids',
		'keywords',
		'limit',
		'offset',
		'allow_repeats',
		'num',
		'order',
		'orderby',
		'search-words-within-words',
		'search_words_within_words',
		'screen_name',
		'highlight_words_within_words',
		'start',
		'status',
		'channel',
		'inclusive_keywords',
		'inclusive_categories',
		'keyword_search_author_name',
		'keyword_search_category_name',
		'use_ignore_word_list',
		'smart_excerpt',
		'search_in',
		'how',
		'partial_author',
		'relevance_proximity',
		'wildcard_character',
		'wildcard_fields',
		'tags',
		'tags-like',
		'allow_regex',
		'regex_fields',
		'fuzzy_weight',
		'fuzzy_distance',
		'site'
	);

	protected $flat					= array(
		'partial_author',
		'how'
	);

	protected 	$standard			= array(
		'entry_id',
		'title',
		'url_title',
		'author_id',
		'status',
		'year',
		'month',
		'day',
	);

	protected 	$tofromdates		= array(
		'date',
		'entry_date',
		'edit_date',
		'expiry_date',
		'comment_expiration_date',
		'recent_comment_date',
	);

	public $common					= array('a', 'and', 'of', 'or', 'the');

	// We allow field and exact field searching
	// on some of the columns in exp_channel_titles.
	public $searchable_ct			= array('entry_id', 'title', 'url_title');
	public $sess					= array();

	public $alphabet 				= "abcdefghijklmnopqrstuvwxyz";

	// keyword suggestion buffer
	public $suggested 				= array();

	// keyword correction buffer
	public $corrected 				= array();

	public $p_limit					= '';
	public $p_page					= '';

	//	Grid fields container
	public $grid_fields				= null;

	// -------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	null
	 */

	function __construct()
	{
		parent::__construct('super_search');

		if ( ! defined('T_SLASH'))
		{
			define('T_SLASH', '/');
		}

		// -------------------------------------
		// Prepare for ee()->session->cache
		// -------------------------------------

		if (isset(ee()->session->cache) === TRUE)
		{
			if (isset(ee()->session->cache['modules']['super_search']) === FALSE)
			{
				ee()->session->cache['modules']['super_search']	= array();
			}

			$this->sess	=& ee()->session->cache['modules']['super_search'];
		}

		// -------------------------------------
		//  Call the killer library and preload it
		// -------------------------------------

		$properties	= array(
			'ampmarker'			=> $this->ampmarker,
			'doubleampmarker'	=> $this->doubleampmarker,
			'negatemarker'		=> $this->negatemarker,
			'basic'				=> $this->basic,
			'flat'				=> $this->flat,
			'grid_field_separator'	=> $this->grid_field_separator,
			'date_fields'		=> array_merge($this->tofromdates, array_keys($this->_date_fields())),
			'fields'			=> array_merge($this->standard, array_keys($this->_fields()))
		);

		$this->ssu = new Utils();
		$this->ssu->set_properties($properties);
	}

	//	END Super search constructor

	// --------------------------------------------------------------------

	/**
	 * Theme Folder URL
	 *
	 * Mainly used for codepack
	 *
	 * @access	public
	 * @return	string	theme folder url with ending slash
	 */

	public function theme_folder_url()
	{
		return $this->theme_url;
	}

	//END theme_folder_url


	// -------------------------------------------------------------

	/**
	 * Cache
	 *
	 * @access	private
	 * @return	boolean
	 */

	function _cache($q = array(), $ids = array(), $results = 0, $type = 'q')
	{
		if (empty($q) === TRUE) return FALSE;

		$log	= $this->sess;

		if ($this->disable_caching === TRUE)
		{
			if (! empty($log['uri']['keywords']))
			{
				$results				= ($results == 0) ? count($ids): $results;
				$log['uri']['keywords']	= $this->ssu->convert_markers($log['uri']['keywords']);

				// Log this search
				$this->model('Data')->log_search($log, $results);
			}

			return FALSE;
		}

		$this->hash	= ($this->hash == '') ? $this->_hash_it($q): $this->hash;

		if (($cache = $this->sess('searches', $this->hash)) !== FALSE) return $cache;

		$ids		= (is_array($ids) === FALSE) ? array(): $ids;

		$results	= ($results == 0) ? count($ids): $results;

		$ids		= (empty($ids) === TRUE) ? '': $this->_cerealize($ids);

		$q	= base64_encode(serialize($q));

		$cache_id	= 0;

		if ($this->model('Data')->caching_is_enabled() === TRUE)
		{
			$sql	= ee()->db->insert_string(
				'exp_super_search_cache',
				array(
					'type'		=> $type,
					'hash'		=> $this->hash,
					'date'		=> ee()->localize->now,
					'results'	=> $results,
					'query'		=> $q,
					'ids'		=> $ids
					)
				);

			$sql	.= " /* Super Search mod.super_search.php _cache() */";

			ee()->db->query($sql);

			$cache_id	= ee()->db->insert_id();
		}


		if (! empty($this->sess['uri']['keywords']))
		{
			$log['uri']['keywords']	= $this->ssu->convert_markers($log['uri']['keywords']);
			$this->model('Data')->log_search($log, $results);
		}

		$this->_save_search_to_history($cache_id, $results, $q);

		$this->sess['searches'][$this->hash]	= $ids;

		return TRUE;
	}
	//	End cache


	// -------------------------------------------------------------

	/**
	 * _cached()
	 *
	 * @access	private
	 * @return	array
	 */

	function _cached($hash = '', $type = 'q')
	{
		if ($this->disable_caching === TRUE) return FALSE;
		if (is_string($hash) === FALSE OR $hash == '') return FALSE;
		if ($this->model('Data')->caching_is_enabled() === FALSE) return FALSE;

		if (($cache = $this->sess('searches', $hash)) !== FALSE)
		{
			return $cache;
		}

		$this->_clear_cached();

		$c	= $this->fetch('Cache')
			->filter('hash', $hash)
			->first();

		if ($c)
		{
			$cache	= (
				$c->ids == ''
			) ? array(): $this->_uncerealize($c->ids);

			$this->sess['searches'][$hash]	= $cache;
			$this->sess['results']			= $c->results;

			$this->_save_search_to_history(
				$c->cache_id,
				$c->results,
				$c->query
			);

			if (! empty($this->sess['uri']['keywords']))
			{
				// Log this search
				$this->model('Data')->log_search($this->sess, $this->sess['results']);
			}

			return $cache;
		}

		return FALSE;
	}

	//	End _cached()


	// -------------------------------------------------------------

	/**
	 * Cerealize
	 *
	 * serialize() and unserialize() add a bunch of characters that
	 * are not needed when storing a one dimensional indexed array.
	 * Why not just use a pipe?
	 *
	 * @access	private
	 * @return	string
	 */

	function _cerealize($arr = array())
	{
		if (is_array($arr) === FALSE) return '';
		if (count($arr) == 1) return array_pop($arr);
		return implode('|', $arr);
	}
	//	End cerealize


	// -------------------------------------------------------------

	/**
	 * Chars decode
	 *
	 * Preps a string from oddball EE character conversions
	 *
	 * @access	private
	 * @return	str
	 */

	function _chars_decode($str = '')
	{
		if ($str == '') return;

		if (function_exists('htmlspecialchars_decode') === TRUE)
		{
			$str	= htmlspecialchars_decode($str);
		}

		if (function_exists('html_entity_decode') === TRUE)
		{
			$str	= html_entity_decode($str);
		}

		$str	= str_replace(array('&amp;', '&#47;', '&#39;', '\''), array('&', '/', '', ''), $str);

		$str	= stripslashes($str);

		return $str;
	}
	//	End chars decode


	// -------------------------------------------------------------

	/**
	 * Check template params
	 *
	 * This method allows people to force params onto and override our query from template params
	 *
	 * @access	private
	 * @return	str
	 */

	function _check_tmpl_params($key = '', $q = array())
	{
		if ($key == '') return $q;

		// -------------------------------------
		//	We completely skip some template params.
		// -------------------------------------

		if (in_array($key, array('order', 'orderby')) === TRUE) return $q;

		// -------------------------------------
		//	We allow some dynamic params to override
		//	template params. Note that channel and site are special cases that get tested further elsewhere.
		// -------------------------------------

		if (in_array($key, array('channel', 'limit', 'num', 'start', 'offset', 'site')) === TRUE AND empty($q[$key]) === FALSE)
		{
			return $q;
		}

		if (ee()->TMPL->fetch_param($key) !== FALSE AND ee()->TMPL->fetch_param($key) != '')
		{
			$q[$key]	= ee()->TMPL->fetch_param($key);

			// -------------------------------------
			//	Prep status / group
			// -------------------------------------

			foreach (array('group', 'status') as $fix)
			{
				if ($key == $fix AND isset($q[$fix]))
				{
					// -------------------------------------
					//	Simple multi-word statuses / groups need to be protected
					// -------------------------------------
					//	When someone uses the status template parameter and they call a multi-word status, they shouldn't be expected to put that in quotes. The status is already in quotes. That would be double double quotes which would be over the top. This conditional tests for that and forces quotes around multi-word statuses so that $$ knows what to do later.
					// -------------------------------------

					if (strpos($q[$fix], '|') === FALSE AND strpos($q[$fix], '"') === FALSE AND strpos($q[$fix], "'") === FALSE AND (strpos($q[$fix], 'not ') != 0 OR strpos($q[$fix], 'not ') === FALSE) AND (strpos($q[$fix], '+') OR  strpos($q[$fix], ' ')))
					{
						if ($fix == 'status')
						{
							$this->_statuses(ee()->TMPL->site_ids);

							//last check - if we have no mulitword statuses in the system, this is redundant
							if (isset($this->sess['statuses']['multiword_status']))
							{
								//check the statuses array - if we have a straight match - wrap it up baby
								$matched = FALSE;

								foreach($this->sess['statuses']['cleaned'] AS $status)
								{
									if (strpos($q[$fix] , $status) !== FALSE AND strpos($status, "+"))
									{
										$matched = TRUE;
									}
								}

								if ($matched)
								{
									$q[$fix]	= '"' . $q[$fix] . '"';
								}
							}
						}
						else
						{
							//apply this blindly for status groups
							$q[$fix]	= '"' . $q[$fix] . '"';
						}
					}
				}
			}
		}

		if (isset($q[$key]) AND strpos($q[$key], '&&') !== FALSE)
		{
			$q[$key]	= str_replace('&&', $this->doubleampmarker, $q[$key]);
		}

		if (isset($q[$key]) === TRUE AND (strpos($q[$key], $this->slash) !== FALSE OR strpos($q[$key], SLASH) !== FALSE))
		{
			$q[$key]	= str_replace(array($this->slash, SLASH), '/', $q[$key]);
		}

		// -------------------------------------
		//	We convert the negation marker, which is a dash, to something obscure so that regular dashes in words do not create problems.
		// -------------------------------------

		if (isset($q[$key]) AND strpos($q[$key], '-') === 0)
		{
			$q[$key]	= $this->separator.$this->negatemarker . trim($q[$key], '-');
		}

		if ($key == 'orderby' AND empty($q['orderby']) === FALSE)
		{
			$q['order']	= $this->_prep_order($q['orderby']);
		}
		elseif ($key == 'order' AND empty($q['order']) === FALSE)
		{
			$q['order']	= $this->_prep_order($q['order']);
		}

		return $q;
	}

	//	End check template params

	// -------------------------------------------------------------

	/**
	 * Clean numeric fields
	 *
	 * @access	private
	 * @return	array
	 */

	function _clean_numeric_fields($arr = array())
	{
		// -------------------------------------
		//	For each field...
		// -------------------------------------

		foreach (array_keys($arr) as $key)
		{
			// -------------------------------------
			//	For each element
			// -------------------------------------

			foreach ($arr[$key] as $k => $v)
			{
				// -------------------------------------
				//	If field expects numeric, try to convert punctuation. This is also the place to handle European monetary formats, but later.
				// -------------------------------------

				if ($this->_get_field_type($key) !== FALSE AND $this->_get_field_type($key) == 'numeric')
				{
					$arr[$key][$k]	= str_replace(array(',', '$'), '', $v);
				}
			}
		}

		return $arr;
	}

	//	End clean numeric fields

	// -------------------------------------------------------------

	/**
	 * Clear cached
	 *
	 * @access	private
	 * @return	array
	 */

	function _clear_cached()
	{
		if ($this->sess('cleared') !== FALSE) return FALSE;

		// -------------------------------------
		//	Should we refresh cache? Have we cleared it recently?
		// -------------------------------------

		if ($this->model('Data')->time_to_refresh_cache(ee()->config->item('site_id')) === FALSE)
		{
			$this->sess['cleared']	= TRUE;
			return FALSE;
		}

		// -------------------------------------
		//	Clear cache now
		// -------------------------------------

		do
		{
			ee()->db->query(
				"DELETE FROM exp_super_search_cache
				WHERE date < ".ee()->db->escape_str((ee()->localize->now - ($this->model('Data')->get_refresh_by_site_id(ee()->config->item('site_id')) * 60)))."
				LIMIT 1000 /* Super Search delete cache */"
			);
		}
		while (ee()->db->affected_rows() == 1000);

		do
		{
			ee()->db->query(" DELETE FROM exp_super_search_history
						WHERE search_date < ".ee()->db->escape_str((ee()->localize->now - (60 * 60)))."
						AND saved = 'n'
						AND cache_id NOT IN (
							SELECT cache_id
							FROM exp_super_search_cache)
						LIMIT 1000 /* Super Search clear search history */");
		}
		while (ee()->db->affected_rows() == 1000);

		$this->model('Data')->set_new_refresh_date(ee()->config->item('site_id'));
		$hash	= $this->model('Data')->_imploder(array(ee()->config->item('site_id')));
		$this->model('Data')->cached['time_to_refresh_cache'][$hash] = FALSE;
		$this->sess['cleared']	= TRUE;
	}
	//	End clear cached


	// -------------------------------------------------------------

	/**
	 * dd()
	 *
	 * @access	public
	 * @return	array
	 */

	function dd($q)
	{
		print_r($q); exit();
	}

	//	End dd()

	// -------------------------------------------------------------

	/**
	 * Do search
	 *
	 * This is the carburator. Feed a parsed URI into this rascal, output is entry ids.
	 *
	 * @access	public
	 * @return	array
	 */

	function do_search($q = array())
	{
		if (empty($q))
		{
			$this->_cache($this->sess('uri'), '', 0);
			return FALSE;
		}

		// -------------------------------------
		//	If dynamic mode has been turned off,
		//	we need to clear the session cached
		//	vars and start over.
		// -------------------------------------

		if (ee()->TMPL->fetch_param('dynamic') !== FALSE AND
			$this->check_no(ee()->TMPL->fetch_param('dynamic'))
		)
		{
			$this->sess['search']	= array();
		}

		// -------------------------------------
		//	Benchmarking
		// -------------------------------------

		$t	= microtime(TRUE);
		ee()->TMPL->log_item('Super Search: Starting do_search()');
		ee()->TMPL->log_item('Super Search: Starting _prep_query()');

		// -------------------------------------
		//	Prep query into something more SQL-able
		// -------------------------------------

		if (($search = $this->_prep_query($q)) === FALSE)
		{
			$this->_cache($this->sess('uri'), '', 0);
			return FALSE;
		}

		// -------------------------------------
		//	Benchmark
		// -------------------------------------

		ee()->TMPL->log_item('Super Search: Ending prep query('.(microtime(TRUE) - $t).')');

		// -------------------------------------
		//	Move our work thusfar into the cached version of q
		// -------------------------------------

		$this->sess['search']['q']	= $search;

		// -------------------------------------
		//	'super_search_extra_basic_fields' hook.
		// -------------------------------------
		//	We call this multiple times so that
		//	people can have $$ recognize extra
		//	arguments in template params and uri.
		// -------------------------------------

		if (ee()->extensions->active_hook('super_search_extra_basic_fields') === TRUE)
		{
			$basic_fields = ee()->extensions->call('super_search_extra_basic_fields', $this);

			foreach ($basic_fields as $bf)
			{
				if (isset($q[$bf]) === TRUE)
				{
					$this->sess['search']['q'][$bf]	= $this->_prep_keywords($q[$bf]);
				}
			}
		}

		// -------------------------------------
		//	Exclude entries already found in
		//	previous calls to Super Search during
		//	the same session.
		// -------------------------------------

		if (
			isset($q['allow_repeats']) === TRUE AND
			$q['allow_repeats'] == 'no'
			AND ! empty($this->sess['previous_entries'])
		)
		{
			$previous_entries = $this->_only_numeric($this->sess['previous_entries']);

			sort($previous_entries);
			$this->sess['search']['q']['previous_entries']	= $previous_entries;

			if (! empty($this->sess['search']['q']['exclude_entry_ids']))
			{
				$this->sess['search']['q']['exclude_entry_ids']	= array_merge(
					$this->sess['search']['q']['exclude_entry_ids'],
					$previous_entries
				);
			}
			else
			{
				$this->sess['search']['q']['exclude_entry_ids']	= $previous_entries;
			}
		}

		// -------------------------------------
		//	'super_search_alter_search' hook.
		// -------------------------------------
		//	All the arguments are saved to the
		//	ee()->session->cache, read and write
		//	to that array.
		// -------------------------------------

		if (ee()->extensions->active_hook('super_search_alter_search') === TRUE)
		{
			$edata = ee()->extensions->call('super_search_alter_search', $this);
			if (ee()->extensions->end_script === TRUE)
			{
				$this->_cache($this->sess('uri'), '', 0);
				return FALSE;
			}
		}

		// -------------------------------------
		//	Do we have a valid search?
		// -------------------------------------

		if (empty($this->sess['search']['q']))
		{
			$this->_cache($this->sess('uri'), '', 0);
			return FALSE;
		}

		// -------------------------------------
		//	Does our search turn into valid SQL?
		// -------------------------------------

		if (($sql = $this->_prep_search_for_sql($q)) === FALSE)
		{
			$this->_cache($this->sess('uri'), '', 0);
			return FALSE;
		}

		// -------------------------------------
		// Do we have any regex searches - new in 2.0?
		// if we do, we need to be extra careful with
		// our db query. They could be passing a bad
		// regex, and that'll CRASH EVERYTHING.
		// so, at the cost of an extra query,
		// we can protect ourselves from it.
		// -------------------------------------

		$query_checked = NULL;

		if ($this->has_regex)
		{
			$query_checked = $this->model('Data')->check_sql($sql);

			if ($query_checked === FALSE)
			{
				// Whoa there bad boy.

				// This is a bad sql string.
				// return false rather than running it.
				// Maybe they're doing something they shouldn't be

				$this->_cache($this->sess('uri'), '', 0);
				return FALSE;
			}
		}

		// If we've already run this query don't repeat ourselves
		if ($query_checked !== NULL)
		{
			$query = $query_checked;
		}
		else
		{
			// -------------------------------------
			//	Hit the DB - this is the core, master, main, primary SuperSearch query
			// -------------------------------------

			//print_r($sql); print_r('<hr />');

			$query	= ee()->db->query($sql);

			//$this->dd($query->result_array());
		}

		$this->sess['results']	= $query->num_rows();

		// -------------------------------------
		//	Ordering by relevance?
		// -------------------------------------
		//	We load the entry ids into an array.
		//	We group them by their relevance count.
		//	Then within that grouping, we let them
		//	retain their order based on the other
		//	supplied order params from elsewhere.
		//	The we sort on the relevance key.
		//	Then we loop the ids back out into a
		//	normal array and hand that off to $$
		//	to do with it what it will. This causes
		//	entries sharing a given relevance count
		//	to not lose their secondary and
		//	tertiary sorting.
		// -------------------------------------

		$ids	= array();

		if ((! empty($this->sess['search']['q']['keywords']['or'])
				OR ! empty($this->sess['search']['q']['keywords']['and']))
			AND (isset($this->sess['search']['q']['relevance']) !== FALSE
				  AND !empty($this->sess['search']['q']['relevance'])))
		{
			foreach ($query->result_array() as $row)
			{
				$count	= $this->_relevance_count($row);

				$rel[(string) $count][$row['entry_id']]	= $row['entry_id'];
			}

			if (! empty($rel))
			{
				krsort($rel, SORT_NUMERIC);

				foreach ($rel as $cnt => $temp)
				{
					$ids	= array_merge($ids, $temp);
				}

				unset($rel);
			}
		}
		else
		{
			foreach ($query->result_array() as $row)
			{
				$ids[]	= $row['entry_id'];
			}
		}

		if ($this->sess['results'] == 0 AND
			ee()->extensions->active_hook('super_search_alter_ids') === FALSE)
		{
			$this->_cache($this->sess('uri'), '', 0);
			return FALSE;
		}

		// -------------------------------------
		//	'super_search_alter_ids' hook.
		// -------------------------------------
		//	Alter the master list of ids.
		// -------------------------------------

		$pure_ids	= array_values(array_unique($ids));

		if (ee()->extensions->active_hook('super_search_alter_ids') === TRUE)
		{
			$ext_ids = ee()->extensions->call('super_search_alter_ids', $ids, $this);

			//double bag it
			if (is_array($ext_ids))
			{
				$ids = $ext_ids;
			}

			if (ee()->extensions->end_script === TRUE)
			{
				$this->_cache($this->sess('uri'), '', 0);
				return FALSE;
			}

			// -------------------------------------
			//	If include_entry_ids has been provided,
			//	then only those ids are eligible.
			//	Whatever the extension is sending
			//	to us must meet that requirement
			// -------------------------------------

			if (! empty($this->sess['search']['q']['include_entry_ids']))
			{
				$ids	= array_intersect($ids, $this->sess['search']['q']['include_entry_ids']);

				if (empty($ids))
				{
					$this->_cache($this->sess('uri'), '', 0);
					return FALSE;
				}
			}
		}

		// -------------------------------------
		//	Make unique
		// -------------------------------------

		$ids	= array_unique($ids);

		// -------------------------------------
		//	'super_search_alter_ids' hook cleanup
		// -------------------------------------
		//	Reorder the list if we've had changes
		// -------------------------------------

		if (ee()->extensions->active_hook('super_search_alter_ids') === TRUE AND
			$pure_ids != $ids)
		{
			// Some third_party has interferred with our $ids
			// Curse those third_parties!!!
			// If we have an order param in our $q, we need to resort

			if (isset($this->sess['search']['q']['order']) AND
				! empty($this->sess['search']['q']['order']))
			{
				// If we're ordering by relevance we don't really want to reorder
				// again. The hook has fired and we now have a polluted id list
				// We can't reorder it by relevance again as we have no way to
				// calculate the relevance on these new 'alien' ids.
				if (! empty($this->sess['search']['q']['relevance']))
				{
					$sql =	"SELECT  t.entry_id " .
							implode(' ', ee()->db->escape_str($this->sess['search']['from'])) .
							" WHERE t.entry_id" .
							" IN (". implode(',', ee()->db->escape_str($ids)) . ") " .
							ee()->db->escape_str($this->sess['search']['q']['order']);

					$sql	= str_replace('%indexes%', '', $sql);

					$query = ee()->db->query($sql);

					$ids = array();

					foreach($query->result_array() AS $result)
					{
						$ids[] = $result['entry_id'];
					}
				}
			}
		}

		// -------------------------------------
		//	Save to cache
		// -------------------------------------

		$this->sess['results']	= count($ids);

		if ($this->sess['results'] == 0)
		{
			$this->_cache($this->sess('uri'), '', 0);
			return FALSE;
		}

		$this->_cache($this->sess('uri'), $ids, $this->sess['results']);

		// -------------------------------------
		//	Return ids
		// -------------------------------------

		ee()->TMPL->log_item('Super Search: Ending do_search('.(microtime(TRUE) - $t).' Results '.$query->num_rows().')');

		return $ids;
	}
	//	End do search


	// -------------------------------------------------------------

	/**
	 * Entries
	 *
	 * @access	public
	 * @return	string
	 */

	function _entries ($ids = array(), $params = array())
	{
		$t	= microtime(TRUE);

		ee()->TMPL->log_item('Super Search: Starting _entries()');

		// -------------------------------------
		//	Execute?
		// -------------------------------------

		if (count($ids) == 0) return FALSE;

		// -------------------------------------
		//	Parse search total
		// -------------------------------------

		$prefix	= $this->either_or(ee()->TMPL->fetch_param('prefix'), 'super_search_');

		// -------------------------------------
		//	Invoke channel class
		// -------------------------------------

		if (class_exists('Channel') === FALSE)
		{
			require PATH_MOD.'channel/mod.channel.php';
		}

		$channel = new Channel;

		$channel = $this->add_pag_to_channel($channel);

		// -------------------------------------
		//	Plant a flag and claim $$ ownership
		//	of the $channel object we created for
		//	use in the $$ extension in the
		//	channel_entries_query_result() method.
		// -------------------------------------

		$channel->is_super_search	= TRUE;

		// -------------------------------------
		//	Invoke typography if necessary
		// -------------------------------------

		ee()->load->library('typography');
		ee()->typography->initialize();
		ee()->typography->convert_curly = FALSE;

		// -------------------------------------
		//	Alias tag params. Template params trump URI params
		// -------------------------------------

		foreach (array('num' => 'limit', 'start' => 'offset') as $key => $val)
		{
			// -------------------------------------
			//	We prefer to find the array value as a template param
			// -------------------------------------

			if (isset(ee()->TMPL->tagparams[$val]))
			{
				unset(ee()->TMPL->tagparams[$key]);
			}

			// -------------------------------------
			//	We'll accept the array key as a template param next
			// -------------------------------------

			if (isset(ee()->TMPL->tagparams[$key]))
			{
				ee()->TMPL->tagparams[$val]	= ee()->TMPL->tagparams[$key];
			}

			// -------------------------------------
			//	We'll next accept our array val as a URI param
			// -------------------------------------

			if (isset($this->sess['uri'][$val]))
			{
				ee()->TMPL->tagparams[$val]	= $this->sess['uri'][$val];
				unset(ee()->TMPL->tagparams[$key]);
			}

			// -------------------------------------
			//	We'll next accept our array key as a URI param
			// -------------------------------------

			if (isset($this->sess['uri'][$key]))
			{
				ee()->TMPL->tagparams[$val]	= $this->sess['uri'][$key];
				unset(ee()->TMPL->tagparams[$key]);
			}
		}

		// -------------------------------------
		//	Force limits?
		// -------------------------------------

		if (($keywords = $this->sess('search', 'q', 'keywords', 'or')) !== FALSE)
		{
			$limit	= (
				! empty(ee()->TMPL->tagparams['limit'])
			) ? ee()->TMPL->tagparams['limit']: '';

			foreach ($keywords as $k)
			{
				if (strlen($k) < $this->wminlength[0])
				{
					if ($limit > $this->wminlength[1])
					{
						$limit	= $this->wminlength[1];
					}
				}
			}

			ee()->TMPL->tagparams['limit']	= (
				count($ids) > $limit
			) ? $limit: count($ids);
		}

		// -------------------------------------
		//	Last limit check, template param
		//	override, undocumented
		// -------------------------------------

		if (isset(ee()->TMPL->tagparams['limit']) AND
			isset(ee()->TMPL->tagparams['max_limit']) AND
			ee()->TMPL->tagparams['max_limit'] < ee()->TMPL->tagparams['limit']
		)
		{
			ee()->TMPL->tagparams['limit']	= ee()->TMPL->tagparams['max_limit'];
		}

		// -------------------------------------
		//	And lastly a safeguard for the Nevin's of the world
		// -------------------------------------

		if (isset(ee()->TMPL->tagparams['limit']) AND ee()->TMPL->tagparams['limit'] > $this->minlength[1])
		{
			ee()->TMPL->tagparams['limit']	= $this->minlength[1];
		}

		// -------------------------------------
		//	Pass params
		// -------------------------------------

		// This forces exp:channel:entries to ignore
		// the category param. People can provide a
		// category in the param, but $$ knows what
		// to do with it and should not be bothered
		// by native EE.
		ee()->TMPL->tagparams['category']	= '';
		ee()->TMPL->tagparams['inclusive']	= '';

		ee()->TMPL->tagparams['show_pages']	= 'all';

		ee()->TMPL->tagparams['dynamic']	= 'no';

		// -------------------------------------
		//	Force status
		// -------------------------------------
		//	Someone's query could call for a combo
		//	of possible statuses. This would return
		//	a number of search results greater than
		//	that which EE would show if we did not
		//	force those same statuses into the status
		//	template param.
		// -------------------------------------

		if (! empty($this->sess['search']['q']['status']['not']))
		{
			ee()->TMPL->tagparams['status']	= 'not ' . implode('|', $this->sess['search']['q']['status']['not']);
		}
		elseif (! empty($this->sess['search']['q']['status']['or']))
		{
			ee()->TMPL->tagparams['status']	= implode('|', $this->sess['search']['q']['status']['or']);
		}

		// -------------------------------------
		//	When we're doing expiration date range searching, we want EE's defaults about expiration dates to be ignored. Since we're searching on expiration date range, we obviously don't want to exclude an entry just because it's expired.
		// -------------------------------------

		if (! empty($this->sess['search']['q']['expiry_datefrom']) OR ! empty($this->sess['search']['q']['expiry_dateto']))
		{
			ee()->TMPL->tagparams['show_expired']	= 'yes';
		}

		// -------------------------------------
		//	Load params
		// -------------------------------------

		foreach ($params as $key => $val)
		{
			ee()->TMPL->tagparams[$key]	= $val;
		}

		// -------------------------------------
		//	Pre-process related data
		// -------------------------------------

		if (version_compare($this->ee_version, '2.6.0', '<'))
		{
			$this->EE->TMPL->tagdata = $this->EE->TMPL->assign_relationship_data(
				$this->EE->TMPL->tagdata
			);
		}

		$this->EE->TMPL->var_single	= array_merge(
			$this->EE->TMPL->var_single,
			$this->EE->TMPL->related_markers
		);

		// -------------------------------------
		//	Execute needed methods
		// -------------------------------------

		$channel->fetch_custom_channel_fields();
		$channel->fetch_custom_member_fields();

		// -------------------------------------
		//  Pagination Tags Parsed Out
		// -------------------------------------

		$channel = $this->fetch_pagination_data($channel);

		// -------------------------------------
		//	Prep pagination
		// -------------------------------------
		//	We like to use the 'offset' (or 'start')
		//	param to tell pagination which page we want.
		//	EE uses P20 or the like. Let's allow
		//	someone to use our 'offset' (or 'start')
		//	param in the context of performance off,
		//	but only when the standard EE pagination
		//	indicator is absent from the QSTR.
		// -------------------------------------

		if (isset($this->sess['newuri']) === TRUE)
		{
			if (strpos($this->sess['newuri'], '/') !== FALSE)
			{
				$this->sess['newuri'] = str_replace(
					'/',
					$this->slash,
					$this->sess['newuri']
				);
			}

			// -------------------------------------
			//	Exception for people using the 'search' parameter
			// -------------------------------------

			if (ee()->TMPL->fetch_param('search') !== FALSE AND
				ee()->TMPL->fetch_param('search') != '' AND
				preg_match(
					'/offset' . $this->separator . '(\d+)?/s',
					$this->sess['newuri'],
					$match
				)
			)
			{
				$this->sess['newuri']	= 'search' . $this->parser . 'offset' . $this->separator . $match['1'];
			}

			// -------------------------------------
			//	Force paginate base
			// -------------------------------------

			if (ee()->TMPL->fetch_param('paginate_base') !== FALSE AND
				ee()->TMPL->fetch_param('paginate_base') != '')
			{
				ee()->TMPL->tagparams['paginate_base']	= rtrim(
					ee()->TMPL->fetch_param('paginate_base'),
					'/'
				) . '/' . ltrim($this->sess['newuri'], '/');
			}
			else
			{
				// -------------------------------------
				//	If someone is using the template param
				//	alled 'search' they may not have a full
				//	URI saved in sess['olduri'] so we try to
				//	fake it. The better approach is for them
				//	to use the paginate_base param above.
				// -------------------------------------

				if (ee()->TMPL->fetch_param('search') !== FALSE AND
					ee()->TMPL->fetch_param('search') != '' AND
					isset(ee()->uri->segments[1]) === TRUE AND
					strpos($this->sess['olduri'], ee()->uri->segments[1]) !== 0
				)
				{
					$temp[]	= ee()->uri->segments[1];

					if (isset(ee()->uri->segments[2]) === TRUE)
					{
						$temp[]	= ee()->uri->segments[2];
					}

					$temp[]	= $this->sess['olduri'];

					$this->sess['olduri']	= implode('/', $temp);
				}

				// -------------------------------------
				//	Force paginate_base
				// -------------------------------------
				//	If we don't tell EE otherwise, it will
				//	try and generate pagination links using
				//	what it thinks is the page URI. And when
				//	it does, it runs that string through some
				//	heavy duty filters that strip out important
				//	characters like single and double quotes.
				//	We run our own sanitize methods on the URI
				//	and need to force our version into the
				//	pagination engine, otherwise people's
				//	pagination links will have vital data
				//	stripped out and they will lose their
				//	search filters.
				// -------------------------------------

				ee()->TMPL->tagparams['paginate_base']	= $this->_prep_paginate_base();
			}
		}

		//Previous to SuperSearch 1.4 pagination was calculated right here.
		//This didn't take into account any additional search params
		//that were passed as part of the tmpl
		//We've moved it later on, but just incase
		//we ever need to reproduce that behviour, this comment
		//will stand as a memorium for it

		// -------------------------------------
		//	Trim the $ids array down to only what
		//	is called for through pagination or
		//	our upper limits in order to improve
		//	performance.
		// -------------------------------------

		$limit = (isset($limit) AND is_numeric($limit)) ? $limit: $this->wminlength[1];
		$start = (isset($start) AND is_numeric($start)) ? $start: 0;

		// -------------------------------------
		//	I had to comment this trimming code out.
		//	I was trying not to send too many entry
		//	ids over to the weblog / channel for parsing.
		//	But if I don't send a complete set over,
		//	pagination will not be built properly over
		//	there. For now, we have to sacrifice
		//	performance for correctly functioning
		//	pagination. - mk
		// -------------------------------------

		$search_total	= count($ids);

		// $ids	= array_slice($ids, $start, $limit);

		// -------------------------------------
		//	Load entry ids into tagparam so that EE will know what to do for us.
		// -------------------------------------

		ee()->TMPL->tagparams['fixed_order']	= $this->_cerealize($ids);

		// -------------------------------------
		//	Grab entry data
		// -------------------------------------

		$channel->build_sql_query();

		$channel->pagination->total_rows	= $search_total;

		if ($channel->sql == '')
		{
			return FALSE;
		}

		$channel->query = ee()->db->query($channel->sql);

		if ($channel->query->num_rows() == 0)
		{
			ee()->TMPL->log_item('Super Search: Ending _entries('.(microtime(TRUE) - $t).')');
			return FALSE;
		}

		$used_ids	= array();

		// -------------------------------------
		//	Prep relevance
		// -------------------------------------

		$relevance	= $this->_prep_relevance();

		// -------------------------------------
		//	If someone uses the search:body="something"
		//	template param, the counts can be thrown
		//	off. This conditional is a patch that
		//	will catch some of the cases.
		// -------------------------------------

		$QSTR	= $channel->query_string;

		if (! preg_match("#^P(\d+)|/P(\d+)#", $channel->query_string, $match))
		{
			if (($start = $this->sess('uri', 'offset')) !== FALSE)
			{
				$channel->query_string	= rtrim($channel->query_string, '/') . '/P' . $start;
			}
		}
		else
		{
			$start	= (! empty($match['1'])) ? $match['1']: $match['0'];
		}

		// After EE 2.4, it goes after as build() is the one that sets ->total_rows
		if ($channel->pagination->total_rows > $search_total)
		{
			$search_total = $channel->pagination->total_rows;
		}

		$transfer = array(
			'total_pages'	=> 'total_pages',
			'current_page'	=> 'current_page',
			'offset'		=> 'offset',
			'page_next'		=> 'page_next',
			'page_previous'	=> 'page_previous',
			'page_links'	=> 'pagination_links', // different!
			'total_rows'	=> 'total_rows',
			'per_page'		=> 'per_page'
		);

		foreach($transfer as $from => $to)
		{
			$channel->$to = $channel->pagination->$from;
		}

		$channel->query_string	= $QSTR;

		//	$channel->build_sql_query() rewrites our total_pages.
		//	So we save our version and then reset it after
		//	$channel->build_sql_query() runs.
		$total_pages_from_channel	= $channel->total_pages;
		$current_page_from_channel	= $channel->current_page;
		$sites_cache = array();

		//	If this goes first, before total_results, total_results will not get confused by curated_result totals
		if (isset($this->sess['total_curated_results']) AND strpos(ee()->TMPL->template, LD.'super_search_total_curated_results'.RD) !== FALSE)
		{
			ee()->TMPL->template = str_replace(
				LD . 'super_search_total_curated_results' . RD,
				$this->sess['total_curated_results'],
				ee()->TMPL->template
			);
			unset($this->sess['total_curated_results']);
		}

		if (isset($this->sess['total_results']) AND strpos(ee()->TMPL->template, LD.'super_search_total_results'.RD) !== FALSE)
		{
			ee()->TMPL->template = str_replace(
				LD . 'super_search_total_results' . RD,
				$this->sess['total_results'],
				ee()->TMPL->template
			);
			unset($this->sess['total_results']);
		}

		// -------------------------------------
		//	Inject additional vars
		// -------------------------------------

		$previous_title_letter	= '';

		foreach ($channel->query->result_array() as $key => $row)
		{
			$used_ids[]							= $row['entry_id'];
			$row['super_search_total_results']	= $search_total;
			$row['super_search_keywords_url']	= '';
			$row['super_search_keywords']		= '';

			if (! empty($this->sess['uri']['keywords']))
			{
				$temp	= $this->ssu->convert_markers($this->sess['uri']['keywords']);
				$row['super_search_keywords_url']	= $temp;
				$row['super_search_keywords']		= str_replace($this->spaces, ' ', $temp);
			}

			// -------------------------------------
			//	Prepare relevance count
			// -------------------------------------

			if (isset($relevance) === TRUE AND $relevance !== FALSE)
			{
				$row['relevance_count']	= $this->_relevance_count($row);
			}
			else
			{
				$row['relevance_count']	= '';
			}

			// -------------------------------------
			//	Prepare auto_path
			// -------------------------------------

			$channel_ids = $this->_channel_ids();

			$path = '';

			if (isset($channel_ids[$row ['channel_id']]))
			{
				$path = (
					empty($channel_ids[$row['channel_id']]['search_results_url'])
				) ?
					$channel_ids[$row['channel_id']]['channel_url'] :
					$channel_ids[$row['channel_id']]['search_results_url'];
			}

			$row['auto_path']	= rtrim($path, '/') . '/' . $row['url_title'];

			// -------------------------------------
			//	Highlight keywords in searchable fields
			// -------------------------------------

			foreach($this->sess['fields']['searchable'] AS $field_name => $field_id)
			{
				//Special handling for the title field
				if ($field_name == 'title')
				{
					if (! empty($row[$field_name]))
					{
						$row['title']	= $this->_highlight_keywords($row['title']);
					}
				}
				elseif (is_numeric($field_id))
				{
					//Handle the case of duplicate channel names across MSM sites
					if (array_key_exists(
							'supersearch_msm_duplicate_fields',
							$this->sess['fields']['searchable']
						) AND
						array_key_exists(
							$field_name ,
							$this->sess['fields']['searchable']['supersearch_msm_duplicate_fields']
						)
					)
					{
						foreach($this->sess['fields']['searchable']['supersearch_msm_duplicate_fields'][$field_name] AS $subkey)
						{
							if (! empty($row['field_id_'.$subkey]))
							{
								$row['field_id_'.$subkey]	= $this->_highlight_keywords($row['field_id_'.$subkey]);
							}
						}
					}
					else
					{
						if (! empty($row['field_id_'.$field_id]))
						{
							$row['field_id_'.$field_id]	= $this->_highlight_keywords($row['field_id_'.$field_id]);
						}
					}
				}
			}

			// -------------------------------------
			//	Check for excerpt
			// -------------------------------------

			if (! empty($this->sess['search']['channels'][$row['channel_id']]['search_excerpt']))
			{
				if ($this->sess['search']['channels'][$row['channel_id']]['search_excerpt'] != 0)
				{
					$field_id		= $this->sess['search']['channels'][$row['channel_id']]['search_excerpt'];

					$excerpt	= strip_tags($row['field_id_' . $field_id]);
					$excerpt_before	= trim(preg_replace("/(\015\012)|(\015)|(\012)/", " ", $excerpt));

					// Check our default site setting
					$use_smart_excerpt = ($this->_pref('enable_smart_excerpt') != 'n') ? TRUE : FALSE;

					// Let this be overridden from the template
					if (isset($this->sess['uri']['smart_excerpt']))
					{
						if ($this->check_no($this->sess['uri']['smart_excerpt']))
						{
							$use_smart_excerpt = FALSE;
						}
						elseif ($this->check_yes($this->sess['uri']['smart_excerpt']))
						{
							$use_smart_excerpt = TRUE;
						}
						elseif ($this->sess['uri']['smart_excerpt'] == 'toggle')
						{
							$use_smart_excerpt = !$use_smart_excerpt;
						}
					}

					if ($use_smart_excerpt)
					{
						$keywords = (isset($this->sess['search']['q']['keywords'])) ? $this->sess['search']['q']['keywords'] : array();

						$excerpt = $this->_smart_excerpt($excerpt_before, $keywords, 50);
					}
					else
					{
						$excerpt	= ee()->functions->word_limiter($excerpt_before, 50);
					}

					$field_content = ee()->typography->parse_type(
						$excerpt,
						array(
							'text_format'		=> (
								isset($row['field_ft_' . $field_id])
							) ?
								$row['field_ft_' . $field_id] :
								'none',

							'html_format'		=> (
								isset($channels[$row['channel_id']])
							) ?
								$channels[$row['channel_id']]['channel_html_formatting'] :
								'all',

							'auto_links'		=> (
								isset($channels[$row['channel_id']])
							) ?
								$channels[$row['channel_id']]['channel_auto_link_urls'] :
								'n',

							'allow_img_url'		=> (
								isset($channels[$row['channel_id']])
							) ?
								$channels[$row['channel_id']]['channel_allow_img_urls'] :
								'y'
						)
					);

					// -------------------------------------
					//	Highlight keywords
					// -------------------------------------

					$field_content	= $this->_highlight_keywords($field_content);

					$row['excerpt']	= $field_content;
				}
			}

			// This patches a problem that I could not
			// find in _highlight_keywords() where
			// sometimes a string would not be returned.
			$row['excerpt']	= (
				isset($row['excerpt']) === FALSE OR
				is_string($row['excerpt']) === FALSE
			) ? '': $row['excerpt'];

			// -------------------------------------
			//	Add additional MSM values
			// -------------------------------------

			if ($row['entry_site_id'] == ee()->config->item('site_id'))
			{
				$row['entry_site_name']			= ee()->config->item('site_name');
				$row['entry_site_label']		= ee()->config->item('site_name');
				$row['entry_site_description']	= ee()->config->item('site_description');
				$row['entry_site_short_name']	= ee()->config->item('site_short_name');
				$row['entry_site_url']			= ee()->config->item('site_url');
			}
			else
			{
				if (count($sites_cache) < 1)
				{
					// We'll have to dig these details out unfortunately

					$squery = ee()->db->select(
						implode(
							', ',
							array(
								'site_id',
								'site_label',
								'site_name',
								'site_description',
								'site_system_preferences'
							)
						)
					)->get('sites');

					foreach($squery->result_array() as $srow)
					{
						$srow['site_system_preferences'] = unserialize(
							base64_decode(
								$srow['site_system_preferences']
							)
						);

						$sites_cache[$srow['site_id']] = $srow;
					}
				}

				$esid = $row['entry_site_id'];

				$row['entry_site_name'] 		= $sites_cache[$esid]['site_label'];
				$row['entry_site_label']		= $sites_cache[$esid]['site_label'];
				$row['entry_site_description']	= $sites_cache[$esid]['site_description'];
				$row['entry_site_short_name']	= $sites_cache[$esid]['site_name'];
				$row['entry_site_url']			= $sites_cache[$esid]['site_system_preferences']['site_url'];
			}

			// -------------------------------------
			//	Set the first letter variable
			// -------------------------------------

			$row[$prefix.'previous_title_letter']	= $previous_title_letter;
			$row[$prefix.'current_title_letter']	= $previous_title_letter = strtoupper(substr($row['title'], 0, 1));

			// -------------------------------------
			//	Manipulate $row
			// -------------------------------------

			if (ee()->extensions->active_hook('super_search_entries_row_inject') === TRUE)
			{
				$row	= ee()->extensions->call('super_search_entries_row_inject', $this, $row);
			}

			// -------------------------------------
			//	Reload
			// -------------------------------------

			$channel->query->result[$key]	= $row;
		}

		unset($sites_cache);

		// -------------------------------------
		//	Let's save this channel query object
		//	to get around an EE 2 problem with result_array().
		// -------------------------------------

		$this->sess['channel_query_object']	= $channel->query;

		// -------------------------------------
		//	Save ids so that our allow_repeats param
		//	will work. This lets is exclude entries
		//	from showing again in the same session
		//	if we have already retrieved them. This
		//	is dependent on the linear parsing order
		//	of course. You can't know what a later
		//	super search call will retrieve and you
		//	don't care. Linear is sufficient.
		// -------------------------------------

		if (empty($this->sess['previous_entries']))
		{
			$this->sess['previous_entries']	= array();
		}

		$this->sess['previous_entries']	= array_merge(
			$this->sess['previous_entries'],
			array_unique($used_ids));

		if (ee()->TMPL->fetch_param('disable') === FALSE OR
			ee()->TMPL->fetch_param('disable') == '' OR
			strpos(ee()->TMPL->fetch_param('disable'), 'categories') === FALSE
		)
		{
			$channel->fetch_categories();
		}

		// -------------------------------------
		//	Parse and return entry data
		// -------------------------------------

		$channel->parse_channel_entries();

		$channel = $this->add_pagination_data($channel);

		// -------------------------------------
		//	Note the trick here with unsetting
		//	our little critter variable
		//	$channel->is_super_search.
		// -------------------------------------

		unset($channel->is_super_search);

		if (version_compare($this->ee_version, '2.6.0', '<'))
		{
			if (count($this->EE->TMPL->related_data) > 0 AND
				count($channel->related_entries) > 0)
			{
				$channel->parse_related_entries();
			}

			// -------------------------------------
			//	Reverse related entries
			// -------------------------------------

			if (count($this->EE->TMPL->reverse_related_data) > 0 AND
				count($channel->reverse_related_entries) > 0)
			{
				$channel->parse_reverse_related_entries();
			}
		}

		$channel->is_super_search	= TRUE;

		$tagdata = $channel->return_data;

		ee()->TMPL->log_item('Super Search: Ending _entries('.(microtime(TRUE) - $t).')');

		return $tagdata;
	}
	//	End entries


	/**
	 * And Or Not
	 *
	 * @access	public
	 * @param	array	$q	array of items to build into an and or string
	 * @return	mixed		empty string if not array or array
	 */
	public function andornot($q = array())
	{
		$temp	= array();

		if (empty($q) OR is_array($q) === FALSE)
		{
			return '';
		}

		foreach ($q as $key => $arr)
		{
			if ($key == 'and' AND ! empty($arr))
			{
				if (is_array($arr) === TRUE)
				{
					foreach ($arr as $v)
					{
						// handle the case where we have a search
						// 'phrase' that would have been in "quotes"
						if (is_string($v) === TRUE AND
							strpos($v , ' ') !== FALSE)
						{
							$v = '&quot;'. $v . '&quot;';
						}

						$temp[]	=	$v;
					}
				}
				else
				{
					// handle the case where we have a search
					// 'phrase' that would have been in "quotes"
					if (strpos($arr, ' ') !== FALSE)
					{
						$arr = '&quot;'. $arr . '&quot;';
					}

					$temp[]	= $arr;
				}
			}

			if ($key == 'not' AND ! empty($arr))
			{
				if (is_array($arr) === TRUE)
				{
					$temp[]	= '-' . implode(' -', $arr);
				}
				else
				{
					$temp[]	= '-' . $arr;
				}
			}

			if ($key == 'or' AND ! empty($arr))
			{
				if (is_array($arr) === TRUE)
				{
					foreach ($arr as $v)
					{
						//handle the case where we have a search
						//'phrase' that would have been in "quotes"
						if (is_string($v) === TRUE AND
							strpos($v , ' ') !== FALSE)
						{
							$v = '&quot;'. $v . '&quot;';
						}

						$temp[]	=	$v;
					}
				}
				else
				{
					if (strpos($arr , ' ') !== FALSE)
					{
						$arr = '&quot;'. $arr . '&quot;';
					}

					$temp[]	= $arr;
				}
			}
		}

		return implode(' ', $temp);
	}
	//END andornot



	// -------------------------------------------------------------

	/**
	 * Extract vars from query
	 *
	 * @access	private
	 * @return	array
	 */

	public function _extract_vars_from_query($q = array())
	{
		if (empty($q)) return array();

		$prefix	= 'super_search_';

		$vars	= array();

		foreach ($q as $key => $arr)
		{
			if (empty($arr)) continue;

			if (in_array($key, array('channel', 'status', 'category')) === TRUE)
			{
				$arr	= (is_string($arr)) ? $this->ssu->prep_keywords($arr): $arr;

				if (isset($arr['and']) === TRUE)
				{
					foreach ($arr['and'] as $val)
					{
						$val	= str_replace(' ', '_', $val);

						$vars[$prefix . $key . '_' . $val]	= TRUE;
					}
				}

				if (isset($arr['or']) === TRUE)
				{
					foreach ($arr['or'] as $val)
					{
						$val	= str_replace(' ', '_', $val);

						$vars[$prefix . $key . '_' . $val]	= TRUE;
					}
				}

				if (isset($arr['not']) === TRUE)
				{
					foreach ($arr['not'] as $val)
					{
						$val	= str_replace(' ', '_', $val);

						$vars[$prefix . $key . '_not_' . $val]	= TRUE;
					}
				}
			}
			elseif ($key == 'field')
			{
				foreach ($arr as $k => $v)
				{
					$vars[$prefix.$k]	= $this->andornot($v);
				}
			}
			elseif ($key == 'exactfield')
			{
				foreach ($arr as $k => $v)
				{
					// assign both forms
					$vars[$prefix.'exact'.$this->modifier_separator.$k]	= $this->andornot($v);
					$vars[$prefix.$k.$this->modifier_separator.'exact']	= $this->andornot($v);
				}
			}
			elseif ($key == 'empty')
			{
				foreach ($arr as $k => $v)
				{
					$vars[$prefix.$k.$this->modifier_separator.'empty']	= $this->andornot($v);
				}
			}
			elseif ($key == 'from')
			{
				foreach ($arr as $k => $v)
				{
					$vars[$prefix.$k.$this->modifier_separator.'from']	= $this->andornot($v);
				}
			}
			elseif ($key == 'to')
			{
				foreach ($arr as $k => $v)
				{
					$vars[$prefix.$k.$this->modifier_separator.'to']	= $this->andornot($v);
				}
			}
			elseif ($key == 'datefrom')
			{
				// assign both forms
				$vars[$prefix.'entry_date'.$this->modifier_separator.'from']	= $arr;
				$vars[$prefix.'date'.$this->modifier_separator.'from']	= $arr;
			}
			elseif ($key == 'dateto')
			{
				// assign both forms
				$vars[$prefix.'entry_date'.$this->modifier_separator.'to']	= $arr;
				$vars[$prefix.'date'.$this->modifier_separator.'to']	= $arr;
			}
			elseif ($key == 'expiry_datefrom')
			{
				$vars[$prefix.'expiry_date'.$this->modifier_separator.'from']	= $arr;
			}
			elseif ($key == 'expiry_dateto')
			{
				$vars[$prefix.'expiry_date'.$this->modifier_separator.'to']	= $arr;
			}
			elseif ($key == 'channel_ids')
			{
				$vars[$prefix.'channel_ids'] = implode($arr, ' ');
			}
			elseif ($key == 'site')
			{
				$vars[$prefix.'site'] = implode($arr, ' ');
			}
			elseif ($key == 'site_id')
			{
				$vars[$prefix.'site'] = implode($arr, ' ');
			}
			elseif ($key == 'keywords_phrase')
			{
				$vars[$prefix.$key] = $arr;
			}
			elseif ($key == 'search_in')
			{
				if (is_array($arr))
				{
					$vars[$prefix.$key] = implode("|", array_keys($arr));
				}
				else
				{
					$vars[$prefix.$key] = $arr;
				}
			}
			elseif ($key == 'how')
			{
				$vars[$prefix.$key] = $arr;
			}
			elseif ($key == 'partial_author')
			{
				$vars[$prefix.$key] = $arr;
			}
			elseif ($key == 'orderby')
			{
				$vars[$prefix.'order'] = $arr;
				$vars[$prefix.$key] = $arr;
			}
			elseif ($key == 'order')
			{
				$vars[$prefix.'order'] = $arr;
				$vars[$prefix.$key] = $arr;
			}
			else
			{
				$vars[$prefix.$key]	= $this->andornot($arr);
			}
		}

		// Override the 'super_search_keywords' value with the _keywords_phrase value
		if (isset($vars[$prefix . 'keywords_phrase'])) $vars[$prefix . 'keywords'] = $vars[$prefix . 'keywords_phrase'];

		return $vars;
	 }
	//	End extract vars from query


	// -------------------------------------------------------------

	/**
	 * Date Fields
	 *
	 * @access	private
	 * @return	array
	 */

	function _date_fields()
	{
		$fields	= array(); $this->_fields();

		if (empty($this->sess['general_field_data']['searchable'])) return $fields;

		foreach ($this->sess['general_field_data']['searchable'] as $name => $attr)
		{
			if ($attr['field_type'] != 'date') continue;

			$fields[$name]	= $attr;
		}

		return $fields;
	}

	//	End _date_fields()

	// -------------------------------------------------------------

	/**
	 * _grid_fields()
	 *
	 * We later wrote a channel routine that could speed this up by eliminating the JOIN. Revisit.
	 *
	 * @access	private
	 * @return	array
	 */

	private function _grid_fields($use_separator = 'y', $site_ids = array())
	{
		//EE 2.6 support
		if (version_compare($this->ee_version, '2.7', '<'))
		{
			return array();
		}

		$use_separator	= ($use_separator == 'y') ? 'y': 'n';

		if (isset($this->sess['search']['q']['grid_fields'][$use_separator]))
		{
			return $this->sess['search']['q']['grid_fields'][$use_separator];
		}

		$this->sess['search']['q']['grid_fields'][$use_separator]	= FALSE;

		if (empty($site_ids) === TRUE)
		{
			if (isset(ee()->TMPL) AND is_object(ee()->TMPL) === TRUE)
			{
				$site_ids	= ee()->TMPL->site_ids;
			}
			else
			{
				$site_ids	= array(ee()->config->item('site_id'));
			}
		}

		// -------------------------------------
		//	Begin SQL
		// -------------------------------------

		$sql	= "/* Super Search _grid_fields() */ SELECT
			gc.col_id,
			gc.field_id,
			gc.col_name,
			gc.col_type,
			cf.site_id,
			cf.group_id,
			cf.field_name,
			c.channel_id,
			CONCAT_WS('" . $this->grid_field_separator . "', cf.field_name, gc.col_name) AS name
		FROM exp_grid_columns gc
		LEFT JOIN exp_channel_fields cf ON cf.field_id = gc.field_id
		LEFT JOIN exp_channels c ON c.field_group = cf.group_id
		WHERE gc.col_search = 'y'
		AND gc.content_type = 'channel'";

		// -------------------------------------
		//	Run query
		// -------------------------------------

		$query	= ee()->db->query($sql);

		// -------------------------------------
		//	Matrix fields?
		// -------------------------------------

		if (ee()->db->table_exists('exp_matrix_cols'))
		{
			// -------------------------------------
			//	Begin SQL
			// -------------------------------------

			$sql	= "/* Super Search _matrix_fields() */ SELECT
				m.col_id,
				m.field_id,
				m.col_name,
				m.col_type,
				cf.site_id,
				cf.group_id,
				cf.field_name,
				c.channel_id,
				CONCAT_WS('" . $this->grid_field_separator . "', cf.field_name, m.col_name) AS name
			FROM exp_matrix_cols m
			LEFT JOIN exp_channel_fields cf ON cf.field_id = m.field_id
			LEFT JOIN exp_channels c ON c.field_group = cf.group_id
			WHERE m.col_search = 'y'";

			// -------------------------------------
			//	Run query
			// -------------------------------------

			$mquery	= ee()->db->query($sql);

			// -------------------------------------
			//	Empty?
			// -------------------------------------

			if ($query->num_rows() == 0 AND $mquery->num_rows() == 0)
			{
				return $this->sess['search']['q']['grid_fields'][$use_separator];
			}
		}
		//no matrix?
		else
		{
			if ($query->num_rows() == 0)
			{
				return $this->sess['search']['q']['grid_fields'][$use_separator];
			}
		}

		// -------------------------------------
		//	Loop and load
		// -------------------------------------

		foreach ($query->result_array() as $row)
		{
			$row['exact_type']	= 'grid';
			$this->sess['search']['q']['grid_fields']['y'][$row['name']][$row['site_id']]	= $row;
			$this->sess['search']['q']['grid_fields']['n'][$row['site_id']][$row['field_name']][$row['col_name']]	= $row;
		}

		if (isset($mquery))
		{
			foreach ($mquery->result_array() as $row)
			{
				$row['exact_type']	= 'matrix';
				$this->sess['search']['q']['grid_fields']['y'][$row['name']][$row['site_id']]	= $row;
				$this->sess['search']['q']['grid_fields']['n'][$row['site_id']][$row['field_name']][$row['col_name']]	= $row;
			}
		}

		// -------------------------------------
		//	Return
		// -------------------------------------

		return $this->sess['search']['q']['grid_fields'][$use_separator];
	}

	//	End _grid_fields()

	// -------------------------------------------------------------

	/**
	 * Fields
	 *
	 * We later wrote a channel routine that could speed this up by eliminating the JOIN. Revisit.
	 *
	 * @access	private
	 * @return	array
	 */

	function _fields($channel = 'searchable', $site_ids = array())
	{
		if (empty($this->sess['search']['q']['channel_ids']) AND ($fields = $this->sess('fields', $channel)) !== FALSE)
		{
			return $fields;
		}
		elseif (! empty($this->sess['search']['q']['channel_ids']) AND ($fields = $this->sess('fields' . md5(implode('', $this->sess['search']['q']['channel_ids'])), $channel)) !== FALSE)
		{
			return $fields;
		}

		if (empty($site_ids) === TRUE)
		{
			if (isset(ee()->TMPL) AND is_object(ee()->TMPL) === TRUE)
			{
				$site_ids	= ee()->TMPL->site_ids;
			}
			else
			{
				$site_ids	= array(ee()->config->item('site_id'));
			}
		}

		$columns	= array(
			'cf.site_id',
			'cf.field_id',
			'cf.field_name',
			'cf.field_search',
			'cf.field_type',
			'cf.field_text_direction',
			'c.channel_id',
			'c.channel_name'
		);

		// -------------------------------------
		//	Begin SQL
		// -------------------------------------

		$sql	= "/* Super Search get fields */ SELECT " . implode(',', $columns) . "
					FROM exp_channel_fields cf
					LEFT JOIN exp_channels c ON c.field_group = cf.group_id
					WHERE cf.site_id IN (".implode(",", ee()->db->escape_str($site_ids)).")
					AND c.channel_id != ''";

		// -------------------------------------
		//	Filter out a custom field by the name
		//	of keywords? 'keywords' is a reserved
		//	word in Super Search. We're going to
		//	get into trouble for this one.
		// -------------------------------------

		$sql	.= " AND cf.field_name != 'keywords'";

		// -------------------------------------
		//	Channel id restriction?
		// -------------------------------------

		if (($channel_ids = $this->sess('search', 'q', 'channel_ids')) !== FALSE)
		{
			$sql	.= " AND c.channel_id IN (" . implode(',', ee()->db->escape_str($channel_ids)) . ")";
		}

		// -------------------------------------
		//	Run query
		// -------------------------------------

		$query	= ee()->db->query($sql);

		if ($query->num_rows() == 0) return array();

		// -------------------------------------
		//	Are there any grid fields? Fetch them.
		// -------------------------------------

		$grid_fields = $this->_grid_fields('n', $site_ids);

		// -------------------------------------
		//	Continue with main prep.
		// -------------------------------------

		$arr						= array();
		$fmt						= array();
		$field_to_channel_map		= array();
		$field_to_channel_map_sql	= array();
		$general_field_data			= array();

		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() as $row)
			{
				foreach ($this->searchable_ct as $val)
				{
					$arr[$row['channel_name']][$val]	= $val;
					$arr['searchable'][$val]			= $val;
				}

				$fmt[$row['field_name']]	= 'ltr';
				$field_to_channel_map['title'][$row['channel_id']]	= $row['channel_id'];

				// Handle fields with the same name across MSMs
				if (isset($arr['all'][$row['field_name']]))
				{
					if ($arr['all'][$row['field_name']] != $row['field_id'])
					{
						if (!isset($arr['all']['supersearch_msm_duplicate_fields'][$row['field_name']][$row['field_id']]))
						{
							//is the the first duplicate?
							if (!isset($arr['all']['supersearch_msm_duplicate_fields'][$row['field_name']]))
							{
								//move the first field_id, now that we know it has a sibling
								$arr['all']['supersearch_msm_duplicate_fields'][$row['field_name']][$arr['all'][$row['field_name']]] = $arr['all'][$row['field_name']];
							}

							//this field_id is already in the main array
							$arr['all']['supersearch_msm_duplicate_fields'][$row['field_name']][$row['field_id']] = $row['field_id'];
						}
					}
				}

				$arr['all'][$row['field_name']]	= $row['field_id'];

				if ($row['field_search'] == 'y')
				{
					if (empty($channel_ids) OR in_array($row['channel_id'], $channel_ids))
					{
						foreach ($this->searchable_ct as $val)
						{
							$field_to_channel_map[$val][$row['channel_id']]	= $row['channel_id'];
						}

						$field_to_channel_map[$row['field_id']][$row['channel_id']]	= $row['channel_id'];
						$arr[$row['channel_name']][$row['field_name']]	= $row['field_id'];
						$fmt[$row['field_name']]						= $row['field_text_direction'];
						$general_field_data[$row['field_name']]			= $row;

						// -------------------------------------
						//	If this a grid field? We need to prepare to recognize it later with correct syntax.
						// -------------------------------------

						if (isset($grid_fields[$row['site_id']][$row['field_name']]))
						{
							foreach ($grid_fields[$row['site_id']][$row['field_name']] as $col => $grid)
							{
								$temp_name	= $row['field_name'] . $this->grid_field_separator . $col;
								$field_to_channel_map[$temp_name][$row['channel_id']]	= $row['channel_id'];
								$arr[$row['channel_name']][$temp_name]					= $row['field_id'];
								$arr['searchable'][$temp_name]							= $row['field_id'];
							}
						}

						// -------------------------------------
						//	Handle fields with the same name across MSMs
						// -------------------------------------

						if (isset($arr['searchable'][$row['field_name']]))
						{
							if ($arr['searchable'][$row['field_name']] != $row['field_id'])
							{
								if (!isset($arr['searchable']['supersearch_msm_duplicate_fields'][$row['field_name']][$row['field_id']]))
								{
									//is the the first duplicate?
									if (!isset($arr['searchable']['supersearch_msm_duplicate_fields'][$row['field_name']]))
									{
										//move the first field_id, now that we know it has a sibling
										$arr['searchable']['supersearch_msm_duplicate_fields'][$row['field_name']][$arr['searchable'][$row['field_name']]] = $arr['searchable'][$row['field_name']];
									}

									//this field_id is already in the main array
									$arr['searchable']['supersearch_msm_duplicate_fields'][$row['field_name']][$row['field_id']] = $row['field_id'];
								}
							}
						}

						// -------------------------------------
						//	This is sort of hidden, but very important later.
						// -------------------------------------

						$arr['searchable'][$row['field_name']]	= $row['field_id'];
					}
				}
			}

			if (! empty($this->sess['search']['q']['channel_ids']))
			{
				$this->sess['fields'.md5(implode('', $this->sess['search']['q']['channel_ids']))]	= $arr;
			}
		}

		// -------------------------------------
		//	Prepare field to channel map
		// -------------------------------------

		foreach ($field_to_channel_map as $field_id => $temp_channel_ids)
		{
			if (count($temp_channel_ids) > 1)
			{
				$field_to_channel_map_sql[$field_id]	= ' AND cd.' . 'channel_id' . ' IN (' . implode(',', $temp_channel_ids) . ')';
			}
			elseif (count($temp_channel_ids) == 1)
			{
				$field_to_channel_map_sql[$field_id]	= ' AND cd.' . 'channel_id' . ' = ' . implode('', $temp_channel_ids);
			}
		}

		$this->sess['fields']							= $arr;
		$this->sess['fields_fmt']						= $fmt;
		$this->sess['field_to_channel_map']				= $field_to_channel_map;
		$this->sess['field_to_channel_map_sql']			= $field_to_channel_map_sql;
		$this->sess['general_field_data']['searchable']	= $general_field_data;

		return (isset($arr[$channel]) === TRUE) ? $arr[$channel]: FALSE;
	}
	//	End _fields


	// --------------------------------------------------------------------

	/**
	 * Statuses
	 *
	 * This is a cleanup function to handle the ambigious nature of passed statuses.
	 *
	 * @access	private
	 * @return	array
	 */

	function _statuses($site_ids = array())
	{
		if (empty($site_ids) === TRUE)
		{
			if (is_object(ee()->TMPL) === TRUE)
			{
				$site_ids	= ee()->TMPL->site_ids;
			}
			else
			{
				$site_ids	= array(ee()->config->item('site_id'));
			}
		}

		$sql = " SELECT status_id, status, site_id, group_id FROM exp_statuses WHERE site_id IN (".implode(',', ee()->db->escape_str($site_ids)) . ")";

		// -------------------------------------
		//	Run query
		// -------------------------------------

		$query	= ee()->db->query($sql);

		$arr	= array();

		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() as $row)
			{
				$arr[$row['site_id']][] 	= $row['status'];
				$arr['all'][]				= $row['status'];
				$arr['cleaned'][]			= str_replace(" ", "+" , $row['status']);

				if (strpos($row['status'] , ' ')) $arr['multiword_status'] = TRUE;
			}
		}

		$this->sess['statuses']	= $arr;

		return;
	}

	//	End fields

	// -------------------------------------------------------------

	/**
	 * Forget last search
	 *
	 * This method deletes the user's last search from the DB if it is found.
	 *
	 * @access	private
	 * @return	string
	 */

	function forget_last_search()
	{
		$tagdata	= ee()->TMPL->tagdata;

		// -------------------------------------
		//	Delete
		// -------------------------------------

		$sql	= "DELETE FROM exp_super_search_history
					WHERE site_id = ".ee()->db->escape_str(ee()->config->item('site_id'));

		$sql	.= " AND saved = 'n'
					AND ((
							member_id != 0
							AND member_id = ".ee()->db->escape_str(ee()->session->userdata('member_id')).")";

		$sql	.= " OR (cookie_id = '".ee()->db->escape_str($this->_get_users_cookie_id())."'))
					LIMIT 1";

		ee()->db->query($sql);

		if (ee()->db->affected_rows() == 0)
		{
			$message	= lang('no_search_history_was_found');

			$tagdata	= ee()->functions->prep_conditionals($tagdata, array('failure' => TRUE, 'success' => FALSE));
			$tagdata	= str_replace(LD.'message'.RD, $message, $tagdata);
			return $tagdata;
		}
		else
		{
			$message	= lang('last_search_cleared');

			$tagdata	= ee()->functions->prep_conditionals($tagdata, array('failure' => FALSE, 'success' => TRUE));
			$tagdata	= str_replace(LD.'message'.RD, $message, $tagdata);
			return $tagdata;
		}
	}
	//	End forget last search


	// -------------------------------------------------------------

	/**
	 * Form (sub)
	 *
	 * This method receives form config info and returns a properly formated EE form.
	 *
	 * @access	private
	 * @return	string
	 */

	function _form($data = array())
	{
		if (count($data) == 0) return '';

		if (! isset($data['tagdata']) OR $data['tagdata'] == '')
		{
			$tagdata	=	ee()->TMPL->tagdata;
		}
		else
		{
			$tagdata	= $data['tagdata'];
			unset($data['tagdata']);
		}

		// -------------------------------------
		//  Special Handling for return="" parameter
		// -------------------------------------

		foreach(array('return', 'RET') as $val)
		{
			if (isset($data[$val]) AND $data[$val] !== FALSE AND $data[$val] != '')
			{
				$data[$val] = str_replace(SLASH, '/', $data[$val]);

				if (preg_match("/".LD."\s*path=(.*?)".RD."/", $data[$val], $match))
				{
					$data[$val] = ee()->functions->create_url($match['1']);
				}
				elseif (stristr($data[$val], "http://") === FALSE)
				{
					$data[$val] = ee()->functions->create_url($data[$val]);
				}
			}
		}

		// -------------------------------------
		//	Generate form
		// -------------------------------------

		$arr	=	array(
			'action'		=> (empty($data['action'])) ? ee()->functions->fetch_site_index(): $data['action'],
			'id'			=> $data['form_id'],
			'enctype'		=> '',
			'onsubmit'		=> (isset($data['onsubmit'])) ? $data['onsubmit'] : ''
		);

		$arr['onsubmit'] = (ee()->TMPL->fetch_param('onsubmit')) ? ee()->TMPL->fetch_param('onsubmit') : $arr['onsubmit'];

		if (isset($data['name']) !== FALSE)
		{
			$arr['name']	= $data['name'];
			unset($data['name']);
		}

		unset($data['form_id']);
		unset($data['onsubmit']);

		$arr['hidden_fields']	= $data;

		unset($arr['hidden_fields']['action']);
		unset($arr['hidden_fields']['method']);
		unset($arr['hidden_fields']['form_name']);

		// -------------------------------------
		//  HTTPS URLs?
		// -------------------------------------

		if (ee()->TMPL->fetch_param('secure_action') == 'yes')
		{
			if (isset($arr['action']))
			{
				$arr['action'] = str_replace('http://', 'https://', $arr['action']);
			}
		}

		if (ee()->TMPL->fetch_param('secure_return') == 'yes')
		{
			foreach(array('return', 'RET') as $return_field)
			{
				if (isset($arr['hidden_fields'][$return_field]))
				{
					if (preg_match("/".LD."\s*path=(.*?)".RD."/", $arr['hidden_fields'][$return_field], $match) > 0)
					{
						$arr['hidden_fields'][$return_field] = ee()->functions->create_url($match['1']);
					}
					elseif (stristr($arr['hidden_fields'][$return_field], "http://") === FALSE)
					{
						$arr['hidden_fields'][$return_field] = ee()->functions->create_url($arr['hidden_fields'][$return_field]);
					}

					$arr['hidden_fields'][$return_field] = str_replace('http://', 'https://', $arr['hidden_fields'][$return_field]);
				}
			}
		}

		// -------------------------------------
		//  Override Form Attributes with form:xxx="" parameters
		// -------------------------------------

		$extra_attributes = array();

		if (is_object(ee()->TMPL) === TRUE AND ! empty(ee()->TMPL->tagparams))
		{
			foreach(ee()->TMPL->tagparams as $key => $value)
			{
				if (strncmp($key, 'form:', 5) == 0)
				{
					if (isset($arr[substr($key, 5)]))
					{
						$arr[substr($key, 5)] = $value;
					}
					else
					{
						$extra_attributes[substr($key, 5)] = $value;
					}
				}
			}
		}

		// -------------------------------------
		//  Create Form
		// -------------------------------------

		$r	= ee()->functions->form_declaration($arr);

		$r	.= stripslashes($tagdata);

		$r	.= "</form>";

		// -------------------------------------
		//	 Add <form> attributes from
		// -------------------------------------

		$allowed = array('accept', 'accept-charset', 'enctype', 'method', 'action',
						 'name', 'target', 'class', 'dir', 'id', 'lang', 'style',
						 'title', 'onclick', 'ondblclick', 'onmousedown', 'onmousemove',
						 'onmouseout', 'onmouseover', 'onmouseup', 'onkeydown',
						 'onkeyup', 'onkeypress', 'onreset', 'onsubmit', 'role');

		foreach($extra_attributes as $key => $value)
		{
			if (! in_array($key, $allowed)) continue;

			$r = str_replace("<form", '<form '.$key.'="'.htmlspecialchars($value).'"', $r);
		}

		if (isset($data['method']) AND $data['method'] == 'get')
		{
			$r	= str_replace('method="post"', 'method="get"', $r);
			$r	= str_replace('<input type="hidden" name="csrf_token"', '<input type="hidden" name=""', $r);
			$r	= str_replace('<input type="hidden" name="history_id"', '<input type="hidden" name=""', $r);
		}

		$r	= str_replace('<input type="hidden" name="site_id" value="1" />', '', $r);

		// -------------------------------------
		//	Return
		// -------------------------------------

		return str_replace('{/exp:', LD.T_SLASH.'exp:', str_replace(T_SLASH, '/', $r));
	}

	//	End form

	// -------------------------------------------------------------

	/**
	 * Cloud
	 *
	 * @access	public
	 * @return	string
	 */

	function cloud()
	{
		$max 				= 1;  // Must be 1, cannot divide by zero!

		$rank_by			= 'count';

		$groups				= (ctype_digit(ee()->TMPL->fetch_param('groups'))) ?
								ee()->TMPL->fetch_param('groups') : 5;

		$start				= (ctype_digit(ee()->TMPL->fetch_param('start'))) ?
								ee()->TMPL->fetch_param('start') : 10;

		$step				= (ctype_digit(ee()->TMPL->fetch_param('step'))) ?
								ee()->TMPL->fetch_param('step') : 2;

		$day_limit			= ee()->TMPL->fetch_param('day_limit', '');

		$start_on			= ee()->TMPL->fetch_param('start_on', '');

		$stop_on			= ee()->TMPL->fetch_param('stop_on', '');

		$site_id			= ee()->TMPL->fetch_param('site_id', ee()->config->item('site_id'));

		$term 				= ee()->TMPL->fetch_param('term', '');

		$term_id			= ee()->TMPL->fetch_param('term_id', '');

		$exclude_term 		= ee()->TMPL->fetch_param('exclude_term', '');

		$exclude_term_id	= ee()->TMPL->fetch_param('exclude_term_id', '');

		$searched_only		= ! $this->check_no(ee()->TMPL->fetch_param('searched_only'));

		$most_popular		= ! $this->check_no(ee()->TMPL->fetch_param('most_popular'));

		$prefix				= ee()->TMPL->fetch_param('prefix', 'super_search_');

		// -------------------------------------
		//  Fixed Order - Override of term_id="" parameter
		// -------------------------------------

		// fixed entry id ordering
		if (($fixed_order = ee()->TMPL->fetch_param('fixed_order')) === FALSE OR
			 preg_match('/[^0-9\|]/', $fixed_order))
		{
			$fixed_order = FALSE;
		}
		else
		{
			// Override Term ID parameter to get exactly these terms
			// Other parameters will still affect results. I blame the user for using them if it
			// does not work they way they want.
			ee()->TMPL->tagparams['term_id'] = $fixed_order;

			$fixed_order = preg_split('/\|/', $fixed_order, -1, PREG_SPLIT_NO_EMPTY);

			// A quick and easy way to reverse the order of these entries.  People might like this.
			if (ee()->TMPL->fetch_param('sort') == 'desc')
			{
				$fixed_order = array_reverse($fixed_order);
			}
		}

		$sql = " SELECT term_id, site_id, term, first_seen, last_seen, count, entry_count
					FROM exp_super_search_terms WHERE 1=1 ";

		if ($searched_only)
		{
			$sql .= " AND count > 0 ";
		}

		$site_ids = array();

		if ($site_id == 'all')
		{
			$site_ids = ee()->db->escape_str(ee()->TMPL->site_ids);

		}
		elseif (is_numeric($site_id) AND in_array($site_id, ee()->TMPL->site_ids))
		{
			$site_ids[] = ee()->db->escape_str($site_id);
		}
		else
		{
			//lets be safe
			foreach(explode(array('|','+','&',' ') , $site_id) as $site)
			{
				if (is_numeric($site) AND in_array($site, ee()->TMPL->site_ids))
				{
					$site_ids[] = ee()->db->escape_str($site);
				}
			}

			if (empty($site_ids))
			{
				$site_ids[] = ee()->db->escape_str(ee()->config->item('site_id'));
			}
		}

		$sql .= " AND site_id IN ('" . implode("','", $site_ids) . "') ";

		//	----------------------------------------
		//	 Narrow Tags via Terms
		//	----------------------------------------

		$sql .= $this->_param_split($term);

		$sql .= $this->_param_split($term_id, 'term_id');

		$sql .= $this->_param_split($exclude_term, 'term NOT ');

		$sql .= $this->_param_split($exclude_term_id, 'term_id NOT ');

		//	----------------------------------------
		//	Limit query by number of days (recently)
		//	----------------------------------------

		if ($day_limit != '')
		{
			$time = ee()->localize->now - ($day_limit * 60 * 60 * 24);

			$sql .= " AND last_seen >= '".ee()->db->escape_str($time)."'";
		}
		else // OR
		{
			//	----------------------------------------
			//	Limit query by date range given in tag parameters
			//	----------------------------------------

			$convert_func = 'convert_human_date_to_gmt';

			if (is_callable(array(ee()->localize, 'string_to_timestamp')))
			{
				$convert_func = 'string_to_timestamp';
			}

			if ($start_on != '')
			{
				$sql .= " AND last_seen >= '".ee()->db->escape_str(ee()->localize->$convert_func($start_on))."'";
			}

			if ($stop_on != '')
			{
				$sql .= " AND last_seen < '".ee()->db->escape_str(ee()->localize->$convert_func($stop_on))."'";
			}

		}

		// --------------------------------------
		//  Pagination checkeroo! - Do Before GROUP BY!
		// --------------------------------------

		$sqla = preg_replace("/SELECT(.*?)\s+FROM\s+/is", 'SELECT COUNT(DISTINCT term_id) AS count FROM ', $sql);

		$query = ee()->db->query($sqla);

		if ($query->row('count') == 0 AND
			 strpos(ee()->TMPL->tagdata, 'paginate') !== FALSE)
		{
			return $this->return_data = $this->no_results('super_search');
		}

		$this->p_limit		= ee()->TMPL->fetch_param('limit', 20);
		$this->total_rows	= $query->row('count');
		$this->p_page		= (
			$this->p_page == '' || ($this->p_limit > 1 AND $this->p_page == 1)
		) ? 0 : $this->p_page;

		if ($this->p_page > $this->total_rows)
		{
			$this->p_page = 0;
		}

		//get pagination info
		$pagination_data = $this->universal_pagination(array(
			'total_results'			=> $this->total_rows,
			'tagdata'				=> ee()->TMPL->tagdata,
			'limit'					=> $this->p_limit,
			'uri_string'			=> ee()->uri->uri_string,
			'current_page'			=> $this->p_page,
			'paginate_prefix'		=> $prefix,
			'auto_paginate'			=> true
		));

		//if we paginated, sort the data
		if ($pagination_data['paginate'] === TRUE)
		{
			ee()->TMPL->tagdata		= $pagination_data['tagdata'];
		}

		//	----------------------------------------
		//	Fix current page if discrepancy between total results and limit
		//	----------------------------------------

		if ($this->current_page > 1 AND $pagination_data['total_results'] <= $this->p_limit)
		{
			$this->current_page	= 1;
		}

		//	----------------------------------------
		//	Find Max for All Pages
		//	----------------------------------------

		if ($this->paginate === TRUE)
		{
			$query = ee()->db->query($sql." ORDER BY count DESC LIMIT 0, 1");

			if ($query->num_rows() > 0)
			{
				$max = $query->row('count');
			}
		}

		//	----------------------------------------
		//	Set order by
		//	----------------------------------------

		$sort = '';

		$ord	= " ORDER BY count DESC ";

		if ($fixed_order !== FALSE)
		{
			$ord = ' ORDER BY FIELD(term_id, '.implode(',', ee()->db->escape_str($fixed_order)).') ';
		}
		elseif (ee()->TMPL->fetch_param('orderby') !== FALSE AND ee()->TMPL->fetch_param('orderby') != '')
		{
			foreach (array(
					'random' 			=> "rand()",
					'count' 			=> 'count',
					'term' 				=> 'term',
					'first_seen'		=> 'first_seen',
					'last_seen'			=> 'last_seen',
					'entry_count'		=> 'entry_count'
				) as $key => $val)
			{
				if ($key == ee()->TMPL->fetch_param('orderby'))
				{
					if (! $most_popular)
					{
						$ord = " ORDER BY ".ee()->db->escape_str($val);

						if ($key == 'term')
						{
							$sort = " ASC ";
						}
					}
				}
			}
		}

		if (
			in_array(ee()->TMPL->fetch_param('sort'), array('asc', 'desc'), TRUE) AND
			! $most_popular
		)
		{
			$sort	= ee()->db->escape_str(ee()->TMPL->fetch_param('sort'));
		}

		$sql_a = $sql . $ord . ' ' . $sort .' ';

		//	----------------------------------------
		//	Set numerical limit
		//	----------------------------------------

		if ($this->paginate === TRUE AND $this->total_rows > $this->p_limit)
		{
			$sql_a .= " LIMIT " .
						ee()->db->escape_str($this->p_page).', ' .
						ee()->db->escape_str($this->p_limit);
		}
		else
		{
			$sql_a .= (
				ctype_digit(ee()->TMPL->fetch_param('limit'))
			) ? ' LIMIT '.ee()->db->escape_str(ee()->TMPL->fetch_param('limit')) : ' LIMIT 20';
		}

		//	----------------------------------------
		//	Query
		//	----------------------------------------

		$query	= ee()->db->query($sql_a);

		//	----------------------------------------
		//	Empty?
		//	----------------------------------------

		if ($query->num_rows() == 0)
		{
			return $this->no_results('super_search');
		}

		if (ee()->TMPL->fetch_param('orderby') !== FALSE AND
			ee()->TMPL->fetch_param('orderby') != ''  AND $most_popular)
		{
			foreach (array(
					'random' 			=> "rand()",
					'count' 			=> 'count',
					'term' 				=> 'term',
					'first_seen'		=> 'first_seen',
					'last_seen'			=> 'last_seen',
					'entry_count'		=> 'entry_count'
				) as $key => $val)
			{
				if ($key == ee()->TMPL->fetch_param('orderby'))
				{
					$ord = " ORDER BY ".ee()->db->escape_str($val);

					$sort = " DESC";

					if ($key == 'term')
					{
						$sort = " ASC";
					}
				}
			}

			if (in_array(ee()->TMPL->fetch_param('sort'), array('asc', 'desc'), TRUE))
			{
				$sort = " " . ee()->db->escape_str(ee()->TMPL->fetch_param('sort'));
			}

			$temp = array();

			foreach($query->result_array() AS $row)
			{
				$temp[] = "'" . ee()->db->escape_str($row['term_id']) . "'";
			}

			$where = " AND term_id IN (" . implode(',', $temp) . ") ";

			$sql_b = $sql . $where . $ord . $sort;

			$query = ee()->db->query($sql_b);
		}

		//	----------------------------------------
		//	What's the max?
		//	----------------------------------------

		// If we have Pagination, we find the MAX value up above.
		// If not, we find it based on the current results.

		if ($this->paginate !== TRUE)
		{
			foreach ($query->result_array() as $row)
			{
				$max	= ($row['count'] > $max) ? $row['count']: $max;
			}
		}

		//	----------------------------------------
		//	Order alpha
		//	----------------------------------------

		$terms	= array();

		foreach ($query->result_array() as $row)
		{
			$terms[$row['term']]['term_id']		= $row['term_id'];
			$terms[$row['term']]['term']		= $row['term'];
			$terms[$row['term']]['count']		= $row['count'];
			$terms[$row['term']]['entry_count']	= $row['entry_count'];
			$terms[$row['term']]['first_seen']	= $row['first_seen'];
			$terms[$row['term']]['last_seen']	= $row['last_seen'];
			$terms[$row['term']]['site_id']		= $row['site_id'];

			$terms[$row['term']]['size']		= ceil($row['count'] / ($max / $groups));

			$terms[$row['term']]['step']		= $terms[$row['term']]['size'] * $step + $start;
		}

		//	----------------------------------------
		//	Parse
		//	----------------------------------------

		$r		= '';
		$count	= 0;

		$qs	= (ee()->config->item('force_query_string') == 'y') ? '' : '?';

		$total_results = count($terms);

		foreach ($terms as $key => $row)
		{
			$key = htmlspecialchars($key);
			$tagdata	= ee()->TMPL->tagdata;

			$count++;
			$row['absolute_count']	= (
				$this->current_page < 2
			) ?
				$count :
				($this->current_page - 1) * $this->p_limit + $count;

			$row['total_results'] 		= $total_results;
			$row['absolute_results'] 	= $this->total_rows;

			//	----------------------------------------
			//	Conditionals
			//	----------------------------------------

			foreach($row AS $subkey => $subval)
			{
				$row[$prefix . $subkey] = $subval;
			}

			$cond					= $row;
			$cond['term']			= $key;
			$cond[$prefix.'term']	= $key;
			$tagdata				= ee()->functions->prep_conditionals($tagdata, $cond);

			//	----------------------------------------
			//	Parse Switch
			//	----------------------------------------

			if (preg_match("/".LD."(switch\s*=.+?)".RD."/is", $tagdata, $match) > 0)
			{
				$sparam = ee()->functions->assign_parameters($match['1']);

				$sw = '';

				if (isset($sparam['switch']) !== FALSE)
				{
					$sopt = explode("|", $sparam['switch']);

					$sw = $sopt[($count + count($sopt)) % count($sopt)];
				}

				$tagdata = ee()->TMPL->swap_var_single($match['1'], $sw, $tagdata);
			}

			//	----------------------------------------
			//	Parse singles
			//	----------------------------------------

			$tagdata = str_replace(LD.'term'.RD, $key, $tagdata);
			$tagdata = str_replace(LD.'term_id'.RD, $row['term_id'], $tagdata);
			$tagdata = str_replace(LD.'total_searches'.RD, $row['count'], $tagdata);
			$tagdata = str_replace(LD.'site_id'.RD, $row['site_id'], $tagdata);
			$tagdata = str_replace(LD.'size'.RD, $row['size'], $tagdata);
			$tagdata = str_replace(LD.'step'.RD, $row['step'], $tagdata);
			$tagdata = str_replace(LD.'count'.RD, $count, $tagdata);
			$tagdata = str_replace(LD.'absolute_count'.RD, $row['absolute_count'], $tagdata);
			$tagdata = str_replace(LD.'absolute_results'.RD, $row['absolute_results'], $tagdata);
			$tagdata = str_replace(LD.'total_results'.RD, $row['total_results'], $tagdata);


			$tagdata = str_replace(LD.$prefix.'term'.RD, $key, $tagdata);
			$tagdata = str_replace(LD.$prefix.'term_id'.RD, $row['term_id'], $tagdata);
			$tagdata = str_replace(LD.$prefix.'total_searches'.RD, $row['count'], $tagdata);
			$tagdata = str_replace(LD.$prefix.'site_id'.RD, $row['site_id'], $tagdata);
			$tagdata = str_replace(LD.$prefix.'size'.RD, $row['size'], $tagdata);
			$tagdata = str_replace(LD.$prefix.'step'.RD, $row['step'], $tagdata);
			$tagdata = str_replace(LD.$prefix.'count'.RD, $count, $tagdata);
			$tagdata = str_replace(LD.$prefix.'absolute_count'.RD, $row['absolute_count'], $tagdata);
			$tagdata = str_replace(LD.$prefix.'absolute_results'.RD, $row['absolute_results'], $tagdata);
			$tagdata = str_replace(LD.$prefix.'total_results'.RD, $row['total_results'], $tagdata);


			foreach (array('last_seen', 'first_seen', $prefix.'last_seen', $prefix.'first_seen') as $val)
			{
				if (preg_match_all("/".LD.$val."\s+format=([\"'])([^\\1]*?)\\1".RD."/s", $tagdata, $matches))
				{
					for($i=0, $s=count($matches[2]); $i < $s; ++$i)
					{
						$str	= $matches[2][$i];

						$codes	= $this->fetch_date_params($matches[2][$i]);

						foreach ($codes as $code)
						{
							$str	= str_replace($code, $this->convert_timestamp($code, $row[$val], TRUE), $str);
						}

						$tagdata	= str_replace($matches[0][$i], $str, $tagdata);
					}
				}
			}

			//	----------------------------------------
			//	Concat
			//	----------------------------------------

			$r	.= $tagdata;
		}

		//	----------------------------------------
		//	Backspace
		//	----------------------------------------

		$backspace			= (
			ctype_digit(ee()->TMPL->fetch_param('backspace'))
		) ? ee()->TMPL->fetch_param('backspace') : 0;

		// Clean up our no_results condition before backspacing
		if ($backspace > 0)
		{
			if (preg_match(
				"/".LD."if " .preg_quote($this->lower_name)."_no_results" .
					RD."(.*?)".LD.preg_quote(T_SLASH, '/')."if".RD."/s",
				$r,
				$match))
			{
				$r = str_replace($match[0], '', $r);
			}

		}

		$tagdata	= ($backspace > 0) ? substr($r, 0, - $backspace): $r;

		// -------------------------------------
		//  Pagination?
		// -------------------------------------

		$tagdata = $this->parse_pagination(array(
			'prefix'	=> $prefix,
			'tagdata'	=> $tagdata
		));

		return $tagdata;
	}
	//	END cloud


	// --------------------------------------------------------------------

	/**
	 * Get cat group ids
	 *
	 * @access	private
	 * @return	array
	 */

	function _get_cat_group_ids()
	{
		// -------------------------------------
		//	Get channel ids
		// -------------------------------------
		//	It helps to have a channel to make
		//	sense of the textual categories provided.
		//	By this point we already have determined
		//	channel ids, but we'll be cautious.
		// -------------------------------------

		if (($channel_ids = $this->sess('search', 'channel_ids')) === FALSE)
		{
			return FALSE;
		}

		// -------------------------------------
		//	Already fetched groups?
		// -------------------------------------

		if ($this->sess('cat_group_ids') === FALSE)
		{
			$sql	= '/* Super Search fetch group ids */ SELECT group_id FROM exp_category_groups WHERE site_id IN ('.implode(',', ee()->db->escape_str(ee()->TMPL->site_ids)).')';

			$query	= ee()->db->query($sql);

			$group_ids	= array();

			foreach ($query->result_array() as $row)
			{
				$group_ids[$row['group_id']]	= $row;
			}

			$this->sess['cat_group_ids']	= $group_ids;
		}

		// -------------------------------------
		//	Loop and return group ids
		// -------------------------------------

		$ids	= array();

		foreach ($channel_ids as $id)
		{
			if (($gid = $this->_channels($id, 'cat_group')) !== FALSE)
			{
				$ids	= array_merge($ids, explode('|', $gid));
			}
		}

		if (count($ids) == 0) return FALSE;

		return $ids;
	}

	//	End get cat group ids

	// -------------------------------------------------------------

	/**
	 * Get field type
	 *
	 * Sometimes we need to know the actual MySQL field type so that we can format our search strings correctly. For example, when we use range searching on a custom field and that field contains price data, we need to strip the $ from the search string so that the search will run correctly.
	 *
	 * @access		private
	 * @argument	$field	text
	 * @return		string
	 */

	function _get_field_type($field = '')
	{
		if ($field == '') return FALSE;

		// -------------------------------------
		//	Saved in cache?
		// -------------------------------------

		if (empty($this->sess['field_types'][$field]) === FALSE)
		{
			return $this->sess['field_types'][$field];
		}

		// -------------------------------------
		//	Site ids
		// -------------------------------------

		if (isset(ee()->TMPL) AND is_object(ee()->TMPL) === TRUE)
		{
			$site_ids	= ee()->TMPL->site_ids;
		}
		else
		{
			$site_ids	= array(ee()->config->item('site_id'));
		}

		// -------------------------------------
		//	Grid fields?
		// -------------------------------------

		if (($grid_fields = $this->_grid_fields()) !== FALSE)
		{
			if (isset($grid_fields[$field][ee()->config->item('site_id')]))
			{
				return $this->sess['field_types'][$field] = 'grid';


				if ($grid_fields[$field][ee()->config->item('site_id')]['exact_type'] == 'matrix')
				{
					return $this->sess['field_types'][$field] = 'matrix';
				}
				else
				{
					return $this->sess['field_types'][$field] = 'grid';
				}
			}
		}

		// -------------------------------------
		//	Get all fields from DB
		// -------------------------------------

		$query	= ee()->db->query("/* Super Search mod.super_search.php _get_field_type() */ DESCRIBE exp_channel_data");

		$flipfields	= array_flip($this->_fields());

		foreach ($query->result_array() as $row)
		{
			if (strpos($row['Field'], 'field_id_') !== FALSE)
			{
				$num	= str_replace('field_id_', '', $row['Field']);

				if (isset($flipfields[$num]) === TRUE)
				{
					if (strpos($row['Type'], 'decimal') !== FALSE OR strpos($row['Type'], 'float') !== FALSE OR strpos($row['Type'], 'int') !== FALSE)
					{
						$this->sess['field_types'][$flipfields[$num]]	= 'numeric';
					}
					else
					{
						$this->sess['field_types'][$flipfields[$num]]	= 'textual';
					}
				}
			}
		}

		// -------------------------------------
		//	How about now?
		// -------------------------------------

		if (empty($this->sess['field_types'][$field]) === FALSE)
		{
			return $this->sess['field_types'][$field];
		}

		return FALSE;
	}

	//	End get field type

	// -------------------------------------------------------------

	/**
	 * Get ids by category
	 *
	 * We don't allow any fudge here. Provided categories must be exact.
	 *
	 * @access	private
	 * @return	array
	 */

	function _get_ids_by_category($category = array(), $exactness = 'exact')
	{
		// -------------------------------------
		//	Anything to work with?
		// -------------------------------------

		if (is_array($category) === FALSE OR count($category) == 0) return FALSE;

		$t	= microtime(TRUE);
		ee()->TMPL->log_item('Super Search: Beginning _get_ids_by_category()');

		// -------------------------------------
		//	Get category group ids
		// -------------------------------------

		if (($group_ids = $this->_get_cat_group_ids()) === FALSE)
		{
			$group_ids = array();
		}

		// -------------------------------------
		//	Prep
		// -------------------------------------

		$and	= array();
		$or		= array();
		$not	= array();

		// -------------------------------------
		//	Do we have and's?
		// -------------------------------------

		if (empty($category['and']) === FALSE)
		{
			$and	= $category['and'];
		}

		// -------------------------------------
		//	Do we have not's?
		// -------------------------------------

		if (empty($category['not']) === FALSE)
		{
			foreach ($category['not'] as $val)
			{
				if ($val == '') continue;

				$not[]	= $this->ssu->escape_str($val);
			}
		}

		// -------------------------------------
		//	Do we have or's?
		// -------------------------------------

		if (empty($category['or']) === FALSE)
		{
			foreach ($category['or'] as $val)
			{
				if ($val == '') continue;

				$or[]	= $this->ssu->escape_str($val);
			}
		}

		// -------------------------------------
		//	Empty?
		// -------------------------------------

		if (empty($and) === TRUE AND empty($not) === TRUE AND empty($or) === TRUE) return FALSE;

		// -------------------------------------
		//	Query by cat_url_title or cat_name?
		// -------------------------------------

		$cat_name_query			= 'c.cat_name';
		$allow_numeric_names	= FALSE;

		if (ee()->TMPL->fetch_param('category_indicator') !== FALSE)
		{
			if (ee()->TMPL->fetch_param('category_indicator') == 'category_url_title')
			{
				$cat_name_query	= 'c.cat_url_title';
			}
			elseif (strpos(ee()->TMPL->fetch_param('category_indicator'), 'allow_numeric') !== FALSE)
			{
				$allow_numeric_names	= TRUE;
			}
		}

		// -------------------------------------
		//	Handle uncategorized posts
		// -------------------------------------

		$include_uncategorized = (ee()->TMPL->fetch_param('include_uncategorized') !== FALSE AND $this->check_yes(ee()->TMPL->fetch_param('include_uncategorized'))) ? TRUE: FALSE;

		// -------------------------------------
		//	Assemble sql
		// -------------------------------------

		$select	= '/* Super Search get entries by category for later comparison */ SELECT cp.entry_id';
		$from	= ' FROM exp_category_posts cp';
		$join	= ' LEFT JOIN exp_categories c ON cp.cat_id = c.cat_id';
		$where	= ' WHERE c.site_id IN ('.implode(',', ee()->db->escape_str(ee()->TMPL->site_ids)).')';

		// -------------------------------------
		//	Group ids?
		// -------------------------------------

		if (count($group_ids) > 0)
		{
			$where	.= ' AND c.group_id IN ('.implode(',', ee()->db->escape_str($group_ids)).')';
		}

		// -------------------------------------
		// And's
		// -------------------------------------
		// This is our gnarly case. We're assembling an array of entry ids that belong to all the 'and'ed categories. Then passing that to our main query as a requirement.
		// -------------------------------------

		$chosen	= array();

		if (! empty($and))
		{
			// -------------------------------------
			//	We can have more than one conjoin block, so we loop. Each time through the loop we do DB work. We hit the DB separately for NOT's. AND searching is DB intensive and so it goes.
			// -------------------------------------

			foreach ($and as $key => $ands)
			{
				if (! empty($ands['main']))
				{
					// -------------------------------------
					//	Convert markers
					// -------------------------------------

					$ands['main']	= $this->ssu->convert_markers($ands['main']);

					// -------------------------------------
					//	Query for main ands
					// -------------------------------------

					$sqla	= array();

					if (($newand = $this->ssu->separate_numeric_from_textual($ands['main'])) !== FALSE)
					{
						$sql	= "SELECT cp.cat_id, cp.entry_id" . $from . $join . $where;

						if (empty($newand['numeric']) === FALSE)
						{
							if ($allow_numeric_names)
							{
								$sqla[]	= $cat_name_query." IN ('".implode("','", ee()->db->escape_str($newand['numeric']))."')";
							}
							else
							{
								$sqla[]	= " c.cat_id IN (".implode(",", ee()->db->escape_str($newand['numeric'])).")";
							}
						}

						if (empty($newand['textual']) === FALSE)
						{
							if ($exactness == 'exact')
							{
								$sqla[]	= $cat_name_query." IN ('".implode("','", ee()->db->escape_str($newand['textual']))."')";
							}
							else
							{
								foreach ($newand['textual'] as $temp)
								{
									$sqla[]	= $cat_name_query." LIKE '%" . ee()->db->escape_str($temp) . "%'";
								}
							}
						}

						$sql	.= " AND (" . implode(" OR ", $sqla) . ")";
					}
					else
					{
						$sql	= "SELECT cp.cat_id, cp.entry_id" . $from . $join . $where . " AND " . $cat_name_query . " IN ('".implode("','", ee()->db->escape_str($ands['main'])) . "')";
					}

					unset($newand);

					$query	= ee()->db->query($sql);

					if ($query->num_rows() > 0)
					{
						$ids			= array();
						$chosen[$key]	= array();

						foreach ($query->result_array() as $row)
						{
							$ids[$row['cat_id']][]	= $row['entry_id'];
							$chosen[$key]				= $row['entry_id'];
						}

						if (count($ids) > 1)
						{
							$chosen[$key]	= call_user_func_array('array_intersect', $ids);
							$chosen[$key]	= array_unique($chosen[$key]);
						}

						// -------------------------------------
						//	If the count of array keys of $ids is less than the count of $and, we have not found a match against each category. This means we did a conjoined search and asked for a category that had no entries assigned. The test for the category should still be counted and thus, this search needs to fail.
						// -------------------------------------

						if (count(array_keys($ids)) < count($ands['main']))
						{
							unset($chosen[$key]);
						}

						unset($ids);
					}
				}

				// -------------------------------------
				//	Do we need to exclude anything?
				// -------------------------------------

				if (! empty($ands['not']))
				{
					// -------------------------------------
					//	Convert markers
					// -------------------------------------

					$ands['not']	= str_replace($this->ssu->negatemarker, '', $ands['not']);

					// -------------------------------------
					//	Load up
					// -------------------------------------

					$sqla	= array();

					if (($newand = $this->ssu->separate_numeric_from_textual($ands['not'])) !== FALSE)
					{
						$sql	= "SELECT cp.cat_id, cp.entry_id" . $from . $join . $where;

						if (empty($newand['numeric']) === FALSE)
						{
							if ($allow_numeric_names)
							{
								$sqla[]	= $cat_name_query." IN ('".implode("','", ee()->db->escape_str($newand['numeric']))."')";
							}
							else
							{
								$sqla[]	= " c.cat_id IN (".implode(",", ee()->db->escape_str($newand['numeric'])).")";
							}
						}

						if (empty($newand['textual']) === FALSE)
						{
							if ($exactness == 'exact')
							{
								$sqla[]	= $cat_name_query." IN ('".implode("','", ee()->db->escape_str($newand['textual']))."')";
							}
							else
							{
								foreach ($newand['textual'] as $temp)
								{
									$sqla[]	= $cat_name_query." LIKE '%" . ee()->db->escape_str($temp) . "%'";
								}
							}
						}

						$sql	.= " AND (" . implode(" OR ", $sqla) . ")";
					}
					else
					{
						$sql	= "SELECT cp.cat_id, cp.entry_id" . $from . $join . $where . " AND " . $cat_name_query . " IN ('".implode("','", ee()->db->escape_str($ands['not'])) . "')";
					}

					unset($newand);

					$query	= ee()->db->query($sql);

					// -------------------------------------
					//	Assemble an array of nots, and run array diff to exclude
					// -------------------------------------

					$ids	= array();

					if ($query->num_rows() > 0)
					{
						foreach ($query->result_array() as $row)
						{
							$ids[]	= $row['entry_id'];
						}

						$chosen[$key]	= array_diff($chosen[$key], $ids);
					}
				}

				// -------------------------------------
				//	If $chosen ended up empty due to the array_diff above, unset it so that it fails a later test
				// -------------------------------------

				if (empty($chosen[$key]))
				{
					unset($chosen[$key]);
				}
			}

			// -------------------------------------
			//	If we had some AND tests, and they all failed AND there are no OR's or NOT's, then this whole query has failed. Since the ANDs failed, and nothing else is present, there can be nothing valid here.
			// -------------------------------------

			if (empty($chosen) AND empty($or) AND empty($not))
			{
				return FALSE;
			}

			// -------------------------------------
			//	Add $chosen to our eventual query
			// -------------------------------------

			if (! empty($chosen))
			{
				$andwhere	= array();

				foreach ($chosen as $choose)
				{
					$andwhere[]	= ' cp.entry_id IN ('.implode(',', ee()->db->escape_str($choose)).')';
				}

				$andwhere	= implode(' OR ', $andwhere);
			}
		}

		// -------------------------------------
		//	Or's
		// -------------------------------------

		if (empty($or) === FALSE)
		{
			// -------------------------------------
			//	Convert markers
			// -------------------------------------

			$or	= $this->ssu->convert_markers($or);

			// -------------------------------------
			//	Break it down chuck
			// -------------------------------------

			if (($newor = $this->ssu->separate_numeric_from_textual($or)) !== FALSE)
			{
				$temp	= array();

				if (! empty($newor['numeric']))
				{
					if ($allow_numeric_names)
					{
						$temp[]	= $cat_name_query." IN ('".implode("','", ee()->db->escape_str($newor['numeric']))."')";
					}
					else
					{
						$temp[]	= 'c.cat_id IN ('.implode(',', ee()->db->escape_str($newor['numeric'])).')';
					}
				}

				if (! empty($newor['textual']))
				{
					if ($exactness == 'exact')
					{
						$temp[]	= $cat_name_query." IN ('".implode("','", ee()->db->escape_str($newor['textual']))."')";
					}
					else
					{
						foreach ($newor['textual'] as $t)
						{
							$temp[]	= $cat_name_query." LIKE '%" . ee()->db->escape_str($t) . "%'";
						}
					}
				}

				$orwhere	= '(' . implode(' OR ', $temp) . ')';
			}
			else
			{
				if ($exactness == 'exact')
				{
					$orwhere	= $cat_name_query." IN ('".implode("','", ee()->db->escape_str($or))."')";
				}
				else
				{
					$temp	= array();

					foreach ($or as $t)
					{
						$temp[]	= $cat_name_query." LIKE '%" . ee()->db->escape_str($t) . "%'";
					}

					$orwhere	= '(' . implode(' OR ', $temp) . ')';
				}
			}
		}

		// -------------------------------------
		//	Not's
		// -------------------------------------
		//	We need a subquery to make this negation thing work because of the EE category structure.
		// -------------------------------------

		if (! empty($not))
		{
			$notwhere	= "\n " . 'SELECT ucp.entry_id FROM exp_category_posts ucp LEFT JOIN exp_categories c ON c.cat_id = ucp.cat_id WHERE 0=1 OR ';

			// -------------------------------------
			//	Convert markers
			// -------------------------------------

			$not	= $this->ssu->convert_markers($not);

			// -------------------------------------
			//	Break it down chuck
			// -------------------------------------

			if (($newnot = $this->ssu->separate_numeric_from_textual($not)) !== FALSE)
			{
				$temp	= array();

				if (! empty($newnot['numeric']))
				{
					if ($allow_numeric_names)
					{
						$temp[]	= $cat_name_query." IN ('".implode("','", ee()->db->escape_str($newnot['numeric']))."')";
					}
					else
					{
						$temp[]	= 'c.cat_id IN ('.implode(',', ee()->db->escape_str($newnot['numeric'])).')';
					}
				}

				if (! empty($newnot['textual']))
				{
					if ($exactness == 'exact')
					{
						$temp[]	= $cat_name_query." IN ('".implode("','", ee()->db->escape_str($newnot['textual']))."')";
					}
					else
					{
						foreach ($newnot['textual'] as $t)
						{
							$temp[]	= $cat_name_query." LIKE '%" . ee()->db->escape_str($t) . "%'";
						}
					}
				}

				$notwhere	.= implode(' OR ', $temp);
			}
			else
			{
				if ($exactness == 'exact')
				{
					$notwhere	.= $cat_name_query." IN ('".implode("','", ee()->db->escape_str($not))."')";
				}
				else
				{
					$temp	= array();

					foreach ($not as $t)
					{
						$temp[]	= $cat_name_query." LIKE '%" . ee()->db->escape_str($t) . "%'";
					}

					$notwhere	= '(' . implode(' OR ', $temp) . ')';
				}
			}

			$notwhere	= 'cp.entry_id NOT IN (' . $notwhere . ') ';
		}

		// -------------------------------------
		//	Not where
		// -------------------------------------
		//	Just like Google, NOT negates across the entire search string. So we AND this chunk onto the main SQL. http://support.google.com/websearch/bin/answer.py?hl=en&answer=136861
		// -------------------------------------

		if (! empty($notwhere))
		{
			$where	.= "\n AND " . $notwhere;
		}

		// -------------------------------------
		//	Assemble AND, OR
		// -------------------------------------
		//	The OR block and the AND block are each OR'd together. They each stand alone as conditions. You could ask for a very complicated AND test, but also throw in an OR test that permits something simple to also get in. Like you might say, show me only members who belong to the 'Baron' category AND the 'Fat' category AND the 'Harkonen' category (Baron&&Fat&&Harkonen) but you would also be fine if something belonging to the 'Sting' category also made it in, like this, search&category=Baron&&Fat&&Harkonen+Sting.
		// -------------------------------------

		if (! empty($andwhere) OR ! empty($orwhere))
		{
			$temp	= array();

			// -------------------------------------
			//	And where
			// -------------------------------------

			if (! empty($andwhere))
			{
				$temp[]	= $andwhere;
			}

			// -------------------------------------
			//	Or where
			// -------------------------------------

			if (! empty($orwhere))
			{
				$temp[]	= $orwhere;
			}

			// -------------------------------------
			//	Implode
			// -------------------------------------

			$where	.= "\n AND (";

			$where	.= implode(' OR ', $temp);

			$where	.= ")";
		}

		// -------------------------------------
		// Run it
		// -------------------------------------

		$sql	= $select . $from . $join . $where;

		$query	= ee()->db->query($sql);

		if ($query->num_rows() == 0)
		{
			ee()->TMPL->log_item('Super Search: Ending _get_ids_by_category() ('.(microtime(TRUE) - $t).')');
			return FALSE;
		}

		$ids	= array();

		foreach ($query->result_array() as $row)
		{
			$ids[]	= $row['entry_id'];
		}

		if ($include_uncategorized)
		{
			$sql = "SELECT t.entry_id FROM exp_channel_titles t LEFT OUTER JOIN exp_category_posts cp ON t.entry_id = cp.entry_id WHERE cp.entry_id IS NULL";

			$query = ee()->db->query($sql);

			if ($query->num_rows() == 0)
			{
				ee()->TMPL->log_item('Super Search: Handling uncategorized posts, none found ('.(microtime(TRUE) - $t).')');
			}
			else
			{
				foreach ($query->result_array() as $row)
				{
					$ids[]	= $row['entry_id'];
				}
			}
		}

		$ids	= array_unique($ids);

		ee()->TMPL->log_item('Super Search: Ending _get_ids_by_category() ('.(microtime(TRUE) - $t).')');

		return $ids;
	}

	//	End get ids by category

	// -------------------------------------------------------------

	/**
	 * Get users cookie id
	 *
	 * This method gets a user's cookie id if they have already been cookied. Otherwise a cookie id is created for them and provided.
	 *
	 * @access	private
	 * @return	string
	 */

	function _get_users_cookie_id()
	{
		// -------------------------------------
		//	Have we already done this?
		// -------------------------------------

		if (isset($this->sess['cookie_id']) === TRUE)
		{
			return $this->sess['cookie_id'];
		}

		// -------------------------------------
		//	Is their cookie already set?
		// -------------------------------------

		$input_cookie = ee()->input->cookie('super_search_history', TRUE);

		if ($input_cookie !== FALSE AND
			 is_numeric($input_cookie) AND
			 $input_cookie >= 10000 AND
			 $input_cookie <= 999999)
		{
			return $this->sess['cookie_id']	= $input_cookie;
		}

		// -------------------------------------
		//	Create a cookie, set it and return it
		// -------------------------------------

		$cookie	= mt_rand(10000, 999999);
		$this->set_cookie('super_search_history', $cookie, 86500);
		return $this->sess['cookie_id']	= $cookie;
	}

	//	End get users cookie id

	// -------------------------------------------------------------

	/**
	 * Hash it
	 *
	 * @access	private
	 * @return	string
	 */

	function _hash_it($arr = array())
	{
		if (is_array($arr) === FALSE OR count($arr) == 0) return FALSE;

		ksort($arr);

		$this->hash	= md5(serialize($arr));

		return $this->hash;
	}

	//	End hash it

	// -------------------------------------------------------------

	/**
	 * Highlight keywords
	 *
	 * I know you probably want me to use regular expressions here. My experiment is to test whether
	 * rand() plus simple str_replace is faster than some complex REGEX that I wouldn't be able to
	 * write in the first place.
	 *
	 * @access	private
	 * @return	string
	 */

	function _highlight_keywords($str = '')
	{
		if (ee()->TMPL->fetch_param('highlight_keywords') === FALSE OR empty($this->sess['search']['q']['keywords'])) return $str;

		$highlight_words_within_words	= (! empty($this->sess['search']['q']['highlight_words_within_words']) AND $this->check_no($this->sess['search']['q']['highlight_words_within_words'])) ? FALSE: TRUE;

		$this->ssu->set_property('highlight_words_within_words', $highlight_words_within_words);

		return $this->ssu->highlight_keywords($str, $this->sess['search']['q']['keywords'], ee()->TMPL->fetch_param('highlight_keywords'));
	}

	//	End highlight keywords

	// -------------------------------------------------------------

	/**
	 * History
	 *
	 * @access	public
	 * @return	string
	 */

	function history()
	{
		// -------------------------------------
		//	Who is this?
		// -------------------------------------

		// if (isset(ee()->session->userdata) === FALSE) return $this->no_results('super_search');

		if (($member_id = ee()->session->userdata('member_id')) === 0)
		{
			if (($cookie_id = $this->_get_users_cookie_id()) === FALSE)
			{
				return $this->no_results('super_search');
			}
		}

		// -------------------------------------
		//	Start the SQL
		// -------------------------------------

		$sql	= "/* Super Search fetch history items */ SELECT history_id AS super_search_id, search_date AS super_search_date, search_name AS super_search_name, results AS super_search_results, saved AS super_search_saved, query
					FROM exp_super_search_history
					WHERE site_id IN (".implode(',', ee()->db->escape_str(ee()->TMPL->site_ids)).")";

		if (empty($member_id) === FALSE)
		{
			$sql	.= " AND member_id = ".ee()->db->escape_str($member_id);
		}
		elseif (empty($cookie_id) === FALSE)
		{
			$sql	.= " AND cookie_id = ".ee()->db->escape_str($cookie_id);
		}

		// -------------------------------------
		//	Filter on saved?
		// -------------------------------------

		if (ee()->TMPL->fetch_param('saved') !== FALSE AND ee()->TMPL->fetch_param('saved') != '')
		{
			if (ee()->TMPL->fetch_param('saved') == 'yes')
			{
				$sql	.= " AND saved = 'y'";
			}
			elseif (ee()->TMPL->fetch_param('saved') == 'no')
			{
				$sql	.= " AND saved = 'n'";
			}
		}
		else
		{
			$sql	.= " AND saved = 'y'";
		}

		// -------------------------------------
		//	Order
		// -------------------------------------

		if (ee()->TMPL->fetch_param('orderby') !== FALSE AND in_array(ee()->TMPL->fetch_param('orderby'), array('results', 'saved', 'search_date')) === TRUE)
		{
			$sql	= " ORDER BY ".ee()->db->escape_str(ee()->TMPL->fetch_param('orderby'));

			if (ee()->TMPL->fetch_param('sort') !== FALSE AND in_array(ee()->TMPL->fetch_param('sort'), array('asc', 'desc')) === TRUE)
			{
				$sql	.= " ".ee()->db->escape_str(ee()->TMPL->fetch_param('sort'));
			}
		}
		elseif (ee()->TMPL->fetch_param('order') !== FALSE AND in_array(ee()->TMPL->fetch_param('order'), array('results', 'saved', 'search_date')) === TRUE)
		{
			$sql	= " ORDER BY ".ee()->db->escape_str(ee()->TMPL->fetch_param('order'));

			if (ee()->TMPL->fetch_param('sort') !== FALSE AND in_array(ee()->TMPL->fetch_param('sort'), array('asc', 'desc')) === TRUE)
			{
				$sql	.= " ".ee()->db->escape_str(ee()->TMPL->fetch_param('sort'));
			}
		}
		else
		{
			$sql	.= " ORDER BY search_date DESC";
		}

		// -------------------------------------
		//	Limit
		// -------------------------------------

		if (ee()->TMPL->fetch_param('limit') !== FALSE AND is_numeric(ee()->TMPL->fetch_param('limit')) === TRUE)
		{
			$sql	.= " LIMIT ".ee()->db->escape_str(ee()->TMPL->fetch_param('limit'));
		}

		// -------------------------------------
		//	Run query
		// -------------------------------------

		$query	= ee()->db->query($sql);

		if ($query->num_rows() === 0)
		{
			return $this->no_results('super_search');
		}

		// -------------------------------------
		//	Find out what we need from tagdata
		// -------------------------------------

		$i	= 0;

		foreach (ee()->TMPL->var_single as $key => $val)
		{
			$i++;

			if (strpos($key, 'format=') !== FALSE)
			{
				$full	= $key;
				$key	= preg_replace("/(.*?)\s+format=[\"'](.*?)[\"']/s", '\1', $key);
				$dates[$key][$i]['format']	= $val;
				$dates[$key][$i]['full']	= $full;
			}
		}

		// -------------------------------------
		//	Localize
		// -------------------------------------

		if (empty($dates) === FALSE)
		{
			setlocale(LC_TIME, ee()->session->userdata('time_format'));
		}

		// -------------------------------------
		//	Parse
		// -------------------------------------

		$prefix	= 'super_search_';
		$r		= '';
		$vars	= array();
		$i  	= 0;

		foreach ($query->result_array() as $row)
		{
			$i++;

			$tagdata	= ee()->TMPL->tagdata;

			// -------------------------------------
			//	Prep query into row
			// -------------------------------------

			if ($row['query'] != '')
			{
				$vars	= $this->_extract_vars_from_query(unserialize(base64_decode($row['query'])));
			}

			// -------------------------------------
			//	Add some additional data into our vars
			// -------------------------------------

			$row[$prefix.'count'] = $i;
			$row['count'] = $i;
			$row[$prefix.'total_results'] = $query->num_rows();
			$row['total_results'] = $query->num_rows();

			// -------------------------------------
			//	Conditionals and switch
			// -------------------------------------

			$tagdata	= ee()->functions->prep_conditionals($tagdata, $row);
			$tagdata	= ee()->functions->prep_conditionals($tagdata, $vars);
			$tagdata	= $this->_parse_switch($tagdata);

			// -------------------------------------
			//	Loop for dates
			// -------------------------------------

			if (empty($dates) === FALSE)
			{
				foreach ($dates as $field => $date)
				{
					foreach ($date as $key => $val)
					{
						if (isset($row[$field]) === TRUE AND is_numeric($row[$field]) === TRUE)
						{
							$tagdata	= str_replace(LD.$val['full'].RD, $this->_parse_date($val['format'], $row[$field]), $tagdata);
						}
					}
				}
			}

			unset($row['super_search_date']);

			// -------------------------------------
			//	Regular parse
			// -------------------------------------

			foreach ($row as $key => $val)
			{
				$key	= $key;

				if (strpos(LD.$key, $tagdata) !== FALSE) continue;

				$tagdata	= str_replace(LD.$key.RD, $val, $tagdata);
			}

			// -------------------------------------
			//	Variable parse
			// -------------------------------------

			foreach ($vars as $key => $val)
			{
				$key	= $key;

				if (strpos(LD.$key, $tagdata) !== FALSE) continue;

				$tagdata	= str_replace(LD.$key.RD, $val, $tagdata);
			}

			// -------------------------------------
			//	Parse empties
			// -------------------------------------

			$tagdata	= $this->ssu->strip_variables($tagdata);

			$r	.= $tagdata;
		}

		return $r;
	}

	//	End history

	// -------------------------------------------------------------

	/**
	 * Homogenize var name
	 *
	 * This methods adds the appropriate prefix of 'super_search' to the front of strings.
	 *
	 * @access	private
	 * @return	string
	 */

	function _homogenize_var_name($str = '')
	{
		if (strncmp('super_', $str, 6) == 0)
		{
			$str	= str_replace('super_', '', $str);
		}

		if (strncmp('search_', $str, 7) == 0)
		{
			$str	= str_replace('search_', '', $str);
		}

		return 'super_search_' . $str;
	}

	//	End homogenize var name

	// -------------------------------------------------------------

	/**
	 * In array insensitive
	 *
	 * PHP's in_array is case sensitive. We sometimes need a looser version
	 *
	 * @access	private
	 * @return	boolean
	 */

	private function _in_array_insensitive($str = '', $array = array())
	{
		return in_array(strtolower($str), array_map('strtolower', $array));
	}

	//	End in array insensitive

	// -------------------------------------------------------------

	/**
	 * Only numeric
	 *
	 * Returns an array containing only numeric values
	 *
	 * @access		private
	 * @return		array
	 */

	function _only_numeric($array)
	{
		if (empty($array) === TRUE) return array();

		if (is_array($array) === FALSE)
		{
			$array	= array($array);
		}

		foreach ($array as $key => $val)
		{
			if (preg_match('/[^0-9]/', $val) != 0 OR trim($val) == '') unset($array[$key]);
		}

		if (empty($array) === TRUE) return array();

		return $array;
	}

	//	End only numeric

	// -------------------------------------------------------------

	/**
	 * Parse date
	 *
	 * Parses an EE date string.
	 *
	 * @access	private
	 * @return	str
	 */

	function _parse_date($format = '', $date = 0)
	{
		if ($format == '' OR $date == 0) return '';

		// -------------------------------------
		//	strftime is much faster, but we have to convert date codes from what EE users expect to use
		// -------------------------------------

		// return strftime($format, $date);

		// -------------------------------------
		//	EE's built in date parser is slow, but for now we'll use it
		// -------------------------------------

		$codes	= $this->fetch_date_params($format);

		if (empty($codes)) return '';

		foreach ($codes as $code)
		{
			$format	= str_replace($code, $this->convert_timestamp($code, $date, TRUE, TRUE), $format);
		}

		return $format;
	}

	//	End parse date

	// -------------------------------------------------------------

	/**
	 * Parse date to timestamp
	 *
	 * Parses Super Search date string to a timestamp.
	 *
	 * @access	private
	 * @return	str
	 */

	function _parse_date_to_timestamp($date = '', $prefix = '', $full_day = FALSE)
	{
		if ($date == '') return '';

		$return	= '';

		$hour = 0; $minute = 0; $second = 0; $month = 1; $day = 1;

		if ($full_day === TRUE)
		{
			$hour = 23; $minute = 59; $second = 59; $month = 12; $day = 31;
		}

		$thedate	= $this->_split_date($date);

		//	mktime(hour, minute, second, month, day, year)

		ee()->load->helper('date');

		switch (count($thedate))
		{
			case 2:	// We have year only
				$day	= ($full_day === FALSE) ? $day: days_in_month($month, $thedate[0].$thedate[1]);
				$return	= $prefix .mktime($hour, $minute, $second, $month, $day, $thedate[0].$thedate[1]);
				break;
			case 3:	// We have year and month
				$day	= ($full_day === FALSE) ? $day: days_in_month($thedate[2], $thedate[0].$thedate[1]);
				$return	= $prefix . mktime($hour, $minute, $second, $thedate[2], $day, $thedate[0].$thedate[1]);
				break;
			case 4:	// We have year, month, day
				$return	= $prefix . mktime($hour, $minute, $second, $thedate[2], $thedate[3], $thedate[0].$thedate[1]);
				break;
			case 5:	// We have year, month, day and hour
				$return	= $prefix . mktime($thedate[4], $minute, $second, $thedate[2], $thedate[3], $thedate[0].$thedate[1]);
				break;
			case 6:	// We have year, month, day, hour and minute
				$return	= $prefix . mktime($thedate[4], $thedate[5], $second, $thedate[2], $thedate[3], $thedate[0].$thedate[1]);
				break;
			case 7:	// We have year, month, day, hour, minute and second
				$return	= $prefix . mktime($thedate[4], $thedate[5], $thedate[6], $thedate[2], $thedate[3], $thedate[0].$thedate[1]);
				break;
		}

		return $return;
	}

	//	End parse date to timestamp

	// -------------------------------------------------------------

	/**
	 * Parse from template params
	 *
	 * @access	private
	 * @return	string
	 */

	function _parse_from_tmpl_params()
	{
		// -------------------------------------
		//	Parse basic parameters
		// -------------------------------------
		//	We are looking for a parameter that we expect to occur only once. Its argument can contain multiple terms following the Google syntax for 'and' 'or' and 'not'.
		// -------------------------------------

		foreach ($this->basic as $key)
		{
			if (ee()->TMPL->fetch_param($key) !== FALSE AND ee()->TMPL->fetch_param($key) != '')
			{
				$param	= ee()->TMPL->fetch_param($key);

				// -------------------------------------
				//	Convert protected strings
				// -------------------------------------

				//	Double ampersands are allowed and indicate inclusive searching

				if (strpos($param, '&&') !== FALSE)
				{
					$param	= str_replace('&&', $this->doubleampmarker, $param);
				}

				//	Protect dashes for negation so that we don't have conflicts with dash in url titles

				if (strpos($param, $this->separator.'-') !== FALSE)
				{
					$param	= str_replace($this->separator.'-', $this->negatemarker, $param);
				}

				if (strpos($param, '-') === 0)
				{
					$param	= str_replace('-', $this->negatemarker, $param);
				}

				if (strpos($param, SLASH) !== FALSE OR strpos($param, $this->slash) !== FALSE)
				{
					$param	= str_replace(array(SLASH, $this->slash), '/', $param);
				}

				$q[$key]	= $param;
			}
		}

		if (empty($q) === TRUE)
		{
			// If the template params are totaly empty, default the limit
			// to the channel limit, just so we emulate what the
			// channel:entries tag does when passed no params
			// otherwise we'll cause issues with no params at all
			$q['limit'] = 100;
		}

		ksort($q);
		$this->sess['uri']	= $q;

		return $q;
	}

	//	End parse from template params

	// -------------------------------------------------------------

	/**
	 * Parse no results condition
	 *
	 * @access	private
	 * @return	boolean
	 */

	function _parse_no_results_condition($q = array(), $method = 'results')
	{
		if (preg_match(
				"/".LD."if " .preg_quote($this->lower_name)."_no_results" .
					RD."(.*?)".LD.preg_quote(T_SLASH, '/')."if".RD."/s",
				ee()->TMPL->tagdata,
				$match
			)
		)
		{
			$tagdata	= $match[1];
		}
		else
		{
			$tagdata	= '';
			//return $this->no_results();
		}

		if ($method == 'results')
		{
			if (strpos($tagdata, LD.'super_search_total_results'.RD) !== FALSE)
			{
				$tagdata	= str_replace(LD.'super_search_total_results'.RD, '0', $tagdata);
			}

			if (strpos(ee()->TMPL->template, LD.'super_search_total_results'.RD) !== FALSE)
			{
				ee()->TMPL->template	= str_replace(LD.'super_search_total_results'.RD, '0', ee()->TMPL->template);
			}
		}

		if ($method == 'curated_results')
		{
			if (strpos($tagdata, LD.'super_search_total_curated_results'.RD) !== FALSE)
			{
				$tagdata	= str_replace(LD.'super_search_total_curated_results'.RD, '0', $tagdata);
			}

			if (strpos(ee()->TMPL->template, LD.'super_search_total_curated_results'.RD) !== FALSE)
			{
				ee()->TMPL->template	= str_replace(LD.'super_search_total_curated_results'.RD, '0', ee()->TMPL->template);
			}
		}

		if (strpos($tagdata, LD.'super_search_suggestion'.RD) !== FALSE)
		{
			if (!isset($q['suggestion']))
			{
				$tagdata	= str_replace(LD.'super_search_suggestion'.RD, '', $tagdata);
			}
			else
			{
				$tagdata	= str_replace(LD.'super_search_suggestion'.RD, $q['suggestion'], $tagdata);
			}
		}

		$tagdata	= $this->_parse_template_vars($tagdata, $q);

		return $tagdata;
	}

	//	End parse no results condition

	// -------------------------------------------------------------

	/**
	 * Parse required condition
	 *
	 * @access	private
	 * @return	boolean
	 */

	function _parse_required_condition($tagdata = '', $required = array())
	{
		// -------------------------------------
		//	Just cleaning up?
		// -------------------------------------

		if (empty($required))
		{
			$tagdata	= ee()->functions->prep_conditionals($tagdata, array('super_search_missing_required_fields' => FALSE));
			$tagdata	= str_replace(LD . 'super_search_required_fields' . RD, '', $tagdata);

			return $tagdata;
		}

		// -------------------------------------
		//	Total results
		// -------------------------------------

		if (strpos(ee()->TMPL->template, LD.'super_search_total_results'.RD) !== FALSE)
		{
			ee()->TMPL->template	= str_replace(LD.'super_search_total_results'.RD, '0', ee()->TMPL->template);
		}

		// -------------------------------------
		//	Conditionals
		// -------------------------------------

		$tagdata	= ee()->functions->prep_conditionals($tagdata, array('super_search_missing_required_fields' => TRUE));

		// -------------------------------------
		//	Variable pair for super_search_required_fields
		// -------------------------------------

		$name	= 'super_search_required_fields';

		if (preg_match_all("|".LD.$name.'.*?'.RD.'(.*?)'.LD.preg_quote(T_SLASH, '/').$name.RD."|s", $tagdata, $matches))
		{
			foreach ($matches[0] as $key => $match)
			{
				$r		= '';

				foreach ($required as $k => $v)
				{
					$tdata	= $matches[1][$key];
					$tdata	= ee()->functions->prep_conditionals($tdata, array('super_search_name' => $k, 'super_search_label' => $v));
					$tdata	= str_replace(array(LD.'super_search_name'.RD, LD.'super_search_label'.RD), array($k, $v), $tdata);
					$r	.= $tdata;
				}

				$tagdata	= str_replace($matches[0][$key], $r, $tagdata);
			}
		}

		// -------------------------------------
		//	Pagination
		// -------------------------------------

		if (strpos($tagdata, LD . 'paginate') !== FALSE)
		{
			$tagdata	= preg_replace("/" . LD . "paginate" . RD . "(.*?)" . LD . preg_quote(T_SLASH, '/') . "paginate" . RD . "/s", "", $tagdata);
		}

		// -------------------------------------
		//	Return
		// -------------------------------------

		return $tagdata;
	}

	//	End parse required condition

	// -------------------------------------------------------------

	/**
	 * Parse template vars
	 *
	 * @access	private
	 * @return	string
	 */

	function _parse_template_vars($tagdata = '', $data = array())
	{
		$data	= (empty($data)) ? $this->sess('uri'): $data;

		// -------------------------------------
		//	Manipulate $data
		// -------------------------------------

		if (ee()->extensions->active_hook('super_search_parse_template_vars_end') === TRUE)
		{
			$prefix	= $this->either_or(ee()->TMPL->fetch_param('prefix'), 'super_search_');
			$data	= ee()->extensions->call('super_search_parse_template_vars_end', $this, $data, $prefix);
		}

		//	----------------------------------------
		//  Parse
		//	----------------------------------------

		$this->sess['parsables']	= $this->ssu->parse_template_vars(ee()->TMPL->template, $data, 'just_return');

		if (empty($tagdata))
		{
			ee()->TMPL->template	= $this->ssu->parse_template_vars(ee()->TMPL->template, $data);

			return '';
		}

		return $this->ssu->parse_template_vars($tagdata, $data);
	}

	//	End parse template vars

	// -------------------------------------------------------------

	/**
	 * Parse URI
	 *
	 * Tests for a URI segment with prefix of 'search&'. When found, explodes and parses that segment into search parameters. We remember to construct and save a URI appropriate query for use in pagination later.
	 *
	 * @access	private
	 * @return	array
	 */

	function _parse_uri($str = '')
	{
		$q	= array();

		//	----------------------------------------
		//  Eject! eject!
		//	----------------------------------------

		if (ee()->TMPL->fetch_param('dynamic') !== FALSE AND $this->check_no(ee()->TMPL->fetch_param('dynamic')) === TRUE) return FALSE;

		//	----------------------------------------
		//  Is there a search& string in $str or the page URI?
		//	----------------------------------------

		$str	= ($str == '') ? $this->ssu->get_uri('parse'): $str;

		//	----------------------------------------
		//  You ain't got no...
		//	----------------------------------------

		if (strpos($str, 'search'.$this->parser) === FALSE AND strpos($str, 'search?') === FALSE)
		{
			$this->_parse_template_vars();
			return FALSE;
		}

		//	----------------------------------------
		//  Google fix
		//	----------------------------------------
		//	This is a bit of hacky work around to get the search results uri working with Google Anyalicts
		//	For GA to parse the search results uri properly it needs a '?' in there
		//	We let the form pass it through, then replace it with our parser marker so the regex stays happy
		//	----------------------------------------

		$str = str_replace('search?','search'.$this->parser,$str);

		//	----------------------------------------
		//  Can we parse the uri into something useful?
		//	----------------------------------------

		if (($q	= $this->ssu->parse_uri($str)) === FALSE)
		{
			$this->_parse_template_vars();
			return FALSE;
		}

		//	----------------------------------------
		//  super_search_lib may have encountered someone trying to search on grid fields like this menu:appetizer=salad. We can take a handoff here and use the array later.
		//	----------------------------------------

		$this->grid_fields	= $this->ssu->grid_fields;

		//	----------------------------------------
		//  Pass cached bits across to our own cache
		//	----------------------------------------

		$this->sess['uri']		= $this->ssu->sess['uri'];
		$this->sess['newuri']	= $this->ssu->sess['newuri'];
		$this->sess['olduri']	= $this->ssu->sess['olduri'];

		//	----------------------------------------
		//  Override uri arguments with template params as appropriate
		//	----------------------------------------

		foreach ($this->basic as $key)
		{
			$q	= $this->_check_tmpl_params($key, $q);
		}

		//	----------------------------------------
		//	Parse search vars in template
		//	----------------------------------------

		$this->_parse_template_vars();

		//	----------------------------------------
		//	Manipulate $q
		//	----------------------------------------

		if (ee()->extensions->active_hook('super_search_parse_uri_end') === TRUE)
		{
			$q	= ee()->extensions->call('super_search_parse_uri_end', $this, $q);
		}

		//	----------------------------------------
		//	Return
		//	----------------------------------------

		return $q;
	}

	//	End parse URI

	// -------------------------------------------------------------

	/**
	 * Parse param
	 *
	 * Tests for a URI segment with prefix of 'search&'. When found, explodes and parses that segment into search parameters.
	 *
	 * @access	private
	 * @return	boolean
	 */

	function _parse_param()
	{
		if (ee()->TMPL->fetch_param('query') !== FALSE AND ee()->TMPL->fetch_param('query') != '')
		{
			if (strpos(ee()->TMPL->fetch_param('query'), 'search&') === FALSE)
			{
				return $this->_parse_uri('search&' . ee()->TMPL->fetch_param('query'));
			}
			else
			{
				return $this->_parse_uri(ee()->TMPL->fetch_param('query'));
			}
		}

		return FALSE;
	}

	//	End parse param

	// -------------------------------------------------------------

	/**
	 * Parse post
	 *
	 * We remember to construct and save a URI appropriate query for use in pagination later.
	 *
	 * @access	private
	 * @return	boolean
	 */

	function _parse_post()
	{
		// -------------------------------------
		//	Prep
		// -------------------------------------

		unset($_POST['XID'], $_POST['submit'], $_POST['csrf_token']);

		if (empty($_POST) === TRUE) return FALSE;

		if (ee()->TMPL->fetch_param('dynamic') !== FALSE AND $this->check_no(ee()->TMPL->fetch_param('dynamic')) === TRUE) return FALSE;

		$_POST	= ee('Security/XSS')->clean($_POST);

		// -------------------------------------
		//	Redirect POST?
		// -------------------------------------

		$redirect_post			= TRUE;
		$redirect_post_forced	= FALSE;

		if (! empty($_POST['redirect_post']))
		{
			if ($this->check_no($_POST['redirect_post']))
			{
				$redirect_post = FALSE;
			}

			$redirect_post_forced = TRUE;
		}

		// -------------------------------------
		//	Parse POST into search array
		// -------------------------------------

		if (($str = $this->ssu->parse_post($_POST)) === FALSE)
		{
			return FALSE;
		}

		// -------------------------------------
		//	Are we redirecting POST searches to the query string method?
		// -------------------------------------

		// We may have a race condition here
		// We're defaulting to yes, but that can get overridden by posts and template variables

		if ($redirect_post_forced !== TRUE)
		{
			// We haven't been passed a value to use from the post data
			// Inspect the tmpl params to see if its anywhere there
			if (ee()->TMPL->fetch_param('redirect_post') !== FALSE)
			{
				if ($this->check_no(ee()->TMPL->fetch_param('redirect_post')))
				{
					$redirect_post = FALSE;
				}
				elseif ($this->check_yes(ee()->TMPL->fetch_param('redirect_post')))
				{
					$redirect_post = TRUE;
				}
			}
		}

		if ($redirect_post === TRUE)
		{
			$str	= trim(str_replace(array(SLASH, '%26%26'), array($this->slash, '&&'), $str), '&');

			$return = '';

			if ($redirect_post == FALSE)
			{
				$return	= ee()->TMPL->fetch_param('redirect_post');
			}

			$return	= $this->_chars_decode($this->_prep_return($return)) . 'search'.$this->parser.$str.'/';

			if ($return != '')
			{
				ee()->functions->redirect($return);
				exit();
			}
		}

		// -------------------------------------
		//	Send it to _parse_uri()
		// -------------------------------------

		if (($q = $this->_parse_uri(ee()->uri->uri_string . 'search' . $this->parser . $str . '/')) === FALSE)
		{
			return FALSE;
		}

		return $q;
	}

	//	End parse post

	// -------------------------------------------------------------

	/**
	 * Parse search
	 *
	 * This routine hunts for a search string across template params, post, uri until it can return something juicy.
	 *
	 * @access	public
	 * @return	string
	 */

	function _parse_search()
	{
		// -------------------------------------
		//	Hardcoded query
		// -------------------------------------

		if (ee()->TMPL->fetch_param('search') !== FALSE AND ee()->TMPL->fetch_param('search') != '')
		{
			$str	= (strpos(ee()->TMPL->fetch_param('search'), 'search&') === FALSE) ? 'search&' . ee()->TMPL->fetch_param('search'): ee()->TMPL->fetch_param('search');

			// -------------------------------------
			//	Handle special case of start param for pagination. When users say they want pagination but they are using the 'search' param, we need to reach into the URI and try to find the 'start' param and work from there. Kind of duct tape like.
			// -------------------------------------

			if (ee()->TMPL->fetch_param('paginate') !== FALSE AND ee()->TMPL->fetch_param('paginate') != '')
			{
				if (preg_match('/' . $this->parser . 'offset' . $this->separator . '(\d+)' . '/s', ee()->uri->uri_string, $match))
				{
					if (preg_match('/' . $this->parser . 'offset' . $this->separator . '(\d+)' . '/s', $str, $secondmatch))
					{
						$str	= str_replace($secondmatch[0], $match[0], $str);
					}
					else
					{
						$str	= str_replace('search' . $this->parser, 'search' . $this->parser . trim($match[0], $this->parser) . $this->parser, $str);
					}
				}
			}

			// -------------------------------------
			//	Handle the special case where users have given the inclusive_keywords param
			// -------------------------------------

			if (ee()->TMPL->fetch_param('inclusive_keywords') !== FALSE AND $this->check_no(ee()->TMPL->fetch_param('inclusive_keywords')))
			{
				$str	= str_replace('search' . $this->parser, 'search' . $this->parser . 'inclusive_keywords' . $this->separator . 'no' . $this->parser, $str);
			}

			// -------------------------------------
			//	Handle the special case where users have given the inclusive_categories param
			// -------------------------------------

			if (ee()->TMPL->fetch_param('inclusive_categories') !== FALSE AND $this->check_yes(ee()->TMPL->fetch_param('inclusive_categories')))
			{
				$str	= str_replace('search' . $this->parser, 'search' . $this->parser . 'inclusive_categories' . $this->separator . 'yes' . $this->parser, $str);
			}

			if (($q = $this->_parse_uri($str.'/')) === FALSE)
			{
				return FALSE;
			}
		}

		// -------------------------------------
		//	Otherwise we accept search queries
		//	from either	URI or POST. See if either
		//	is present, defaulting to POST.
		// -------------------------------------

		else
		{
			if (($q = $this->_parse_post()) === FALSE)
			{
				if (($q = $this->_parse_uri()) === FALSE)
				{
					if (($q = $this->_parse_from_tmpl_params()) === FALSE)
					{
						return FALSE;
					}
				}
			}
		}

		// -------------------------------------
		//	Good job get out
		// -------------------------------------

		if (empty($q)) return FALSE;

		return $q;
	}

	//	End parse search

	// -------------------------------------------------------------

	/**
	 * Parse switch
	 *
	 * Parses the friends_switch variable so that admins can create zebra stripe UI's.
	 *
	 * @access	private
	 * @return	str
	 */

	function _parse_switch($tagdata = '')
	{
		if ($tagdata == '') return '';

		// -------------------------------------
		//	Parse Switch
		// -------------------------------------

		if ($this->parse_switch != '' OR preg_match("/".LD."(switch\s*=(.+?))".RD."/is", $tagdata, $match) > 0)
		{
			$this->parse_switch	= ($this->parse_switch != '') ? $this->parse_switch: $match;

			$val	= $this->cycle(explode('|', str_replace(array('"', "'"), '', $this->parse_switch['2'])));

			$tagdata = str_replace($this->parse_switch['0'], $val, $tagdata);
		}

		return $tagdata;
	}

	//	End parse date

	// -------------------------------------------------------------

	/**
	 * Parse for required
	 *
	 * If some search arguments have been required to make for a valid search, test their presence here.
	 *
	 * @access	private
	 * @return	str
	 */

	function _parse_for_required($q = array())
	{
		// -------------------------------------
		//	Is anything being required?
		// -------------------------------------

		if (isset(ee()->TMPL) === TRUE AND ee()->TMPL->fetch_param('required') !== FALSE AND ee()->TMPL->fetch_param('required') != '')
		{
			$required	= explode('|', ee()->TMPL->fetch_param('required'));

			foreach ($required as $val)
			{
				$out[$val]	= ucwords(str_replace('_', ' ', $val));
			}

			$required	= $out;
		}
		else
		{
			return FALSE;
		}

		// -------------------------------------
		//	Loop through our query and see what's required
		// -------------------------------------

		foreach ($q as $key => $val)
		{
			if (is_string($val) === TRUE AND ! empty($val))
			{
				unset($required[$key]);
			}

			if (is_array($val) === TRUE)
			{
				foreach ($val as $k => $v)
				{
					if (is_string($v) === TRUE AND ! empty($v))
					{
						unset($required[$k]);
					}
				}
			}
		}

		// -------------------------------------
		//	Is there anything left in $required?
		// -------------------------------------

		if (! empty($required))
		{
			return $required;
		}

		// -------------------------------------
		//	Return happy.
		// -------------------------------------

		return FALSE;
	}

	//	End parse for required

	// -------------------------------------------------------------

	/**
	 * _pref()
	 *
	 * @access	private
	 * @return	array
	 */

	private function _pref($pref)
	{
		$p = $this->fetch('Preference')
			->filter('site_id', ee()->config->item('site_id'))
			->filter('preference_name', $pref)
			->first();

		if (! $p)
		{
			$default_prefs = $this->make('Preference')->default_prefs;

			if (isset($default_prefs[$pref]['default']))
			{
				return $default_prefs[$pref]['default'];
			}
			else
			{
				return FALSE;
			}
		}

		return $p->preference_value;
	}

	//	End _pref()

	// -------------------------------------------------------------

	/**
	 * Prep author
	 *
	 * @access	private
	 * @return	array
	 */

	function _prep_author($author = array())
	{
		if (empty($author['not']) === TRUE AND empty($author['or']) === TRUE) return FALSE;

		$indicator	= 'username';

		if (ee()->TMPL->fetch_param('author_indicator') !== FALSE AND in_array(ee()->TMPL->fetch_param('author_indicator'), array('author_id', 'member_id', 'screen_name', 'username', 'email')) === TRUE)
		{
			$indicator	= ee()->TMPL->fetch_param('author_indicator');
		}

		$indicator	= ($indicator == 'author_id') ? 'member_id': $indicator;

		$sql	= '/* Super Search: ' . __FUNCTION__ . ' */
		SELECT member_id FROM exp_members WHERE member_id != 0';

		if (! empty($author['not']))
		{
			foreach ($author['not'] as $key => $val)
			{
				$author['not'][$key]	= $this->ssu->escape_str($val);
			}

			if (isset($this->sess['search']['q']['partial_author']) === TRUE AND $this->check_yes($this->sess['search']['q']['partial_author']) == TRUE AND $indicator != 'member_id' AND $indicator != 'author_id')
			{
				$sql .= ' AND (';

				foreach($author['not'] AS $single)
				{
					$sql .= '(' . $indicator . ' NOT LIKE \'%' . $this->ssu->escape_str($single) . '%\') AND ';
				}

				$sql .= ' 1=1) ';

			}
			else
			{
				$sql	.= ' AND '.$indicator.' NOT IN (\''.implode("','", $author['not']).'\')';
			}
		}

		if (empty($author['or']) === FALSE)
		{
			foreach ($author['or'] as $key => $val)
			{
				$author['or'][$key]	= $this->ssu->escape_str($val);
			}

			if (isset($this->sess['search']['q']['partial_author']) === TRUE AND $this->check_yes($this->sess['search']['q']['partial_author']) == TRUE AND $indicator != 'member_id' AND $indicator != 'author_id')
			{
				$sql .= ' AND (';

				foreach($author['or'] AS $single)
				{
					$sql .= '(' . $indicator . ' LIKE \'%' . $this->ssu->escape_str($single) . '%\') OR ';
				}

				$sql .= ' 1=0) ';

			}
			else
			{
				$sql	.= ' AND '.$indicator.' IN (\''.implode("','", $author['or']).'\')';
			}
		}

		unset($author);

		$query	= ee()->db->query($sql);

		if ($query->num_rows() == 0) return FALSE;

		foreach ($query->result_array() as $row)
		{
			$author[]	= $row['member_id'];
		}

		return $author;
	}

	//	End prep author

	// -------------------------------------------------------------

	/**
	 * Prep channel
	 *
	 * Returns an array of channel ids. This method has a wide ranging effect on behaviour. It looks for hard coded channel ids in a template param. As well, it looks for channel names provided in either a template param called 'channel' or in the URI preceded by the marker 'channel'. In either of these last two cases, regular search syntax can be used to include or exclude channels for search.
	 *
	 * @access	private
	 * @return	array
	 */

	function _prep_channel($channel_string = '')
	{
		// -------------------------------------
		//	Do we have hardcoded channel ids?
		// -------------------------------------

		$channel_ids	= array();

		if (ee()->TMPL->fetch_param('channel_id') !== FALSE AND ee()->TMPL->fetch_param('channel_id') != '')
		{
			$channel_ids	= explode('|', ee()->TMPL->fetch_param('channel_id'));
		}

		// -------------------------------------
		//	Channel names in a param or in the URI.
		// -------------------------------------
		//	Remember, these can come through the 'channel' template param or through the 'channel' marker in the URI. Search syntax applies such that negated channels have their entries excluded from search.
		// -------------------------------------

		if (! empty($channel_string))
		{
			// -------------------------------------
			//	Break this into an array if it's a string
			// -------------------------------------

			if (is_string($channel_string))
			{
				$channel_names = $this->_prep_keywords($channel_string);
			}
			elseif (is_array($channel_string))
			{
				$channel_names	= $channel_string;
			}

			// -------------------------------------
			//	Do we have hardcoded channel names?
			// -------------------------------------

			if (ee()->TMPL->fetch_param('channel') !== FALSE AND ee()->TMPL->fetch_param('channel') != '')
			{
				// -------------------------------------
				//	Are we negating?
				// -------------------------------------

				if (strpos(ee()->TMPL->fetch_param('channel'), 'not ') === 0)
				{
					$channel_names['not']	= (isset($channel_names['not']) === TRUE) ? $channel_names['not']: array();
					$channel_names['not']	= array_merge(explode("|", str_replace("not ", "", ee()->TMPL->fetch_param('channel'))));
				}
				else
				{
					$params					= explode("|", ee()->TMPL->fetch_param('channel'));
					$channel_names['or']	= (isset($channel_names['or'])) ? $channel_names['or']: array();
					$channel_names['or']	= (! empty($channel_names['or'])) ? array_intersect($params, $channel_names['or']) : $params;

					// -------------------------------------
					//	If this specific filter § in no channel names, then the user asked to search for channels that were not allowed in the channel param. We should fail out in this condition.
					// -------------------------------------

					if (empty($channel_names['or']))
					{
						return FALSE;
					}
				}
			}
		}

		// -------------------------------------
		//	Loop and filter
		// -------------------------------------

		$ids		= array();
		$channels	= array();

		foreach ($this->model('Data')->get_channels_by_site_id_and_channel_id(ee()->TMPL->site_ids, $channel_ids) as $row)
		{
			// -------------------------------------
			//	We don't want excluded blogs in our arrays
			// -------------------------------------

			if (! empty($channel_names['not']) AND (in_array($row['channel_name'], $channel_names['not']) OR in_array($row['channel_id'], $channel_names['not']))) continue;

			// -------------------------------------
			//	And if we only want certain blogs, then filter as well.
			// -------------------------------------

			if (! empty($channel_names['or']) AND ! in_array($row['channel_name'], $channel_names['or']) AND ! in_array($row['channel_id'], $channel_names['or'])) continue;

			// -------------------------------------
			//	Populate arrays.
			// -------------------------------------

			$ids[]							= $row['channel_id'];
			$channels[$row['channel_id']]	= $row;
		}

		// -------------------------------------
		//	Empty after filtering? Fail out
		// -------------------------------------

		if (count($ids) == 0) return FALSE;

		// -------------------------------------
		//	Add results to cache and return
		// -------------------------------------

		sort($ids);
		$this->sess['search']['channel_ids']	= $ids;
		$this->sess['search']['channels']		= $channels;

		return $ids;
	}

	//	End prep channel

	// -------------------------------------------------------------

	/**
	 * _prep_grid_sql()
	 *
	 * @access	private
	 * @return	array
	 */

	private function _prep_grid_sql($field_name, $operator, $val, $channel_sql = '')
	{
		$sql	= '';

		// -------------------------------------
		//	Get grid fields
		// -------------------------------------

		$grid_fields	= $this->_grid_fields();

		// -------------------------------------
		//	Does our field exist?
		// -------------------------------------

		if (! isset($grid_fields[$field_name])) return FALSE;

		// -------------------------------------
		//	SQL
		// -------------------------------------

		$sql	= array();

		// -------------------------------------
		//	Quotes
		// -------------------------------------

		$like	= ($operator == 'LIKE' OR $operator == 'NOT LIKE') ? '%': '';

		$quotes	= (is_numeric($val) AND $like != '%') ? "": "'";

		// -------------------------------------
		//	Table
		// -------------------------------------

		foreach ($grid_fields[$field_name] as $grid)
		{
			if ($grid['exact_type'] == 'matrix')
			{
				if (! isset($this->sess['matrix_table']))
				{
					$this->sess['search']['from'][]	= 'LEFT JOIN exp_matrix_data mtx ON mtx.entry_id = t.entry_id';
				}

				$this->sess['matrix_table'][]	= 'field_id_' . $grid['field_id'];

				// -------------------------------------
				//	Is this is a date column
				// -------------------------------------

				if ($grid['col_type'] == 'date')
				{
					$val	= $this->_parse_date_to_timestamp($val);

					if ($operator == 'LIKE')
					{
						$operator	= '=';
						$quotes		= '';
						$like		= '';
					}
				}

				// -------------------------------------
				//	Switch
				// -------------------------------------

				$sql[]	= '(mtx.col_id_' . $grid['col_id'] . ' ' . $operator . ' ' . $quotes . $like . $val . $like . $quotes . $channel_sql . ')';
			}
			else
			{
				$table	= 'exp_channel_grid_field_' . $grid['field_id'];

				if (! isset($this->sess['grid_table'][$grid['field_id']]))
				{
					$this->sess['search']['from'][]	= 'LEFT JOIN ' . $table . ' ON ' . $table . '.entry_id = t.entry_id';

					$this->sess['grid_table'][$grid['field_id']]	= $table;
				}

				// -------------------------------------
				//	Is this is a date column
				// -------------------------------------

				if ($grid['col_type'] == 'date')
				{
					$val		= $this->_parse_date_to_timestamp($val);

					if ($operator == 'LIKE')
					{
						$operator	= '=';
						$quotes		= '';
						$like		= '';
					}
				}

				$sql[]	= '(' . $table . '.col_id_' . $grid['col_id'] . ' ' . $operator . ' ' . $quotes . $like . $val . $like . $quotes . $channel_sql . ')';
			}
		}

		// -------------------------------------
		//	Return
		// -------------------------------------

		return implode(' OR ', $sql);
	}

	//	End _prep_grid_sql()

	// -------------------------------------------------------------

	/**
	 * Prep group
	 *
	 * @access	private
	 * @return	array
	 */

	function _prep_group($group = array())
	{
		if (empty($group['not']) AND empty($group['or'])) return FALSE;

		// -------------------------------------
		//	Conjoined searching on group is invalid and returns no results.
		// -------------------------------------

		if (! empty($group['and'])) return FALSE;

		$sql	= '/* Super Search: ' . __FUNCTION__ . ' */
		SELECT m.member_id FROM exp_members m LEFT JOIN exp_member_groups mg ON mg.group_id = m.group_id WHERE mg.site_id = ' . ee()->db->escape_str(ee()->config->item('site_id'));

		if (! empty($group['not']))
		{
			foreach ($group['not'] as $key => $val)
			{
				$group['not'][$key]	= $this->ssu->escape_str($val);
			}

			$sql	.= ' AND mg.group_title NOT IN (\''.implode("','", $group['not']).'\')';
			$sql	.= ' AND mg.group_id NOT IN (\''.implode("','", $group['not']).'\')';
		}

		if (! empty($group['or']))
		{
			foreach ($group['or'] as $key => $val)
			{
				$group['or'][$key]	= $this->ssu->escape_str($val);
			}

			$sql	.= ' AND (mg.group_title IN (\''.implode("','", $group['or']).'\')';
			$sql	.= ' OR mg.group_id IN (\''.implode("','", $group['or']).'\'))';
		}

		unset($group);

		$query	= ee()->db->query($sql);

		if ($query->num_rows() == 0) return FALSE;

		foreach ($query->result_array() as $row)
		{
			$group[]	= $row['member_id'];
		}

		return $group;
	}

	//	End prep group

	// -------------------------------------------------------------

	/**
	 * Prep site ids
	 *
	 *  We want to be able to dynamically assign site ids, but we need to be careful here on what we're letting through
	 *
	 * @access	private
	 * @return	array
	 */

	function _prep_site_ids($site = '')
	{
		if ($site == '') return ee()->TMPL->site_ids;

		$arr = array();

		$str = $this->spaces;

		$sites = ee()->TMPL->sites;

		if (strstr($site, $this->pipes) !== FALSE) $str = $this->pipes;

		foreach(explode($str, $site) AS $site_id => $val)
		{
			if (is_numeric($val))
			{
				if (isset($sites[$val])) $arr[] = $val;
			}
			else
			{
				foreach(ee()->TMPL->sites as $site_id => $site_name)
				{
					if ($site_name == $val) $arr[] = $site_id;
				}
			}
		}

		if (empty ($arr)) $arr = ee()->TMPL->site_ids;
		else $arr = array_unique($arr);

		return $arr;
	}

	//	End prep_site_ids

	// -------------------------------------------------------------

	/**
	 * Clean keywords
	 *
	 *
	 * @access	private
	 * @return	string
	 */

	function _clean_keywords($keywords = '')
	{
		// -------------------------------------
		//	Convert spaces
		// -------------------------------------

		if (strpos($keywords, $this->spaces) !== FALSE)
		{
			$keywords	= str_replace($this->spaces, ' ', $keywords);
		}

		return $keywords;
	}

	//	End clean_keywords

	// -------------------------------------------------------------

	/**
	 * Prep keywords
	 *
	 * REGEX is expensive stuff. We could rewrite this method to explode into individual characters, loop through the resulting array, flag our identifiers like negation, quotes and such, and assemble keywords again as we go. Might be much faster. But as it stands, the method, on most keyword strings, executes silly fast.
	 *
	 * @access	private
	 * @return	array
	 */

	function _prep_keywords($keywords = '', $inclusive = FALSE, $type = '', $search = array())
	{
		// -------------------------------------
		//  Parse the uri array into something SQL-able
		// -------------------------------------

		if (($keywords = $this->ssu->prep_keywords($keywords, $inclusive, $type, $search)) === FALSE) return FALSE;

		// -------------------------------------
		//  Do we want fuzzy matching?
		// -------------------------------------

		if ($this->_pref('enable_fuzzy_searching') == 'y' AND $type == 'keywords')
		{
			$keywords = $this->_prep_fuzzy_keywords($keywords);
		}

		// -------------------------------------
		//  Return
		// -------------------------------------

		return $keywords;
	}

	//	End prep keywords

	// -------------------------------------------------------------

	/**
	 * Prep Fuzzy Keywords
	 *
	 * @access private
	 * @return array
	 */

	function _prep_fuzzy_keywords($arr = array())
	{
		// -------------------------------------
		//	Pre prep hook
		// -------------------------------------

		if (ee()->extensions->active_hook('super_search_prep_fuzzy_keywords_start') === TRUE)
		{
			$arr	= ee()->extensions->call('super_search_prep_fuzzy_keywords_start', $this, $arr);
		}

		// We only want to use the fuzzy methods that are enabled
		// in this order

		// 1. Phonetics
		// 2. Plurals
		// 3. Basic spelling

		if ($this->_pref('enable_fuzzy_searching_phonetics') == 'y' AND ! empty($arr))
		{
			$arr = $this->_prep_fuzzy_phonetics($arr);
		}

		if ($this->_pref('enable_fuzzy_searching_plurals') == 'y' AND ! empty($arr))
		{
			$arr = $this->_prep_fuzzy_plurals($arr);
		}

		if ($this->_pref('enable_fuzzy_searching_spelling') == 'y' AND ! empty($arr))
		{
			$arr = $this->_prep_fuzzy_spelling($arr);
		}

		// -------------------------------------
		//	Post prep hook
		// -------------------------------------

		if (ee()->extensions->active_hook('super_search_prep_fuzzy_keywords_end') === TRUE)
		{
			$arr	= ee()->extensions->call('super_search_prep_fuzzy_keywords_end', $this, $arr);
		}

		return $arr;
	}

	//	End prep fuzzy keywords

	// -------------------------------------------------------------

	/**
	 * Prep Fuzzy Keywords - phonetics
	 *
	 * @access private
	 * @return array
	 */

	function _prep_fuzzy_phonetics($arr = array())
	{
		ee()->TMPL->log_item('Super Search: preping for fuzzy search by phonetics ');

		$terms = array();

		// Handle 'ye ORs
		foreach($arr['or'] as $or)
		{
			if (ctype_digit($or) === TRUE) continue;

			$terms[] = " SOUNDEX('".$this->ssu->escape_str($or)."') ";
		}

		// Handle 'ye ANDs
		foreach($arr['and'] as $and)
		{
			if (! empty($and['main']))
			{
				foreach ($and['main'] as $term)
				{
					$term	= $this->ssu->convert_markers($term);

					if (ctype_digit($term) === TRUE) continue;

					$terms[] = " SOUNDEX('".$this->ssu->escape_str($term)."') ";
				}
			}
		}

		if (empty($terms)) return $arr;

		$sql = " SELECT term, term_soundex, first_seen, last_seen, count, entry_count
					FROM exp_super_search_terms
					WHERE term_soundex IN
						(" . implode(", ", $terms) . ") ";

		$query = $this->model('Data')->check_sql($sql);

		if ($query === FALSE) return $arr;

		$temp = array();

		foreach($query->result_array() as $row)
		{
			// We should have our original terms in here too
			if ($this->_in_array_insensitive($row['term'], array_values($arr['or'])))
			{
				$temp[$row['term_soundex']] = $row['term'];
			}

			foreach ($arr['and'] as $and)
			{
				// We should have our original terms in here too
				if (isset($and['main']) AND $this->_in_array_insensitive($row['term'], array_values($and['main'])))
				{
					$temp[$row['term_soundex']] = $row['term'];
				}
			}
		}

		foreach($query->result_array() as $row)
		{
			// Get the matching top term for this $row
			if (! ($this->_in_array_insensitive($row['term'], array_values($arr['or']))))
			{
				if (isset($temp[$row['term_soundex']]))
				{
					$parent = $temp[$row['term_soundex']];

					// Append this to the proper place
					if ($this->_in_array_insensitive($parent, array_values($arr['or'])))
					{
						//$arr['or'][] = $row['term'];

						if (isset($arr['or_fuzzy'][$parent]) === FALSE)
						{
							$arr['or_fuzzy'][$parent] = array();
						}

						$arr['or_fuzzy'][$parent][] =$row['term'];
					}
				}
			}

			foreach ($arr['and'] as $and)
			{
				if (isset($and['main']))
				{
					// Get the matching top term for this $row
					if ($this->_in_array_insensitive($row['term'], array_values($and['main'])))
					{
						if (isset($temp[$row['term_soundex']]))
						{
							$parent = $temp[$row['term_soundex']];

							// Append this to the proper place
							if ($this->_in_array_insensitive($parent, array_values($and['main'])))
							{
								if (isset($arr['and_fuzzy'][$parent]) === FALSE)
								{
									$arr['and_fuzzy'][$parent] = array();
								}

								$arr['and_fuzzy'][$parent][] = $row['term'];
							}
						}
					}
				}
			}
		}

		ee()->TMPL->log_item('Super Search: finished fuzzy phonetic keyword adjustment');

		return $arr;
	}

	//	End prep fuzzy keywords

	// -------------------------------------------------------------

	/**
	 * Prep Fuzzy Keywords - plurals
	 *
	 * @access private
	 * @return array
	 */

	function _prep_fuzzy_plurals($arr = array())
	{
		ee()->TMPL->log_item('Super Search: preping for fuzzy search by plurals ');

		$terms = array();

		// Handle 'ye ORs
		foreach($arr['or'] as $or)
		{
			$terms[] = $this->ssu->escape_str($or);
		}

		// Handle 'ye ANDs
		foreach($arr['and'] as $and)
		{
			if (isset($and['main']))
			{
				foreach ($and['main'] as $term)
				{
					$terms[] = $this->ssu->escape_str($term);
				}
			}
		}

		if (empty($terms)) return $arr;

		$suggestions = array();
		$all = array();

		// Get our list of potential pluarls (and singulars)
		foreach($terms as $term)
		{
			$temp = $this->_suggestion_plurals($term);

			$suggestions[$term] = $temp;

			$all = array_merge($all, $temp);
		}

		// Test these variants to see if they exist in the lexicon
		$sql = " SELECT term FROM exp_super_search_terms
					WHERE term in ('" . implode("','", ee()->db->escape_str($all)) . "')
					AND entry_count > 0 ";

		$query = $this->model('Data')->check_sql($sql);

		if ($query === FALSE) return $arr;

		$suggestions_valid = array();

		// now filter it down with just the valid terms in our lexicon
		if ($query->num_rows() > 0)
		{
			foreach($query->result_array() as $row)
			{
				foreach($suggestions as $parent => $variants)
				{
					if (in_array($row['term'], $variants))
					{
						$suggestions_valid[$parent][] = $row['term'];
					}
				}
			}
		}

		if (count($suggestions_valid) > 0)
		{
			// We have some valid suggestions

			foreach($suggestions_valid as $parent => $valid)
			{
				if ($this->_in_array_insensitive($parent, $arr['or']))
				{
					if (isset($arr['or_fuzzy'][$parent]) === FALSE)
					{
						$arr['or_fuzzy'][$parent] = array();
					}

					$arr['or_fuzzy'][$parent] = array_merge($arr['or_fuzzy'][$parent], $valid);
				}

				if (isset($arr['and']))
				{
					foreach ($arr['and'] as $key => $val)
					{
						if (isset($val['main']) AND $this->_in_array_insensitive($parent, $val['main']))
						{
							if (! isset($arr['and_fuzzy'][$parent]))
							{
								$arr['and_fuzzy'][$parent] = array();
							}

							$arr['and_fuzzy'][$parent][] = $valid;
						}
					}
				}
			}
		}

		ee()->TMPL->log_item('Super Search: finished fuzzy plurals keyword adjustment');

		return $arr;
	}

	//	End prep fuzzy pluarls

	// -------------------------------------------------------------

	/**
	 * Prep Fuzzy Keywords - spelling
	 *
	 * @access private
	 * @return array
	 */

	function _prep_fuzzy_spelling($arr = array())
	{
		ee()->TMPL->log_item('Super Search: prepping for fuzzy search by spelling ');

		$terms = array();

		// Handle 'ye ORs
		foreach($arr['or'] as $or)
		{
			$terms[$or] = $this->ssu->escape_str($or);
		}

		// Handle 'ye ANDs
		foreach($arr['and'] as $and)
		{
			if (isset($and['main']))
			{
				foreach ($and['main'] as $term)
				{
					$terms[$term] = $this->ssu->escape_str($term);
				}
			}
		}

		if (empty($terms)) return $arr;

		$suggestions = array();
		$all = array();

		// Now we only have a terms list that contains invalid spellings (at least spellings that don't appear
		// in our corpus.) Get our list of potential spellings
		$suggestions_valid = $this->_suggestion_spelling($terms);

		if (count($suggestions_valid) > 0)
		{
			// We have some valid suggestions
			foreach($suggestions_valid as $parent => $valid)
			{
				if ($this->_in_array_insensitive($parent, $arr['or']))
				{
					if (isset($arr['or_fuzzy'][$parent]) === FALSE)
					{
						$arr['or_fuzzy'][$parent] = array();
					}

					$arr['or_fuzzy'][$parent][] = $valid;
				}

				if (isset($arr['and']))
				{
					foreach ($arr['and'] as $key => $val)
					{
						if (isset($val['main']) AND $this->_in_array_insensitive($parent, $val['main']))
						{
							if (! isset($arr['and_fuzzy'][$parent]))
							{
								$arr['and_fuzzy'][$parent] = array();
							}

							$arr['and_fuzzy'][$parent][] = $valid;
						}
					}
				}
			}
		}

		ee()->TMPL->log_item('Super Search: finished fuzzy spelling keyword adjustment');

		return $arr;
	}

	//	End prep fuzzy spelling

	// -------------------------------------------------------------

	/**
	 * Prep order
	 *
	 * @access	private
	 * @return	string
	 */

	function _prep_order($order = '')
	{
		$arr	= array();

		// -------------------------------------
		//	Sticky test
		// -------------------------------------

		if (ee()->TMPL->fetch_param('sticky') === FALSE OR $this->check_no(ee()->TMPL->fetch_param('sticky')) === FALSE)
		{
			$arr[]	= 't.sticky DESC';
		}

		// -------------------------------------
		//	Graceful fail
		// -------------------------------------

		if ($order == '')
		{
			$arr[]	= 't.entry_date DESC';
			$arr[]	= 't.entry_id DESC';
			return ' ORDER BY '.implode(',', $arr);
		}

		// -------------------------------------
		//	Allow random ordering
		// -------------------------------------

		if ($order == 'random')
		{
			$arr[] = 'RAND()';
		}

		// -------------------------------------
		//	Protect custom ordering punctuation like +, -, etc. Some people will use Super Search to sort lists of students and their grades; A+ A B- etc. We need to protect the punctuation there.
		// -------------------------------------

		if (strpos($order, 'custom') !== FALSE)
		{
			if (preg_match_all('/([a-zA-Z0-9\-\_]+)\\' . $this->spaces . 'custom\\' . $this->spaces . '[\'\"]([\w\+\-,]+)[\'\"]/s', $order, $match))
			{
				foreach ($match[0] as $key => $val)
				{
					$order	= str_replace($val, '<<replace' . $key . 'replace>>', $order);

					if (isset($match[1][$key]) === TRUE)
					{
						$custom_orders[$key]['field']	= $match[1][$key];

						if (isset($match[2][$key]) === TRUE)
						{
							$custom_orders[$key]['value']	= $match[2][$key];
						}
					}
				}
			}
		}

		// -------------------------------------
		//	Convert order string to array
		// -------------------------------------

		if (strpos($order, $this->spaces) === FALSE AND strpos($order, ' ') === FALSE AND strpos($order, 'rating') === FALSE)
		{
			$order	= $order . "|asc";
		}
		else
		{
			$order	= str_replace(array($this->spaces.$this->spaces.$this->spaces, $this->spaces.$this->spaces, $this->spaces), ' ', strtolower($order));

			if (strpos($order, ' desc') !== FALSE)
			{
				$order	= str_replace(' desc', '|desc', $order);
			}

			if (strpos($order, ' asc') !== FALSE)
			{
				$order	= str_replace(' asc', '|asc', $order);
			}
		}

		$order	= explode(' ', $order);

		// -------------------------------------
		//	Process orders
		// -------------------------------------

		if (is_array($order) === TRUE)
		{
			$customfields	= $this->_fields('all');
			$fields			= $this->_table_columns('exp_channel_titles');

			if (isset($custom_orders) AND is_array($custom_orders))
			{
				foreach($custom_orders as $custom_order)
				{
					// -------------------------------------
					//	Explicit entry id order
					// -------------------------------------

					if ($custom_order['field'] == 'entry_id')
					{
						//For the odd circumstance where we're passed an order by entry_id
						$temp	= 'FIELD(';
						$temp	.= 'cd.entry_id';
						$temp	.= ',';
						$temp	.= "'" . str_replace(",", "','", ee()->db->escape_str($custom_order['value'])) . "'";
						$temp	.= ')';
						$arr[]	= $temp;
					}

					// -------------------------------------
					//	Custom order by channel name / title
					// -------------------------------------

					if (($custom_order['field'] == 'channel_name' OR $custom_order['field'] == 'channel_title') AND ! empty($this->sess['search']['channels']))
					{
						$channel_orders	= array();

						foreach ($this->sess['search']['channels'] as $channel_id => $channel_meta)
						{
							$channel_orders['channel_name'][$channel_meta['channel_name']]	= $channel_id;
							$channel_orders['channel_title'][$channel_meta['channel_title']]	= $channel_id;
						}

						foreach (explode(',', $custom_order['value']) as $val)
						{
							if (isset($channel_orders[$custom_order['field']][$val]))
							{
								$custom_channels[]	= $channel_orders[$custom_order['field']][$val];

								unset($channel_orders[$custom_order['field']][$val]);	// We unset the main array since we need to dump its remaining contents into our order directive. If people search across more channels than they explicitly order by, the ignored channels will end up at the top.
							}
						}

						if (! empty($custom_channels))
						{
							$custom_channels	= array_merge($custom_channels, array_values($channel_orders[$custom_order['field']]));

							$arr[]	= 'FIELD(' . 't.channel_id,' . "'" . implode("','", ee()->db->escape_str($custom_channels)) . "'" . ')';
						}
					}
				}
			}

			foreach ($order as $str)
			{
				// -------------------------------------
				//	Can we detect custom orders?
				// -------------------------------------

				if (preg_match('/<<replace(\d+)replace>>/s', $str, $match))
				{
					if (! empty($custom_orders[$match[1]]['field']) AND ! empty($custom_orders[$match[1]]['value']) AND isset($customfields[$custom_orders[$match[1]]['field']]) === TRUE)
					{
						$temp	= 'FIELD(';
						$temp	.= 'cd.field_id_';
						$temp	.= $customfields[$custom_orders[$match[1]]['field']];
						$temp	.= ',';
						$temp	.= "'" . str_replace(",", "','", ee()->db->escape_str($custom_orders[$match[1]]['value'])) . "'";
						$temp	.= ')';
						$arr[]	= $temp;
					}

					continue;
				}

				// -------------------------------------
				//	Proceed as normal
				// -------------------------------------

				$ord	= explode('|', $str);

				if (isset($fields[$ord[0]]) === TRUE)
				{
					$arr[]	= (isset($ord[1]) === TRUE AND in_array($ord[1], array('asc', 'desc'))) ? 't.'.$fields[$ord[0]].' '.strtoupper($ord[1]): 't.'.$fields[$ord[0]].' ASC';
				}

				if (isset($customfields[$ord[0]]) === TRUE AND is_numeric($customfields[$ord[0]]) === TRUE)
				{
					$arr[]	= (isset($ord[1]) === TRUE AND in_array($ord[1], array ('asc', 'desc'))) ? 'cd.field_id_'.$customfields[$ord[0]].' '.strtoupper($ord[1]): 'cd.field_id_'.$customfields[$ord[0]].' ASC';
				}

				if ($ord[0] == 'channel' OR $ord[0] == 'channel_title' OR $ord[0] == 'channel_name')
				{
					// We don't actually have the channel_names available to use at this point,
					// so we'll do a sub-query to get the list then convert to their respective ids

					// TODO
					// get this to honor the site_ids passed
					$subsql = " SELECT channel_id FROM exp_channels ";

					$subsql .= " WHERE site_id IN ('" . implode("','", ee()->db->escape_str($this->_only_numeric( $this->sess['search']['q']['site']))) . "') ORDER BY ";

					if ($ord[0] == 'channel') $ord[0] = 'channel_name';

					$subsql  .= (isset($ord[1]) === TRUE AND in_array($ord[1], array ('asc', 'desc'))) ? $ord[0] .' '.strtoupper($ord[1]): $ord[0].' ASC';

					$subquery = ee()->db->query($subsql);

					$channel_ids_ordered = array();

					foreach($subquery->result_array() AS $result)
					{
						$channel_ids_ordered[] = $result['channel_id'];
					}

					if (count($channel_ids_ordered) > 0)
					{
						// Now we've got our channel_ids in the correct order, pretend this is a custom_order param
						$temp  =  ' FIELD(';
						$temp  .= 't.channel_id';
						$temp  .= ',';
						$temp  .= "'" . implode("','", ee()->db->escape_str($channel_ids_ordered)) . "'";
						$temp  .= ')';

						$arr[]	= $temp;
					}
				}

				if ($ord[0] == 'random')
				{
					$arr[] = "RAND()";
				}

				// -------------------------------------
				//	Manipulate order
				// -------------------------------------

				if (ee()->extensions->active_hook('super_search_prep_order') === TRUE)
				{
					$failsafe	= $arr;

					$arr	= ee()->extensions->call('super_search_prep_order', $this, $arr, $ord);

					$arr	= (is_null($arr)) ? $failsafe: $arr;
				}
			}
		}

		// -------------------------------------
		//	Remove empties
		// -------------------------------------

		$arr	= $this->_remove_empties($arr);

		return ' ORDER BY '.implode(', ', $arr);
	}

	//	End prep order

	// -------------------------------------------------------------

	/**
	 * Prep paginate base
	 *
	 * When we have to force pagination base, we have to do a lot of gymnastics.
	 *
	 * @access	private
	 * @return	string
	 */

	function _prep_paginate_base()
	{
		$str	= rtrim(str_replace($this->urimarker, str_replace('/', $this->slash, $this->sess['newuri']), $this->sess['olduri']), '/');

		$str	= str_replace('"', '%22', $str);	// This string gets passed to EE which handles pagination. It strips quotes so we escape them.
		$str	= str_replace("'", '%27', $str);	// This string gets passed to EE which handles pagination. It strips quotes so we escape them.

		// -------------------------------------
		//	Sometimes people run EE from a subdir and that appears in the uri. EE only wants to see template segments in the uri, plus a query string. So we want to strip out that subdir.
		// -------------------------------------

		if (preg_match("/https*:\/\/(.+)\/(.+)/s", ee()->config->item('site_url'), $match))
		{
			if (! empty($match['2']))
			{
				$str	= str_replace($match['2'], '', $str);
			}
		}

		$str	= str_replace(ee()->config->item('site_index'), '', $str);

		// -------------------------------------
		//	Return
		// -------------------------------------

		return $str;
	}

	//	End prep paginate base

	// -------------------------------------------------------------

	/**
	 * Prep query
	 *
	 * @access	private
	 * @return	array
	 */

	function _prep_query($q)
	{
		if (empty($q)) return FALSE;

		// -------------------------------------
		//  Parse the uri array into something SQL-able
		// -------------------------------------

		if (($search = $this->ssu->prep_query($q)) === FALSE) return FALSE;

		// -------------------------------------
		//	Set a variable to control exact keyword searching. search-words-within-words set to no overrides default behavior and requires that searches return only for exact words not their conjugates.
		// -------------------------------------
		//	'any'		= return results where any of the given keywords are found.
		//	'all'		= return results where all of the given keywords are found.
		//	'none'		= return results where none of the given keywords are found.
		//	'phrase'	= return results where the exact phrase is found.
		//	These arguments only apply to the 'or' part of a keyword search. We think that the && and - syntax is specific enough that if it is used, it should override any of these flags.
		//	'search_words_within_words'	= return results where the given keywords are found in the text whether as distinct words or not.
		// -------------------------------------

		if (! empty($q['how']))
		{
			if ($q['how'] == 'exact' OR $q['how'] == 'phrase')
			{
				$this->how	= 'phrase';
			}
			elseif ($q['how'] == 'any')
			{
				$this->how					= 'any';
				$this->inclusive_keywords	= FALSE;
			}
			elseif ($q['how'] == 'none')
			{
				$this->how	= 'none';
			}
		}

		if (isset($q['inclusive_keywords']) AND $this->check_no($q['inclusive_keywords']))
		{
			$this->inclusive_keywords	= FALSE;
		}

		// -------------------------------------
		//	We prefer search_words_within_words to search-words-within-words, so convert
		// -------------------------------------

		if (isset($q['search-words-within-words']))
		{
			$q['search_words_within_words']	= $q['search-words-within-words'];
		}

		if (isset($q['search_words_within_words']) AND $this->check_no($q['search_words_within_words']))
		{
			if ($this->how == 'any')
			{
				$this->how	.= ' no-search-words-within-words';
			}
			elseif ($this->how == 'all')
			{
				$this->how	.= ' no-search-words-within-words';
			}
			elseif ($this->how == 'none')
			{
				$this->how	.= ' no-search-words-within-words';
			}
		}

		// -------------------------------------
		//  Set words within words rules
		// -------------------------------------

		if (! empty($q['highlight_words_within_words']))
		{
			$search['highlight_words_within_words']	= $q['highlight_words_within_words'];
		}

		// -------------------------------------
		//  Validate for site id
		// -------------------------------------

		if (empty($search['site'])) return FALSE;
		ee()->TMPL->site_ids	= $search['site'];

		// -------------------------------------
		//  Validate for channel id
		// -------------------------------------

		$search['channel']	= (isset($search['channel']) === TRUE) ? $search['channel']: '';
		if (($search['channel_ids'] = $this->_prep_channel($search['channel'])) === FALSE) return FALSE;

		// -------------------------------------
		//	Prep wildcards
		// -------------------------------------
		//	In the class definition of $this->allow_wildcards, wild card searching is off by default. Turn it on with the allow_wildcards='y' parameter. By default, all fields will be wildcard searchable unless
		// -------------------------------------

		if (isset($q['wildcard_character']) === TRUE AND trim($q['wildcard_character']) != '')
		{
			$this->wildcard = ee()->db->escape_str($q['wildcard_character']);
		}

		if (! empty($q['wildcard_fields']))
		{
			$this->allow_wildcards = TRUE;	// This is set to FALSE in the class definition. But if someone merely indicates the wildcard_fields param, we turn the feature on and sort out the details in $this->model('Data')->flag_state(). If they exclude a field, then all the rest are fair game. If they include some fields, then only those can be searched. Either way, something is searchable.

			if (strtolower(trim($q['wildcard_fields'])) == 'all')
			{
				unset($q['wildcard_fields']);
			}
			else
			{
				$this->sess['uri']['wildcard_fields']	= $this->_prep_keywords(str_replace('-', $this->negatemarker, $q['wildcard_fields']));
			}
		}

		// -------------------------------------
		//	Prep regex fields
		// -------------------------------------

		if (isset($q['allow_regex']) === TRUE OR isset($q['allow_regex']) === TRUE)
		{
			if (isset($q['allow_regex']) === TRUE AND $this->check_yes($q['allow_regex']) === TRUE)
			{
				$this->allow_regex = TRUE;
			}

			if (isset($q['regex_fields']) === TRUE)
			{
				$this->sess['uri']['regex_fields']	= $this->_prep_keywords(str_replace('-', $this->negatemarker, $q['regex_fields']));
			}
		}

		// -------------------------------------
		//	Prep search in
		// -------------------------------------

		if (isset($q['search_in']))
		{
			// We have a passed value for search_in
			// this allows users to override the default search behaviour
			// of 'keywords' to search specifically in any of the set
			// searchable fields. The same effect can be achieved passing the
			// field names directly, but this allows for dynamic changes
			// without hacks on the user side

			if (isset($search['keywords']))
			{
				$search_in = ee()->db->escape_str($q['search_in']);

				// be kind to people (I've seen people actually try this so give them a hand)
				$everywhere = array('everything', 'everywhere', 'all');
				$titles = array('title', 'titles');

				$everything_override = FALSE;

				$fields = array();

				$searches = explode('|', $search_in);

				foreach($searches as $srch)
				{
					if (!(in_array($srch, $everywhere) === TRUE OR trim($srch) == ''))
					{
						if (in_array($srch, $titles) === TRUE)
						{
							$fields['title'] = $search['keywords'];
						}
						else
						{
							// Assume they're passing a valid fieldname. If they're not we'll just ignore their input later anyway
							$fields[$srch] = $search['keywords'];
						}
					}
					else
					{
						// We have an everywhere marker.
						// this overrides it all. Bail the whole thing
						$everything_override = TRUE;
					}
				}

				if (! $everything_override)
				{
					$search['search_in'] = $fields;

					$preload_fields	= TRUE;
				}
			}
		}

		// -------------------------------------
		//	Run keywords through our prep so that fuzzies will be triggered.
		// -------------------------------------

		if (isset($q['keywords']))
		{
			$search['keywords']	= $this->_prep_keywords($q['keywords'], $this->inclusive_keywords, 'keywords', $search);
		}

		// -------------------------------------
		//	Prep include entry ids
		// -------------------------------------

		if (isset($q['include_entry_ids']) === TRUE)
		{
			$include_entry_ids	= $this->_only_numeric(explode('|', $q['include_entry_ids']));
			sort($include_entry_ids);
			$search['include_entry_ids']	= $include_entry_ids;
		}

		// -------------------------------------
		//	Prep exclude entry ids
		// -------------------------------------

		if (isset($q['exclude_entry_ids']) === TRUE)
		{
			$exclude_entry_ids	= $this->_only_numeric(explode('|', $q['exclude_entry_ids']));
			sort($exclude_entry_ids);
			$search['exclude_entry_ids']	= $exclude_entry_ids;
		}

		// -------------------------------------
		//	Fail?
		// -------------------------------------

		if (empty($search)) return FALSE;

		// -------------------------------------
		//	Return
		// -------------------------------------

		return $search;
	}

	//	End prep query

	// -------------------------------------------------------------

	/**
	 * Prep relevance
	 *
	 * @access	private
	 * @return	array
	 */

	function _prep_relevance()
	{
		// -------------------------------------
		//	Check params
		// -------------------------------------

		if (ee()->TMPL->fetch_param('relevance') === FALSE OR ee()->TMPL->fetch_param('relevance') == '')
		{
			return FALSE;
		}

		// -------------------------------------
		//	Convert spaces
		// -------------------------------------

		$relevance	= str_replace(' ', $this->spaces, ee()->TMPL->fetch_param('relevance'));

		// -------------------------------------
		//	Count words within words?
		// -------------------------------------

		if (strpos($relevance, 'count_words_within_words') !== FALSE)
		{
			$this->relevance_count_words_within_words	= TRUE;

			$relevance	= trim(str_replace('count_words_within_words', '', $relevance), $this->spaces);
		}

		// -------------------------------------
		//	Simple argument?
		// -------------------------------------

		if (strpos($relevance, $this->spaces) === FALSE)
		{
			$temp	= explode($this->separator, $relevance);

			if (count($temp) > 1)
			{
				$arr[$temp[0]]		= $temp[1];
			}
			else
			{
				$arr[$relevance]	= 1;
			}

			return $arr;
		}

		// -------------------------------------
		//	Compound argument?
		// -------------------------------------

		$arr	= array();

		foreach (explode($this->spaces, $relevance) as $val)
		{
			$temp	= explode($this->separator, $val);

			if (count($temp) > 1)
			{
				$arr[$temp[0]]	= $temp[1];
			}
			else
			{
				$arr[$temp[0]]	= 1;
			}
		}

		if (empty($arr) === TRUE) return FALSE;

		return $arr;
	}

	//	End prep relevance

	// -------------------------------------------------------------

	/**
	 * Prep relevance multiplier
	 *
	 * @access	private
	 * @return	array
	 */

	function _prep_relevance_multiplier()
	{
		// -------------------------------------
		//	Check params
		// -------------------------------------

		if (ee()->TMPL->fetch_param('relevance_multiplier') === FALSE OR ee()->TMPL->fetch_param('relevance_multiplier') == '')
		{
			return FALSE;
		}

		// -------------------------------------
		//	Convert spaces
		// -------------------------------------

		$multiplier	= str_replace(' ', $this->spaces, ee()->TMPL->fetch_param('relevance_multiplier'));

		// -------------------------------------
		//	Simple argument?
		// -------------------------------------

		if (strpos($multiplier, $this->spaces) === FALSE)
		{
			$temp	= explode($this->separator, $multiplier);

			if (count($temp) > 1)
			{
				$arr[$temp[0]]		= $temp[1];
			}
			else
			{
				$arr[$multiplier]	= 1;
			}

			return $arr;
		}

		// -------------------------------------
		//	Compound argument?
		// -------------------------------------

		$arr	= array();

		foreach (explode($this->spaces, $multiplier) as $val)
		{
			$temp	= explode($this->separator, $val);

			if (count($temp) > 1)
			{
				$arr[$temp[0]]	= $temp[1];
			}
			else
			{
				$arr[$temp[0]]	= 1;
			}
		}

		if (empty($arr) === TRUE) return FALSE;

		return $arr;
	}

	//	End prep relevance multiplier

	// -------------------------------------------------------------

	/**
	 * Prep relevance proxmity
	 *
	 * @access	private
	 * @return	array
	 */

	function _prep_relevance_proximity($q = array())
	{
		if (isset($q['relevance_proximity']))
		{
			if ($this->check_yes($q['relevance_proximity']) == TRUE)
			{
				$this->relevance_proximity = $this->relevance_proximity_default;
			}
			elseif (is_numeric($q['relevance_proximity']) == TRUE)
			{
				$this->relevance_proximity = $q['relevance_proximity'];
			}
		}

		return $this->relevance_proximity;
	}

	//	End prep relevance proximity

	// -------------------------------------------------------------

	/**
	 * Prep fuzzy weight
	 *
	 * @access	private
	 * @return	array
	 */

	function _prep_fuzzy_weight($q = array())
	{
		if (isset($q['fuzzy_weight']))
		{
			if ($this->check_yes($q['fuzzy_weight']) == TRUE)
			{
				$this->fuzzy_weight = $this->fuzzy_weight_default;
			}
			elseif ($this->check_no($q['fuzzy_weight']) == TRUE)
			{
				// No fuzzy weight : ie. fuzzy matches get the same value as normal keywords
				$this->fuzzy_weight = 1;
			}
			elseif (is_numeric($q['fuzzy_weight']) == TRUE)
			{
				$this->fuzzy_weight = $q['fuzzy_weight'];
			}
		}

		return $this->fuzzy_weight;
	}

	//	End prep return

	// -------------------------------------------------------------

	/**
	 * Prep search for sql
	 *
	 * @access	private
	 * @return	string
	 */

	private function _prep_search_for_sql($q = array())
	{
		$t	= microtime(TRUE);

		if (isset($q['search_in'])) {
			$q['search_in'] = str_replace('+', '|', $q['search_in']);
		}

		if (isset($this->sess['search'])) {
			if (isset($this->sess['search']['q'])) {
				if (isset($this->sess['search']['q']['search_in'])) {
					$sessSearchIn = $this->sess['search']['q']['search_in'];

					foreach ($sessSearchIn as $key => $val) {
						$keyList = explode('+', $key);
						unset($this->sess['search']['q']['search_in'][$key]);
						foreach ($keyList as $modifiedKey) {
							$this->sess['search']['q']['search_in'][$modifiedKey] = $val;
						}
					}
				}
			}
		}

		// -------------------------------------
		//	Begin SQL
		// -------------------------------------

		$select	= '/* Super Search _prep_search_for_sql() */ SELECT t.entry_id';

		$this->sess['search']['from']	= array();
		$this->sess['search']['from'][]	= 'FROM exp_channel_titles t';
		$this->sess['search']['from'][]	= 'LEFT JOIN exp_channel_data cd ON cd.entry_id = t.entry_id %indexes% ';

		if (isset($q['keyword_search_author_name']) AND $this->check_yes($q['keyword_search_author_name']))
		{
			$this->sess['uri']['keyword_search_author_name']	= 'y';
			$this->sess['search']['from'][]						= 'LEFT JOIN exp_members m ON t.author_id = m.member_id ';
		}

		if (isset($q['keyword_search_category_name']) AND $this->check_yes($q['keyword_search_category_name']))
		{
			$this->sess['uri']['keyword_search_category_name']	= 'y';
			$this->sess['search']['from'][]						= 'LEFT JOIN exp_categories cat ON cat.cat_id IN (SELECT cat_id FROM exp_category_posts cat_p WHERE cat_p.entry_id = t.entry_id)';
		}

		$where	= ' WHERE t.entry_id != 0 ';
		$and	= array();
		$not	= array();
		$or		= array();
		$subids	= array();
		$and_special = array();

		// -------------------------------------
		//	Show future?
		// -------------------------------------

		if (ee()->TMPL->fetch_param('show_future_entries') === FALSE OR ee()->TMPL->fetch_param('show_future_entries') != 'yes')
		{
			$where	.= ' AND t.entry_date < '.ee()->db->escape_str(ee()->localize->now);
		}

		// -------------------------------------
		//	Show expired?
		// -------------------------------------

		if (ee()->TMPL->fetch_param('show_expired') === FALSE OR ee()->TMPL->fetch_param('show_expired') != 'yes')
		{
			if (empty($this->sess['search']['q']['expiry_datefrom']) AND empty($this->sess['search']['q']['expiry_dateto']))
			{
				$where	.= ' AND (t.expiration_date = 0 OR t.expiration_date > '.ee()->db->escape_str(ee()->localize->now).')';
			}
		}

		// -------------------------------------
		//	Prep order here so that it's part of the cache
		// -------------------------------------

		if (($neworder = $this->sess('uri', 'orderby')) !== FALSE)
		{
			$order	= $this->_prep_order($neworder);
		}
		elseif (($neworder = $this->sess('uri', 'order')) !== FALSE)
		{
			$order	= $this->_prep_order($neworder);
		}
		elseif (ee()->TMPL->fetch_param('orderby') !== FALSE AND ee()->TMPL->fetch_param('orderby') != '')
		{
			$order	= $this->_prep_order(ee()->TMPL->fetch_param('orderby'));
		}
		elseif (ee()->TMPL->fetch_param('order') !== FALSE AND ee()->TMPL->fetch_param('order') != '')
		{
			$order	= $this->_prep_order(ee()->TMPL->fetch_param('order'));
		}
		else
		{
			$order	= $this->_prep_order();
		}

		$this->sess['search']['q']['order']	= $order;

		// -------------------------------------
		//	Prep relevance to be part of cache as well
		// -------------------------------------

		$this->sess['search']['q']['relevance']				= $this->_prep_relevance();

		$this->sess['search']['q']['relevance_multiplier']	= $this->_prep_relevance_multiplier();

		$this->sess['search']['q']['relevance_proximity']	= $this->_prep_relevance_proximity($q);

		$this->sess['search']['q']['fuzzy_weight']			= $this->_prep_fuzzy_weight($q);

		$this->fuzzy_distance								= (isset($q['fuzzy_distance']) AND ctype_digit($q['fuzzy_distance'])) ? $q['fuzzy_distance']: $this->fuzzy_distance;

		// -------------------------------------
		//	Cached?
		// -------------------------------------

		if (($ids = $this->_cached($this->_hash_it($this->sess('uri')))) !== FALSE)
		{
			ee()->TMPL->log_item('Super Search: Ending cached _prep_search_for_sql() ('.(microtime(TRUE) - $t).')');

			if (empty($ids))
			{
				return FALSE;
			}

			if (is_string($ids) === TRUE)
			{
				$ids	= explode("|", $ids);
			}

			// -------------------------------------
			//	Some assembly required
			// -------------------------------------

			$sql	= $select . ' ' . implode(' ', $this->sess['search']['from']) . ' ' . $where . ' AND t.entry_id IN ('. implode(',', $ids) .')';

			// Clean up our %indexes% marker
			$sql	= str_replace('%indexes%', '', $sql);

			//	Glue order directives back on
			$sql	= $sql . ' ' . $this->sess['search']['q']['order'];

			//	Return
			return $sql;
		}

		// -------------------------------------
		//	Are we working with categories?
		// -------------------------------------

		if (! empty($this->sess['search']['q']['category']))
		{
			if (($tempids = $this->_get_ids_by_category($this->sess['search']['q']['category'])) !== FALSE)
			{
				$subids	= array_merge($subids, $tempids);
			}

			// -------------------------------------
			//	Test category conditions
			// -------------------------------------

			if (empty($tempids))
			{
				return FALSE;
			}
		}

		// -------------------------------------
		//	Are we working with loose categories?
		// -------------------------------------

		if (empty($this->sess['search']['q']['category-like']) === FALSE)
		{
			if (($tempids = $this->_get_ids_by_category($this->sess['search']['q']['category-like'], 'not-exact')) !== FALSE)
			{
				$subids	= array_merge($subids, $tempids);
			}

			// -------------------------------------
			//	Test category conditions
			// -------------------------------------
			//	If we're checking for categories with either 'or' or 'and' and we receive nothing back, we have to fail here.
			// -------------------------------------

			if (empty($tempids) === TRUE)
			{
				return FALSE;
			}
		}

		// -------------------------------------
		//	Are we looking for authors?
		// -------------------------------------

		if (empty($this->sess['search']['q']['author']) === FALSE)
		{
			// -------------------------------------
			//	No authors?
			// -------------------------------------
			//	If we were looking for authors and we found none in the DB by the names provided, we have to fail out right here.
			// -------------------------------------

			if (($author = $this->_prep_author($this->sess['search']['q']['author'])) === FALSE)
			{
				return FALSE;
			}

			$and[]	= 't.author_id IN ('.implode(',', ee()->db->escape_str($author)).')';
		}

		// -------------------------------------
		//	Are we looking for member groups?
		// -------------------------------------

		if (! empty($this->sess['search']['q']['group']))
		{
			// -------------------------------------
			//	No groups?
			// -------------------------------------
			//	If we were looking for groups and we found none in the DB by the names provided, we have to fail out right here.
			// -------------------------------------

			if (($group = $this->_prep_group($this->sess['search']['q']['group'])) === FALSE)
			{
				return FALSE;
			}

			$and[]	= 't.author_id IN ('.implode(',', ee()->db->escape_str($group)).')';
		}

		// -------------------------------------
		//	Are we looking to include entry ids?
		// -------------------------------------

		if (empty($this->sess['search']['q']['include_entry_ids']) === FALSE)
		{
			$and[]	= 't.entry_id IN ('.implode(',', ee()->db->escape_str($this->sess['search']['q']['include_entry_ids'])).')';
		}

		// -------------------------------------
		//	Are we looking to exclude entry ids?
		// -------------------------------------

		if (empty($this->sess['search']['q']['exclude_entry_ids']) === FALSE)
		{
			$and[]	= 't.entry_id NOT IN ('.implode(',', ee()->db->escape_str($this->sess['search']['q']['exclude_entry_ids'])).')';
		}

		// -------------------------------------
		//	Prep status
		// -------------------------------------

		$force_status	= TRUE;

		if (! empty($this->sess['search']['q']['status']))
		{
			if (($temp = $this->_prep_sql('not', 't.status', $this->sess['search']['q']['status'], 'exact')) !== FALSE)
			{
				$force_status	= FALSE;
				$not[]	= $temp;
			}

			if (($temp = $this->_prep_sql('or', 't.status', $this->sess['search']['q']['status'], 'exact')) !== FALSE)
			{
				$force_status	= FALSE;
				$and[]	= $temp;
			}
		}

		if ($force_status === TRUE)
		{
			$and[]	= 't.status = \'open\'';
		}

		// -------------------------------------
		//	Prep keyword search
		// -------------------------------------

		if (! empty($this->sess['search']['q']['keywords']))
		{
			$fieldor	= array();
			$fieldand	= array();

			// -------------------------------------
			//	Alter the actual keywords array for exact, none and all cases
			// -------------------------------------
			//	'exact' means only return results where the exact phrase is found.
			//	'all' means only return results where all of the given keywords are found. This is a conjoined keyword search like mike&&joe&&sally.
			//	'none' means only return results where none of the given keywords are found.
			// -------------------------------------

			if (strpos($this->how, 'phrase') !== FALSE AND ! empty($this->sess['search']['q']['keywords_phrase']))
			{
				$this->sess['search']['q']['keywords']['or']	= array($this->sess['search']['q']['keywords_phrase']);
				$this->how	= 'any no-search-words-within-words';	// We have forced the 'or' into a single string so the SQL will line up. No additional designation of exactness is needed.
			}

			if (strpos($this->how, 'all') !== FALSE AND ! empty($this->sess['search']['q']['keywords']['or']))
			{
				if (! empty($this->sess['search']['q']['keywords']['and'][0]['main']))
				{
					$this->sess['search']['q']['keywords']['and'][0]['main']	= array_merge($this->sess['search']['q']['keywords']['and'][0]['main'], $this->sess['search']['q']['keywords']['or']);
				}
				else
				{
					$this->sess['search']['q']['keywords']['and'][]['main']	= $this->sess['search']['q']['keywords']['or'];
				}

				unset($this->sess['search']['q']['keywords']['or']);
			}

			if (strpos($this->how, 'none') !== FALSE AND ! empty($this->sess['search']['q']['keywords']['or']))
			{
				$this->sess['search']['q']['keywords']['not']	= array_merge($this->sess['search']['q']['keywords']['not'], $this->sess['search']['q']['keywords']['or']);

				unset($this->sess['search']['q']['keywords']['or']);
			}

			// -------------------------------------
			//	Prep author's screen_name for keyword search
			// -------------------------------------

			if (isset($q['keyword_search_author_name']) AND $this->check_yes($q['keyword_search_author_name']))
			{
				$indicator	= 'm.username';

				if (ee()->TMPL->fetch_param('author_indicator') !== FALSE AND in_array(ee()->TMPL->fetch_param('author_indicator'), array('author_id', 'member_id', 'screen_name', 'username', 'email')) === TRUE)
				{
					$indicator	= 'm.' . ee()->TMPL->fetch_param('author_indicator');
				}

				$indicator	= ($indicator == 'm.author_id') ? 'm.member_id': $indicator;

				if (($temp = $this->_prep_sql('not', $indicator, $this->sess['search']['q']['keywords'], $this->how, 'test')) !== FALSE)
				{
					$not[]	= $temp;
				}

				if (($temp = $this->_prep_sql('or', $indicator, $this->sess['search']['q']['keywords'], $this->how)) !== FALSE)
				{
					$fieldor[]	= $temp;
				}

				if (($temp = $this->_prep_sql('and', $indicator, $this->sess['search']['q']['keywords'], $this->how)) !== FALSE)
				{
					// This is special.
					// In the case we're doing inclusive searches, we actually want the name searching to be part of the or search set
					$and_special[]	= $temp;
				}
			}

			// -------------------------------------
			//	Prep post's category name for category fuzzy search on keywords
			// -------------------------------------

			if (isset($q['keyword_search_category_name']) AND $this->check_yes($q['keyword_search_category_name']))
			{
				if (($temp = $this->_prep_sql('not', 'cat.cat_name', $this->sess['search']['q']['keywords'], $this->how)) !== FALSE)
				{
					$not[]	= $temp;
				}

				if (($temp = $this->_prep_sql('or', 'cat.cat_name', $this->sess['search']['q']['keywords'],  $this->how)) !== FALSE)
				{
					$fieldor[]	= $temp;
				}

				if (($temp = $this->_prep_sql('and', 'cat.cat_name', $this->sess['search']['q']['keywords'],  $this->how)) !== FALSE)
				{
					// This is special.
					// In the case we're doing inclusive searches, we actually want the category name searching to be part of the or search set
					$and_special[]	= $temp;
				}
			}

			// -------------------------------------
			//	Prep title or url_title for keyword search
			// -------------------------------------

			$searchIn = isset($q['search_in']) ? explode('|', $q['search_in']) : null;

			if (is_null($searchIn)) {
				$hasTitle = $hasUrlTitle = true;
			} else {
				if (in_array('all', $searchIn)) {
					$hasTitle = $hasUrlTitle = true;
				} else {
					$hasTitle = in_array('title', $searchIn);
					$hasUrlTitle = in_array('url_title', $searchIn);
				}
			}

			if (is_null($searchIn) || $hasTitle || $hasUrlTitle) {
				$searchableFields = array();

				if (is_null($searchIn)) {
					$searchableFields = array('title', 'url_title');
				} else {
					if ($hasTitle) {
						$searchableFields[] = 'title';
					}
					if ($hasUrlTitle) {
						$searchableFields[] = 'url_title';
					}
				}

				foreach ($searchableFields as $key) {
					if (($temp = $this->_prep_sql(
							'not',
							't.' . $key,
							$this->sess['search']['q']['keywords'],
							$this->how,
							$key,
							$key
						)) !== false
					) {
						$not[] = $temp;
					}

					if (($temp = $this->_prep_sql(
							'or',
							't.' . $key,
							$this->sess['search']['q']['keywords'],
							$this->how,
							$key,
							$key
						)) !== false
					) {
						$fieldor[] = $temp;
					}
				}
			}

			// -------------------------------------
			//	Prep custom fields for keyword search
			// -------------------------------------

			if (($customfields = $this->_fields('searchable', ee()->TMPL->site_ids)) !== FALSE)
			{
				foreach ($customfields as $key => $val)
				{
					if (! is_numeric($val)) continue;

					if (isset($this->sess['search']['q']['search_in']) AND ! isset($this->sess['search']['q']['search_in'][$key])) continue;

					//Handle the case of duplicate channel names across MSM sites
					if (array_key_exists('supersearch_msm_duplicate_fields', $customfields) AND  array_key_exists($key , $customfields['supersearch_msm_duplicate_fields']))
					{
						//we have the duplicate field name/channel name case to handle
						foreach($customfields['supersearch_msm_duplicate_fields'][$key] AS $subkey => $subval)
						{
							if (($temp = $this->_prep_sql('not', 'cd.field_id_'.$subval, $this->sess['search']['q']['keywords'], $this->how, $subval, $key)) !== FALSE)
							{
								$not[]	= $temp;
							}

							if (($temp = $this->_prep_sql('or', 'cd.field_id_'.$subval, $this->sess['search']['q']['keywords'], $this->how, $subval, $key)) !== FALSE)
							{
								$fieldor[]	= $temp;
							}
						}
					}
					else
					{
						if (($temp = $this->_prep_sql('not', 'cd.field_id_'.$val, $this->sess['search']['q']['keywords'], $this->how, $val, $key)) !== FALSE)
						{
							$not[]	= $temp;
						}

						if (($temp = $this->_prep_sql('or', 'cd.field_id_'.$val, $this->sess['search']['q']['keywords'], $this->how, $val, $key)) !== FALSE)
						{
							$fieldor[]	= $temp;
						}
					}
				}
			}

			// -------------------------------------
			//	Prep for conjoined keyword search. Here's the anding.
			// -------------------------------------

			if (! empty($this->sess['search']['q']['keywords']['and']))
			{
				foreach ($this->sess['search']['q']['keywords']['and'] as $ands)
				{
					$big_chunk	= array();	// These will be or'd together

					//	----------------------------------------
					//  Loop through main keywords
					//	----------------------------------------

					if (! empty($ands['main']))
					{
						// -------------------------------------
						//	Convert markers
						// -------------------------------------

						$ands['main']	= $this->ssu->convert_markers($ands['main']);

						// -------------------------------------
						//	Loop
						// -------------------------------------

						foreach ($ands['main'] as $keyword)
						{
							$lil_chunk	= array();

							$arr	= array(
								'keywords'		=> array($keyword),
								'exactness'		=> 'non-exact',
								'word_boundary'	=> (strpos($this->how, 'no-search-words-within-words') !== FALSE) ? TRUE: FALSE
							);

							// -------------------------------------
							//	Prep standard fields for conjoined keyword search
							// -------------------------------------

							foreach (array('title', 'url_title') as $field)
							{
								$arr['field_name']		= $field;
								$arr['db_field_name']	= 't.' . $field;

								if (($temp = $this->ssu->prep_sql('or', $arr)) !== FALSE)
								{
									$lil_chunk	= array_merge($lil_chunk, array($temp));
								}
							}

							// -------------------------------------
							//	Prep custom fields for conjoined keyword search
							// -------------------------------------

							if (($customfields = $this->_fields('searchable', ee()->TMPL->site_ids)) !== FALSE)
							{
								foreach ($customfields as $key => $val)
								{
									if (! is_numeric($val)) continue;

									if (isset($this->sess['search']['q']['search_in']) AND ! isset($this->sess['search']['q']['search_in'][$key])) continue;

									//Handle the case of duplicate channel names across MSM sites
									if (array_key_exists('supersearch_msm_duplicate_fields', $customfields) AND array_key_exists($key, $customfields['supersearch_msm_duplicate_fields']))
									{
										//we have the duplicate field name/channel name case to handle
										foreach($customfields['supersearch_msm_duplicate_fields'][$key] AS $subkey => $subval)
										{
											$arr['field_name']		= $subkey;
											$arr['db_field_name']	= 'cd.field_id_' . $subval;

											if (($temp = $this->ssu->prep_sql('or', $arr)) !== FALSE)
											{
												$lil_chunk	= array_merge($lil_chunk, array($temp));
											}
										}
									}
									else
									{
										$arr['field_name']		= $key;
										$arr['db_field_name']	= 'cd.field_id_' . $val;

										if (($temp = $this->ssu->prep_sql('or', $arr)) !== FALSE)
										{
											$lil_chunk	= array_merge($lil_chunk, array($temp));
										}
									}
								}

								if (! empty($lil_chunk))
								{
									$big_chunk[]	= '(' . NL . implode(' OR ', $lil_chunk) . NL . ')';
								}
							}
						}
					}

					//	----------------------------------------
					//  Loop through negated keywords
					//	----------------------------------------

					if (! empty($ands['not']))
					{
						// -------------------------------------
						//	Convert markers
						// -------------------------------------

						$ands['not']	= str_replace($this->ssu->negatemarker, '', $ands['not']);

						// -------------------------------------
						//	Loop
						// -------------------------------------

						foreach ($ands['not'] as $keyword)
						{
							$lil_chunk	= array();

							$arr	= array(
								'keywords'		=> array($keyword),
								'exactness'		=> 'non-exact',
								'word_boundary'	=> (strpos($this->how, 'no-search-words-within-words') !== FALSE) ? TRUE: FALSE
							);

							// -------------------------------------
							//	Prep standard fields for conjoined keyword search
							// -------------------------------------

							foreach (array('title') as $field)
							{
								$arr['field_name']		= $field;
								$arr['db_field_name']	= 't.' . $field;

								if (($temp = $this->ssu->prep_sql('not', $arr)) !== FALSE)
								{
									$lil_chunk	= array_merge($lil_chunk, array($temp));
								}
							}

							// -------------------------------------
							//	Prep custom fields for conjoined keyword search
							// -------------------------------------

							if (($customfields = $this->_fields('searchable', ee()->TMPL->site_ids)) !== FALSE)
							{
								foreach ($customfields as $key => $val)
								{
									if (! is_numeric($val)) continue;

									//Handle the case of duplicate channel names across MSM sites
									if (array_key_exists('supersearch_msm_duplicate_fields', $customfields) AND array_key_exists($key, $customfields['supersearch_msm_duplicate_fields']))
									{
										//we have the duplicate field name/channel name case to handle
										foreach($customfields['supersearch_msm_duplicate_fields'][$key] AS $subkey => $subval)
										{
											$arr['field_name']		= $subkey;
											$arr['db_field_name']	= 'cd.field_id_' . $subval;

											if (($temp = $this->ssu->prep_sql('not', $arr)) !== FALSE)
											{
												$lil_chunk	= array_merge($lil_chunk, array($temp));
											}
										}
									}
									else
									{
										$arr['field_name']		= $key;
										$arr['db_field_name']	= 'cd.field_id_' . $val;

										if (($temp = $this->ssu->prep_sql('not', $arr)) !== FALSE)
										{
											$lil_chunk	= array_merge($lil_chunk, array($temp));
										}
									}
								}

								if (! empty($lil_chunk))
								{
									$big_chunk[]	= '(' . NL . implode(' AND ', $lil_chunk) . NL . ')';
								}
							}
						}
					}

					//	----------------------------------------
					//  Glue big chunk
					//	----------------------------------------

					$fieldand[]	= NL . '(' . implode(' AND ', $big_chunk) . ')';
				}
			}

			$fieldandor	= array();

			if (! empty($fieldand))
			{
				$fieldandor[]	= implode(' OR ', $fieldand);
			}

			if (! empty($fieldor))
			{
				$fieldandor[]	= NL . implode(' OR ', $fieldor);
			}

			if (! empty($fieldandor))
			{
				$and[]	= NL . '(' . implode(' OR ', $fieldandor) . ')';
			}
		}

		//	----------------------------------------
		//  Fields
		//	----------------------------------------

		if (! empty($this->sess['search']['q']['field']))
		{
			$search['field']	= $this->sess['search']['q']['field'];

			$arr	= array(
				'exactness'		=> 'non-exact',
				'word_boundary'	=> (strpos($this->how, 'no-search-words-within-words') !== FALSE) ? TRUE: FALSE
			);

			foreach ($this->standard as $field)
			{
				$arr['field_name']		= $field;
				$arr['db_field_name']	= 't.' . $field;
				$fieldor				= array();
				$fieldand				= array();

				//	----------------------------------------
				//  Or
				//	----------------------------------------

				if (! empty($search['field'][$field]['or']))
				{
					$arr['keywords']	= $search['field'][$field]['or'];

					if (($temp = $this->ssu->prep_sql('or', $arr)) !== FALSE)
					{
						$fieldor	= array_merge($fieldor, array($temp));
					}
				}

				//	----------------------------------------
				//  Not
				//	----------------------------------------

				if (! empty($search['field'][$field]['not']))
				{
					$arr['keywords']	= $search['field'][$field]['not'];

					if (($temp = $this->ssu->prep_sql('not', $arr)) !== FALSE)
					{
						$not	= array_merge($not, array($temp));
					}
				}

				//	----------------------------------------
				//  And
				//	----------------------------------------

				if (! empty($search['field'][$field]['and']))
				{
					$big_chunk	= array();	// These will be or'd together

					foreach ($search['field'][$field]['and'] as $ands)
					{
						//	----------------------------------------
						//  Loop through main keywords
						//	----------------------------------------

						$lil_chunk	= array();

						if (! empty($ands['main']))
						{
							// -------------------------------------
							//	Convert markers
							// -------------------------------------

							$ands['main']	= $this->ssu->convert_markers($ands['main']);

							// -------------------------------------
							//	Loop
							// -------------------------------------

							foreach ($ands['main'] as $keyword)
							{
								$arr['keywords']	= array($keyword);

								if (($temp = $this->ssu->prep_sql('or', $arr)) !== FALSE)
								{
									$lil_chunk	= array_merge($lil_chunk, array($temp));
								}
							}
						}

						//	----------------------------------------
						//  Loop through negated keywords
						//	----------------------------------------

						if (! empty($ands['not']))
						{
							// -------------------------------------
							//	Convert markers
							// -------------------------------------

							$ands['not']	= str_replace($this->ssu->negatemarker, '', $ands['not']);

							// -------------------------------------
							//	Loop
							// -------------------------------------

							foreach ($ands['not'] as $keyword)
							{
								$arr['keywords']	= array($keyword);

								if (($temp = $this->ssu->prep_sql('not', $arr)) !== FALSE)
								{
									$lil_chunk	= array_merge($lil_chunk, array($temp));
								}
							}
						}

						$big_chunk[]	= '(' . NL . implode(' AND ', $lil_chunk) . NL . ')';
					}

					//	----------------------------------------
					//  Glue big chunk
					//	----------------------------------------

					$fieldand[]	= '(' . NL . implode(' OR ', $big_chunk) . NL . ')';
				}

				$fieldandor	= array();

				if (! empty($fieldand))
				{
					$fieldandor[]	= NL . implode(' AND ', $fieldand);
				}

				if (! empty($fieldor))
				{
					$fieldandor[]	= NL . implode(' OR ', $fieldor);
				}

				if (! empty($fieldandor))
				{
					$and[]	= NL . implode(' OR ', $fieldandor);
				}
			}

			if (($customfields = $this->_fields('searchable', ee()->TMPL->site_ids)) !== FALSE)
			{
				foreach ($customfields as $field => $val)
				{
					if (! ctype_digit($val)) continue;

					$grid_fields	= $this->_grid_fields();

					$arr['field_name']		= $field;
					$arr['db_field_name']	= 'cd.field_id_' . $val;
					$fieldor				= array();
					$fieldand				= array();

					//	----------------------------------------
					//  We loop no matter what, usually only once, but if someone uses the same field name between MSM sites, we loop for each since we need a separate field_id_13 type SQL block each time.
					//	----------------------------------------

					$field_loop	= array($val);

					if (isset($customfields['supersearch_msm_duplicate_fields'][$field]))
					{
						$field_loop	= $customfields['supersearch_msm_duplicate_fields'][$field];
					}

					rsort($field_loop);	// We are refusing to support both ANDed searching and MSM duplicate custom field names. But it can still be attempted by people. This rsort will mean that someone's ANDed MSM dupe field search attempt will find ANDed results against the oldest MSM site. Often the '1' MSM site id is the mothership and this feels like a fair way to fail. The last element in the $field_loop array will be the lowest number site_id and the residual value of $val will be that.

					foreach($field_loop as $val)	// I am reusing this $val var on purpose for convenience
					{
						$arr['db_field_name']	= 'cd.field_id_' . $val;

						if (! empty($search['field'][$field]['or']))
						{
							$arr['keywords']	= $search['field'][$field]['or'];

							if (isset($grid_fields[$field]))
							{
								$fieldor	= array_merge($fieldor, array($this->_prep_grid_sql($field, 'LIKE', $arr['keywords'][0], $this->sess['field_to_channel_map_sql'][$customfields[$field]])));
							}
							elseif (($temp = $this->ssu->prep_sql('or', $arr)) !== FALSE)
							{
								$fieldor	= array_merge($fieldor, array($temp));
							}
						}

						if (! empty($search['field'][$field]['not']))
						{
							$arr['keywords']	= $search['field'][$field]['not'];

							if (isset($grid_fields[$field]))
							{
								$not	= array_merge($fieldor, array($this->_prep_grid_sql($field, 'NOT LIKE', $arr['keywords'][0], $this->sess['field_to_channel_map_sql'][$customfields[$field]])));
							}
							elseif (($temp = $this->ssu->prep_sql('not', $arr)) !== FALSE)
							{
								$not	= array_merge($not, array($temp));
							}
						}
					}

					if (! empty($search['field'][$field]['and']))
					{
						$big_chunk	= array();	// These will be or'd together

						foreach ($search['field'][$field]['and'] as $ands)
						{
							//	----------------------------------------
							//  Loop through main keywords
							//	----------------------------------------

							$lil_chunk	= array();

							if (! empty($ands['main']))
							{
								// -------------------------------------
								//	Convert markers
								// -------------------------------------

								$ands['main']	= $this->ssu->convert_markers($ands['main']);

								// -------------------------------------
								//	Loop
								// -------------------------------------

								foreach ($ands['main'] as $keyword)
								{
									$arr['keywords']	= array($keyword);

									if (($temp = $this->ssu->prep_sql('or', $arr)) !== FALSE)
									{
										$lil_chunk	= array_merge($lil_chunk, array($temp));
									}
								}
							}

							//	----------------------------------------
							//  Loop through negated keywords
							//	----------------------------------------

							if (! empty($ands['not']))
							{
								// -------------------------------------
								//	Convert markers
								// -------------------------------------

								$ands['not']	= str_replace($this->ssu->negatemarker, '', $ands['not']);

								// -------------------------------------
								//	Loop
								// -------------------------------------

								foreach ($ands['not'] as $keyword)
								{
									$arr['keywords']	= array($keyword);

									if (($temp = $this->ssu->prep_sql('not', $arr)) !== FALSE)
									{
										$lil_chunk	= array_merge($lil_chunk, array($temp));
									}
								}
							}

							$big_chunk[]	= '(' . NL . implode(' AND ', $lil_chunk) . NL . ')';
						}

						//	----------------------------------------
						//  Glue big chunk
						//	----------------------------------------

						$fieldand[]	= '(' . NL . implode(' OR ', $big_chunk) . NL . ')';
					}

					$fieldandor	= array();

					if (! empty($fieldand))
					{
						$fieldandor[]	= NL . implode(' AND ', $fieldand);
					}

					if (! empty($fieldor))
					{
						$fieldandor[]	= NL . implode(' OR ', $fieldor);
					}

					if (! empty($fieldandor))
					{
						$and[]	= NL . implode(' OR ', $fieldandor);
					}
				}
			}
		}

		// -------------------------------------
		//	Prep fields for per-field exact search
		// -------------------------------------
		//	While in our loop, if we discover that someone is searching on a field that does not exist, we want to return FALSE. We don't want to give them results for bunk searches.
		// -------------------------------------

		if (($customfields = $this->_fields('searchable', ee()->TMPL->site_ids)) !== FALSE AND empty($this->sess['search']['q']['exactfield']) === FALSE)
		{
			foreach ($this->sess['search']['q']['exactfield'] as $key => $val)
			{
				if (empty($customfields[$key]) === TRUE) return FALSE;

				$field_number	= $customfields[$key];

				// -------------------------------------
				//	We expect searching on custom fields, but also allow searching on some exp_channel_titles fields.
				// -------------------------------------

				if (is_numeric($field_number))
				{
					$field_loop	= array($field_number);

					$grid_fields	= $this->_grid_fields();

					if (isset($customfields['supersearch_msm_duplicate_fields'][$key]))
					{
						$field_loop	= $customfields['supersearch_msm_duplicate_fields'][$key];
					}

					rsort($field_loop);

					$msmnot = $msmand = array();

					foreach ($field_loop as $field_number)
					{
						if (isset($grid_fields[$key]))
						{
							if (isset($val['not'][0]))
							{
								$msmnot[]	= $this->_prep_grid_sql($key, '!=', $val['not'][0], $this->sess['field_to_channel_map_sql'][$customfields[$key]]);

								continue;
							}
							elseif (isset($val['or'][0]))
							{
								$msmand[]	= $this->_prep_grid_sql($key, '=', $val['or'][0], $this->sess['field_to_channel_map_sql'][$customfields[$key]]);

								continue;
							}
						}

						if (($temp = $this->_prep_sql('not', 'cd.field_id_'.$field_number, $val, 'exact', $field_number, $key)) !== FALSE)
						{
							$msmnot[]	= $temp;
						}

						if (($temp = $this->_prep_sql('or', 'cd.field_id_'.$field_number, $val, 'exact', $field_number, $key)) !== FALSE)
						{
							$msmand[]	= $temp;
						}
					}

					if (! empty($msmnot))
					{
						$not[]	= '(' . implode(' OR ', $msmnot) . ')';
					}

					if (! empty($msmand))
					{
						$and[]	= '(' . implode(' OR ', $msmand) . ')';
					}
				}
				elseif (in_array($customfields[$key], $this->searchable_ct) === TRUE)
				{
					if (($temp = $this->_prep_sql('not', 't.'.$customfields[$key], $val, 'exact', $customfields[$key], $key)) !== FALSE)
					{
						$not[]	= $temp;
					}

					if (($temp = $this->_prep_sql('or', 't.'.$customfields[$key], $val, 'exact', $customfields[$key], $key)) !== FALSE)
					{
						$and[]	= $temp;
					}
				}
			}
		}

		// -------------------------------------
		//	Prep fields for empty search
		// -------------------------------------

		//	Conversion note. The designation to test for empty fields is not stored in the AND anymore. So change this when the time is ripe.

		if (($customfields = $this->_fields('searchable', ee()->TMPL->site_ids)) !== FALSE AND empty($this->sess['search']['q']['empty']) === FALSE)
		{
			foreach ($this->sess['search']['q']['empty'] as $key => $val)
			{
				if (! is_numeric($customfields[$key])) continue;
				if (! isset($this->sess['field_to_channel_map_sql'][$customfields[$key]])) return FALSE;

				$operator	= ($val['or'][0] == 'y') ? '=': '!=';

				// -------------------------------------
				//	Below you will see that once had this code set up so that if someone submitted a search to return entries where a specific field was empty, the implication would be that only that connected channel should be searched. This means that other entries not having the custom field assigned would also be blocked from showing. We've had a complaint about this. So I am changing behavior for now and we'll see what the response is. mitchell@solspace.com 2010 12 03
				// -------------------------------------

				if ($this->_get_field_type($key) == 'numeric')
				{
					$and[]	= 'cd.field_id_'.$customfields[$key]." ".$operator." 0 ";
				}
				elseif ($this->_get_field_type($key) == 'grid')
				{
					$and[]	= $this->_prep_grid_sql($key, $operator, '', $this->sess['field_to_channel_map_sql'][$customfields[$key]]);
				}
				else
				{
					$and[]	= 'cd.field_id_'.$customfields[$key]." ".$operator." ''";
				}
			}
		}

		// -------------------------------------
		//	Prep fields for greater than search
		// -------------------------------------

		//	This from field test is stored in a different place. As we convert, find this rascal.

		if (($customfields = $this->_fields('searchable', ee()->TMPL->site_ids)) !== FALSE AND empty($this->sess['search']['q']['from']) === FALSE)
		{
			foreach ($this->sess['search']['q']['from'] as $key => $val)
			{
				// if (empty($customfields[$key]['or'])) return FALSE;
				if (! isset($this->sess['field_to_channel_map_sql'][$customfields[$key]])) return FALSE;

				if (is_numeric($customfields[$key]))	// Numeric here means we have an EE custom field
				{
					//	Is this a custom date field? Use it.
					if (isset($this->sess['search']['q'][$key . 'from']))
					{
						//	If we're searching with a negative number, that's a unix timestamp older than 1970. We need to exclude entries with a 0 date field value since that only means the date has not yet been set for that entry.
						$and[]	= '(cd.field_id_'.$customfields[$key]." != 0" . $this->sess['field_to_channel_map_sql'][$customfields[$key]] . ')';

						$and[]	= '(cd.field_id_'.$customfields[$key]." >= " . $this->sess['search']['q'][$key . 'from'] . $this->sess['field_to_channel_map_sql'][$customfields[$key]] . ')';
					}
					elseif ($this->_get_field_type($key) == 'numeric' AND is_numeric($val['or'][0]) === TRUE)
					{
						$and[]	= '(cd.field_id_'.$customfields[$key]." >= " . $val['or'][0] . $this->sess['field_to_channel_map_sql'][$customfields[$key]] . ')';
					}
					elseif ($this->_get_field_type($key) == 'grid')
					{
						$and[]	= $this->_prep_grid_sql($key, '>=', $val['or'][0], $this->sess['field_to_channel_map_sql'][$customfields[$key]]);
					}
					else
					{
						$and[]	= '(cd.field_id_'.$customfields[$key]." >= '" . $val['or'][0] . "'" . $this->sess['field_to_channel_map_sql'][$customfields[$key]] . ")";
						$and[]	= '(cd.field_id_'.$customfields[$key]." != ''" . $this->sess['field_to_channel_map_sql'][$customfields[$key]] . ")";
					}
				}
				elseif (in_array($customfields[$key], $this->searchable_ct))
				{
					$and[]	= 't.'.$customfields[$key]." >= '" . $val['or'][0] . "'";
					$and[]	= 't.'.$customfields[$key]." != ''";
				}
			}
		}

		// -------------------------------------
		//	Prep fields for less than search
		// -------------------------------------

		//	This to field test is stored in a different place. As we convert, find this rascal.

		if (($customfields = $this->_fields('searchable', ee()->TMPL->site_ids)) !== FALSE AND empty($this->sess['search']['q']['to']) === FALSE)
		{
			foreach ($this->sess['search']['q']['to'] as $key => $val)
			{
				// if (empty($customfields[$key]['or'])) return FALSE;
				if (isset($this->sess['field_to_channel_map_sql'][$customfields[$key]]) === FALSE) return FALSE;

				if (is_numeric($customfields[$key]) !== FALSE)
				{
					//	Is this a custom date field? Use it.
					if (isset($this->sess['search']['q'][$key . 'to']))
					{
						//	If we're searching with a negative number, that's a unix timestamp older than 1970. We need to exclude entries with a 0 date field value since that only means the date has not yet been set for that entry.
						$and[]	= '(cd.field_id_'.$customfields[$key]." != 0" . $this->sess['field_to_channel_map_sql'][$customfields[$key]] . ')';

						$and[]	= '(cd.field_id_'.$customfields[$key]." <= " . $this->sess['search']['q'][$key . 'to'] . $this->sess['field_to_channel_map_sql'][$customfields[$key]] . ')';
					}
					elseif ($this->_get_field_type($key) == 'numeric' AND is_numeric($val['or'][0]) === TRUE)
					{
						$and[]	= '(cd.field_id_'.$customfields[$key]." <= ".$val['or'][0] . $this->sess['field_to_channel_map_sql'][$customfields[$key]] . ')';
					}
					elseif ($this->_get_field_type($key) == 'grid')
					{
						$and[]	= $this->_prep_grid_sql($key, '<=', $val['or'][0], $this->sess['field_to_channel_map_sql'][$customfields[$key]]);
					}
					else
					{
						$and[]	= '(cd.field_id_'.$customfields[$key]." <= '" . $val['or'][0] . "'" . $this->sess['field_to_channel_map_sql'][$customfields[$key]] . ")";
						$and[]	= '(cd.field_id_'.$customfields[$key]." != ''" . $this->sess['field_to_channel_map_sql'][$customfields[$key]] . ")";
					}
				}
				elseif (in_array($customfields[$key], $this->searchable_ct) === TRUE)
				{
					$and[]	= 't.'.$customfields[$key]." <= '" . $val['or'][0] . "'";
					$and[]	= 't.'.$customfields[$key]." != ''";
				}
			}
		}

		// -------------------------------------
		//	Prep 'from date' search
		// -------------------------------------
		//	We'll allow simple year indicators, year + month, year + month + day, all the way up to full seconds indicators. The string we expect is additive. All values except year are expected in two digits.
		// -------------------------------------

		if (empty($this->sess['search']['q']['datefrom']) === FALSE AND is_numeric($this->sess['search']['q']['datefrom']) === TRUE)
		{
			$and[]	= 't.entry_date >= ' . $this->sess['search']['q']['datefrom'];
		}
		elseif (empty($this->sess['search']['q']['entry_datefrom']) === FALSE AND is_numeric($this->sess['search']['q']['entry_datefrom']) === TRUE)
		{
			$and[]	= 't.entry_date >= ' . $this->sess['search']['q']['entry_datefrom'];
		}

		// -------------------------------------
		//	Prep 'to date' search
		// -------------------------------------
		//	We'll allow simple year indicators, year + month, year + month + day, all the way up to full seconds indicators. The string we expect is additive. All values except year are expected in two digits.
		// -------------------------------------

		if (empty($this->sess['search']['q']['dateto']) === FALSE AND is_numeric($this->sess['search']['q']['dateto']) === TRUE)
		{
			$and[]	= 't.entry_date <= ' . $this->sess['search']['q']['dateto'];
		}
		elseif (empty($this->sess['search']['q']['entry_dateto']) === FALSE AND is_numeric($this->sess['search']['q']['entry_dateto']) === TRUE)
		{
			$and[]	= 't.entry_date <= ' . $this->sess['search']['q']['entry_dateto'];
		}

		// -------------------------------------
		//	Prep 'expiry from date' search
		// -------------------------------------
		//	We'll allow simple year indicators, year + month, year + month + day, all the way up to full seconds indicators. The string we expect is additive. All values except year are expected in two digits.
		// -------------------------------------

		if (empty($this->sess['search']['q']['expiry_datefrom']) === FALSE AND is_numeric($this->sess['search']['q']['expiry_datefrom']) === TRUE)
		{
			$and[]	= 't.expiration_date != 0';	// Exp dates of 0 mean that no exp date has been recorded for the entry. If an entry has no exp date, then it can't possibly meet the exp date range tests.
			$and[]	= 't.expiration_date >= ' . $this->sess['search']['q']['expiry_datefrom'];
		}

		// -------------------------------------
		//	Prep 'expiry to date' search
		// -------------------------------------
		//	We'll allow simple year indicators, year + month, year + month + day, all the way up to full seconds indicators. The string we expect is additive. All values except year are expected in two digits.
		// -------------------------------------

		if (empty($this->sess['search']['q']['expiry_dateto']) === FALSE AND is_numeric($this->sess['search']['q']['expiry_dateto']) === TRUE)
		{
			$and[]	= 't.expiration_date != 0';	// Exp dates of 0 mean that no exp date has been recorded for the entry. If an entry has no exp date, then it can't possibly meet the exp date range tests.
			$and[]	= 't.expiration_date <= ' . $this->sess['search']['q']['expiry_dateto'];
		}

		// -------------------------------------
		//	Add site ids
		// -------------------------------------

		$and[]	= 't.site_id IN (' . implode(',', ee()->db->escape_str(ee()->TMPL->site_ids)) . ')';

		// -------------------------------------
		//	Manipulate $and, $not, $or
		// -------------------------------------

		if (ee()->extensions->active_hook('super_search_do_search_and_array') === TRUE)
		{
			$arr	= array('from' => $this->sess['search']['from'], 'and' => $and, 'not' => $not, 'or' => $or, 'subids' => $subids);

			$arr	= ee()->extensions->call('super_search_do_search_and_array', $this, $arr);

			if (ee()->extensions->end_script === TRUE)
			{
				return FALSE;
			}

			$this->sess['search']['from']	= (empty($arr['from'])) ? $this->sess['search']['from']: $arr['from'];
			$and	= (empty($arr['and'])) ? $and: $arr['and'];
			$not	= (empty($arr['not'])) ? $not: $arr['not'];
			$or		= (empty($arr['or'])) ? $or: $arr['or'];
			$subids	= (empty($arr['subids'])) ? $subids: $arr['subids'];
		}

		// -------------------------------------
		//	Anything to query?
		// -------------------------------------

		if (empty($this->sess['search']['from']) AND empty($and) AND empty($not) AND empty($or) AND empty($subids))
		{
			return FALSE;
		}

		// -------------------------------------
		//	Save subids for later
		// -------------------------------------

		if (empty($subids) === FALSE)
		{
			$this->sess['search']['subids']	= $subids;
		}

		// -------------------------------------
		//	Ordering by relevance?
		// -------------------------------------
		//	Warning: On large sets of data retrieved from the DB, pulling more than just the entry id will result in memory errors on most shared hosting environments. Therefore, warnings should be issued to users about searching with keywords, particularly short ones, that will return large data sets.
		//	Consider defining a MySQL level function to count and order strings like this: http://forge.mysql.com/tools/tool.php?id=65
		// -------------------------------------

		if ((! empty($this->sess['search']['q']['keywords']['or'])
				OR ! empty($this->sess['search']['q']['keywords']['and']))
			AND (isset($this->sess['search']['q']['relevance']) !== FALSE
				  AND !empty($this->sess['search']['q']['relevance'])))
		{
			foreach (array('title', 'url_title') as $key)
			{
				if (array_key_exists($key, $this->sess['search']['q']['relevance']) === TRUE)
				{
					$select	.= ', t.' . $key;
				}
			}

			if (count($this->sess['search']['q']['relevance']) > 0 AND ($fields = $this->_fields('all', ee()->TMPL->site_ids)) !== FALSE)
			{
				foreach ($this->sess['search']['q']['relevance'] as $key => $val)
				{
					if (isset($fields[$key]) === TRUE)
					{
						$select	.= ', cd.field_id_'.$fields[$key].' AS `'.$key.'`';
					}
				}
			}
		}

		// -------------------------------------
		//	Weighing relevance by mutliplier fields
		// -------------------------------------
		//	Warning: On large sets of data retrieved from the DB, pulling more than just the entry id will result in memory errors on most shared hosting environments. Therefore, warnings should be issued to users about searching with keywords, particularly short ones, that will return large data sets.
		//	Consider defining a MySQL level function to count and order strings like this: http://forge.mysql.com/tools/tool.php?id=65
		// -------------------------------------

		if ((	!empty($this->sess['search']['q']['keywords']['or'])
				OR ! empty($this->sess['search']['q']['keywords']['and']))
			AND (isset($this->sess['search']['q']['relevance']) !== FALSE
				  AND !empty($this->sess['search']['q']['relevance']))
			AND (isset($this->sess['search']['q']['relevance_multiplier']) !== FALSE
				  AND !empty($this->sess['search']['q']['relevance_multiplier'])))
		{
			if (array_key_exists('title', $this->sess['search']['q']['relevance_multiplier']) === TRUE)
			{
				$select	.= ', t.title';
				unset($this->sess['search']['q']['relevance_multiplier']['title']);
			}

			if (count($this->sess['search']['q']['relevance_multiplier']) > 0 AND ($fields = $this->_fields('all', ee()->TMPL->site_ids)) !== FALSE)
			{
				foreach ($this->sess['search']['q']['relevance_multiplier'] as $key => $val)
				{
					if (isset($fields[$key]) === TRUE)
					{
						$select	.= ', cd.field_id_'.$fields[$key].' AS `'.$key.'`';
					}
				}
			}
		}

		// -------------------------------------
		//	Some assembly required
		// -------------------------------------

		$sql	= $select . ' ' . implode(' ', $this->sess['search']['from']) . ' ' . $where;

		// -------------------------------------
		//	Continue 'where'
		// -------------------------------------

		// $and[]	= "(/*Begin second OR statement*/((t.title LIKE '%more%')) OR ((cd.field_id_2 LIKE '%more%') AND cd.weblog_id = 1) OR ((cd.field_id_4 LIKE '%more%') AND cd.weblog_id = 2) OR ((cd.field_id_5 LIKE '%more%') AND cd.weblog_id = 2) OR ((cd.field_id_6 LIKE '%more%') AND cd.weblog_id = 2)/*End second OR statement*/)";

		// $and[]	= "(/*Begin third OR statement*/((t.title LIKE '%juice%')) OR ((cd.field_id_2 LIKE '%juice%') AND cd.weblog_id = 1) OR ((cd.field_id_4 LIKE '%juice%') AND cd.weblog_id = 2) OR ((cd.field_id_5 LIKE '%juice%') AND cd.weblog_id = 2) OR ((cd.field_id_6 LIKE '%juice%') AND cd.weblog_id = 2)/*End third OR statement*/)";

		if (empty($this->sess['search']['q']['channel_ids']) === FALSE)
		{
			$sql	.= ' AND t.' . 'channel_id' . ' IN ('.implode(',', $this->sess['search']['q']['channel_ids']).')';
		}

		if (empty($and) === FALSE)
		{
			$sql	.= ' AND '.implode(' AND ', $and);
		}

		if (empty($not) === FALSE)
		{
			$sql	.= ' AND '.implode(' AND ', $not);
		}

		if (empty($subids) === FALSE)
		{
			$sql	.= " /*Begin subids statement*/ AND t.entry_id IN (".implode(",", $subids).") /*End subids statement*/ ";
		}

		if (empty($or) === FALSE)
		{
			$sql	.= ' AND (/*Begin OR statement*/'.implode(' OR ', $or).'/*End OR statement*/)';
		}

		// -------------------------------------
		//	Add order
		// -------------------------------------

		$sql	.= $order;

		// -------------------------------------
		//	Force limits?
		// -------------------------------------

		if (isset($this->sess['search']['q']['keywords']['or']) === TRUE)
		{
			$limit	= '';

			foreach ($this->sess['search']['q']['keywords']['or'] as $k)
			{
				if (strlen($k) < $this->minlength[0] OR in_array($k, $this->common) === TRUE)
				{
					$limit = ' LIMIT '.$this->minlength[1];
				}
			}

			$sql	.= $limit;
		}

		// -------------------------------------
		//	If there are no keywords then we may be serving Super Search from template params. In which case we need to force an upper limit on or we can crash a server with the SQL call.
		// -------------------------------------

		elseif (empty($this->sess['search']['q']['keywords']))
		{
			$sql	.= ' LIMIT '.$this->minlength[1];
		}

		// -------------------------------------
		//	Handled Third Party Search Indexes
		// -------------------------------------

		if ($this->_pref('third_party_search_indexes') != '')
		{
			// We may have a third party search index to handle

			$new_sql = $sql;

			$have_indexes = FALSE;

			// Loop and replace
			foreach(explode("|", $this->_pref('third_party_search_indexes')) AS $field_id)
			{
				// Ignore matrix and grid fields when we are searching directly on them.
				if (isset($this->sess['matrix_table']) AND in_array($field_id, $this->sess['matrix_table'])) continue;

				// Does this field_id exist in our search string?
				$count = 0;

				$new_sql = str_replace('cd.'.$field_id.' ', 'ci.'.$field_id.' ', $new_sql, $count);

				if ($count > 0) $have_indexes = TRUE;
			}

			if ($have_indexes === TRUE)
			{
				$join_str = " LEFT JOIN exp_super_search_indexes ci ON ci.entry_id = t.entry_id ";

				$new_sql = str_replace('%indexes%', $join_str , $new_sql);
			}
			else
			{
				// Clean up our %indexes% marker
				$new_sql = str_replace('%indexes%', '', $new_sql);
			}

			$sql = $new_sql;
		}
		else
		{
			// Clean up our %indexes% marker
			$sql = str_replace('%indexes%', '', $sql);
		}

		return $sql;
	}

	//	End _prep_search_for_sql()

	// -------------------------------------------------------------

	/**
	 * Prep sql
	 *
	 * @access	private
	 * @return	string
	 */

	function _prep_sql($type = 'or', $field = '', $keywords = array(), $how = 'any', $field_id = '', $field_name = '')
	{
		// -------------------------------------
		//	Basic validity test
		// -------------------------------------

		if ($field == '' OR is_array($keywords) === FALSE OR count($keywords) == 0) return FALSE;

		// -------------------------------------
		//	EE stores custom field data in columns of a single DB table. These columns can contain data for a blog entry even when the custom field no longer belongs to that channel. Janky architecture. We have to correct against that by forcing a channel test attached to any custom field test we run. This might even speed things up.
		// -------------------------------------

		$exceptions	= array('t.title', 't.status');

		if (isset($this->sess['uri']['keyword_search_author_name']) AND $this->check_yes($this->sess['uri']['keyword_search_author_name']))
		{
			$exceptions[] = 'm.screen_name';
			$exceptions[] = 'm.email';
			$exceptions[] = 'm.username';
		}

		if (isset($this->sess['uri']['keyword_search_category_name']) AND $this->check_yes($this->sess['uri']['keyword_search_category_name']))
		{
			$exceptions[] = 'cat.cat_name';
		}

		if (isset($this->sess['field_to_channel_map_sql'][$field_id]) === FALSE AND in_array($field, $exceptions) === FALSE) return FALSE;

		// -------------------------------------
		//	Are we ignoring any fields via template param?
		// -------------------------------------

		if ($field_name != '' AND ee()->TMPL->fetch_param('ignore_field') !== FALSE AND in_array($field_name, explode("|", ee()->TMPL->fetch_param('ignore_field'))) === TRUE)
		{
			return FALSE;
		}

		// -------------------------------------
		//	Check the state of our logical flag
		// -------------------------------------

		$allow_regex 		= FALSE;
		$allow_wildcards 	= FALSE;

		if (! empty($this->sess['uri']['regex_fields']))
		{
			$allow_regex = $this->model('Data')->flag_state(
				$this->allow_regex,
				$this->sess['uri']['regex_fields'],
				$field,
				$this->sess['fields']['searchable']
			);
		}

		if (! empty($this->sess['uri']['wildcard_fields']))
		{
			$allow_wildcards = $this->model('Data')->flag_state(
				$this->allow_wildcards,
				$this->sess['uri']['wildcard_fields'],
				$field,
				$this->sess['fields']['searchable']
			);
		}

		$this->has_regex = FALSE;

		// -------------------------------------
		//	Go!
		// -------------------------------------

		$arr	= array();

		// -------------------------------------
		//	Prep conjunction
		// -------------------------------------

		if ($type == 'and' AND empty($keywords['and']) === FALSE)
		{
			$temp	= array();

			$keywords['and']	= $this->ssu->convert_markers($keywords['and']);

			foreach ($keywords['and'] as $val)
			{
				if ($val == '') continue;

				if ($how == 'exact')
				{
					$temp[]	= $field." = '" . $this->ssu->escape_str($val) . "'";
				}
				elseif ($how == 'no-search-words-within-words')
				{
					$temp[]	= $field." REGEXP '[[:<:]]".$this->ssu->escape_str($val)."[[:>:]]'";
				}
				else
				{
					if ($allow_regex)
					{
						$this->has_regex = TRUE;

						$temp[]	= $field." REGEXP '".$this->ssu->escape_str($val)."'";
					}
					elseif ($allow_wildcards AND stripos($val, $this->wildcard) !== FALSE)
					{
						$temp[]	= $field." REGEXP '".str_replace($this->wildcard, '[a-zA-Z0-9]+', $this->ssu->escape_str($val)) ."'";
					}
					else
					{
						if (is_array($val) && isset($val['main']))
						{
							foreach($val['main'] as $key => $val)
							{
								$temp[]	= $field." LIKE '%".$this->ssu->escape_str($val)."%'";
							}
						}
						else
						{
							$temp[]	= $field." LIKE '%" . $this->ssu->escape_str($val) . "%'";
						}
					}
				}
			}

			if (count($temp) > 0)
			{
				$arr[]	= '('.implode(' AND ', $temp).')';
			}
		}

		// -------------------------------------
		//	Prep exclusion
		// -------------------------------------

		if ($type == 'not' AND empty($keywords['not']) === FALSE)
		{
			$temp	= array();

			$keywords['not']	= $this->ssu->convert_markers($keywords['not']);

			foreach ($keywords['not'] as $val)
			{
				if ($val == '') continue;

				if ($how == 'exact')
				{
					$temp[]	= $field." != '".$this->ssu->escape_str($val)."'";
				}
				elseif (strpos($how, 'no-search-words-within-words') !== FALSE)
				{
					$temp[]	= $field." NOT REGEXP '[[:<:]]".$this->ssu->escape_str($val)."[[:>:]]'";
				}
				else
				{
					if ($allow_regex)
					{
						$this->has_regex = TRUE;

						$temp[]	= $field." REGEXP '".$this->ssu->escape_str($val)."'";
					}
					elseif ($allow_wildcards AND stripos($val, $this->wildcard) !== FALSE)
					{
						$temp[]	= $field." NOT REGEXP '" . str_replace($this->wildcard, '[a-zA-Z0-9]+', $this->ssu->escape_str($val)) . "'";
					}
					else
					{
						if (is_array($val) && isset($val['main']))
						{
							foreach($val['main'] as $key => $val)
							{
								$temp[]	= $field." NOT LIKE '%".$this->ssu->escape_str($val)."%'";
							}
						}
						else
						{
							$temp[]	= $field." NOT LIKE '%" . $this->ssu->escape_str($val) . "%'";
						}
					}
				}
			}

			if (count($temp) > 0)
			{
				$arr[]	= '('.implode(' AND ', $temp).')';
			}
		}

		// -------------------------------------
		//	Prep inclusion
		// -------------------------------------

		if ($type == 'or' AND ! empty($keywords['or']))
		{
			$temp_keywords = $keywords['or'];

			if (isset($keywords['or_fuzzy']) AND is_array($keywords['or_fuzzy']))
			{
				foreach($keywords['or_fuzzy'] as $fuzzy_set)
				{
					$temp_keywords = array_merge($temp_keywords, $fuzzy_set);
				}
			}

			$temp	= array();

			$temp_keywords	= $this->ssu->convert_markers($temp_keywords);

			foreach ($temp_keywords as $val)
			{
				if ($val == '') continue;

				if (strpos($val, $this->spaces) !== FALSE)
				{
					$val	= str_replace($this->spaces, ' ', $val);
				}

				if ($how == 'exact')
				{
					$temp[]	= $field." = '".$this->ssu->escape_str($val)."'";
				}
				elseif (strpos($how, 'no-search-words-within-words') !== FALSE)
				{
					$temp[]	= $field." REGEXP '[[:<:]]".$this->ssu->escape_str($val)."[[:>:]]'";
				}
				else
				{
					if ($allow_regex)
					{
						$this->has_regex = TRUE;

						$temp[]	= $field." REGEXP '".$this->ssu->escape_str($val)."'";
					}
					elseif ($allow_wildcards AND stripos($val, $this->wildcard) !== FALSE)
					{
						$temp[]	= $field." REGEXP '".str_replace($this->wildcard, '[a-zA-Z0-9]+', $this->ssu->escape_str($val)) ."'";
					}
					else
					{
						if (is_array($val) && isset($val['main']))
						{
							foreach($val['main'] as $key => $val)
							{
								$temp[]	= $field." LIKE '%".$this->ssu->escape_str($val)."%'";
							}
						}
						else
						{
							$temp[]	= $field." LIKE '%" . $this->ssu->escape_str($val) . "%'";
						}
					}
				}
			}

			if (count($temp) > 0)
			{
				$arr[]	= '('.implode(' OR ', $temp).')';
			}
		}

		// -------------------------------------
		//	Glue
		// -------------------------------------

		if (empty($arr) === TRUE) return FALSE;

		if (in_array($field, $exceptions) === TRUE OR empty($this->sess['field_to_channel_map_sql'][$field_id]))
		{
			return '(' . implode(' AND ', $arr) . ')';
		}
		else
		{
			if ($type == 'not')
			{
				return '(' . implode(' AND ', $arr) . ')';
			}

			return '(' . implode(' AND ', $arr) . $this->sess['field_to_channel_map_sql'][$field_id] . ')';
		}
	}

	//	End prep sql

	// -------------------------------------------------------------

	/**
	 * Prep xid
	 *
	 * EE has gotten more secure since 2.6.x. Force XID's everywhere
	 *
	 * @access	private
	 * @return	array
	 */

	private function _prep_xid($tagdata = '')
	{
		$tagdata	= (empty($tagdata)) ? ee()->TMPL->tagdata: $tagdata;

		// -------------------------------------
		//	Make an attempt to help people's XID's work. Force one on to tagdata assuming that they are inside their own <form declaration
		// -------------------------------------

		ee()->load->helper('form');

		$data['hidden_fields']	= array();

		// Add the CSRF Protection Hash
		if (ee()->config->item('csrf_protection') == TRUE)
		{
			$data['hidden_fields'][
				ee()->security->get_csrf_token_name()
			] = ee()->security->get_csrf_hash();
		}

		if (ee()->config->item('secure_forms') == 'y')
		{
			if (! isset($data['hidden_fields']['XID']))
			{
				$data['hidden_fields'] = array_merge(array('XID' => '{XID_HASH}'), $data['hidden_fields']);
			}
			elseif ($data['hidden_fields']['XID'] == '')
			{
				$data['hidden_fields']['XID']  = '{XID_HASH}';
			}
		}

		if (is_array($data['hidden_fields']))
		{
			$form = "<div class='hiddenFields'>\n";

			foreach ($data['hidden_fields'] as $key => $val)
			{
				$form .= '<input type="hidden" name="'.$key.'" value="'.form_prep($val).'" />'."\n";
			}

			$form .= "</div>\n\n";
		}

		return $tagdata . $form;
	}

	//	End _prep_xid()

	// -------------------------------------------------------------

	/**
	 * Prep ignore words
	 *
	 * @access	private
	 * @return	string
	 */

	function _prep_ignore_words($keywords = '', $use_ignore_word_list_passed = '')
	{
		// -------------------------------------
		//	Basic validity test
		// -------------------------------------

		if ($keywords == '') return '';

		$ignore_word_list = $this->_pref('ignore_word_list');

		// nothing to do anyway, bail
		if ($ignore_word_list == '') return $keywords;

		// Is filtering enabled or overridden?
		$use_ignore_word_list = $this->check_yes($this->_pref('use_ignore_word_list'));

		if ($use_ignore_word_list_passed != '')
		{
			if ($this->check_yes($use_ignore_word_list_passed)) $use_ignore_word_list = TRUE;

			elseif ($this->check_no($use_ignore_word_list_passed)) $use_ignore_word_list = FALSE;

			elseif ($use_ignore_word_list_passed == 'toggle') $use_ignore_word_list = !$use_ignore_word_list;
		}

		// This has been turned off, return
		if (!$use_ignore_word_list) return $keywords;

		$keywords = ' '.$keywords;

		$keywords_start = $keywords;

		// We need to filter our keywords
		$words = explode('||', $this->_pref('ignore_word_list'));

		foreach($words AS $word)
		{
			// Test to see if this string is in the keywords
			if (stripos($keywords, $word))
			{
				// regex is expensive, so only do this when we have a candidate for matching
				$pattern = "/(?:^|[^a-zA-Z])" . preg_quote($word, '/') . "(?:$|[^a-zA-Z])/i";

				$keywords = preg_replace($pattern, ' ', $keywords);
			}
		}

		// Clean up any double spaces we might have
		$keywords = preg_replace('!\s+!', ' ', $keywords);

		if ($keywords_start != $keywords)
		{
			// Add a marker to say we've replaced something
			$this->sess['search']['q']['ignore_word_list_used'] = 'yes';
			$this->sess['search']['q']['pre_replace_keywords'] = $keywords_start;

			if (trim($keywords) == '')
			{
				return FALSE;
			}
		}

		return $keywords;
	}

	//	End prep ignore words

	/**
	 * Relevance count
	 *
	 * @access	private
	 * @return	integer
	 */

	function _relevance_count($row = array())
	{
		// -------------------------------------
		//	If there are no keywords, we don't do relevance.
		// -------------------------------------

		if (empty($this->sess['search']['q']['keywords']['or']) AND
			 empty($this->sess['search']['q']['keywords']['and'])) return 0;

		// -------------------------------------
		//	Get our relevance and multiplier arrays
		// -------------------------------------

		$relevance = array();

		$relevance_multiplier = array();

		if (isset($this->sess['search']['q']['relevance']) !== FALSE
			AND !empty($this->sess['search']['q']['relevance']))
		{
			$relevance = $this->sess['search']['q']['relevance'];
		}

		if (isset($this->sess['search']['q']['relevance_multiplier']) !== FALSE
			AND !empty($this->sess['search']['q']['relevance_multiplier']))
		{
			$relevance_multiplier = $this->sess['search']['q']['relevance_multiplier'];
		}

		// -------------------------------------
		//	Keyword list, we're searching on both 'or' and 'and' keywords, merge them
		// -------------------------------------

		$keywords	= array();

		if (! empty($this->sess['search']['q']['keywords']['or']))
		{
			$keywords	= $this->sess['search']['q']['keywords']['or'];
		}

		if (! empty($this->sess['search']['q']['keywords']['and'][0]['main']))
		{
			$keywords	= array_merge($keywords, $this->sess['search']['q']['keywords']['and'][0]['main']);
		}
		$fuzzy_keywords = array();
		$term_proximity = array();
		$term_frequency = array();

		if (isset($this->sess['search']['q']['keywords']['and_fuzzy']) AND is_array($this->sess['search']['q']['keywords']['and_fuzzy']))
		{
			foreach($this->sess['search']['q']['keywords']['and_fuzzy'] as $fuzzy_set)
			{
				$fuzzy_keywords = array_merge($fuzzy_keywords, $fuzzy_set);
			}
		}

		if (isset($this->sess['search']['q']['keywords']['or_fuzzy']) AND is_array($this->sess['search']['q']['keywords']['or_fuzzy']))
		{
			foreach($this->sess['search']['q']['keywords']['or_fuzzy'] as $fuzzy_set)
			{
				$fuzzy_keywords = array_merge($fuzzy_keywords, $fuzzy_set);
			}
		}

		// -------------------------------------
		//	Boundary
		// -------------------------------------

		$boundary	= ($this->relevance_count_words_within_words == TRUE) ? '': '\b';
		// This additional flag goes into our regular expression below and controls whether we count our keywords if they appear within other words.

		$n		= 0;
		$f 		= 0;
		$p 		= 0;
		$q 		= 0;
		$hash	= md5(serialize($relevance));

		if (isset($row['entry_id']) === TRUE AND isset($this->sess['search']['relevance_count'][$hash][$row['entry_id']]) === TRUE)
		{
			return $this->sess['search']['relevance_count'][$hash][$row['entry_id']];
		}

		foreach ($relevance as $key => $val)
		{
			if (empty($row[$key]) === TRUE OR empty($keywords)) continue;

			foreach ($keywords as $w)
			{
				if (function_exists('stripos') === FALSE OR stripos($row[$key], $w) !== FALSE)
				{
					// -------------------------------------
					//	This is still a boneheaded relevance algorithm. But at least with preg_match the counts can be a bit more accurate.
					// -------------------------------------

					$match	= '#' . $boundary . $w . $boundary . '#is';

					if (preg_match_all($match, $row[$key], $matches))
					{
						if (isset($matches[0]) === TRUE)
						{
							// -------------------------------------
							//	Term frequency relevance
							// -------------------------------------

							if (isset($this->sess['search']['relevance_frequency'][$w]) AND $this->relevance_frequency > 0)
							{
								$n = $n + ((count($matches[0]) * $val) * ($this->sess['search']['relevance_frequency'][$w] * $this->relevance_frequency));
							}
							else
							{
								$n	= $n + (count($matches[0]) * $val);
							}
						}
					}
				}
			}
		}

		// -------------------------------------
		//	Proximity of keywords relevance adjustment
		// -------------------------------------

		// Only do proximity based relevance when we have more than one keyword
		if (count($keywords) > 1 AND $this->relevance_proximity > 0)
		{
			$holder = array();

			// Do some fancier relevance calculations based on proximity
			foreach($relevance as $key => $val)
			{
				if (empty($row[$key]) === TRUE OR empty($keywords)) continue;

				//TODO : fix this so it honors the boundary setting

				foreach($keywords as $w)
				{
					$finished = FALSE;

					$offset = 0;

					$total_length = strlen($row[$key]);

					while(!$finished)
					{
						$offset = stripos($row[$key], $w, $offset);

						if ($offset !== FALSE OR $offset > $total_length - strlen($w))
						{
							$holder[$key][$w][] = $offset;

							$offset = $offset + strlen($w);
						}
						else
						{
							$finished = TRUE;
						}
					}
				}

				// Drop any fields that only have a single term in
				if (isset($holder[$key]) AND count($holder[$key]) < 2) unset($holder[$key]);
			}

			// The real grunt work
			foreach($holder as $key => $thisrow)
			{
				$checked = array();

				foreach($thisrow as $term1 => $loc1)
				{
					if (! isset($checked[$term1] ))
					{
						foreach($thisrow as $term2 => $loc2)
						{
							if ($term2 != $term1 AND ! isset($checked[$term2]))
							{
								// Set our marker so we don't repeat ourselves
								$checked[$term1] = $term2;

								$best = $this->relevance_proximity_threshold;

								// Get the best location proximty

								foreach($loc1 as $pos1)
								{
									foreach($loc2 as $pos2)
									{
										// Figure out where these terms are relative to each other
										$lt = ($pos1 < $pos2 ? $pos1 : $pos2);
										$rt = ($pos1 < $pos2 ? $pos2 : $pos1);
										$adjust =  ($pos1 < $pos2 ? strlen($term1) : strlen($term2));

										if (($rt - ($lt + $adjust)) < $best)
										{
											// New Best!
											$best = $rt - ($lt + $adjust);
										}
									}
								}

								if ($best < $this->relevance_proximity_threshold)
								{
									$term_proximity[] = $best;
								}
							}
						}
					}
				}
			}
		}

		//	End proxmimity calculations

		// -------------------------------------
		//	Fuzzy keywords relevance
		// -------------------------------------

		if (count($fuzzy_keywords) > 0)
		{
			// Because we're being fuzzy here, allow sub-word matches
			$boundary	= "\b";

			foreach ($relevance as $key => $val)
			{
				if (empty($row[$key]) === TRUE OR empty($fuzzy_keywords)) continue;

				foreach ($fuzzy_keywords as $w)
				{
					if (function_exists('stripos') === FALSE OR (is_string($w) AND stripos($row[$key], $w) !== FALSE))
					{
						// -------------------------------------
						//	This is still a boneheaded relevance algorithm. But at least with preg_match the counts can be a bit more accurate.
						// -------------------------------------

						$match	= '/' . $boundary . $w . $boundary . '/is';

						if (preg_match_all($match, $row[$key], $matches))
						{
							if (isset($matches[0]) === TRUE)
							{
								$f	= $f + (count($matches[0]) * $val);
							}
						}
					}
				}
			}

			// Reweight our fuzzy relevance by our adjustment value
			$f = $f * $this->fuzzy_weight;
		}

		$n = $n + $f;

		// -------------------------------------
		//	Term proximity relevance
		// -------------------------------------

		if (count($term_proximity) > 0)
		{
			$d = 0;

			foreach($term_proximity as $distance)
			{
				// Closer is better!
				// Grade the proximity against an inverse square
				// then adjusted with our proxmity_weight var
				// this then gets taken as the sum and further adjusts the total relevance

				if ($distance == 0) continue;

				$d = $d + $this->relevance_proximity / ($distance * $distance);
			}

			if ($d > 0) $n = $n + ($n * $d);
		}

		// -------------------------------------
		//	If we have a relevance multiplication marker adjust our multiplier accordingly
		// -------------------------------------

		if (count($relevance_multiplier) > 0)
		{
			$adjust = FALSE;

			$m = 0;

			foreach($relevance_multiplier as $key => $val)
			{
				if (isset($row[$key]) AND is_numeric($row[$key]) AND is_numeric($val))
				{
					$adjust = TRUE;

					$m += $row[$key] * $val;
				}
			}

			if ($adjust === TRUE) $n = $n * $m;
		}

		$this->sess['search']['relevance_count'][$hash][$row['entry_id']]	= $n;

		return $n;
	}

	//	End relevance count

	// -------------------------------------------------------------

	/**
	 * Remove empties
	 *
	 * @access	private
	 * @return	array
	 */

	function _remove_empties($arr = array())
	{
		$a	= array();

		if (is_null($arr)) return $a;

		foreach ($arr as $key => $val)
		{
			if ($val == '') continue;

			$a[$key]	= $val;
		}

		return $a;
	}

	//	End remove empties

	// -------------------------------------------------------------

	function curated_results()
	{
		$t	= microtime(TRUE);

		ee()->TMPL->log_item('Super Search: Starting curated_results()');

		// -------------------------------------
		//	Are they allowed here?
		// -------------------------------------

		if ($this->_security() === FALSE)
		{
			return $this->_parse_no_results_condition();
		}

		// -------------------------------------
		//	Is there a search
		// -------------------------------------

		if (($q = $this->_parse_search()) === FALSE)
		{
			return $this->_parse_no_results_condition($q, 'curated_results');
		}

		// -------------------------------------
		//	Prep query into something more SQL-able
		// -------------------------------------

		$this->initializeFuzzyDistance();

		if (($search = $this->_prep_query($q)) === FALSE)
		{
			return $this->_parse_no_results_condition();
		}

		if (empty($search['keywords_phrase']))
		{
			return $this->_parse_no_results_condition(array(), 'curated_results');
		}

		// -------------------------------------
		//	Prep order here so that it's part of the cache
		// -------------------------------------

		$order	= '';

		if (($neworder = $this->sess('uri', 'orderby')) !== FALSE)
		{
			$order	= $this->_prep_order($neworder);
		}
		elseif (($neworder = $this->sess('uri', 'order')) !== FALSE)
		{
			$order	= $this->_prep_order($neworder);
		}
		elseif (ee()->TMPL->fetch_param('orderby') !== FALSE AND ee()->TMPL->fetch_param('orderby') != '')
		{
			$order	= $this->_prep_order(ee()->TMPL->fetch_param('orderby'));
		}
		elseif (ee()->TMPL->fetch_param('order') !== FALSE AND ee()->TMPL->fetch_param('order') != '')
		{
			$order	= $this->_prep_order(ee()->TMPL->fetch_param('order'));
		}
		else
		{
			$order	= $this->_prep_order();
		}

		// -------------------------------------
		//	Do channel title and channel data search
		// -------------------------------------

		$limit 			= ee()->TMPL->fetch_param('limit', 10);
		$channels 		= explode('|', ee()->TMPL->fetch_param('channel'));
		$channel_ids 	= array();

		ee()->db->select('channel_id')
			->from('channels')
			->where_in('channel_name', $channels);

		$query = ee()->db->get();

		foreach($query->result_array() as $row)
		{
			$channel_ids[] = $row['channel_id'];
		}

		// -------------------------------------
		//	Grab any curated entry IDs that are related to our term
		// -------------------------------------

		ee()->db->select('ce.entry_id')
			->from('super_search_terms AS t')
			->join('super_search_curated_entries AS ce', 't.term_id = ce.term_id', 'left')
			->join('channel_titles AS ct', 'ce.entry_id = ct.entry_id', 'left')
			->where('t.term', $search['keywords_phrase']);

		if (! empty($channel_ids))
		{
			ee()->db->where_in('ct.channel_id', $channel_ids);
		}

		$query = ee()->db->get();

		$ids = array();

		foreach($query->result_array() as $row)
		{
			if (empty($row['entry_id'])) continue;
			$ids[] = $row['entry_id'];
		}

		if (empty($ids))
		{
			return $this->_parse_no_results_condition(array(), 'curated_results');
		}

		//	Apply order
		$sql	= "SELECT t.entry_id
			FROM exp_channel_titles t
			LEFT JOIN exp_channel_data cd ON cd.entry_id = t.entry_id
			WHERE t.entry_id IN (" . implode(',', $ids) . ")";

		$sql	.= $order;

		$query	= ee()->db->query($sql);

		$ids = array();

		foreach($query->result_array() as $row)
		{
			if (empty($row['entry_id'])) continue;
			$ids[] = $row['entry_id'];
		}

		if (empty($ids))
		{
			return $this->_parse_no_results_condition(array(), 'curated_results');
		}

		//$ids 	= array_slice($ids, 0, $limit);
		$hasPE 	= isset($this->sess['search']['q']['previous_entries']);
		$pe 	= ($hasPE) ? $this->sess['search']['q']['previous_entries'] : array();
		$pe 	= array_unique(array_merge($pe, $ids));

		$this->sess['search']['q']['previous_entries'] = ($hasPE) ? $pe : $ids;

		$this->sess['total_curated_results']	= count($ids);

		$params = array();

		if (($tagdata = $this->_entries($ids, $params)) === FALSE)
		{
			return $this->_parse_no_results_condition($q, 'curated_results');
		}

		ee()->TMPL->log_item('Super Search: Ending curated results() '.(microtime(TRUE) - $t));

		return $tagdata;
	}

	// End curated_results

	// -------------------------------------------------------------

	/**
	 * Results
	 *
	 * @access	public
	 * @return	string
	 */

	function results()
	{
		$t	= microtime(TRUE);

		ee()->TMPL->log_item('Super Search: Starting results()');

		/*

		$entries = ee('Model')
			->get('ChannelEntry');

		$entries->filter('site_id', ee()->config->item('site_id'));

		$entries->filterGroup();
		$entries->filter('field_id_2', 'LIKE', '%you%');
		$entries->orfilter('field_id_4', 'LIKE', '%you%');
		$entries->endFilterGroup();

		$entries->filterGroup();
		$entries->filter('field_id_2', 'LIKE', '%cowboy%');
		$entries->orfilter('field_id_4', 'LIKE', '%cowboy%');
		$entries->endFilterGroup();

		$entries->filterGroup();
		$entries->filter('field_id_2', 'LIKE', '%phat%');
		$entries->orfilter('field_id_4', 'LIKE', '%phat%');
		$entries->endFilterGroup();

		//$entries->orFilterGroup();
		//$entries->filter('field_id_2', 'LIKE', '%cowboy%');
		//$entries->orfilter('field_id_4', 'LIKE', '%cowboy%');
		//$entries->endFilterGroup();

		$result = $entries->all()
			->getDictionary('entry_id', 'title');

		print_r($result);


		//exit();
		*/



		// -------------------------------------
		//	Are they allowed here?
		// -------------------------------------

		if (version_compare($this->ee_version, '2.7', '>=') &&
			version_compare($this->ee_version, '2.8', '<'))
		{
			ee()->security->restore_xid();
		}

		if ($this->_security() === FALSE)
		{
			return $this->_parse_no_results_condition();
		}

		// -------------------------------------
		//	Is there a search
		// -------------------------------------

		$this->initializeFuzzyDistance();

		if (($q = $this->_parse_search()) === FALSE)
		{
			return $this->_parse_no_results_condition($q);
		}

		// -------------------------------------
		//	Are there required search arguments?
		// -------------------------------------

		if (($required = $this->_parse_for_required($q)) !== FALSE)
		{
			$this->save_search_form(ee()->TMPL->template, 'just_replace');
			return ee()->TMPL->tagdata	= $this->_parse_required_condition(ee()->TMPL->tagdata, $required);
		}
		else
		{
			ee()->TMPL->tagdata	=	$this->_parse_required_condition(ee()->TMPL->tagdata);
		}

		// -------------------------------------
		//	Do channel title and channel data search
		// -------------------------------------

		if (($ids = $this->do_search($q)) === FALSE)
		{
			return $this->_parse_no_results_condition($q);
		}

		$this->sess['total_results']	= count($ids);

		$params	= array();

		if (($tagdata = $this->_entries($ids, $params)) === FALSE)
		{
			return $this->_parse_no_results_condition($q);
		}

		ee()->TMPL->log_item('Super Search: Ending results() '.(microtime(TRUE) - $t));

		$this->save_search_form(ee()->TMPL->template, 'just_replace');

		return $tagdata;
	}

	//	End results

	// -------------------------------------------------------------

	/**
	 * Return message
	 *
	 * @access	public
	 * @return	string
	 */

	function _return_message($post = FALSE, $tagdata = '', $cond = array())
	{
		if (empty($cond['message'])) return FALSE;

		if ($post === TRUE)
		{
			return $this->show_error(array($cond['message']));
		}

		$tagdata	= ee()->functions->prep_conditionals($tagdata, $cond);
		$tagdata	= str_replace(LD.'message'.RD, $cond['message'], $tagdata);

		return $tagdata;
	}

	//	End return message

	// -------------------------------------------------------------

	/**
	 * Save search
	 *
	 * This method allows people to save a search that has been cached in order to prevent it from being uncached.
	 *
	 * @access	public
	 * @return	string
	 */

	function save_search()
	{
		// -------------------------------------
		//	Security
		// -------------------------------------

		if ($this->_security('posting') === FALSE)
		{
			return FALSE;
		}

		$post	= (empty($_POST)) ? FALSE: TRUE;

		$search_name	= ee()->input->get_post('super_search_name');

		$return	= (! empty($_POST['return'])) ? ee()->input->get_post('return'): ee()->input->get_post('RET');

		$return	= str_replace(array('&amp;', ';'), array('&', ''), $return);

		// -------------------------------------
		//	Get search id
		// -------------------------------------

		if (isset(ee()->TMPL) === TRUE AND ee()->TMPL->fetch_param('search_id') !== FALSE AND ee()->TMPL->fetch_param('search_id') != '')
		{
			$search_id	= ee()->TMPL->fetch_param('search_id');
		}
		elseif (ee()->input->get_post('super_search_id') !== FALSE AND is_numeric(ee()->input->get_post('super_search_id')) === TRUE)
		{
			$search_id	= ee()->input->get_post('super_search_id');
		}
		elseif (preg_match('/\/(\d+)/s', ee()->uri->uri_string, $match))
		{
			$search_id	= $match['1'];
		}
		else
		{
			return $this->_return_message($post, '', array('message' => lang('search_not_found')));
		}

		// -------------------------------------
		//	Delete mode?
		// -------------------------------------

		if ((isset(ee()->TMPL) === TRUE AND ee()->TMPL->fetch_param('delete') !== FALSE AND ee()->TMPL->fetch_param('delete') == 'yes') OR strpos(ee()->uri->uri_string, '/delete') !== FALSE OR ee()->input->get_post('delete_mode') == 'yes')
		{
			$sql	= "DELETE FROM exp_super_search_history
				WHERE history_id = ".ee()->db->escape_str($search_id)
				." AND
				(
					(member_id != 0
					AND member_id = ".ee()->db->escape_str(ee()->session->userdata('member_id')).")
					OR
					(cookie_id = ".ee()->db->escape_str($this->_get_users_cookie_id()).")
				)
				LIMIT 1";

			ee()->db->query($sql);

			if ($post === FALSE)
			{
				return $this->_return_message($post, ee()->TMPL->tagdata, array('failure' => FALSE, 'success' => TRUE, 'message' => lang('search_successfully_deleted')));
			}
			else
			{
				ee()->functions->redirect($return);
			}
		}

		// -------------------------------------
		//	Search name?
		// -------------------------------------

		if (empty($search_name))
		{
			return $this->_return_message($post, '', array('message' => lang('missing_name')));
		}

		// -------------------------------------
		//	Get all of this user's history for testing
		// -------------------------------------

		$sql	= "/* Super Search get user's search history for validation */ SELECT *
					FROM exp_super_search_history
					WHERE
					(
						member_id != 0
						AND member_id = ".ee()->db->escape_str(ee()->session->userdata('member_id')).")
						OR
						(cookie_id = ".ee()->db->escape_str($this->_get_users_cookie_id()).")";

		$query	= ee()->db->query($sql);

		// -------------------------------------
		//	No history at all?
		// -------------------------------------

		if ($query->num_rows() == 0)
		{
			return $this->_return_message($post, '', array('message' => lang('search_not_found')));
		}

		// -------------------------------------
		//	Prepare helper arrays
		// -------------------------------------

		foreach ($query->result_array() as $row)
		{
			$cache_ids[$row['cache_id']]		= $row;
			$history_ids[$row['history_id']]	= $row;
			$names[$row['search_name']]		= $row;
		}

		// -------------------------------------
		//	Is our search found?
		// -------------------------------------

		if (isset($history_ids[$search_id]) === FALSE)
		{
			return $this->_return_message($post, '', array('message' => lang('search_not_found')));
		}

		// -------------------------------------
		//	Are we changing a name? Is it unique?
		// -------------------------------------

		if (
			$history_ids[$row['history_id']]['search_name'] != $search_name
			AND isset($names[$search_name]) === TRUE
			AND $names[$search_name]['history_id'] != $search_id
			)
		{
			return $this->_return_message($post, '', array('message' => lang('duplicate_name')));
		}

		// -------------------------------------
		//	Update DB
		// -------------------------------------

		$sql	= ee()->db->update_string(
			'exp_super_search_history',
			array(
				'search_name'	=> $search_name,
				'saved'			=> 'y'
				),
			array(
				'history_id'	=> ee()->db->escape_str($search_id)
				)
			);

		// $sql	.= " ON DUPLICATE KEY UPDATE search_name = VALUES(search_name), saved = VALUES(saved)";

		if ($history_ids[$row['history_id']]['search_name'] != $search_name)
		{
			ee()->db->query($sql);
		}

		// -------------------------------------
		//	Return
		// -------------------------------------

		if ($post === FALSE)
		{
			return $this->_return_message($post, ee()->TMPL->tagdata, array('failure' => FALSE, 'success' => TRUE, 'message' => lang('search_successfully_saved')));
		}
		else
		{
			ee()->functions->redirect($return);
		}
	}

	//	End save search

	// -------------------------------------------------------------

	/**
	 * Save search form
	 *
	 * This method creates a form that users can submit to save searches to their histories.
	 *
	 * @access	public
	 * @return	string
	 */

	function save_search_form($tagdata = '', $just_replace = '')
	{
		// -------------------------------------
		//	Just return save search form?
		// -------------------------------------

		if ($just_replace != '' AND preg_match("/".LD.'super_search_save_search_form'.RD."(.*?)".LD.'\\'.T_SLASH.'super_search_save_search_form'.RD."/s", ee()->TMPL->template, $match))
		{
			ee()->TMPL->template	= str_replace($match[0], $this->save_search_form($match[1]), ee()->TMPL->template);
		}

		// -------------------------------------
		//	Do we have a search by id?
		// -------------------------------------

		if (ee()->TMPL->fetch_param('search_id') !== FALSE AND is_numeric(ee()->TMPL->fetch_param('search_id')) === TRUE)
		{
			$search_id	= ee()->TMPL->fetch_param('search_id');
		}

		// -------------------------------------
		//	We may already know the history id and cache ids
		// -------------------------------------

		if (! empty($this->sess['search_history']))
		{
			$results	= $this->sess['search_history'];
		}
		else
		{
			// -------------------------------------
			//	Check the DB for a search history
			// -------------------------------------

			$sql	= "/* Super Search find user's last search */
				SELECT
					history_id,
					cache_id,
					results AS super_search_results,
					search_name AS super_search_name,
					search_date AS super_search_date
				FROM exp_super_search_history
				WHERE site_id = '".ee()->db->escape_str(ee()->config->item('site_id'))."'";

			if (! empty($search_id))
			{
				$sql	.= " AND history_id = ".ee()->db->escape_str($search_id);
			}
			else
			{
				$sql	.= " AND saved = 'n'";	// We're looking for the single search history entry that captures the very last search they conducted.

				if (ee()->session->userdata('member_id') != 0)
				{
					$sql	.= " AND (member_id = ".ee()->db->escape_str(ee()->session->userdata('member_id'));
					$sql	.= " OR cookie_id = ".ee()->db->escape_str($this->_get_users_cookie_id()).")";
				}
				else
				{
					$sql	.= " AND cookie_id = ".ee()->db->escape_str($this->_get_users_cookie_id());
				}
			}

			$sql	.= " ORDER BY search_date DESC";
			$sql	.= " LIMIT 1";

			$query	= ee()->db->query($sql);

			if ($query->num_rows() == 0)
			{
				return $this->no_results('super_search');
			}

			$results	= $query->row_array();
		}

		// -------------------------------------
		//	Prep tagdata
		// -------------------------------------

		$tagdata	= ($tagdata != '') ? $tagdata: ee()->TMPL->tagdata;

		foreach ($results as $key => $val)
		{
			$key	= $this->_homogenize_var_name($key);

			if (strpos($tagdata, LD.$key) === FALSE) continue;

			if ($key == 'super_search_date' AND preg_match_all("/".$key."\s+format=[\"'](.*?)[\"']/s", $tagdata, $matches))
			{
				foreach ($matches[0] as $k => $v)
				{
					$tagdata	= str_replace(LD.$v.RD, $this->_parse_date($matches[1][$k], $val), $tagdata);
				}
			}

			$tagdata	= str_replace(LD.$key.RD, $val, $tagdata);
		}

		// -------------------------------------
		//	Prep data
		// -------------------------------------

		$config['ACT']				= ee()->functions->fetch_action_id('Super_search', 'save_search');

		$config['RET']				= (isset($_POST['RET'])) ? $_POST['RET'] : ee()->functions->fetch_current_uri();
		$config['RET']				= str_replace(array('&amp;', ';'), array('&', ''), $config['RET']);

		$config['tagdata']			= $tagdata;

		$config['super_search_id']	= $results['history_id'];

		$config['cache_id']			= $results['cache_id'];

		$config['delete_mode']		= (ee()->TMPL->fetch_param('delete_mode') == 'yes') ? 'yes': '';

		$config['form_id']			= (ee()->TMPL->fetch_param('form_id')) ? ee()->TMPL->fetch_param('form_id'): 'save_search_form';

		$config['form_name']		= (ee()->TMPL->fetch_param('form_name')) ? ee()->TMPL->fetch_param('form_name'): 'save_search_form';

		$config['return']			= (ee()->TMPL->fetch_param('return')) ? ee()->TMPL->fetch_param('return'): '';

		// -------------------------------------
		//	Declare form
		// -------------------------------------

		return $this->_form($config);
	}

	//	End save search form

	// -------------------------------------------------------------

	/**
	 * Save search to history
	 *
	 * @access	private
	 * @return	boolean
	 */

	function _save_search_to_history($cache_id = 0, $results = 0, $q = '')
	{
		if ($this->disable_history === TRUE) return FALSE;
		if (empty($this->sess['uri']) === TRUE) return FALSE;

		// -------------------------------------
		//	Let's set a history cookie for them
		// -------------------------------------

		if (($cookie_id = $this->_get_users_cookie_id()) === FALSE)
		{
			return FALSE;
		}

		// -------------------------------------
		//	Save to DB
		// -------------------------------------

		$newuri	= (empty($this->sess['newuri']) === FALSE) ? $this->sess['newuri']: '';

		$arr	= array(
			'cache_id'		=> $cache_id,
			'member_id'		=> ee()->session->userdata('member_id'),
			'cookie_id'		=> $cookie_id,
			'ip_address'	=> ee()->input->ip_address(),
			'site_id'		=> ee()->config->item('site_id'),
			'search_date'	=> ee()->localize->now,
			'search_name'	=> lang('search'),
			'results'		=> $results,
			'hash'			=> $this->hash,
			'query'			=> $q,
		);

		$sql	= ee()->db->insert_string('exp_super_search_history', $arr);

		$sql	.= " ON DUPLICATE KEY UPDATE cache_id = VALUES(cache_id), member_id = VALUES(member_id), cookie_id = VALUES(cookie_id), search_date = VALUES(search_date), saved = 'n', results = VALUES(results), hash = VALUES(hash), query = VALUES(query) /* Super Search save search to history */";

		ee()->db->query($sql);

		$arr['history_id']				= ee()->db->insert_id();
		$this->sess['search_history']	= $arr;

		return TRUE;
	}

	//	End save search to history

	// -------------------------------------------------------------

	/**
	 * form()
	 *
	 * This method is really not as dramatic as it sounds. It just lets people parse search variables from $tagdata so that they can let people come back to a remembered search and execute it again.
	 *
	 * @access	public
	 * @return	string
	 */

	function form()
	{
		// -------------------------------------
		//	Prep tagdata
		// -------------------------------------

		$tagdata	= ee()->TMPL->tagdata;

		// -------------------------------------
		//	We need to know about a search id for later
		// -------------------------------------

		$search_id	= (is_numeric(ee()->TMPL->fetch_param('search_id'))) ? ee()->TMPL->fetch_param('search_id'): '';

		// -------------------------------------
		//	Prep data
		// -------------------------------------

		$config['action']			= $this->_prep_return(ee()->TMPL->fetch_param('return'));
		$config['method']			= ee()->TMPL->fetch_param('method');

		//$config['RET']				= (isset($_POST['RET'])) ? $_POST['RET'] : ee()->functions->fetch_current_uri();
		//$config['RET']				= str_replace(array('&amp;', ';'), array('&', ''), $config['RET']);

		$config['tagdata']			= $tagdata;

		$config['history_id']		= $search_id;

		$config['form_id']			= (ee()->TMPL->fetch_param('form_id')) ? ee()->TMPL->fetch_param('form_id'): 'search_form';

		$config['form_name']		= (ee()->TMPL->fetch_param('form_name')) ? ee()->TMPL->fetch_param('form_name'): 'search_form';

		// -------------------------------------
		//	Is this being called before a results() call in the same template?
		// -------------------------------------

		//if (empty($search_id))
		//{
		//	// -------------------------------------
		//	//	Is this being called before a results() call in the same template?
		//	// -------------------------------------
        //
		//	$results_test	= FALSE;
        //
		//	foreach (ee()->TMPL->tag_data as $val)
		//	{
		//		if ($val['class'] == 'super_search')
		//		{
		//			if ($val['method'] == 'form')
		//			{
		//				$results_test	= TRUE;
		//			}
        //
		//			if ($results_test === TRUE AND $val['method'] == 'results')
		//			{
		//				return $tagdata;
		//			}
		//		}
		//	}
		//}

		// -------------------------------------
		//	Who is this?
		// -------------------------------------

		if (($member_id = ee()->session->userdata('member_id')) === 0)
		{
			if (($cookie_id = $this->_get_users_cookie_id()) === FALSE)
			{
				$config['tagdata']	= $this->ssu->strip_variables($tagdata);

				return $this->_form($config);
			}
		}

		// -------------------------------------
		//	Start the SQL
		// -------------------------------------

		$sql	= "/* Super Search grab last search for vars */
			SELECT history_id AS search_id, search_date AS super_search_date, search_name AS name, results, saved, query
			FROM exp_super_search_history
			WHERE site_id IN (" . implode(',', ee()->db->escape_str(ee()->TMPL->site_ids)) . ")";

		if (empty($member_id) === FALSE)
		{
			$sql	.= " AND member_id = " . ee()->db->escape_str($member_id);
		}
		elseif (empty($cookie_id) === FALSE)
		{
			$sql	.= " AND cookie_id = " . ee()->db->escape_str($cookie_id);
		}

		// -------------------------------------
		//	Do we have a search id?
		// -------------------------------------
		//	If we have a search id, we pull that search. If we do not have an id then we will pull the user's last search which we know if the only search in their history that has not yet been saved by them. We save the last search for each user who touches the system.
		// -------------------------------------

		if (! empty($search_id))
		{
			$sql	.= " AND history_id = ".ee()->db->escape_str($search_id);
		}
		else
		{
			$sql	.= " AND saved = 'n'";
		}

		// -------------------------------------
		//	Order
		// -------------------------------------

		$sql	.= " ORDER BY search_date DESC";

		// -------------------------------------
		//	Limit
		// -------------------------------------

		$sql	.= " LIMIT 1";

		// -------------------------------------
		//	Run query
		// -------------------------------------

		$query	= ee()->db->query($sql);

		if ($query->num_rows() === 0)
		{
			$config['tagdata']	= $this->ssu->strip_variables($tagdata);

			return $this->_form($config);
		}

		// -------------------------------------
		//	Parse
		// -------------------------------------

		$tagdata	= $this->_parse_template_vars($tagdata, unserialize(base64_decode($query->row('query'))));

		$config['tagdata']	= $tagdata;

		// -------------------------------------
		//	Declare form
		// -------------------------------------

		return $this->_form($config);
	}

	//	End form()

	// -------------------------------------------------------------

	/**
	 * Search
	 *
	 * DEPRECATED
	 *
	 * This method is really not as dramatic as it sounds. It just lets people parse search variables from $tagdata so that they can let people come back to a remembered search and execute it again.
	 *
	 * @access	public
	 * @return	string
	 */

	function search()
	{
		ee()->TMPL->tagdata	= $this->_prep_xid();

		// -------------------------------------
		//	We need to know about a search id for later
		// -------------------------------------

		$search_id	= (is_numeric(ee()->TMPL->fetch_param('search_id'))) ? ee()->TMPL->fetch_param('search_id'): '';

		// -------------------------------------
		//	Is this being called before a results() call in the same template?
		// -------------------------------------

		if (empty($search_id))
		{
			// -------------------------------------
			//	Is this being called before a results() call in the same template?
			// -------------------------------------

			$results_test	= FALSE;

			foreach (ee()->TMPL->tag_data as $val)
			{
				if ($val['class'] == 'super_search')
				{
					if ($val['method'] == 'search')
					{
						$results_test	= TRUE;
					}

					if ($results_test === TRUE AND $val['method'] == 'results')
					{
						return ee()->TMPL->tagdata;
					}
				}
			}
		}

		// -------------------------------------
		//	Who is this?
		// -------------------------------------

		if (($member_id = ee()->session->userdata('member_id')) === 0)
		{
			if (($cookie_id = $this->_get_users_cookie_id()) === FALSE)
			{
				return $this->ssu->strip_variables(ee()->TMPL->tagdata);
			}
		}

		// -------------------------------------
		//	Start the SQL
		// -------------------------------------

		$sql	= "/* Super Search grab last search for vars */
			SELECT history_id AS search_id, search_date AS super_search_date, search_name AS name, results, saved, query
			FROM exp_super_search_history
			WHERE site_id IN (" . implode(',', ee()->db->escape_str(ee()->TMPL->site_ids)) . ")";

		if (empty($member_id) === FALSE)
		{
			$sql	.= " AND member_id = " . ee()->db->escape_str($member_id);
		}
		elseif (empty($cookie_id) === FALSE)
		{
			$sql	.= " AND cookie_id = " . ee()->db->escape_str($cookie_id);
		}

		// -------------------------------------
		//	Do we have a search id?
		// -------------------------------------
		//	If we have a search id, we pull that search. If we do not have an id then we will pull the user's last search which we know if the only search in their history that has not yet been saved by them. We save the last search for each user who touches the system.
		// -------------------------------------

		if (! empty($search_id))
		{
			$sql	.= " AND history_id = ".ee()->db->escape_str($search_id);
		}
		else
		{
			$sql	.= " AND saved = 'n'";
		}

		// -------------------------------------
		//	Order
		// -------------------------------------

		$sql	.= " ORDER BY search_date DESC";

		// -------------------------------------
		//	Limit
		// -------------------------------------

		$sql	.= " LIMIT 1";

		// -------------------------------------
		//	Run query
		// -------------------------------------

		$query	= ee()->db->query($sql);

		if ($query->num_rows() === 0)
		{
			return $this->ssu->strip_variables(ee()->TMPL->tagdata);
		}

		// -------------------------------------
		//	Return parsed
		// -------------------------------------

		return $this->_parse_template_vars(ee()->TMPL->tagdata, unserialize(base64_decode($query->row('query'))));
	}

	//	End search

	// --------------------------------------------------------------------

	/**
	 *	Search Tests
	 *
	 *	@access		public
	 *	@return		string
	 */

	public function search_tests()
	{
		//	----------------------------------------
		//  Which test group?
		//	----------------------------------------

		$group	= 'category';

		if (! empty(ee()->uri->segments[2]) AND strpos(ee()->uri->segments[2], '&') === FALSE)
		{
			$group	= ee()->uri->segments[2];
		}

		//	----------------------------------------
		//  Prepare search array
		//	----------------------------------------

		$search['category']	= array(
			'category=1'		=> 'Searches for category 1. Should return 2.',
			'category=first'	=> 'Searches for category with url_title of "first". Should return 2. ',
			'category=-1'		=> 'Searches for NOT category 1. Should return 1.',
			'category=-first'	=> 'Searches for NOT category "first". Should return 1.',
			'category=2'		=> 'Searches for category 2. Should return 1 & 2. ',
			'category=second'	=> 'Searches for category with url_title of "second". Should return 1 & 2. ',
			'category=-3'		=> 'Searches for category not 3. Should return 2. ',
			'category=-third'	=> 'Searches for category url_title not "third". Should return 2. ',
			'category=4'		=> 'Searches for category 4. Should return 1. ',
			'category=boogie-down'	=> 'Searches for category with url_title "boogie-down", assumes template parameter category_indicator="category_url_title". Should return 1. ',
			'category=%22boogie down%22'	=> 'Searches for category with name "boogie down", assumes template parameter category_indicator set to default of "category_name". Should return 1. ',
			'category=-4'		=> 'Searches for category not 4. Should return 2. ',
			'category=-boogie-down'	=> 'Searches for category with url_title not "boogie-down", assumes template parameter category_indicator="category_url_title". Should return 2. ',
			'category=1+4'		=> 'Searches for category 1 or 4. Should return 1 & 2. ',
			'category=first+boogie-down'	=> 'Searches for category url_title "first" or "boogie-down", assumes template parameter category_indicator="category_url_title". Should return 1 & 2. ',
			'category=3+-4'		=> 'Searches for category 3 and not 4. [nothing].',
			'category=third+-boogie-down'	=> 'Searches for category url_title "third" and not "boogie-down", assumes template parameter category_indicator="category_url_title". [nothing]. ',
			'category=2&&3'		=> 'Searches for category 2 and 3. Must have both. Should return 1.',
			'category=2+3'		=> 'Searches for category 2 and 3. Must have both. Assumes template parameter inclusive_categories="yes". Should return 1.',
			'category=second&&third'	=> 'Searches for category url_title "second" and "third". Must have both. Should return 1. ',
			'category=1+2&&3'		=> 'Searches for category 1 or 2 and 3. Must have either 1 or both 2 and 3. Should return 1 & 2. ',
			'category=first+second&&third'	=> 'Searches for category url_title "first" or "second" and "third". Should return 1 & 2. ',
			'category=1&&2'	=> 'Searches for category 1 and 2. Must have both. Should return 2. ',
			'category=first&&second'	=> 'Searches for category url_title "first" and "second". Must have both. Should return 2. ',
			'category=first&&boogie-down'	=> ' Searches for category url_title "first" and "boogie-down". [nothing].',
			'category=2&&3+1&&2'	=> ' Searches for category 2 and 3 or 1 and 2. Must have both 2 and 3 or have both 1 and 2. Should return 1 & 2.',
			'category=1&&4+3&&1'	=> 'Searches for category 1 and 4 or 3 and 1. [nothing]. ',
			'category=second&&third+-first'	=> 'Searches for category url_title "second" and "third" and not "first". Should return 1. ',
			'category=1+boogie-down'	=> 'Searches for category 1 or category url_title "boogie-down", assumes template parameter category_indicator="category_url_title". Should return 1 & 2. ',
			'keywords=third'	=> 'With the parameter keyword_search_category_name set to "yes". Should find [1].',
			'keywords=second'	=> 'With the parameter keyword_search_category_name set to "yes". Should find [1] & [2].'
		);

		$search['category-like']	= array(
			'category-like=1'		=> 'Should return 2',
			'category-like=fir'		=> 'Should return 2',
			'category-like=second'	=> 'Should return 1 & 2',
			'category-like=-thi'	=> 'Should return 2',
			'category-like=ogie-do'	=> 'Assumes template parameter category_indicator="category_url_title". Should return 1.',
			'category-like=-boogie-down'	=> 'Assumes template parameter category_indicator="category_url_title". Should return 2',
			'category-like=rst+gie-down'	=> 'Assumes template parameter category_indicator="category_url_title". Should return 1 & 2',
			'category-like=ird+-boogie-do'	=> 'Assumes template parameter category_indicator="category_url_title". [nothing]',
			'category-like=ond&&rd'	=> 'Assumes template parameter category_indicator="category_url_title". Should return 1',
			'category-like=fir+second&&thi'	=> 'Assumes template parameter category_indicator="category_url_title". Should return 1 & 2',
			'category-like=fir&&cond'	=> 'Assumes template parameter category_indicator="category_url_title". Should return 2',
			'category-like=st&&gie-down'	=> 'Assumes template parameter category_indicator="category_url_title". [nothing]',
		);

		$search['channel']	= array(
			'channel=1'		=> 'Should return 1 & 2.',
			'channel=main'	=> 'Should return 1 & 2.',
			'channel=news'	=> 'Should return 3.',
			'channel=-news'	=> 'Should return 1 & 2.',
		);

		$search['group']	= array(
			'group=5'		=> 'Should return 1.',
			'group=members'	=> 'Should return 1.',
			'group=1'		=> 'Should return 2 & 3.',
			'group=%22super+admins%22'	=> 'Should return 2 & 3.',
		);

		$search['keywords']	= array(
			'keywords=mitchell'		=> 'Search for "mitchell" within other words or by itself. Should return 1. ',
			'keywords=solspace'		=> 'Search for "solspace". Should return 1 & 2. ',
			'keywords=%22big+phat+solspace%22'		=> 'Search for the phrase "big phat solspace". Should return 1. ',
			'keywords=%22big+phat+solspace%22+-kelsey'	=> 'Search for the phrase "big phat solspace" but make sure to exclude anything with "kelsey". [nothing]. ',
			'keywords=-solspace'	=> 'Search for anything without "solspace". Should return 3. ',
			'keywords=solspace&&cookie'	=> 'Search for entries with both "solspace" and "cookie". Should return 1. ',
			'keywords=solspace&&cookie&&-mitchell'	=> 'Search for entries with both "solspace" and "cookie" but make sure "mitchell" is nowhere to be found. [nothing]. ',
			'keywords=detroit+solspace&&cookie&&-mitchell'	=> 'Search for "detroit" or both "solspace" and "cookie" excluding "mitchell". This search tests for "detroit" as the one condition and the two conjoins plus negation as the second condition. Should return 2. ',
			'keywords=detroit+-mitchell'	=> 'Search for "detroit" and make sure none of the results includes "mitchell". Should return 2. ',
			'keywords=mañuel'	=> 'This is searching on a foreign character, the enya. Make sure it searches properly and displays properly. Should return 2. ',
			'keywords=A%26W'	=> 'This is searching for the root beer company. The ampersand in A&W needs to be escaped. Even Google expects ampersands to be escaped since they are such an important url character. If you have the ignore common words setting on, the "A" will be ignored and no results will be returned. Otherwise should return 1. ',
			'keywords=peanuts'	=> 'This searches inside a Grid field. Should return [1].',
			'keywords=beanie'	=> 'This searches inside a Matrix field. Should return [2].'
		);

		$search['fuzzy-keywords']	= array(
			'keywords=cokie'		=> 'Assume searching for "cookie" was intended. Should return 1.',
			'keywords=detrt'	=> 'Assume searching for "detroit" was intended. Should return 2.',
			'keywords=solpce&&cowby'	=> '"Solspace" and "Cowboy" were intended. But the misspellings occurred in the context of conjoined keyword searching. Conjoined searching is already so complex, for a $100 software product of this complexity, we are not supporting fuzzy conjoined searching. Should return [nothing].'
		);

		$search['field']	= array(
			'title=mitchell'			=> 'Should return 1',
			'title=jake'				=> 'Should return 2',
			'mail=jake'					=> 'Should return 2',
			'mail=jake@solspace.com'	=> 'Should return 2',
			'mail=solspace'				=> 'Should return 1 & 2',
			'body=det'					=> 'Should return 2',
			'mail=solspace&&mitchell'	=> 'Should return 1',
			'mail=solspace&&mitchell+solspace&&jake'	=> 'Should return 1 & 2',
			'title=jake&mail=solspace&&mitchell'	=> '[nothing]',
			'title=mitchell&title=jake'	=> 'Only one of the two arguments is accepted when there are duplicates. Should return 2.',
			'title=-jake'			=> 'Should return 1 & 3',
			'mail=-solspace'			=> 'Should return 3',
			'title=ja&&ke'			=> 'Should return 2',
			'title=ja&&ke+mi'		=> 'Should return 1 & 2. (This says ja and ke OR mi.)',
			'title=ja&&ke+mi&&sam'	=> 'Should return 2. (This says ja and ke OR mi and sam.)',
			'title=-e+ja&&ke+mi&&sam'	=> '[nothing]. (This says ja and ke OR mi and sam as long as nothing contains e.)',
			'screen=%22jake+solspace%22'	=> 'Should return 2',
			'mail=solspace&&-com'		=> '[nothing]',
			'body=detroit'				=> 'Should return 2',
		);

		$search['exact-field']	= array(
			'exact-title=mitchell'			=> 'Should return 1',
			'exact-summary=cookie'			=> '[Nothing]',
			'exact-body=detroit'					=> 'Should return 2',
			'exact-body=detroit&exact-title=jake'	=> 'Should return 2',
			'exact-body=detroit&exact-title=mitchell'	=> '[nothing]',
			'exact-mail=mitchell'				=> '[nothing]',
			'exact-mail=mitchell@solspace.com'	=> 'Should return 1',
			'exact-mail=mitchell@solspace.com+jake@solspace.com'	=> 'This is looking for the exact string of "mitchell@solspace.com jake@solspace.com". [Nothing]',
			'exact-mail=mitchell@solspace.com&&jake@solspace.com'	=> 'This search is trying a conjoined search. It is invalid and is ignored. Should return [1], [2], & [3].',
		);

		$search['field-empty']	= array(
			'body-empty=y'			=> 'Should return 1 & 3',
			'body-empty=n'			=> 'Should return 2',
		);

		$search['field-from']	= array(
			'entry_id-from=1'			=> 'Should return 1, 2, 3',
			'entry_id-from=2'			=> 'Should return 2, 3',
			'start_date-from=2000'		=> 'Should return 2',
			'start_date-from=1970'		=> 'Should return 1, 2 & 3',
			'entry_id-from=2&start_date-from=1970'			=> 'Should return 2 & 3',
			'entry_id-from=1&entry_id-to=2'			=> 'Should return 1 & 2',
			'entry_id-from=2&entry_id-to=1'			=> '[nothing]',
			'price-from=7'				=> 'Should return 1 & 2',
			'price-from=7&price-to=300'	=> 'Should return 2',
			'price-from=300'			=> 'Should return 1',
		);

		$search['date_field-from']	= array(
			'date-from=2000'			=> 'Should return 1, 2, 3',
			'date-from=2015'			=> '[nothing]',
			'date-from=2000&join_date-to=2012'	=> 'Should return 1, 2, 3',
			'entry_date-from=201203'			=> 'Should return 1, 2, 3',
			'expiry_date-from=201305'			=> 'Should return 2',
			'entry_date-from=201206'			=> 'Should return 3',
			'expiry_date-to=20120315'			=> 'Entries with no expiration date are valid for all expiry date range searches. Should return 1 & 3.',
			'special_date-to=today'				=> 'Should return 1, 2, 3',
			'special_date-to=1999'				=> 'Should return 2 & 3',
			'special_date-from=yesterday'		=> '[nothing]',
		);

		$search['order']	= array(
			'order=date'	=> 'Order entries by entry date ascending. Entry [1] should appear above entry [2].',
			'order=date+desc'	=> 'Order entries by entry date descending. Entry [2] should appear above entry [1].',
			'order=rating'		=> 'Order entries by overall Bayesian rating descending. Entry 3 is unrated. Entry [2] should appear above entry [1]. Entry [1] at bottom.',
			'order=rating+asc'	=> 'Order entries by overall Bayesian rating ascending. Entry [1] should appear above entry [2].',
			'order=rating_field-sexiness'	=> 'Order entries by sexiness rating descending. Entry [1] should appear above entry [2].',
		);

		$search['relevance']	= array(
			'keywords=butternubs'		=> 'This is a test of the "relevance" parameter in the template: relevance="summary=1". Entry [1] with all of the "butternub" references in the summary field should rank higher than entry [2].',
			'keywords=butternubs&order=rating+desc'		=> 'Make sure rating order and relevance play well together, meaning no PHP errors.',
			'keywords=butternub'		=> 'Temporarily add "butternubs" to the body field of Greg\'s entry. Temporarily change the relevance param to relevance="summery=1+body=10". Should rank entry [3] above [1] then [2].',
		);

		$search['status']	= array(
			'status=open'			=> 'Should return 1 & 2',
			'status=-closed'		=> 'Should return 1 & 2',
			'status=open+closed'	=> 'Should return 1, 2, 3',
		);

		$search['author']	= array(
			'author="mitchell"'					=> 'Should find entry [2] & [3].',
			'author=jake'						=> 'Should find entry [1].',
			'author=%22mitchell+kimbrough%22'	=> 'Assumes author_indicator=screen_name. Should find entry [2] & [3].',
			'author=%22jake+solspace%22'		=> 'Assumes author_indicator=screen_name. Should find entry [1].',
			'author=2'							=> 'Assumes author_indicator=author_id. Should find entry [1].',
			'author=mitchell@solspace.com'		=> 'Assumes author_indicator=email. Should find entry [2] & [3].',
			'keywords=mitchell'				=> 'When the "keyword_search_author_name" parameter is set to yes. Assumes author_indicator=screen_name. Should find entries [1], [2], [3].',
			'keywords=mitchel'					=> 'When the "keyword_search_author_name" parameter is set to yes. Assumes author_indicator=email. Should find entries [1].',
		);

		$search['partial-author']	= array(
			'author="mitch"'					=> 'Should find entry [2] & [3].',
			'author=mitch+ake'					=> 'Should find entry [1], [2], [3].',
			'author=%22mitchell+kimbrough%22'	=> 'Assumes author_indicator=screen_name. Should find entry [2] & [3].',
			'author=mitchell@'					=> 'Assumes author_indicator=email. Should find entry [2] & [3].',
			'author="pace"'						=> 'Assumes author_indicator=screen_name. Should find entry [1].',
		);

		$search['search_in']	= array(
			'search_in=summary|body&keywords=kelsey'		=> 'Search for "kelsey" but only in fields summary and body. Should return [1].',
			'search_in=summary&keywords=detroit'			=> 'Search for "detroit" but only in the summary field. Should return [nothing].',
		);

		$search['how']	= array(
			'how=exact&keywords=kelsey'		=> 'Find stuff that has the exact term "kelsey". Should return entry [1].',
			'how=phrase&keywords=kelsey+which+would'	=> 'Find stuff that has the exact phrase "kelsey which would". Should return [1].',
			'how=any&keywords=kelsey+mañuel'	=> 'Find "kelsey" or "mañuel". Should return entry [1] and entry [2].',
			'how=all&keywords=kelsey+mañuel'	=> 'Find stuff that has "kelsey" and "mañuel". Should return [nothing].',
			'how=all&keywords=kelsey+cookie'	=> 'Find stuff that has "kelsey" and "mañuel". Should return [1].',
			'how=none&keywords=kelsey'		=> 'Find stuff that does NOT have the term "kelsey". Should return entry [2] & [3].',
		);

		$search['highlight_keywords']	= array(
			'keywords=detroit'		=> 'Make sure the word "detroit" is highlighted in the returned body field.',
			'keywords=butternubs'	=> 'Make sure the word "butternubs" is highlighted in the returned summary field.',
		);

		$search['conjoin']	= array(
			'keywords=detroit&&butternubs'				=> 'Make sure both the words "detroit" and "butternubs" return correct results. Should return [2].',
			'keywords=detroit&&butternubs&&mitchell'	=> 'Make sure both the words "detroit" and "butternubs" and "mitchell" return correct results. Should return [nothing].',
		);

		$search['tags']	= array(
			'tags=mike'			=> 'Should return [1].',
			'tags=jill'			=> 'Should return [2].',
			'tags=mike+jill'	=> 'Should return [1] & [2].',
		);

		$search['grid']	= array(
			'menu:appetizer=peanuts'	=> 'Should return [1].',
			'menu:appetizer=fries'		=> 'Should return [1].',
			'menu:entree=fries'			=> 'Should return [nothing].',
			'menu:entree=-fries'		=> 'This is a case where people will complain. People think they are saying, return entries where the Grid column called entree contains no rows with the value of fries. But that\'s insane to code for. It would be a ridiculous DB hit and it would take me forever to write the code. Just not worth it. This syntax currently returns any entry that has a grid field where the column called entree has any row not containing the word fries. Should return [1, 2].',
			'menu:appetizer-from=fr'	=> 'Should return [1,2].',
			'menu:appetizer-to=fr'		=> 'Should return [nothing].',
			'menu:entree=soup'			=> 'Should return [nothing].',
			'menu:entree-empty=y'		=> 'Should return [1,2].',
			'menu:entree-exact=burger'	=> 'Should return [2].',
			'menu:exact-entree=burger'	=> 'Should return [nothing]. Note that this construct where the "exact" appears before the grid column name is not supported due to it\'s complexity in parsing.',
		);

		$search['matrix']	= array(
			'wardrobe:winter=socks'		=> 'Should return [1].',
			'wardrobe:winter=beanie'	=> 'Should return [2].',
			'wardrobe:summer=soup'		=> 'Should return [nothing].',
			'wardrobe:summer=sun'		=> 'Should return [1, 2].',
			'wardrobe:summer-empty=y'	=> 'Should return [nothing].',
			'wardrobe:summer-exact=sun hat'	=> 'Should return [2].',
			'wardrobe:exact-summer=burger'	=> 'Should return [nothing]. Note that this construct where the "exact" appears before the matrix column name is not supported due to it\'s complexity in parsing.',
		);

		$search['combos']	= array(
			'keywords=jak+cowb&start_date-from=2000'		=> 'Should return [2].',
			'keywords=jak+cowb&start_date-from=1970&category=First&&Second'		=> 'Should return [2].',
			'keywords=jak+cowb&start_date-from=1970&category=First&&Second&exact-screen=jake+solspace'		=> 'Should return [2].',
			'keywords=jak+cowb&start_date-from=1970&category=First&&Second&exact-screen=solbread'		=> '[nothing]',
		);

		$group	= (empty($search[$group])) ? 'category': $group;

		//	----------------------------------------
		//  Loop and prep
		//	----------------------------------------

		$i			= 0;
		$base		= ee()->uri->segments[1] . '/';
		$segment	= 'search&';
		$cond		= array();

		foreach ($search[$group] as $key => $test)
		{
			$cond[$i]['label']			= $key;
			$cond[$i]['explanation']	= $test;
			$cond[$i]['link']			= ee()->functions->create_url($base . $group . '/' . $segment . $key);
			$cond[$i]['selected']		= (! empty(ee()->uri->segments[3]) AND (ee()->uri->segments[3] == ($segment . $key) OR str_replace(' ', $this->spaces, ee()->uri->segments[3]) == ($segment . $key))) ? TRUE: FALSE;
			$i++;
		}

		//	----------------------------------------
		//  Explanation parse
		//	----------------------------------------

		$explanations['category']	= '<p>Two entries are categorized. Entry 1 belongs to [2] Second, [3] Third and [4] Boogie Down and [5] 90001. Entry 2 belongs to [1] First and [2] Second.</p>';

		$explanations['category-like']	= '<p>Two entries are categorized. Entry 1 belongs to [2] Second, [3] Third and [4] Boogie Down and [5] 90001. Entry 2 belongs to [1] First and [2] Second.</p>';

		$explanations['channel']	= '<p>There are 3 channels, [1] Main and [2] Blog and [3] News. Entries 1 and 2 belong to [1] Main. Entry 3 belongs to [3] News.</p>';

		$explanations['group']	= '<p>Entry 1 belongs to Jake Solspace who belongs to group [5] for Members. Entry 2 & 3 belong to Mitchell Kimbrough who belongs to group [1] Super Admins.</p>';

		$explanations['keywords']	= '<p>Three entries exist on the site.<br />Entry 1 has keywords of [mitchell] and [solspace] and [cowboy] and [cookie] and [kelsey] and [A&W] as well as the phrase "big phat solspace".<br />Entry 2 has keywords of [jake] and [solspace] and [cowboy] and [detroit] and [mañuel].<br />Entry 3 has none of the above keywords and belongs to the News channel.</p>';

		$explanations['fuzzy-keywords']	= '<p>Three entries exist on the site.<br />Entry 1 has keywords of [mitchell] and [solspace] and [cookie] and [kelsey].<br />Entry 2 has keywords of [jake] and [solspace] and [detroit] and [mañuel].<br />Entry 3 has none of the above keywords.</p>';

		$explanations['field']	= '<p>Two entries exist on the site. Entry 1 has title of [mitchell], screen field of [Solspace], mail field of [mitchell@solspace.com] and summary of [Cookie]. Entry 2 has title of [jake], screen of [Jake Solspace], email of [jake@solspace.com] and body field of [Detroit].</p>';

		$explanations['exact-field']	= '<p>Two entries exist on the site. Entry 1 has title of [mitchell], screen field of [Solspace], mail field of [mitchell@solspace.com] and summary of [Cookie]. Entry 2 has title of [jake], screen of [Jake Solspace], email of [jake@solspace.com] and body field of [Detroit].</p>';

		$explanations['field-empty']	= '<p>Two entries exist on the site. Entry 1 has a body field that is [empty]. Entry 2 has a body field that is [not empty].</p>';

		$explanations['field-from']	= '<p>Three entries exist on the site. Entry 1 has entry id of [1] and start_date year of [1972] and price of [$345]. Entry 2 has entry id of [2] and start_date year of [2006] and price of [$7]. Entry 3 has entry id of [3] and start date of [2013-04-18].</p>';

		$explanations['date_field-from']	= '<p>Two entries exist on the site. Entry 1 has entry date of [March 16, 2012] and a custom date field called special_date with a value of 2000-05-01. Entry 2 has entry date of [May 31, 2012] and expiration date of [June 12, 2013].</p>';

		$explanations['order']	= '<p>Two entries exist on the site. Entry 1 has a entry date of March 16, 2012. Entry 2 has a entry date of May 31, 2012. The Rating module is installed as well. Two rating fields exist in the module, "rating" and "sexiness". Entry 1 has rating of [1] and sexiness of [5]. Entry 2 has rating of [5] and sexiness of [4].</p>';

		$explanations['relevance']	= '<p>Entry [1] has "butternubs" 3 times in the Summary field. Entry [2] has "butternubs" only once in the Summary field.</p>';

		$explanations['status']	= '<p>Three entries exist on the site. Entry [1] and [2] have status of open. Entry [3] was open through this testing, but close it real quick for this status stuff.</p>';

		$explanations['author']	= '<p>Two entries exist on the site. Entry 1 was authored by Jake Solspace with username "jake" and entry 2 was authored by Mitchell Kimbrough with username "mitchell". "author_indicator=" is the template parameter used to indicate what argument in the url is used. Choices are author_id, member_id, screen_name. username is default.</p>';

		$explanations['partial-author']	= '<p>Two entries exist on the site. Entry 1 was authored by Jake Solspace with username "jake" and entry 2 was authored by Mitchell Kimbrough with username "mitchell". "author_indicator=" is the template parameter used to indicate what argument in the url is used. Choices are author_id, member_id, screen_name. username is default.</p>';

		$explanations['search_in']	= '<p>Three entries exist on the site. Entry 1 has keywords of [mitchell] and [solspace] and [cookie] and [kelsey] and [A&W] as well as the phrase "big phat solspace" all in the body field. Entry 2 has keyword of  [detroit] only in the body field. Entry 3 has none of the above keywords.</p>';

		$explanations['how']	= '<p>Three entries exist on the site. Entry 1 has keywords of [mitchell] and [solspace] and [cookie] and [kelsey] and [A&W] as well as the phrase "Kelsey which would" all in the body field. Entry 2 has keyword of  [mañuel] in the summary field. Entry 3 has none of the above keywords.</p>';

		$explanations['highlight_keywords']	= '<p>An entry exists on the site with the keyword "detroit" several times in the body field.</p>';

		$explanations['conjoin']	= '<p>Test the various ways that && syntax can break including relevance, keyword highlighting, sorting, variable output.</p>';

		$explanations['tags']	= '<p>Entry [1] has a tag of "Mike". Entry [2] has tags of "Jill" and "Jane".</p>';

		$explanations['grid']	= '<p>Entry [1] has a grid field called "menu". It has three columns. The first column is "appetizer", the second column is "entree", the third column is "dessert". There are two rows. The first row has "Peanuts | Peanuts | Juice". The second row has "Fries | null | Jelly". Entry [2] has the same grid field called "menu". The first row has "Salad | burger | Cake". The second row has "Soup | null | Milk".</p>';

		$explanations['matrix']	= '<p>Entry [1] has a matrix field called "wardrobe". It has two columns. The first column is "winter", the second column is "summer". There are two rows. The first row has "Socks | Sun Glasses". The second row has "Kleenex | Sunscreen". Entry [2] has the same matrix field called "wardrobe". The first row has "Boots | Flip Flops". The second row has "Beanie | Sun Hat".</p>';

		$explanations['combos']	= '<p>Mix and match across various criteria.</p>';

		ee()->TMPL->template	= str_replace(LD . 'search_tests_summary' . RD, $explanations[$group], ee()->TMPL->template);

		/*

		Entry 1
		------------
		Title
		Mitchell

		Category
		[2] Second, [3] Third, [4] Boogie Down, [5] 90001

		Channel
		[1] Main

		Status
		Open

		Entry Date
		2012 03 16

		Author
		Jake Solspace who belongs to group [5] Members

		Screen field
		Solspace

		Mail field
		mitchell@solspace.com

		Price field
		$345

		Summary field
		If you had a Cookie would you give it to a cowboy? No. You would give it to Kelsey which would be jive since cowboys are where it's at. I guess you could give the cowboy an A&W root beer. That would make big phat solspace the best. It could be that your supply of butternubs is not as great as the supply of butternubs that my butternubs truck has.

		Body field
		[empty]

		Start Date field
		1972 09 30

		Special Date field
		2000 05 01

		Menu field (Grid)
		Appetizer		Entree		Dessert
		Peanuts			Peanuts		Juice
		Fries						Jelly

		Wardrobe field (Matrix)
		Winter			Summer
		Socks			Sun Glasses
		Kleenex			Sunscreen

		Rating module ratings
		rating = 1
		sexiness = 5

		Tag module tags
		Mike

		Entry 2
		------------
		Title
		Jake

		Category
		[1] First, [2] Second

		Channel
		[1] Main

		Status
		Open

		Entry Date
		2012 05 31

		Expiration Date
		2013 06 12

		Author
		Mitchell Kimbrough who belongs to group [1] Super Admins

		Screen field
		Jake Solspace

		Mail field
		jake@solspace.com

		Price field
		$7

		Summary field
		Do you wanna be a cowboy? Mañuel does. And the term butternubs appears only once here.

		Body
		Detroit

		Start Date Field
		2006 06 23

		Menu field (Grid)
		Appetizer		Entree		Dessert
		Salad			Burger		Cake
		Soup						Milk

		Wardrobe field (Matrix)
		Winter			Summer
		Boots			Flip Flops
		Beanie			Sun Hat

		Rating module ratings
		rating = 5
		sexiness = 4

		Tag module tags
		Jill
		Jane

		Entry 3
		------------
		Title
		Greg

		Channel
		[3] News

		Status
		Open

		*/

		//	----------------------------------------
		//  Navigation parse
		//	----------------------------------------

		$nav_links	= '';

		foreach (array_keys($search) as $key)
		{
			$nav_links	.= '<a href="' . ee()->functions->create_url($base . $key) . '">' . $key . '</a> &middot; ';
		}

		$nav_links	= substr($nav_links, 0, -10);

		ee()->TMPL->template	= str_replace(LD . 'search_tests_navigation' . RD, '<p>' . $nav_links .  '</p>', ee()->TMPL->template);

		//	----------------------------------------
		//  Parse and bug out
		//	----------------------------------------

		$segment	= (! empty(ee()->uri->segments[3])) ? ee()->uri->segments[3]: '';

		$r	= '<h1>Search Tests</h1>' . NL;
		$r	.= '<p>' . $segment . '</p>' . NL;
		$r	.= '<p>' . $nav_links . '</p>' . NL;
		$r	.= $explanations[$group] . NL;
		$r	.= '<ol>';

		foreach ($cond as $val)
		{
			$selected	= ($val['selected']) ? ' style="background:#ccc"': '';
			$r	.= '<li' . $selected . '><a href="' . $val['link'] . '">' . $val['label'] . '</a>
<ul><li>' . $val['explanation'] . '</li></ul></li>';
		}

		$r	.= '</ol>';

		return $r;
	}

	//	End search tests

	// -------------------------------------------------------------

	/**
	 * Suggestion Plurals
	 *
	 * Gets the most likely plural versions of a passed word, and returns the array
	 * for validation checking
	 *
	 * @access	private
	 * @param 	array
	 * @return	array
	 */

	function _suggestion_plurals($word)
	{
		// We follow the standard 5 regular plural rules to start
		// Technically we should detect the phontic morphemes, but
		// thats hard, instead attempt the same thing with some
		// rough rules. It's not as complete, but we're checking the
		// validity agains the lexicon later, so it's ok

		// Of note : this isn't perfect. But it's good enough for now
		// It's english specific, but if theres a need we can expand on this
		// to add other language rules too.

		// 1. Ends in -s, -e or -o, add -es (kiss->kisses, phase->phases, hero->heroes)
		// 2. Ends in -y, drop the last -y, add -ies (cherry->cherries)
		// 3. Ends in -f or -fe, drop the -f or -fe, add -ves (leaf->leaves, knife->knives)
		// 4. Ends in -a, becomes -ae (or -æ) (formula->forumlae)
		// 5. Ends in -ex or -ix, becomes -ices or -es (matrix->matrices, index->indices)
		// 6. Ends in -is, becomes -es (axis->axes, crisis->crises)
		// 7. Ends in -ies, stays as is (series->series, species->species)
		// 8. Ends in -on, becomes -a (criterion->criteria)
		// 9. Ends in -um, becomes -a (millennium->millennia)
		// 10. Ends in -us, becomes -i, -era, -ora or -es (alumnus->alumni, cactus->cacti, uterus->uteri->uteruses)
		// x. Special cases
		//		foot -> feet
		//		goose -> geese
		//		man -> men
		//		mouse -> mice
		// 		tooth -> teeth
		// 		woman -> women

		$arr = array();

		$plural = $this->model('Data')->get_plural($word);

		$single = $this->model('Data')->get_singular($word);

		if ($plural != $word) $arr[] = $plural;

		if ($single != $word) $arr[] = $single;

		$arr = array_unique($arr);

		return $arr;

	}

	//	End _suggestion_plurals

	// -------------------------------------------------------------

	/**
	 * Suggestion Spelling
	 *
	 * Gets some a whole bunch of variants on the passed terms ordered by their levenshtein distance
	 * these will later get checked against the db for fitness
	 *
	 * @access	private
	 * @param 	array
	 * @return	array
	 */

	function _suggestion_spelling($words = array())
	{
		return $this->model('Data')->spelling_suggestions($words, $this->fuzzy_distance);
	}

	//	End _suggestion_spelling

	// -------------------------------------------------------------

	/**
	 * _suggestions
	 *
	 *  We follow a very similar method to the one described here : http://www.norvig.com/spell-correct.html
	 *  Basically it boils down to the follow steps :
	 *  1. Check if it's a known word
	 *  2. Check for variants of distance = 1
	 *  3. Check for variants of distance = 2
	 *  4. Mark it as an unknown, move on.
	 *
	 * @access	private
	 * @return	array
	 */

	function _suggestions($q, $count = 0)
	{
		if ($count == 0 AND isset($q['keywords']))
		{
			ee()->TMPL->log_item('Super Search: Staring to look for search suggestions');

			// This is our last hope. Correct at all costs



			// Break the phrase up
			$keywords_str = $q['keywords'];

			if (strpos($keywords_str, $this->spaces) !== FALSE)
			{
				$keywords_str	= str_replace($this->spaces, ' ', $keywords_str);
			}

			$keywords = explode(' ', $keywords_str);

			// ----------------------------------
			//  These 100 words make up 1/2 of all written material
			//  and by not checking them we should be able to greatly
			//  speed up the spellchecker
			// ----------------------------------
			//  'Borrowed' directly from EE's Spellcheck.php
			//   / with love - jb.
			// ----------------------------------

			$common = array('the', 'of', 'and', 'a', 'to', 'in', 'is', 'you', 'that',
							'it', 'he', 'was', 'for', 'on', 'are', 'as', 'with', 'his',
							'they', 'I', 'at', 'be', 'this', 'have', 'from', 'or', 'one',
							'had', 'by', 'word', 'but', 'not', 'what', 'all', 'were', 'we',
							'when', 'your', 'can', 'said', 'there', 'use', 'an', 'each',
							'which', 'she', 'do', 'how', 'their', 'if', 'will', 'up',
							'other', 'about', 'out', 'many', 'then', 'them', 'these', 'so',
							'some', 'her', 'would', 'make', 'like', 'him', 'into', 'time',
							'has', 'look', 'two', 'more', 'write', 'go', 'see', 'number',
							'no', 'way', 'could', 'people', 'my', 'than', 'first', 'water',
							'been', 'call', 'who', 'oil', 'its', 'now', 'find', 'long',
							'down', 'day', 'did', 'get', 'come', 'made', 'may', 'part');


			// Make sure we're clean
			$this->suggested = array();
			$this->corrected = array();

			// First check if it's a known word

			foreach($keywords AS $keyword)
			{
				if (! in_array(strtolower($keyword) , $common))
				{
					$this->suggested[strtolower($keyword)] =  strtolower($keyword);
				}
			}

			if (count($this->suggested) > 0)
			{
				// We have some values to check in our lexicon
				ee()->TMPL->log_item('Super Search: Checking the keywords are known');

				$this->_suggestion_known($this->suggested);
			}

			// Now check for edits with distance 1
			$attempt = array();

			if (count($this->suggested) > 0)
			{
				ee()->TMPL->log_item('Super Search: Looking for search terms with a lehvinstein distance = 1');

				// We have to do this per each keyword
				// so we an map it back later

				foreach($this->suggested AS $suggested)
				{
					$this_attempt = @array_merge(
						$this->_suggestion_deletion($suggested),
						$this->_suggestion_transposition($suggested),
						$this->_suggestion_alteration($suggested),
						$this->_suggestion_insertion($suggested));

					$attempt = array_merge($attempt, $this_attempt);
				}

				// Now recheck against our known results
				$this->_suggestion_known($attempt);
			}

			// We could check for spelling suggestions with a further edit distance,
			// but it gets expensive quickly.
			// Better to keep a note of it and look at our lesuire later on

			// Nothing we can do, bail
			if (count($this->suggested) > 0)
			{
				// Record the terms we can't find suggestions for
				// so we can work on them later at our lesuire
				$this->_suggestions_remember();

				ee()->TMPL->log_item('Super Search: Still have search terms we don\'t know. Stopping suggestion search');
			}
		}

		if (count($this->corrected) > 0)
		{
			// We have at least one suggestion
			// YEAH! Go for a bit of substitution

			// This really needs to be the search phrase, not just the keywords
			$q['suggestion'] = strtolower($q['keywords']);

			if (strpos($q['suggestion'], $this->spaces) !== FALSE)
			{
				$q['suggestion']	= str_replace($this->spaces, ' ', $q['suggestion']);
			}

			$q['suggestion'] = str_replace($this->corrected, array_keys($this->corrected), $q['suggestion']);

		}

		ee()->TMPL->log_item('Super Search: Finished looking for suggestions');

		return $q;
	}

	//	End _suggestion

	// -------------------------------------------------------------

	/**
	 * _suggestion_remember
	 *
	 * @access	private
	 * @return	array
	 */

	function _suggestions_remember()
	{
		// We have failed to find any suggestions for these keywords
		// So record them for more intesive searching at another time

		$sql = " INSERT INTO exp_super_search_lexicon (term, type, size, lang, term_date)
					VALUES ";

		$temp = array();

		foreach($this->suggested AS $suggest)
		{
			$temp[] = " ('" . ee()->db->escape_str($suggest) . "', 'unknown', '" . ee()->db->escape_str(strlen($suggest)) . "', 'en', '" . ee()->db->escape_str(time()) . "') ";
		}

		$sql .= implode(' , ', $temp);

		ee()->db->query($sql);
	}

	//	End suggest remember

	// -------------------------------------------------------------

	/**
	 * _suggestion_known
	 *
	 * @access	private
	 * @return	array
	 */

	function _suggestion_known($attempt = array())
	{
		if (count($attempt) == 0) return;

		$sql = " SELECT term, term_id, type
					FROM exp_super_search_terms
					WHERE term IN ('" . implode("','", array_keys(ee()->db->escape_str($attempt))) . "')";

		$query = ee()->db->query($sql);

		if ($query->num_rows > 0)
		{
			// We have at least one of our terms in our lexicon, deal with it

			foreach($query->result_array() AS $key => $row)
			{
				if ($row['type'] == 'misspelling')
				{
					// This is a recognized misspelling of a corrected word
					// Update the corrected array with the proper spelling

					if ($lookup_multi)
					{
						// Find the base term that we used to get here
					}
					$this->corrected[$row['term']] = $attempt[$row['term']];

					unset($this->suggested[$row['term']]);
				}
				elseif ($row['type'] == 'variant')
				{
					// This is a variant of a different word, with better results
					// ie. a plural etc..

					$this->corrected[$row['term']] = $attempt[$row['term']];

					unset($this->suggested[$attempt[$row['term']]]);
				}
				elseif ($row['type'] == 'valid')
				{
					// this appears to be valid word, we just have not results for
					// this filter set.
					// Remove from the suggested array, but leave out of the corrected

					$this->corrected[$row['term']] = $attempt[$row['term']];

					unset($this->suggested[$attempt[$row['term']]]);
				}
				elseif ($row['type'] == 'unknown')
				{
					// We've tried to correct this before and couldn't, stop here

					unset($this->suggested[$attempt[$row['term']]]);

				}
				elseif ($row['type'] == 'seed')
				{
					// We have a term from the seed lexicon
					if ($row['term'] != $attempt[$row['term']]) $this->corrected[$row['term']] = $attempt[$row['term']];

					unset($this->suggested[$attempt[$row['term']]]);
				}
				else
				{
					// This is strange
				}
			}
		}
	}

	//	End suggestion known

	// -------------------------------------------------------------

	/**
	 * _suggestion_deletion
	 *
	 * @access	private
	 * @return	array
	 */

	function _suggestion_deletion($word)
	{
		for($x=0; $x<strlen($word); $x++)
		{
		  $newword = substr($word, 0, $x) . substr($word, $x+1, strlen($word));

		  $results[$newword] = $word;
		}

		return $results;
	}

	//	End suggestion deletion

	// -------------------------------------------------------------

	/**
	 * _suggestion_transposition
	 *
	 * @access	private
	 * @return	array
	 */

	function _suggestion_transposition($word)
	{
		for($x=0; $x<strlen($word)-1; $x++)
		{
			$newword = substr($word, 0, $x) . $word[$x+1] . $word[$x] . substr($word, $x+2, strlen($word));

			if ($newword != $word) $results[$newword] = $word;
		}

		return $results;
	}

	//	End suggestion transposition

	// -------------------------------------------------------------

	/**
	 * _suggestion_alteration
	 *
	 * @access	private
	 * @return	array
	 */

	function _suggestion_alteration($word)
	{
		for ($c=0; $c<strlen($this->alphabet); $c++)
		{
			for ($x=0; $x<strlen($word); $x++)
			{
				$newword = substr($word, 0, $x) . $this->alphabet[$c] . substr($word, $x+1, strlen($word));

				if ($newword != $word) $results[$newword] = $word;
			}
		}

		return $results;
	}

	//	End suggestion alteration

	// -------------------------------------------------------------

	/**
	 * _suggestion_insertion
	 *
	 * @access	private
	 * @return	array
	 */

	function _suggestion_insertion($word)
	{
		for ($c=0;$c<strlen($this->alphabet);$c++)
		{
			for ($x=0;$x<strlen($word)+1;$x++)
			{
				$newword = substr($word, 0, $x) . $this->alphabet[$c] . substr($word, $x, strlen($word));

				if ($newword != $word) $results[$newword] = $word;
			}
		}

		return $results;
	}

	//	End suggestion insertion

	// -------------------------------------------------------------

	/**
	 * Security
	 *
	 * @access	private
	 * @return	boolean
	 */

	function _security($posting = 'not_posting')
	{
		// -------------------------------------
		//  Is the current user allowed to search?
		// -------------------------------------

		if (ee()->session->userdata['can_search'] == 'n' AND ee()->session->userdata['group_id'] != 1)
		{
			return $this->show_error(array(lang('search_not_allowed')));
		}

		// -------------------------------------
		//	Is the user banned?
		// -------------------------------------

		if (ee()->session->userdata['is_banned'] === TRUE)
		{
			return $this->show_error(array(lang('search_not_allowed')));
		}

		// -------------------------------------
		//	Is the IP address and User Agent required?
		// -------------------------------------

		if (ee()->config->item('require_ip_for_posting') == 'y' AND $posting == 'posting')
		{
			if ((ee()->input->ip_address() == '0.0.0.0' OR ee()->session->userdata['user_agent'] == '') AND ee()->session->userdata['group_id'] != 1)
			{
				return $this->show_error(array(lang('search_not_allowed')));
			}
		}

		// -------------------------------------
		//	Blacklist / Whitelist Check
		// -------------------------------------

		if (ee()->blacklist->blacklisted == 'y' && ee()->blacklist->whitelisted == 'n')
		{
			return $this->show_error(array(lang('search_not_allowed')));
		}

		// -------------------------------------
		//	Return
		// -------------------------------------

		return TRUE;
	}

	//	End security

	// -------------------------------------------------------------

	/**
	 * Separate numeric from textual
	 *
	 * We want two arrays derived from one. One array will contain the numeric values in a given array and the other will contain all other values. We use this when we are searching by categories and we might receive some cat ids as well as some cat names or cat urls in an array of search terms.
	 * If no numeric values are found, we return FALSE.
	 *
	 * @access	private
	 * @return	array
	 */

	function _separate_numeric_from_textual($arr = array())
	{
		if (empty($arr) === TRUE) return FALSE;

		$new['textual']	= array(); $new['numeric'] = array();

		foreach ($arr as $val)
		{
			if (is_numeric($val) === TRUE)
			{
				$new['numeric'][]	= $val;
			}
			else
			{
				$new['textual'][]	= $val;
			}
		}

		if (empty($new['numeric']) === TRUE) return FALSE;

		return $new;
	}

	//	End separate numeric from textual

	// -------------------------------------------------------------

	/**
	 * Sess
	 *
	 * This is a really convenient utility, but it takes up extra fractions of milliseconds. We should phase it out.
	 *
	 * @access	public
	 * @return	null
	 */

	function sess()
	{
		$s = func_num_args();

		if ($s == 0)
		{
			return FALSE;
		}

		// -------------------------------------
		//  Find Our Value, If It Exists
		// -------------------------------------

		$value = (isset($this->sess[func_get_arg(0)])) ? $this->sess[func_get_arg(0)] : FALSE;

		for($i = 1; $i < $s; ++$i)
		{
			if (! isset($value[func_get_arg($i)]))
			{
				return FALSE;
			}

			$value = $value[func_get_arg($i)];
		}

		return $value;
	}

	//	End sess

	// -------------------------------------------------------------

	/**
	 * Smart excerpt
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */

	function _smart_excerpt($str = '', $keywords = array(), $num = 100)
	{
		if (strlen($str) < $num)
		{
			return $str;
		}

		$terms = array();

		if (! empty($keywords['and'][0]['main']))
		{
			foreach ($keywords['and'][0]['main'] as $val)
			{
				$terms[]	= $this->model('Data')->get_singular(strtolower($val));
			}
		}

		if (! empty($keywords['or']))
		{
			foreach ($keywords['or'] as $val)
			{
				$terms[]	= $this->model('Data')->get_singular(strtolower($val));
			}
		}

		//	This orders our terms from longest to shortest. For me, this is a cheap and easy way to show an excerpt highlighting the most complex term in the search, which in a lot of cases is the more important one.
		if (! function_exists('str_sort'))
		{
			function str_sort($a,$b) {return strlen($b)-strlen($a);}
		}

		usort($terms, 'str_sort');

		// -------------------------------------
		//	Now load on fuzzies. We want them ranked last.
		// -------------------------------------

		if (! empty($keywords['or_fuzzy']))
		{
			$fuzzy_terms	= array();

			foreach ($keywords['or_fuzzy'] as $key => $temp)
			{
				foreach ($temp as $val)
				{
					$fuzzy_terms[]	= $this->model('Data')->get_singular(strtolower($val));
				}
			}

			usort($fuzzy_terms, 'str_sort');

			$terms	= array_merge($terms, $fuzzy_terms);
		}

		// -------------------------------------
		//	Find our term in our string and try to do so from the middle out.
		// -------------------------------------

		$smart_excerpt_threshold	= 10 * 5;

		foreach ($terms as $term)
		{
			if (($shorty = stristr($str, $term)) !== FALSE)
			{
				//	If the excerpt is the same length as the term, we found the term at the end of a sentence and would only be returning that word as the excerpt. That's a joke. Let's back that thing up a little bit.
				if ((strlen($shorty) - strlen($term)) < $smart_excerpt_threshold)
				{
					// allows the split to work properly with multi-byte Unicode characters
					if (is_php('4.3.2') === TRUE)
					{
						$arr = preg_split('#' . $term . '#u', $str, -1, PREG_SPLIT_NO_EMPTY);
					}
					else
					{
						$arr = preg_split('#' . $term . '#', $str, -1, PREG_SPLIT_NO_EMPTY);
					}

					if (str_word_count($arr[0]) >= ($num - str_word_count($term)) AND ! empty($arr[0]))
					{

						if (is_php('4.3.2') === TRUE)
						{
							$temp = preg_split('#\s#u', $arr[0], -1, PREG_SPLIT_NO_EMPTY);
						}
						else
						{
							$temp = preg_split('#\s#', $arr[0], -1, PREG_SPLIT_NO_EMPTY);
						}

						while (count($temp) >= ($num - str_word_count($term)))
						{
							array_shift($temp);
						}

						$arr[0]	= implode(' ', $temp);
					}

					$shorty	= $arr[0] . ' ' . $term;
				}

				return ' &#8230;' . ee()->functions->word_limiter($shorty, $num);
			}
		}

		return ' &#8230;' . ee()->functions->word_limiter($str, $num);
	}

	//	end smart excerpt

	// -------------------------------------------------------------

	/**
	 * Split date
	 *
	 * Break a date string into chunks of 2 chars each.
	 *
	 * @access	private
	 * @return	array
	 */

	function _split_date($str = '')
	{
		if ($str == '') return array();

		if (function_exists('str_split'))
		{
			$thedate	= str_split($str, 2); unset($str);
			return $thedate;
		}

		$temp	= preg_split('//', $str, -1, PREG_SPLIT_NO_EMPTY);

		do
		{
			$t = array();

			for ($i=0; $i<2; $i++)
			{
				$t[]	= array_shift($temp);
			}

			$thedate[]	= implode('', $t);
		}
		while (count($temp) > 0);

		return $thedate;
	}

	//	End split date

	// -------------------------------------------------------------

	/**
	 * Table columns
	 *
	 * Retrieves, stores and returns an array of the columns in a table
	 * At the moment, I've decided it's all stupid. We'll just go static
	 *
	 * @access	private
	 * @return	array
	 */

	function _table_columns($table = '')
	{
		if ($table == '') return FALSE;

		// -------------------------------------
		//	Make it static, make it fast
		// -------------------------------------

		$fields['exp_channel_titles']	= array(
			'entry_id' => 'entry_id',
			'site_id' => 'site_id',
			'channel_id' => 'channel_id',
			'author_id' => 'author_id',
			'pentry_id' => 'pentry_id',
			'forum_topic_id' => 'forum_topic_id',
			'ip_address' => 'ip_address',
			'title' => 'title',
			'url_title' => 'url_title',
			'status' => 'status',
			'versioning_enabled' => 'versioning_enabled',
			'view_count_one' => 'view_count_one',
			'view_count_two' => 'view_count_two',
			'view_count_three' => 'view_count_three',
			'view_count_four' => 'view_count_four',
			'allow_comments' => 'allow_comments',
			'allow_trackbacks' => 'allow_trackbacks',
			'sticky' => 'sticky',
			'date' => 'entry_date',
			'entry_date' => 'entry_date',
			'dst_enabled' => 'dst_enabled',
			'year' => 'year',
			'month' => 'month',
			'day' => 'day',
			'expiration_date' => 'expiration_date',
			'comment_expiration_date' => 'comment_expiration_date',
			'edit_date' => 'edit_date',
			'recent_comment_date' => 'recent_comment_date',
			'comment_total' => 'comment_total',
			'trackback_total' => 'trackback_total',
			'sent_trackbacks' => 'sent_trackbacks',
			'recent_trackback_date' => 'recent_trackback_date'
		);

		$fields['exp_members']	= array(
			'member_id' => 'member_id',
			'username' => 'username',
			'screen_name' => 'screen_name',
			'email' => 'email',
			'url' => 'url',
			'location' => 'location',
			'occupation' => 'occupation',
			'interests' => 'interests',
			'bday_d' => 'bday_d',
			'bday_m' => 'bday_m',
			'bday_y' => 'bday_y',
			'bio' => 'bio',
			'signature' => 'signature',
			'avatar_filename' => 'avatar_filename',
			'avatar_width' => 'avatar_width',
			'avatar_height' => 'avatar_height',
			'photo_filename' => 'photo_filename',
			'photo_width' => 'photo_width',
			'photo_height' => 'photo_height',
			'sig_img_filename' => 'sig_img_filename',
			'sig_img_width' => 'sig_img_width',
			'sig_img_height' => 'sig_img_height',
			'join_date' => 'join_date',
			'last_visit' => 'last_visit',
			'last_activity' => 'last_activity',
			'total_entries' => 'total_entries',
			'total_comments' => 'total_comments',
			'total_forum_topics' => 'total_forum_topics',
			'total_forum_posts' => 'total_forum_posts',
			'last_entry_date' => 'last_entry_date',
			'last_comment_date' => 'last_comment_date',
			'language' => 'language',
			'timezone' => 'timezone',
			'daylight_savings' => 'daylight_savings',
			'time_format' => 'time_format'
		);

		// -------------------------------------
		//	Manipulate $fields
		// -------------------------------------

		if (ee()->extensions->active_hook('super_search_table_columns') === TRUE)
		{
			$fields	= ee()->extensions->call('super_search_table_columns', $this, $fields);
		}

		if (isset($fields[$table]) === FALSE) return FALSE;

		return $fields[$table];
	}

	//	End table columns

	// -------------------------------------------------------------

	/**
	 * Uncerealize
	 *
	 * serialize() and unserialize() add a bunch of characters that are not needed when storing a one dimensional indexed array. Why not just use a pipe?
	 *
	 * @access	private
	 * @return	string
	 */

	function _uncerealize($str = '')
	{
		return explode('|', $str);
	}

	//	End uncerealize

	// -------------------------------------------------------------

	/**
	 * Variables
	 *
	 * @access	public
	 * @return	string
	 */

	function variables()
	{
		$p		= 'super_search_';
		$parse	= array();

		// -------------------------------------
		//	Carry on with the prep of our parsing array.
		// -------------------------------------

		if (($sess = $this->sess('uri')) === FALSE)
		{
			// -------------------------------------
			//	Our parse array may have already been prepped in search().
			// -------------------------------------

			if (($parsables = $this->sess('parsables')) !== FALSE)
			{
				$parse	= $parsables;

				// -------------------------------------
				//	Manipulate $q
				// -------------------------------------

				if (ee()->extensions->active_hook('super_search_array_variables') === TRUE)
				{
					$parse	= ee()->extensions->call('super_search_array_variables', $this, $parse, $p);
				}

				// -------------------------------------
				//	The rest is as they say, easy
				// -------------------------------------

				return ee()->TMPL->parse_variables(ee()->TMPL->tagdata, array($parse));
			}

			$sess	= array();
		}

		// -------------------------------------
		//	Start looping.
		// -------------------------------------

		foreach ($this->basic as $key)
		{
			if (strpos(ee()->TMPL->tagdata, $p . $key) === FALSE) continue;

			$parse[$p . $key]	= '';

			if (isset($sess[$key]) === TRUE)
			{
				// -------------------------------------
				//	Convert protected strings
				// -------------------------------------

				$sess[$key]	= str_replace(array($this->negatemarker), array('-'), $sess[$key]);

				// -------------------------------------
				//	Parse
				// -------------------------------------

				$parse[$p . $key]	= (strpos($sess[$key], $this->doubleampmarker) === FALSE) ? $sess[$key]: str_replace($this->doubleampmarker, '&&', $sess[$key]);
			}
		}

		// -------------------------------------
		//	Prepare boolean variables
		// -------------------------------------
		//	Some search parameters can have multiple values, like status. People can search for multiple status params at once. We would like to be able to evaluate for the presence of each of those statuses as boolean variables. So this: search&status=open+closed+"First+Looks+-empty" would allow {super_search_status_open}, {super_search_status_closed}, {super_search_status_First_Looks} and {super_search_status_not_empty} to evaluate as true. We replace spaces with underscores and quotes with nothing.
		// -------------------------------------

		foreach (array('channel', 'status', 'category') as $key)
		{
			if (isset($sess[$key]) === FALSE) continue;

			//	Protect dashes for negation so that we don't have conflicts with dash in url titles
			if (strpos($sess[$key], $this->separator.'-') !== FALSE)
			{
				$sess[$key]	= str_replace($this->separator.'-', $this->negatemarker, $sess[$key]);
			}

			if (strpos($sess[$key], '-') === 0)
			{
				$sess[$key]	= str_replace('-', $this->negatemarker, $sess[$key]);
			}

			$temp	= $this->_prep_keywords($sess[$key]);

			if (isset($temp['and']) === TRUE)
			{
				foreach ($temp['and'] as $val)
				{
					$val	= str_replace(' ', '_', $val);

					$parse[$p . $key . '_' . $val]	= TRUE;
				}
			}

			if (isset($temp['or']) === TRUE)
			{
				foreach ($temp['or'] as $val)
				{
					$val	= str_replace(' ', '_', $val);

					$parse[$p . $key . '_' . $val]	= TRUE;
				}
			}

			if (isset($temp['not']) === TRUE)
			{
				foreach ($temp['not'] as $val)
				{
					$val	= str_replace(' ', '_', $val);

					$parse[$p . $key . '_not_' . $val]	= TRUE;
				}
			}
		}

		// -------------------------------------
		//	Prepare date from and date to
		// -------------------------------------

		$parse[$p.'date'.$this->modifier_separator.'from']	= '';
		$parse[$p.'date'.$this->modifier_separator.'to']		= '';

		if (isset($sess['datefrom']) === TRUE)
		{
			$parse[$p.'date'.$this->modifier_separator.'from']	= $sess['datefrom'];
		}

		if (isset($sess['dateto']) === TRUE)
		{
			$parse[$p.'date'.$this->modifier_separator.'to']	= $sess['dateto'];
		}

		// -------------------------------------
		//	Allow an alias of date for entry_date
		// -------------------------------------

		$parse[$p.'entry_date'.$this->modifier_separator.'from']	= '';
		$parse[$p.'entry_date'.$this->modifier_separator.'to']		= '';

		if (isset($sess['datefrom']) === TRUE)
		{
			$parse[$p.'entry_date'.$this->modifier_separator.'from']	= $sess['datefrom'];
		}

		if (isset($sess['dateto']) === TRUE)
		{
			$parse[$p.'entry_date'.$this->modifier_separator.'to']	= $sess['dateto'];
		}

		// -------------------------------------
		//	Prepare expiry date from and date to
		// -------------------------------------

		$parse[$p.'expiry_date'.$this->modifier_separator.'from']	= '';
		$parse[$p.'expiry_date'.$this->modifier_separator.'to']		= '';

		if (isset($sess['expiry_datefrom']) === TRUE)
		{
			$parse[$p.'expiry_date'.$this->modifier_separator.'from']	= $sess['expiry_datefrom'];
		}

		if (isset($sess['expiry_dateto']) === TRUE)
		{
			$parse[$p.'expiry_date'.$this->modifier_separator.'to']	= $sess['expiry_dateto'];
		}

		// -------------------------------------
		//	Prepare custom fields
		// -------------------------------------

		if (($fields = $this->_fields('searchable', ee()->TMPL->site_ids)) !== FALSE)
		{
			foreach ($fields as $key => $val)
			{
				if (strpos(ee()->TMPL->tagdata, $p.$key) === FALSE
					AND strpos(ee()->TMPL->tagdata, $p.'exact'.$this->modifier_separator.$key) === FALSE
					AND strpos(ee()->TMPL->tagdata, $p.$key.$this->modifier_separator.'exact') === FALSE
					AND strpos(ee()->TMPL->tagdata, $p.$key.$this->modifier_separator.'empty') === FALSE
					AND strpos(ee()->TMPL->tagdata, $p.$key.$this->modifier_separator.'from') === FALSE
					AND strpos(ee()->TMPL->tagdata, $p.$key.$this->modifier_separator.'to') === FALSE) continue;

				$parse[$p.$key]			= '';
				$parse[$p.'exact'.$this->modifier_separator.$key]	= '';
				$parse[$p.$key.$this->modifier_separator.'exact']	= '';
				$parse[$p.$key.$this->modifier_separator.'empty']	= '';
				$parse[$p.$key.$this->modifier_separator.'from']	= '';
				$parse[$p.$key.$this->modifier_separator.'to']	= '';

				if (isset($sess['field'][$key]) === TRUE)
				{
					$parse[$p.$key]	= (strpos($sess['field'][$key], $this->doubleampmarker) === FALSE) ? $sess['field'][$key]: str_replace($this->doubleampmarker, '&&', $sess['field'][$key]);

					$temp	= $this->_prep_keywords($sess['field'][$key]);

					if (isset($temp['and']) === TRUE)
					{
						foreach ($temp['and'] as $val)
						{
							$val	= str_replace(' ', '_', $val);

							$parse[$p . $key . '_' . $val]	= TRUE;
						}
					}

					if (isset($temp['or']) === TRUE)
					{
						foreach ($temp['or'] as $val)
						{
							$val	= str_replace(' ', '_', $val);

							$parse[$p . $key . '_' . $val]	= TRUE;
						}
					}

					if (isset($temp['not']) === TRUE)
					{
						foreach ($temp['not'] as $val)
						{
							$val	= str_replace(' ', '_', $val);

							$parse[$p . $key . '_not_' . $val]	= TRUE;
						}
					}
				}

				if (isset($sess['exactfield'][$key]) === TRUE)
				{
					if (is_array($sess['exactfield'][$key]))
					{
						$collapased = implode('|',  $sess['exactfield'][$key]);

						$parse[$p . 'exact' . $this->modifier_separator . $key]	=
							(strpos($collapased, $this->doubleampmarker) === FALSE)
								? $collapased
								: str_replace($this->doubleampmarker, '&&', $collapased);
					}
					else
					{
						$parse[$p.$key.$this->modifier_separator.'exact']	=
							(strpos($sess['exactfield'][$key], $this->doubleampmarker) === FALSE)
								? $sess['exactfield'][$key]
								: str_replace($this->doubleampmarker, '&&', $sess['exactfield'][$key]);
					}
				}

				if (isset($sess['empty'][$key]) === TRUE)
				{
					$parse[$p.$key.$this->modifier_separator.'empty']	= $sess['empty'][$key];
				}

				if (isset($sess['from'][$key]) === TRUE)
				{
					$parse[$p.$key.$this->modifier_separator.'from']	= $sess['from'][$key];
				}

				if (isset($sess['to'][$key]) === TRUE)
				{
					$parse[$p.$key.$this->modifier_separator.'to']	= $sess['to'][$key];
				}
			}
		}

		// -------------------------------------
		//	Revert fake ampersands to real ones
		// -------------------------------------

		$parse	= str_replace($this->ampmarker, '&', $parse);

		// -------------------------------------
		//	Manipulate $q
		// -------------------------------------

		if (ee()->extensions->active_hook('super_search_array_variables') === TRUE)
		{
			$parse	= ee()->extensions->call('super_search_array_variables', $this, $parse, $p);
		}

		// -------------------------------------
		//	The rest is as they say, easy
		// -------------------------------------

		return ee()->TMPL->parse_variables(ee()->TMPL->tagdata, array($parse));
	}

	//	END variables()

	// -------------------------------------------------------------

	/**
	 * Channel ids
	 *
	 * @access	private
	 * @return	array
	 */

	function _channel_ids($id = '', $param = '')
	{
		// -------------------------------------
		//	Already done?
		// -------------------------------------

		if (($channel_ids = $this->sess('channel_ids')) === FALSE)
		{
			// -------------------------------------
			//	Fetch
			// -------------------------------------

			$channels	= $this->model('Data')->get_channels_by_site_id_keyed_to_name(ee()->TMPL->site_ids);
			$channel_ids	= $this->model('Data')->get_channels_by_site_id(ee()->TMPL->site_ids);

			$this->sess['channels']		= $channels;
			$this->sess['channel_ids']	= $channel_ids;
		}

		if ($id == '')
		{
			return $channel_ids;
		}

		if ($id != '' AND $param != '' AND isset($channel_ids[$id][$param]) === TRUE)
		{
			return $channel_ids[$id][$param];
		}

		if (isset($channel_ids[$id]) === TRUE)
		{
			return $id;
		}

		return FALSE;
	}

	//	End channel ids

	// -------------------------------------------------------------

	/**
	 * Prep return
	 *
	 * @access		private
	 * @return		string
	 */

	function _prep_return($return = '')
	{
		if (! empty($return))
		{
		}
		elseif (ee()->input->get_post('return') !== FALSE AND
			 ee()->input->get_post('return') != '')
		{
			$return	= ee()->input->get_post('return');
		}
		elseif (ee()->input->get_post('RET') !== FALSE AND
				 ee()->input->get_post('RET') != '')
		{
			$return	= ee()->input->get_post('RET');
		}
		else
		{
			$return = ee()->functions->fetch_current_uri();
		}

		if (preg_match("/".LD."\s*path=(.*?)".RD."/", $return, $match))
		{
			$return	= ee()->functions->create_url($match['1']);
		}
		elseif (stristr($return, "http://") === FALSE AND
				 stristr($return, "https://") === FALSE)
		{
			$return	= ee()->functions->create_url($return);
		}

		if (substr($return, -1) != '/')
		{
			$return .= '/';
		}

		return $return;
	}

	// End prep return

	// -------------------------------------------------------------

	/**
	 * Channels
	 *
	 * @access	private
	 * @return	array
	 */

	function _channels($channel = '', $param = '')
	{
		// -------------------------------------
		//	Already done?
		// -------------------------------------

		if (($channel_ids = $this->sess('channel_ids')) === FALSE)
		{
			// -------------------------------------
			//	Fetch
			// -------------------------------------

			$channels		= $this->model('Data')->get_channels_by_site_id_keyed_to_name(ee()->TMPL->site_ids);
			$channel_ids	= $this->model('Data')->get_channels_by_site_id(ee()->TMPL->site_ids);

			$this->sess['channels']		= $channels;
			$this->sess['channel_ids']	= $channel_ids;
		}

		if ($channel == '')
		{
			return FALSE;
		}

		if ($channel != '' AND $param != '' AND isset($channels[$channel][$param]) === TRUE)
		{
			return $channels[$channel][$param];
		}

		if (isset($channels[$channel]) === TRUE)
		{
			return $channel;
		}

		return FALSE;
	}

	//	End channels

	// -------------------------------------------------------------

	/**
	 * Param split
	 *
	 * @access	private
	 * @return	string
	 */

	 function _param_split($term = '', $sql_marker = ' term ')
	 {
		if ($term != '')
		{
			$terms = array();

			$splitters = array('|', '+', '&', ' ', ',');

			$has_spliter = FALSE;

			foreach($splitters AS $split)
			{
				if (strpos($term, $split) != FALSE)
				{
					foreach(explode($split, $term) as $single)
					{
						if ($single != '')	$terms[] = $this->ssu->escape_str($single);
					}
				}
			}

			if (count($terms) < 1)
			{
				$terms[] = $this->ssu->escape_str($term);
			}

			return " AND " . $sql_marker . " IN ('" . implode("','", $terms) . "') ";

		}
		else
		{
			return '';
		}
	 }

	 //	End param split

	/**
	 * Initializes the missing fuzzy distance
	 */
	private function initializeFuzzyDistance()
	{
		$this->fuzzy_distance = ee()->TMPL->fetch_param('fuzzy_distance', 2);
	}
}

// END CLASS Super search

// -------------------------------------------------------------
