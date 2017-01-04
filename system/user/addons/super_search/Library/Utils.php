<?php

namespace Solspace\Addons\SuperSearch\Library;
use Solspace\Addons\SuperSearch\Library\AddonBuilder;

class Utils extends AddonBuilder
{
	private $inclusive_keywords	 	= FALSE;
	private $inclusive_categories	= FALSE;

	public $modifier_separator		= '-';
	public $parser					= '&';
	public $separator				= '=';
	public $grid_field_separator	= ':';
	public $slash					= 'SLASH';
	public $spaces					= '+';
	public $pipes 					= '|';
	public $wildcard 				= '*';
	public $parsing_prefix			= 'super_search_';
	public $ampmarker				= '45lkj78fd23!lk';
	public $doubleampmarker			= '98lk7854cik3fgd9';
	public $negatemarker			= '87urnegate09u8';
	public $openparenmarker			= 'lkajsdkjh2812376gkjjbaskdfgh67';
	public $closeparenmarker		= 'lkajsdkjh2009865376gkfgh67';
	public $spaceinquotemarker		= 'llkjadsko097123knlkjkc';
	public $urimarker				= 'jhgkjkajkmjksjkrlr3409oiu';
	public $gridmarker				= 'njnibtsbbcyu7474368undfnu8h8';
	public $highlight_words_within_words	= TRUE;

	public $_buffer 				= array();	// Cut and Paste Buffer
	public $marker 		 			= '';		// Cut and Paste Marker

	private $basic					= array(
		'category',
		'categorylike',
		'category_like',
		'category-like',
		'group',
		'keywords',
		'limit',
		'allow_repeats',
		'order',
		'orderby',
		'search-words-within-words',
		'search_words_within_words',
		'start',
		'inclusive_keywords',
		'inclusive_categories',
		'keyword_search_author_name',
		'keyword_search_category_name',
		'smart_excerpt',
		'wildcard_fields',
		'site'
	);

	private $fields			= array();
	public $grid_fields		= null;
	private $flat			= array();
	private $date_fields	= array();
	private $birthdays		= array();
	private $ages			= array();

	public $sess			= array();

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

		if (function_exists ('mb_internal_encoding'))
		{
			mb_internal_encoding('UTF-8');
		}
	}

	// End constructor

	// -------------------------------------------------------------

	/**
	 * Clean keywords
	 *
	 *
	 * @access	private
	 * @return	string
	 */

	private function _clean_keywords($keywords = '')
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

	// --------------------------------------------------------------------

	/**
	 *	Convert markers
	 *
	 *	@access		public
	 *	@param		string
	 *	@return		string
	 */

	public function convert_markers($subject)
	{
		if (is_array($subject))
		{
			foreach ($subject as $key => $val)
			{
				$subject[$key]	= str_replace(
					array(
						$this->openparenmarker,
						$this->closeparenmarker,
						$this->doubleampmarker,
						$this->ampmarker,
						$this->negatemarker,
						$this->spaceinquotemarker,
						$this->spaces
					),
					array(
						'(',
						')',
						'&&',
						'&',
						'-',
						' ',
						' '
					),
				$val);
			}
		}
		else
		{
			$subject	= str_replace(
				array(
					$this->openparenmarker,
					$this->closeparenmarker,
					$this->doubleampmarker,
					$this->ampmarker,
					$this->negatemarker,
					$this->spaceinquotemarker,
					$this->spaces
				),
				array(
					'(',
					')',
					'&&',
					'&',
					'-',
					' ',
					' '
				),
			$subject);
		}

		return $subject;
	}

	//	End convert markers

	// -------------------------------------------------------------

	/**
	 *	Removes/Cuts A Piece of Content Out of String to Save it From Being Manipulated During a Find/Replace
	 *
	 *  Many thanks to gosha bine ("http://tagarga.com/blok/on/080307") for the code, it is rather brilliantly executed. -Paul
	 *
	 *	@access		public
	 *	@param		string
	 *	@param		bool|string
	 *	@return		string
	 */

	function cut($subject, $regex = FALSE)
    {
        if (is_array($subject))
        {
        	//$subject[0]	= strip_tags($subject[0]);
        	$this->_buffer[md5($subject[0])] = $subject[0];
        	return ' '.$this->marker.md5($subject[0]).$this->marker.' ';
       	}

       	return preg_replace_callback($regex, array(&$this, 'cut'), $subject);
    }

    //	END cut()

	// --------------------------------------------------------------------

	/**
	 * dd()
	 */
	public function dd($data)
	{
		print_r($data); exit();
	}
	//End dd()

	// -------------------------------------------------------------

	/**
	 *	Escape str
	 *
	 *  We want ee()->db->escape_str to also turn % into \% so that these searches, somefield LIKE '%15\%%', will work
	 *
	 *	@access		public
	 *	@param		string
	 *	@param		bool|string
	 *	@return		string
	 */

	function escape_str($str)
    {
    	if (is_array($str))
    	{
    		foreach ($str as $key => $val)
    		{
    			$str[$key]	= $this->escape_str($val);
    		}
    	}
    	else
    	{
			$str	= ee()->db->escape_str($str);

			if (strpos($str, '%') === FALSE OR strpos($str, '\%') !== FALSE) return $str;

			$str	= str_replace('%', '\%', $str);
    	}

		return $str;
    }

    //	End escape_str()

    // --------------------------------------------------------------------

	/**
	 * Get property
	 *
	 * @access	public
	 * @return	null
	 */

	public function get_property($property)
	{
		if (isset($this->$property) === FALSE) return FALSE;

		return $this->$property;
	}

	// End get property

    // -------------------------------------------------------------

	/**
	 * Remove empties
	 *
	 * @access	private
	 * @return	array
	 */

	public function remove_empties($arr = array())
	{
		$a	= array();

		foreach ($arr as $key => $val)
		{
			if ($val == '') continue;

			$a[$key]	= $val;
		}

		return $a;
	}

	//	End remove empties

    // -------------------------------------------------------------

	/**
	 * Sanitize
	 *
	 * @access	private
	 * @return	string
	 */

	private function _sanitize($str = '')
	{
		$bad	= array('$', 		'(', 		')',	 	'%26',		'%28', 		'%29');
		$good	= array('&#36;',	$this->openparenmarker,	$this->closeparenmarker,	$this->ampmarker,		$this->openparenmarker,	$this->closeparenmarker);

		ee()->load->helper('text');
		// $str = (ee()->config->item('auto_convert_high_ascii') == 'n') ? ascii_to_entities($str): $str;

		return str_replace($bad, $good, $str);
	}

	//	End sanitize

    // -------------------------------------------------------------

	/**
	 * Separate numeric from textual
	 *
	 * We want two arrays derived from one. One array will contain the numeric values in a given array and the other will contain all other values. We use this when we are searching by categories and we might receive some cat ids as well as some cat names or cat urls in an array of search terms.
	 * If no numeric values are found, we return FALSE.
	 *
	 * @access	public
	 * @return	array
	 */

	public function separate_numeric_from_textual($arr = array())
	{
		if (empty($arr) === TRUE) return FALSE;

		$new['textual']	= $new['numeric'] = array();

		foreach ($arr as $val)
		{
			$val	= trim ($val);

			if (is_numeric($val) === TRUE)
			{
				$new['numeric'][]	= $val;
			}
			else
			{
				$new['textual'][]	= $val;
			}
		}

		if (empty($new['numeric']) AND empty($new['textual'])) return FALSE;

		return $new;
 	}

	//	End separate numeric from textual

    // --------------------------------------------------------------------

	/**
	 * Set property
	 *
	 * @access	public
	 * @return	null
	 */

	public function set_property($property, $value, $merge_arrays = TRUE)
	{
		if (isset($this->$property) === FALSE) return FALSE;

		if (is_array($value) === FALSE)
		{
			$this->$property	= $value;
		}
		else
		{
			if ($merge_arrays === TRUE)
			{
				$this->$property	= array_unique(array_merge($this->$property, $value));
			}
			else
			{
				$this->$property	= $value;
			}
		}

		return TRUE;
	}

	// End set property

    // -------------------------------------------------------------

	/**
	 * Set properties
	 *
	 * Set a bunch of properties in batch.
	 *
	 * @access	public
	 * @return	boolean
	 */

	public function set_properties($properties = array())
	{
		foreach ($properties as $property => $val)
		{
			$this->set_property($property, $val);
		}

		return TRUE;
	}

	//	end set_properties()

    // -------------------------------------------------------------

	/**
	 * Strip variables
	 *
	 * This quick method strips variables like this {super_search_some_value} and like this {/super_search_some_value} from a string.
	 *
	 * @access	public
	 * @return	string
	 */

	public function strip_variables($tagdata = '')
	{
		if ($tagdata == '') return '';

		$tagdata	= preg_replace(
			"/" . LD . "(" . preg_quote(T_SLASH, '/') . ")?" . $this->parsing_prefix . "(.*?)" . RD . "/s",
			"",
			$tagdata
		);

		return $tagdata;
	}

	//	End strip variables

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
	 * Get uri
	 *
	 * EE applies some filtering to ee()->uri->uri_string that prevents us from using quotes and = signs in the uri. It strips those as a security measure just in case someone uses a segment in an EE tag param. We need and want those for our queries and will not be making our $uri available to other parts of EE. This method goes through most of the EE security routines and skips the part where EE strips out what we want.
	 *
	 * @access	public
	 * @return	string
	 */

	public function get_uri($method = 'fetch')
	{
		$str	= FALSE;

		// -------------------------------------
		//	Is search& present in the uri?
		// -------------------------------------

		if ($method == 'fetch' AND strpos(ee()->uri->uri_string, '/search&') === FALSE) return $str;

		ee()->load->helper('string');

		// -------------------------------------
		//	EE 2 has one too many security provisions that filter the URI. EE 2, through CI, strips out our extra ampersands. We need those. The strippage happens not in /system/core/URI.php but in /system/core/Router.php where $this->uri->_explode_segments() is called. We are allowed by the code to call _fetch_uri_string() again and grab the uri_string variable before it is filtered further. We apply our own security to it.
		// -------------------------------------

		// -------------------------------------
		// To get our uri's to be compatibile with Google Analytics they require a '?' in the search uri.  If we use the standard _fetch_uri_string() method to fetch our uri it gets filtered out well before we ever see it. So in the case where we require GA recording we'll use _parse_request_uri() instead, and get the full unabridged uri back to play with. Joel 2011 05 11
		// -------------------------------------

		//	utf8_decode(rawurldecode($thingy))

		if ($method == 'parse')
		{
			$str	= rtrim(ee('Security/XSS')->clean($this->_sanitize(trim_slashes($_SERVER['REQUEST_URI']))), '/') . '/';
		}
		else
		{
			ee()->uri->_fetch_uri_string();

			$str	= rtrim(ee('Security/XSS')->clean($this->_sanitize(trim_slashes(ee()->uri->uri_string()))), '/') . '/';
		}

		// -------------------------------------
		//	Return
		// -------------------------------------

		return str_replace(';', '', $str);
	}

	// End get uri

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

	function highlight_keywords($str = '', $keywords = array(), $highlight_keywords = 'em')
	{
		// -------------------------------------
        //  Bug out
        // -------------------------------------

		if ($str == '' OR $highlight_keywords == '' OR $highlight_keywords == 'no' OR
			(empty($keywords['or']) === TRUE AND
			  empty($keywords['and']) === TRUE)) return $str;

		$str	= strip_tags($str);

		// -------------------------------------
        //  Press on
        // -------------------------------------

		$tag	= 'em';

		if ($highlight_keywords != '')
		{
			switch ($highlight_keywords)
			{
				case 'b':
					$tag	= 'b';
					break;
				case 'i':
					$tag	= 'i';
					break;
				case 'span':
					$tag	= 'span';
					break;
				case 'strong':
					$tag	= 'strong';
					break;
				case 'mark':
					$tag	= 'mark';
					break;
				default:
					$tag	= 'em';
					break;
			}
		}

		// -------------------------------------
        //  Prepare Keywords for Highlighting
        // -------------------------------------

		$main = array();

		if (! empty($keywords['or']))
		{
			$main = $keywords['or'];

			if (isset($keywords['or_fuzzy']) AND is_array($keywords['or_fuzzy']))
			{
				foreach($keywords['or_fuzzy'] as $fuzzy_set)
				{
					$main = array_merge($main, $fuzzy_set);
				}
			}
		}
		elseif (! empty($keywords['and']))
		{
			foreach ($keywords['and'] as $and)
			{
				if (! isset($and['main'])) continue;
				$main	= array_merge($main, $and['main']);
			}

			if (isset($keywords['and_fuzzy']) AND is_array($keywords['and_fuzzy']))
			{
				foreach($keywords['and_fuzzy'] as $fuzzy_set)
				{
					$main = array_merge($main, $fuzzy_set);
				}
			}
		}

		$phrases	= array();
		$words		= array();

		foreach ($main as $key => $word)
		{
			if (is_string($word))
			{
				if (stripos($str, ''.$word) === FALSE) continue;

				if (strpos($word, ' ') !== FALSE)
				{
					$phrases[]	= $word;
				}
				else
				{
					$words[] = $word;
				}
			}
		}

		// Phrases happen *before* words.
		$replace = array_merge($phrases, $words);

		// -------------------------------------
        //  No Words or Phrases for Highlighting? Return
        // -------------------------------------

		if (empty($replace)) return $str;

		// -------------------------------------
        //  Cut Out Valid HTML Elements
        // -------------------------------------

        $this->marker = md5(time());
        //$this->marker = 'sloppyboppy';

		$html_tag = <<<EVIL
								#
									</?\w+
											(
												"[^"]*" |
												'[^']*' |
												[^"'>]+
											)*
									>
								#sx
EVIL;

		$str = $this->cut($str, $html_tag);

		// -------------------------------------
        //  Do the Replace Magic
        // -------------------------------------

		// we require a trailing space (it gets collapsed on display anyway) for the prep_replace to properly markup keywords at the very end of strings
		$str .= " ";

		// highlight words within words? Be default we do. It can be turned off though.
		$highlight_words_within_words	= ($this->highlight_words_within_words === FALSE) ? '\b': '';

        foreach($replace as $item)
        {
        	$str = preg_replace("/([^.\/?&]" . $highlight_words_within_words . "|^)(".preg_quote($item).")(" . $highlight_words_within_words . "(?![^><]*?(?:>|<\/a)))/imsuU" , "$1<".preg_quote($tag).">$2</".preg_quote($tag).">$3", $str);
        	// $str = preg_replace("/(\b" . preg_quote($item) . "\b(?![^><]*?(?:>|<\/a)))/i" , "$1<".preg_quote($tag).">$2</".preg_quote($tag).">$3", $str);

			$str = preg_replace("|(<[A-Za-z]* [^>]*)<".preg_quote($tag).">(".preg_quote($item).")</".preg_quote($tag).">([^<]*>)|imsuU" , "$1$2$3" , $str);
        }

		return $this->paste($str);
	}

	//	End highlight keywords

    // -------------------------------------------------------------

	/**
	 * Parse date
	 *
	 * Parses an EE date string.
	 *
	 * @access	public
	 * @return	str
	 */

	public function parse_date($format = '', $date = 0)
	{
		if ($format == '' OR $date == 0) return '';

        // -------------------------------------
		//	strftime is much faster, but we have to convert date codes from what EE users expect to use
		// -------------------------------------

		// return strftime($format, $date);

        // -------------------------------------
		//	EE's built in date parser is slow, but for now we'll use it
		// -------------------------------------

		$codes	= ee()->localize->fetch_date_params($format);

		if (empty($codes)) return '';

		foreach ($codes as $code)
		{
			$format	= str_replace($code, ee()->localize->convert_timestamp($code, $date, TRUE, TRUE), $format);
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
	 * @access	public
	 * @return	str
	 */

	public function parse_date_to_timestamp($date = '', $full_day = FALSE)
	{
		// -------------------------------------
		//	Validate
		// -------------------------------------

		if ($date == '') return FALSE;

		// -------------------------------------
		//	Keywords instead of a numeric date?
		// -------------------------------------

		if (is_numeric($date) === FALSE)
		{
			switch ($date)
			{
				case 'yesterday':
					$date		= date('Ymd', strtotime('yesterday', ee()->localize->now));
					break;
				case 'nextweek':
					$date	= date('Ymd', strtotime('+1 week', ee()->localize->now));
					break;
				case 'tomorrow':
					$date	= date('Ymd', strtotime('tomorrow', ee()->localize->now));
					break;
				case 'today':
				default:
					$date	= date('Ymd', strtotime('today', ee()->localize->now));
					break;
			}
		}

		// -------------------------------------
		//	Prep defaults
		// -------------------------------------

		$return	= '';

		$hour = 0; $minute = 0; $second = 0; $month = 1; $day = 1;

		if ($full_day === TRUE)
		{
			$hour = 23; $minute = 59; $second = 59; $month = 12; $day = 31;
		}

		// -------------------------------------
		//	Split the date into pieces
		// -------------------------------------

		$thedate	= $this->_split_date($date);

		// -------------------------------------
		//	mktime(hour, minute, second, month, day, year)
		// -------------------------------------

		ee()->load->helper('date');

		switch (count($thedate))
		{
			case 1:	// One digit means nothing to us. We fail this gracefully.
				$return	= 0;
				break;
			case 2:	// We have year only
				$day	= ($full_day === FALSE) ? $day: days_in_month($month, $thedate[0].$thedate[1]);
				$return	= mktime($hour, $minute, $second, $month, $day, $thedate[0].$thedate[1]);
				break;
			case 3:	// We have year and month
				$day	= ($full_day === FALSE) ? $day: days_in_month($thedate[2], $thedate[0].$thedate[1]);
				$return	= mktime($hour, $minute, $second, $thedate[2], $day, $thedate[0].$thedate[1]);
				break;
			case 4:	// We have year, month, day
				$return	= mktime($hour, $minute, $second, $thedate[2], $thedate[3], $thedate[0].$thedate[1]);
				break;
			case 5:	// We have year, month, day and hour
				$return	= mktime($thedate[4], $minute, $second, $thedate[2], $thedate[3], $thedate[0].$thedate[1]);
				break;
			case 6:	// We have year, month, day, hour and minute
				$return	= mktime($thedate[4], $thedate[5], $second, $thedate[2], $thedate[3], $thedate[0].$thedate[1]);
				break;
			case 7:	// We have year, month, day, hour, minute and second
				$return	= mktime($thedate[4], $thedate[5], $thedate[6], $thedate[2], $thedate[3], $thedate[0].$thedate[1]);
				break;
		}

		// -------------------------------------
		//	Return
		// -------------------------------------

		return $return;
	}

	//	End parse date to timestamp

    // -------------------------------------------------------------

	/**
	 * Parse POST
	 *
	 * Receive a POST array and clean and concatenate it into a $$ query string.
	 *
	 * @access	public
	 * @return	string
	 */

	public function parse_post($post = array())
	{
        // -------------------------------------
		//	Parse POST into search array
		// -------------------------------------

		$str					= '';
		$parsed					= array();

		foreach ($post as $key => $val)
		{
			if ($val == '' OR in_array($key, $parsed) === TRUE) continue;

			// -------------------------------------
			//	Correct for some EE and user funky bits
			// -------------------------------------

			if (is_array($val) === TRUE)
			{
				foreach ($val as $k => $v)
				{
					if (is_string($v) === TRUE)
					{
						$val[$k]	= str_replace(array('/', ';', '&'), array($this->slash, '', '%26'), $v);
					}
				}
			}

			if (is_string($val) === TRUE)
			{
				$val	= str_replace(array('/', ';', '&'), array($this->slash, '', '%26'), $val);
			}

			// -------------------------------------
			//	Exact field arrays get special treatment
			// -------------------------------------

			if (is_array($val) === TRUE AND strpos($key, 'exact') === 0)
			{
				$temp	= '';

				foreach ($val as $k => $v)
				{
					if (strpos($v, '&&') !== FALSE)
					{
						$parsed[]	= $key.'_'.$k;
						$temp	.= $v;
					}
					else
					{
						$parsed[]	= $key.'_'.$k;
						$str	.= $key.'_'.$k.$this->separator.$v.$this->parser;
					}
				}

				if (! empty($temp))
				{
					$str	.= $key . $this->separator . rtrim($temp, '&') . $this->parser;
				}
			}

			// -------------------------------------
			//	Order field as an array gets special handling
			// -------------------------------------

			elseif ($key == 'order' AND is_array($val) === TRUE)
			{
				$str	.= $key.$this->spaces;

				foreach ($val as $v)
				{
					if ($v == '') continue;
					$str	.= $v.$this->spaces;
				}

				$str	.= $this->parser;
			}

			// -------------------------------------
			//	Handle post arrays
			// -------------------------------------

			elseif (is_array($val))
			{
				$str	.= $key.$this->separator;
				$temp	= '';

				foreach ($val as $k => $v)
				{
					$v	= str_replace('%26%26', '&&', $v);

					if (strpos($v, '&&') !== FALSE)
					{
						$parsed[]	= $key.'_'.$k;

						// -------------------------------------
						//	Spaces in an array POST value indicate that someone wants to do an exact phrase search, so we should put those in quotes for later parsing.
						// -------------------------------------

						if (strpos($v, ' ') !== FALSE)
						{
							$v	= '"' . str_replace(' ', $this->spaces, rtrim($v, '&')) . '"&&';
						}

						$temp	.= $v;
					}
					else
					{
						$v		= stripslashes($v);
						$parsed[]	= $key.'_'.$k;

						// -------------------------------------
						//	Spaces in an array POST value indicate that someone wants to do an exact phrase search, so we should put those in quotes for later parsing.
						// -------------------------------------

						if (strpos($v, ' ') !== FALSE)
						{
							$v	= '"' . str_replace(' ', $this->spaces, $v) . '"';
						}

						$str	.= $v.$this->spaces;
					}
				}

				if (! empty($temp))
				{
					$str	.= rtrim($temp, '&');
				}

				$str	= rtrim($str, $this->spaces);

				$str	.= $this->parser;
			}
			else
			{
				$str	.= $key.$this->separator.$val.$this->parser;
			}
		}

		$str	= rtrim(stripslashes($str), $this->parser);

		$str 	= str_replace(' ', $this->spaces, $str);

		return (empty($str)) ? FALSE: $str;
	}

	//	End parse post

    // -------------------------------------------------------------

	/**
	 * Parse template vars
	 *
	 * @access	private
	 * @return	string
	 */

	public function parse_template_vars($tagdata = '', $search = array(), $just_return_parsables = '')
	{
		$p		= $this->parsing_prefix;
		$parse	= array();

		// -------------------------------------
		//	Prepare boolean variables
		// -------------------------------------
		//	Some search parameters can have multiple values, like status. People can search for multiple status params at once. We would like to be able to evaluate for the presence of each of those statuses as boolean variables. So this: search&status=open+closed+"First+Looks+-empty" would allow {super_search_status_open}, {super_search_status_closed}, {super_search_status_First_Looks} and {super_search_status_not_empty} to evaluate as true. We replace spaces with underscores and quotes with nothing.
		// -------------------------------------

		foreach (array('channel', 'keywords', 'status', 'category') as $key)
		{
			if (empty($search[$key])) continue;

			$temp	= $this->prep_keywords($search[$key]);

			if (! empty($temp['and']))
			{
				foreach ($temp['and'] as $k => $ands)
				{
					if (! empty($ands['main']))
					{
						// -------------------------------------
						//	Convert markers
						// -------------------------------------

						$ands['main']	= $this->convert_markers($ands['main']);

						// -------------------------------------
						//	Loop for each conjoin and assemble into the $parse array
						// -------------------------------------

						foreach ($ands['main'] as $val)
						{
							if (empty($val)) continue;

							$val	= str_replace(' ', '_', $val);

							$parse[$p . $key . '_and_' . $val]	= TRUE;
						}
					}

					if (! empty($ands['not']))
					{
						// -------------------------------------
						//	Convert markers
						// -------------------------------------

						$ands['not']	= str_replace($this->negatemarker, '', $ands['not']);

						// -------------------------------------
						//	Loop for each conjoin and assemble into the $parse array
						// -------------------------------------

						foreach ($ands['not'] as $val)
						{
							if (empty($val)) continue;

							$val	= str_replace(' ', '_', $val);

							$parse[$p . $key . '_not_' . $val]	= TRUE;
						}
					}
				}
			}

			if (! empty($temp['or']))
			{
				foreach ($temp['or'] as $val)
				{
					if (empty($val)) continue;

					$val	= str_replace(' ', '_', $val);

					$parse[$p . $key . '_' . $val]	= TRUE;
				}
			}

			if (! empty($temp['not']))
			{
				foreach ($temp['not'] as $val)
				{
					if (empty($val)) continue;

					$val	= str_replace(' ', '_', $val);

					$parse[$p . $key . '_not_' . $val]	= TRUE;
				}
			}
		}

		// -------------------------------------
		//	Dates
		// -------------------------------------

		foreach ($this->date_fields as $key)
		{
			if (strpos($tagdata, $p.$key) === FALSE
				AND strpos($tagdata, $p.$key.$this->modifier_separator.'from') === FALSE
				AND strpos($tagdata, $p.$key.$this->modifier_separator.'to') === FALSE) continue;

			$parse[$p . $key]											= '';
			$parse[$p . $key . $this->modifier_separator . 'from']	= '';
			$parse[$p . $key . $this->modifier_separator . 'to']		= '';

			if (! empty($search[$key.'from']))
			{
				$parse[$p.$key.$this->modifier_separator.'from']	= $search[$key.'from'];
			}

			if (! empty($search[$key.'to']))
			{
				$parse[$p.$key.$this->modifier_separator.'to']	= $search[$key.'to'];
			}

			if (! empty($search[$key]))
			{
				$parse[$p.$key]	= $search[$key];
			}
		}

		// -------------------------------------
		//	Basic
		// -------------------------------------

		foreach ($this->basic as $key)
		{
			if (strpos($tagdata, $p . $key) === FALSE) continue;

			$parse[$p . $key]	= '';

			if (isset($search[$key]))
			{
				// -------------------------------------
				//	Push keywords url
				// -------------------------------------

				if ($key == 'keywords')
				{
					$parse[$p . 'keywords_url']	= $search[$key];
				}

				// -------------------------------------
				//	Load
				// -------------------------------------

				$parse[$p . $key]	= $search[$key];

				// -------------------------------------
				//	Explode separated vals into parsable chunks like this {if super_search_vintage_2010}checked="checked"{/if}
				// -------------------------------------

				if (strpos($search[$key], ' ') !== FALSE)
				{
					foreach (explode(' ', $search[$key]) as $v)
					{
						$parse[$p . $key . '_' . $v]	= TRUE;
					}
				}
			}
		}

		// -------------------------------------
		//	Fields
		// -------------------------------------

		foreach ($this->fields as $key)
		{
			if (strpos($tagdata, $p . $key) === FALSE) continue;

			$parse[$p . $key]	= '';

			if (isset($search['field'][$key]))
			{
				// -------------------------------------
				//	Load
				// -------------------------------------

				$parse[$p . $key]	= $search['field'][$key];

				// -------------------------------------
				//	Explode separated vals into parsable chunks like this {if super_search_vintage_2010}checked="checked"{/if}
				// -------------------------------------

				if (strpos($search['field'][$key], $this->spaces) !== FALSE)
				{
					foreach (explode($this->spaces, $search['field'][$key]) as $v)
					{
						$parse[$p . $key . '_' . $v]	= TRUE;
					}
				}
				else
				{
					$parse[$p . $key . '_' . $search['field'][$key]]	= TRUE;
				}
			}
		}

		// -------------------------------------
		//	Prepare custom fields
		// -------------------------------------

		foreach ($this->fields as $key)
		{
			if (strpos($tagdata, $p.$key) === FALSE
				AND strpos($tagdata, $p.'exact'.$this->modifier_separator.$key) === FALSE
				AND strpos($tagdata, $p.$key.$this->modifier_separator.'exact') === FALSE
				AND strpos($tagdata, $p.$key.$this->modifier_separator.'empty') === FALSE
				AND strpos($tagdata, $p.$key.$this->modifier_separator.'from') === FALSE
				AND strpos($tagdata, $p.$key.$this->modifier_separator.'to') === FALSE) continue;

			if (isset($search[$key]) === TRUE)
			{
				$parse[$p.$key]	= (strpos($search[$key], $this->doubleampmarker) === FALSE) ? $search[$key]: str_replace($this->doubleampmarker, '&&', $search[$key]);
			}
			elseif (isset($search['field'][$key]) === TRUE)
			{
				$parse[$p.$key]	= (strpos($search['field'][$key], $this->doubleampmarker) === FALSE) ? $search['field'][$key]: str_replace($this->doubleampmarker, '&&', $search['field'][$key]);
			}
			else
			{
				$parse[$p.$key]	= '';
			}

			if (isset($search['exactfield'][$key]) === TRUE)
			{
				$parse[$p.'exact'.$this->modifier_separator.$key]	=
				$parse[$p.$key.$this->modifier_separator.'exact'] =
					(strpos($search['exactfield'][$key], $this->doubleampmarker) === FALSE)
						? $search['exactfield'][$key]
						: str_replace($this->doubleampmarker, '&&', $search['exactfield'][$key]);
			}
			else
			{
				$parse[$p.'exact'.$this->modifier_separator.$key]	= '';
				$parse[$p.$key.$this->modifier_separator.'exact']	= '';
			}

			if (isset($search['empty'][$key]) === TRUE)
			{
				$parse[$p.$key.$this->modifier_separator.'empty']	= $search['empty'][$key];
			}
			else
			{
				$parse[$p.$key.$this->modifier_separator.'empty']	= '';
			}

			if (isset($search['from'][$key]) === TRUE)
			{
				$parse[$p.$key.$this->modifier_separator.'from']	= $search['from'][$key];
			}
			else
			{
				$parse[$p.$key.$this->modifier_separator.'from']	= '';
			}

			if (isset($search['to'][$key]) === TRUE)
			{
				$parse[$p.$key.$this->modifier_separator.'to']	= $search['to'][$key];
			}
			else
			{
				$parse[$p.$key.$this->modifier_separator.'to']	= '';
			}
		}

		// -------------------------------------
		//	One more loop to convert markers
		// -------------------------------------

		$parse	= $this->convert_markers($parse);

		// -------------------------------------
		//	Just return?
		// -------------------------------------

		if ($just_return_parsables != '')
		{
			return $parse;
		}

		// -------------------------------------
		//	Hack 'n' chomp
		// -------------------------------------

		$tagdata	= ee()->functions->prep_conditionals($tagdata, $parse);

		foreach ($parse as $key => $val)
		{
			if (strpos($key, 'date') !== FALSE)
			{
				if (strpos($tagdata, 'format=') !== FALSE)
				{
					if (preg_match_all("/" . LD . $key . "\s+format=[\"'](.*?)[\"']" . RD . "/s", $tagdata, $format))
					{
						$full_day	= (substr($key, -3) == '-to') ? TRUE: FALSE;

						foreach ($format[0] as $k => $v)
						{
							if (isset($format[1][$k]) === TRUE)
							{
								$tagdata	= str_replace($format[0][$k], $this->parse_date($format[1][$k], $this->parse_date_to_timestamp($val, '', $full_day)), $tagdata);
							}
						}
					}
				}
			}

			$val	= (strpos($val, '"') === FALSE AND strpos($val, "'") === FALSE) ? $val: str_replace(array('"', "'"), array('&quot;', '&#039;'), stripslashes($val));

			$tagdata	= str_replace(LD.$key.RD, str_replace($this->spaces, ' ', $val), $tagdata);
		}

		return $tagdata;
	}

	//	End parse template vars

    // -------------------------------------------------------------

	/**
	 * Parse URI
	 *
	 * Protect some things. Break the string apart into an array. Return that as a nice tidy array representing a set of search filters.
	 *
	 * @access	public
	 * @return	array
	 */

	public function parse_uri($str = '')
	{
		// -------------------------------------
		//	Remove any pagination data that's coming after the face. We dont want this later
		// -------------------------------------

		$str	= preg_replace("#(^|\/)P(\d+)#", "", $str);

		// -------------------------------------
		//	Cleanie uppie
		// -------------------------------------

		$search		= array(' ', '&#36', $this->slash, SLASH, ';');
		$replace	= array($this->spaces, '$', '/', '/', '');
		$str		= $this->sess['olduri'] = str_replace($search, $replace, rtrim($str, '/'));

		// -------------------------------------
		//	Convert protected strings
		// -------------------------------------
		//	Double ampersands are allowed and indicate inclusive searching
		// -------------------------------------

		if (strpos($str, '&&') !== FALSE)
		{
			// There is an edge condition where if a passed uri without a clean end
			// has double ampersands the parse incorrectly builds the inclusive set
			// 		ie. category=1&&2&&keywords=test
			// in this case the trailing && is replacing the native html '&' parser break
			// and fouling up, searching for categories 1, 2 and 'keywords=test' inclusively.
			// This breaks things.

			// to fix this we'll look for any &&'s and if there's a trailing '&' and the
			// following block contains a '=' we'll dynamically fix it, by removing one
			// of the trailing &'s

			$double_pattern = '/(&&)([a-zA-Z0-9_\-\.\+]+)=/i';

			if (preg_match_all($double_pattern, $str, $submatches))
			{
				// We have a match against our pattern, replace the stagglers with a single &

				foreach($submatches[0] as $submatch)
				{
					$cleaned = str_replace('&&', '&', $submatch);

					$str = str_replace($submatch, $cleaned, $str);
				}
			}

			$str	= str_replace('&&', $this->doubleampmarker, $str);
		}

		// -------------------------------------
		//	Protect dashes for negation so that we don't have conflicts with dash in url titles. Note that we only care about dashes preceded by a separator or spacer character, any other dash could be part of a valid word.
		// -------------------------------------

		foreach (array($this->separator, $this->spaces, $this->doubleampmarker, $this->ampmarker) as $dash)
		{
			if (strpos($str, $dash.'-') !== FALSE)
			{
				$str	= str_replace($dash.'-', $dash.$this->negatemarker, $str);
			}
		}

		// -------------------------------------
		//	Start URI array
		// -------------------------------------

		$newuri	= array('search');
		$q		= array();

		// -------------------------------------
		//	Explode the query into an array and prep it
		// -------------------------------------

		$temp_fields	= $this->fields;

		foreach (explode($this->parser, $str) as $val)
		{
			// -------------------------------------
			//	Loop through $this->fields preemptively and create quasi-fields when grid searching is detected.
			// -------------------------------------

			$val = htmlspecialchars($val);
			foreach ($temp_fields as $key)
			{
				if (strpos($val, $key.$this->grid_field_separator) === 0)
				{
					$tempy	= explode($this->separator, $val);
					$temp	= str_replace(array(
						'exact' . $this->modifier_separator,
						$this->modifier_separator . 'empty',
						$this->modifier_separator . 'exact',
						$this->modifier_separator . 'from',
						$this->modifier_separator . 'to',
						$key.$this->grid_field_separator,
						$this->separator
					), '', $tempy[0]);
					$this->fields[]	= $key.$this->grid_field_separator.$temp;
					$this->grid_fields[$key][]	= $temp;
				}
			}

			// -------------------------------------
			//	Parse custom fields
			// -------------------------------------
			//	We loop through our searchable custom fields, see if they are in the URI, log them and move on.
			// -------------------------------------

			foreach ($this->fields as $key)
			{
				if (strpos($val, $key.$this->separator) === 0)
				{
					$newuri[]	= $val;
					$q['field'][$key]	= str_replace($key.$this->separator, '', $val);
				}

				// -------------------------------------
				//	We're looking for custom fields with the prefix of 'exact'. They indicate that we want an exact match on the value of that field.
				// -------------------------------------

				if (strpos($val, 'exact'.$this->modifier_separator.$key.$this->separator) === 0)
				{
					$newuri[]	= $val;
					$q['exactfield'][$key]	= str_replace('exact'.$this->modifier_separator.$key.$this->separator, '', $val);
				}

				// -------------------------------------
				// Also allow the 'exact' prefix to come after the marker
				// -------------------------------------

				if (strpos($val, $key.$this->modifier_separator.'exact'.$this->separator) === 0)
				{
					$newuri[]	= $val;
					$q['exactfield'][$key]	= str_replace($key.$this->modifier_separator.'exact'.$this->separator, '', $val);
				}

				// -------------------------------------
				//	We're looking for custom fields with the prefix of 'exact' that are sent through the query string as an array. They indicate that we want an exact match on the value of that field where several values are acceptable exact matches.
				// -------------------------------------

				if (strpos($val, 'exact'.$this->modifier_separator.$key) === 0 AND preg_match('/exact'.$this->modifier_separator.$key.'\_\d+/s', $val, $match))
				{
					$newuri[]	= $val;
					$temp = explode($this->separator, $val);
					if (isset($temp[1]) === FALSE) continue;
					$q['exactfield'][$key][]	= $temp[1];
				}

				// -------------------------------------
				// Also allow the 'exact' prefix to come after the marker
				// -------------------------------------

				if (strpos($val, $key.$this->modifier_separator.'exact') === 0 AND preg_match('/'.$key.$this->modifier_separator.'exact\_\d+/s', $val, $match))
				{
					$newuri[]	= $val;
					$temp = explode($this->separator, $val);
					if (isset($temp[1]) === FALSE) continue;
					$q['exactfield'][$key][]	= $temp[1];
				}

				if (strpos($val, $key.$this->modifier_separator.'empty'.$this->separator) === 0)
				{
					$newuri[]	= $val;
					$q['empty'][$key]	= str_replace($key.$this->modifier_separator.'empty'.$this->separator, '', $val);
				}

				if (strpos($val, $key.$this->modifier_separator.'from'.$this->separator) === 0)
				{
					$newuri[]	= $val;
					$q['from'][$key]	= str_replace($key.$this->modifier_separator.'from'.$this->separator, '', $val);
				}

				if (strpos($val, $key.$this->modifier_separator.'to'.$this->separator) === 0)
				{
					$newuri[]	= $val;
					$q['to'][$key]	= str_replace($key.$this->modifier_separator.'to'.$this->separator, '', $val);
				}
			}

			if (isset($q['exactfield']) === TRUE) ksort($q['exactfield']);
			if (isset($q['field']) === TRUE) ksort($q['field']);

			// -------------------------------------
			//	Parse date ranges
			// -------------------------------------
			//	datefrom and dateto can be provided in order to find ranges of entries by date. 20090601 = June 1, 2009. 2009 = 2009. 200906 = June 2009. 2009060105 = 5 am June 1, 2009. 20090601053020 = 5:30 am and 20 seconds June 1, 2009. 2009060123 = 11 pm June 1, 2009.
			//	We parse the expiry and standard date together to stop substring matches for 'date-from' getting 'expiry_date-from'
			// -------------------------------------

			foreach ($this->date_fields as $date)
			{
				$expl	= explode($this->separator, $val);

				if (count($expl) < 2) continue;

				if ($expl[0] == $date.$this->modifier_separator.'from')
				{
					$newuri[]			= $val;
					$q[$date . 'from']	= $expl[1];
				}

				if ($expl[0] == $date.$this->modifier_separator.'to')
				{
					$newuri[]			= $val;
					$q[$date . 'to']	= $expl[1];
				}

				if ($expl[0] == $date)
				{
					$newuri[]	= $val;
					$q[$date]	= $expl[1];
				}
			}

			// -------------------------------------
			//	Parse birthdays
			// -------------------------------------
			//	We allow tests on birthday like today, tomorrow, yesterday, nextweek.
			// -------------------------------------

			foreach (array_merge($this->birthdays, $this->ages) as $key)
			{
				if (strpos($val, $key.$this->separator) === 0)
				{
					$newuri[]	= $val;

					$q[$key]	= str_replace(array($key.$this->separator), '', $val);
				}
			}

			// -------------------------------------
			//	Parse Inclusive Keywords
			// -------------------------------------
			//	We're allowing the user to pass through a marker to turn the individual keywords into an inclusive search rather than the standard 'or' search
			// -------------------------------------

			if (strpos($val, 'inclusive_keywords') !== FALSE)
			{
				$q['inclusive_keywords']	= str_replace('inclusive_keywords'.$this->separator, '', $val);
			}

			// -------------------------------------
			//	We allow users to enable keywords searching on author names, if they're passing this param set the marker
			// -------------------------------------

			if (strpos($val, 'keyword_search_author_name') !== FALSE)
			{
				$q['keyword_search_author_name']	= str_replace('keyword_search_author_name'.$this->separator, '', $val);
			}

			// -------------------------------------
			//	We allow users to enable keywords searching on category names, if they're passing this param set the marker
			// -------------------------------------

			if (strpos($val, 'keyword_search_category_name') !== FALSE)
			{
				$q['keyword_search_category_name']	= str_replace('keyword_search_category_name'.$this->separator, '', $val);
			}

			// -------------------------------------
			//	Parse basic parameters
			// -------------------------------------
			//	We are looking for a parameter that we expect to occur only once. Its argument can contain multiple terms following the Google syntax for 'and' 'or' and 'not'.
			// -------------------------------------

			foreach ($this->basic as $key)
			{
				if (strpos($val, $key.$this->separator) === 0)
				{
					$newuri[]	= $val;

					$q[$key]	= str_replace(array($key.$this->separator), '', $val);
				}
			}
		}

		ksort($q);
		$this->sess['uri']	= $q;

		// -------------------------------------
		//	Save new uri
		// -------------------------------------
		//	We will very likely be paginating later. We will need a coherent search string for each pagination link. And at the very end of the string we place the 'start' parameter. Our pagination routine then appends the start number to that string.
		// -------------------------------------

		if (! empty($newuri))
		{
			$newuri	= str_replace(array($this->doubleampmarker, $this->negatemarker, '"', '\''), array('&&', '-', '%22', '%22'), implode($this->parser, $newuri));

			if (preg_match('/offset' . $this->separator . '(\d+)?/s', $newuri) == 0)
			{
				$newuri	.= $this->parser . 'offset' . $this->separator . '0';
			}

			$this->sess['newuri']	= $newuri;
		}

		// -------------------------------------
		//	Return
		// -------------------------------------

		return $q;
	}

	//	End parse uri

    // -------------------------------------------------------------

    /**
	 * Prep ignore words
	 *
	 * @access	private
	 * @return	string
	 */

	private function _prep_ignore_words($keywords = '', $use_ignore_word_list_passed = '')
	{
		// -------------------------------------
		//	Basic validity test
		// -------------------------------------

		if ($keywords == '') return '';

		$ignore_word_list = ee()->config->item('ignore_word_list');

		// nothing to do anyway, bail
		if ($ignore_word_list == '') return $keywords;

		// Is filtering enabled or overridden?
		$use_ignore_word_list = $this->check_yes(ee()->config->item('use_ignore_word_list'));

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
		$words = explode('||', ee()->config->item('ignore_word_list'));

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
		$keywords = preg_replace('!\s+!', ' ', trim($keywords));

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

    // -------------------------------------------------------------

	/**
	 *	Paste Removed Data Back into a String
	 *
	 *	@access		public
	 *	@param		string
	 *	@return		string
	 */

	function paste($subject)
    {
        foreach($this->_buffer as $key => $val)
        {
        	$subject = str_replace(' '.$this->marker.$key.$this->marker.' ', $val, $subject);
        }

        return $subject;
    }

    // END paste()

	// -------------------------------------------------------------

	/**
	 * Prep conjoined keywords
	 *
	 * We have a special case when a keyword string contains conjoining. It means that something must have all of a certain set of keywords. We load such a string into our keyword array in a special manner.
	 *
	 * And, if a keyword string has two blocks of conjoined keywords, then we cry. Actually, we just ignore the second since that's just crazy making.
	 *
	 * @access	private
	 * @return	array
	 */

	private function _prep_conjoined_keywords($keywords = '', &$arr = array())
	{
        // -------------------------------------
		//	Just in case we're a big dum dummy.
		// -------------------------------------

		if (strpos($keywords, $this->doubleampmarker) === FALSE)
		{
			return $keywords;
		}

        // -------------------------------------
		//	Blow up the string into conjoin components
		// -------------------------------------

		$zulu = $keywords	= explode($this->doubleampmarker, $keywords);
		$outbound	= array();
		$temparr	= array();

        // -------------------------------------
		//	If quotes have been used inside a conjoin block, let's protect the space characters from the upcoming explosions.
		// -------------------------------------

		foreach ($keywords as $index => $phrase)
		{
			if (preg_match_all('/"(.*?)"/s', $phrase, $matches))
			{
				foreach ($matches[0] as $key => $match)
				{
					$phrase	= str_replace($match, str_replace($this->spaces, $this->spaceinquotemarker, $matches[1][$key]), $phrase);
				}
			}

			$keywords[$index]	= $phrase;
		}

        // -------------------------------------
		//	The first chunk in the exploded array is potentially special if it contains spaces. If it contains spaces, the last term is the one that belongs to the conjoin. The others should be shoved back into the keyword string.
		// -------------------------------------

		$i	= 0;

		if (! empty($keywords[0]) AND strpos($keywords[0], $this->spaces) !== FALSE)
		{
			$temp					= explode($this->spaces, $keywords[0]);
			$phrase					= array_pop($temp);
			$not					= (strpos($phrase, $this->negatemarker) === 0) ? 'not': 'main';
			$arr['and'][$i][$not][]	= $phrase;
			$outbound[]				= implode($this->spaces, $temp);
			unset($keywords[0]);
		}

        // -------------------------------------
		//	Now we loop. As we loop we check for spaces. If we find spaces, then we are about to bounce into a new conjoin block. But in there, we also check to see if there is an orphan word that needs to be shoved into our main outbound array.
		// -------------------------------------

		$keywords	= array_values($keywords);	// Reset the indices of the array.

		foreach ($keywords as $index => $string)
		{
			// -------------------------------------
			//	Spaces in the string? Yes? Last array element? No?
			// -------------------------------------

			if (strpos($string, $this->spaces) !== FALSE AND $index != (count($keywords) - 1))
			{
				$temp		= explode($this->spaces, $string);
				$phrase		= array_shift($temp);
				$not		= (strpos($phrase, $this->negatemarker) === 0) ? 'not': 'main';
				$arr['and'][$i][$not][]	= $phrase;
				$i++;	// We got the last viable phrase for the current AND, we can jump to the next from this point on.
				$phrase		= array_pop($temp);
				$not		= (strpos($phrase, $this->negatemarker) === 0) ? 'not': 'main';
				$arr['and'][$i][$not][]	= $phrase;
				$outbound[]	= implode($this->spaces, $temp);
			}

			// -------------------------------------
			//	Spaces in the string? Yes? Last array element? Yes?
			// -------------------------------------

			if (strpos($string, $this->spaces) !== FALSE AND $index == (count($keywords) - 1))
			{
				$temp		= explode($this->spaces, $string);
				$phrase		= array_shift($temp);
				$not		= (strpos($phrase, $this->negatemarker) === 0) ? 'not': 'main';
				$arr['and'][$i][$not][]	= $phrase;
				$outbound[]	= implode($this->spaces, $temp);
			}

			// -------------------------------------
			//	Spaces in the string? No.
			// -------------------------------------

			if (strpos($string, $this->spaces) === FALSE)
			{
				$not		= (strpos($string, $this->negatemarker) === 0) ? 'not': 'main';
				$arr['and'][$i][$not][]	= $string;
			}
		}

		// -------------------------------------
		//	Spaces in the string? No.
		// -------------------------------------

		return implode($this->spaces, $this->remove_empties($outbound));
	}

	//	End prep conjoined keywords

	// -------------------------------------------------------------

	/**
	 * Prep keywords
	 *
	 * REGEX is expensive stuff. We could rewrite this method to explode into individual characters, loop through the resulting array, flag our identifiers like negation, quotes and such, and assemble keywords again as we go. Might be much faster. But as it stands, the method, on most keyword strings, executes silly fast.
	 *
	 * @access	public
	 * @return	array
	 */

	public function prep_keywords($keywords = '', $inclusive = FALSE, $type = '', $search = array())
	{
		if (is_string($keywords) === FALSE OR $keywords == '') return FALSE;

		// -------------------------------------
		//	Ignore words?
		// -------------------------------------

		if (isset($search['use_ignore_word_list']))
		{
			$keywords	= $this->_prep_ignore_words($keywords, $search['use_ignore_word_list']);
		}
		else
		{
			$keywords	= $this->_prep_ignore_words($keywords);
		}

		// -------------------------------------
		//	Start
		// -------------------------------------

		$arr	= array('and' => array(), 'not' => array(), 'or' => array());

        // -------------------------------------
		//	Are we using standard EE status syntax?
		// -------------------------------------

		if (strpos($keywords, '|') !== FALSE OR strpos($keywords, 'not ') === 0)
		{
			// -------------------------------------
			//	Are we negating?
			// -------------------------------------

			if (strpos($keywords, 'not ') === 0)
			{
				$arr['not']	= explode('|', str_replace('not ' , '', $keywords));
			}
			else
			{
				$arr['or']	= explode('|', $keywords);
			}

			// -------------------------------------
			//	Save
			// -------------------------------------

			$arr['not']	= $this->escape_str($this->remove_empties($arr['not']));
			$arr['or']	= $this->escape_str($this->remove_empties($arr['or']));

			if (! empty($arr['not'])) sort($arr['not']);
			if (! empty($arr['or'])) sort($arr['or']);
			ksort($arr);

			return $arr;
		}

        // -------------------------------------
		//	Are we working with conjoined keywords? We will re-arrange the keyword string and return it for normal parsing. We pass $arr by reference so that it can be written to remotely.
		// -------------------------------------

		if (strpos($keywords, $this->doubleampmarker) !== FALSE)
		{
			$keywords	= $this->_prep_conjoined_keywords($keywords, $arr);
		}

       	// -------------------------------------
		//	Basic cleanup
		// -------------------------------------

       	$keywords = $this->_clean_keywords($keywords);

        // -------------------------------------
		//	Parse out negated but quoted strings
		// -------------------------------------

		if (preg_match_all('/' . $this->negatemarker . '["](.*?)["]/s', $keywords, $match))
		{
			foreach ($match[1] as $val)
			{
				$arr['not'][]	= $this->escape_str($val);
			}

			$keywords	= preg_replace('/' . $this->negatemarker . '["](.*?)["]/s', '', $keywords);
		}

        // -------------------------------------
		//	Parse out inclusive strings
        // -------------------------------------
        //	This is a special case, not too common
        //	People can do inclusive category
        //	searching which means they can require
        //	that an entry belong to all of a given
        //	set of categories.
		// -------------------------------------

		$and	= 'or';

		if (strpos($keywords, $this->doubleampmarker) !== FALSE)
		{
			$and		= 'and';
			$keywords	= explode($this->doubleampmarker, $keywords);
		}
		else
		{
			$keywords	= array($keywords);
		}

		// -------------------------------------
		//	Let's loop and parse our strings
		// -------------------------------------

		foreach ($keywords as $phrase)
		{
			// -------------------------------------
			//	Parse out quoted strings
			// -------------------------------------

			if (preg_match_all('/["](.*?)["]/s', $phrase, $match))
			{
				foreach ($match[1] as $val)
				{
					// -------------------------------------
					//	Filter and / or depending on inclusion
					// -------------------------------------
					//	This is deceptively simple and may just	plain not work. If we're in the context	of inclusion, quoted phrases go to the 'and' array, otherwise the 'or' array.
					// -------------------------------------

					$arr[$and][]	= $val;
				}

				$phrase	= preg_replace('/["](.*?)["]/s', '', $phrase);
			}

			// -------------------------------------
			//	Parse out negated strings
			// -------------------------------------

			if (preg_match_all("/".$this->negatemarker."([a-zA-Z0-9_\-]+)/s", $phrase, $match))
			{
				foreach ($match[1] as $val)
				{
					$arr['not'][]	= $val;
				}

				$phrase	= preg_replace("/".$this->negatemarker."([a-zA-Z0-9_\-]+)/s", '', $phrase);
			}

			// -------------------------------------
			//	Load remaining OR keywords
			// -------------------------------------
			//	If we're in the context of inclusion, the first word in the phrase is added to the 'and' array while the others are given to the 'or' array. This means when I can ask for 'apples&&oranges bananas' I will end up retrieving entries that have both 'apples' and 'oranges' or 'bananas'.
			// -------------------------------------

			$temp	= explode(' ', trim($phrase));

			if (empty($temp) === FALSE AND $and == 'and')	// That was fun to type :-)
			{
				$arr['and'][]	= array_shift($temp);
			}

			if (empty($temp) === FALSE)
			{
				$arr['or']	= array_merge($arr['or'], $temp);
			}
		}

		// ---------------------------------------
		//	Inclusive Keywords
		// ---------------------------------------
		//	If we've been passed an inclusive variable we'll turn all our hard won $arr['or'] into $arr['and']. This can be set on the results loop, or passed through on the search params
		// ---------------------------------------

    	if ($inclusive AND isset($arr['or']) AND ($type = 'keywords' OR $type = 'category'))
		{
			// Only turn keyword chunks that have more than one keyword
			// into inclusive sets, to avoid odd edge cases

			if (count($arr['or']) > 1)
			{
				$arr['and'][0]['main'] = array_merge($arr['and'], $arr['or']);

				$arr['or'] = array();
			}
		}

        // -------------------------------------
		//	Save
		// -------------------------------------

		$arr['and']	= $this->remove_empties($arr['and']);
		$arr['not']	= $this->remove_empties($arr['not']);
		$arr['or']	= $this->remove_empties($arr['or']);

		return $arr;
	}

	//	End prep keywords

    // -------------------------------------------------------------

	/**
	 * Prep query
	 *
	 * We turn the array created by parse_uri into a more complex array that is ready for SQL consumption
	 *
	 * @access	public
	 * @return	array
	 */

	public function prep_query($search = array())
	{
		// -------------------------------------
		//	Validate
		// -------------------------------------

		if (empty($search)) return FALSE;

		// -------------------------------------
		//	Commencé
		// -------------------------------------

		$q	= array();

        // -------------------------------------
		//	Prep site ids
		// -------------------------------------
		//	Fail if no site id is present
		// -------------------------------------

		$search['site']	= (isset($search['site']) === TRUE) ? $search['site']: '';

		$passed_sites	= $this->prep_site_ids($search['site']);

		$tmpl_sites		= $this->prep_site_ids(ee()->TMPL->fetch_param('site'));

		$search_sites	= array_intersect($tmpl_sites, $passed_sites);

		if (empty($search_sites)) return FALSE;

		$q['site']		= $search_sites;

        // -------------------------------------
		//	Prep channel
		// -------------------------------------

		$q['channel']	= (isset($search['channel'])) ? $search['channel']: '';
		$q['channel']	= $this->prep_keywords($q['channel']);

        // -------------------------------------
		//	Flat variables
		// -------------------------------------

		foreach ($this->flat as $flat)
		{
			if (isset($search[$flat]))
			{
				$q[$flat]	= $search[$flat];
			}
		}

        // -------------------------------------
		//	Prep group &c
		// -------------------------------------

		foreach (array('author', 'group', 'status') as $fix)
		{
			if (! empty($search[$fix]))
			{
				$q[$fix]	= $this->prep_keywords($search[$fix]);
			}
		}

        // -------------------------------------
		//	Prep fields
		// -------------------------------------

		foreach ($this->fields as $field)
		{
			// -------------------------------------
			//	Run our types tests
			// -------------------------------------

			foreach (array('field', 'exactfield', 'empty', 'from', 'to') as $type)
			{
				if (isset($search[$type][$field]) AND $search[$type][$field] != '')
				{
					$q[$type][$field]	= $this->prep_keywords($search[$type][$field]);
				}

				// -------------------------------------
				//	Merge OR's on exact fields into one single value
				// -------------------------------------

				if ($type == 'exactfield' AND isset($q[$type][$field]['or']))
				{
					$temp	= implode(' ', $q[$type][$field]['or']);
					$q[$type][$field]['or']	= array($temp);
				}
			}
		}

        // -------------------------------------
		//	Prep date fields
		// -------------------------------------

		foreach ($this->date_fields as $field)
		{
			// -------------------------------------
			//	Run our types tests
			// -------------------------------------

			foreach (array('from', 'to') as $type)
			{
				if (! empty($search[$field.$type]))
				{
					$fullday	= ($type == 'from') ? FALSE: TRUE;

					$q[$field.$type]	= $this->parse_date_to_timestamp($search[$field.$type], $fullday);
				}
			}
		}

        // -------------------------------------
		//	Prep birthdays
		// -------------------------------------

		foreach (array_merge($this->birthdays, $this->ages) as $field)
		{
			if (! empty($search[$field]))
			{
				$q[$field]	= $search[$field];
			}
		}

        // -------------------------------------
		//	Prep categories
		// -------------------------------------

		foreach (array('category', 'category-like') as $type)
		{
			// -------------------------------------
			//	Prep inclusive categories
			// -------------------------------------

			if (isset($search['inclusive_categories']) AND $this->check_yes($search['inclusive_categories']))
			{
				$this->inclusive_categories = TRUE;
			}
			elseif (isset($search['inclusive_categories']) AND $this->check_no($search['inclusive_categories']))
			{
				$this->inclusive_categories = FALSE;
			}

			// -------------------------------------
			//	Prep inclusive categories
			// -------------------------------------

			if (! empty($search[$type]))
			{
				$q[$type]	= $this->prep_keywords($search[$type], $this->inclusive_categories, 'category');
			}
		}

        // -------------------------------------
		//	Prep keywords
		// -------------------------------------

		if (! empty($search['keywords']))
		{
			// -------------------------------------
			//	Preload inclusive keywords
			// -------------------------------------
			//	we have the option to overload the default 'OR' searching using the param 'inclusive_keywords', pass this to the prep_keywords method now, as it gets used in a number of other places
			// -------------------------------------

			$inclusive_keywords = $this->inclusive_keywords;

			if (isset($this->sess['uri']['inclusive_keywords']) AND $this->check_no($this->sess['uri']['inclusive_keywords']))
			{
				$inclusive_keywords = FALSE;
			}

			if (isset($this->sess['uri']['where']) AND $this->sess['uri']['where'] == 'all')
			{
				$inclusive_keywords = TRUE;
			}
			elseif (isset($this->sess['uri']['where']) AND $this->sess['uri']['where'] == 'any')
			{
				$inclusive_keywords = FALSE;
			}

			// -------------------------------------
			//	Save keywords phrase
			// -------------------------------------
			//	Set a clean search phrase here, before we start messing with our keywords, so we can cleanly repopulate the search box later on
			// -------------------------------------

			$q['keywords_phrase'] = $this->_clean_keywords($search['keywords']);

			// -------------------------------------
			//	Ignore words?
			// -------------------------------------

			if (isset($search['use_ignore_word_list']))
			{
				$q['keywords']	= $this->_prep_ignore_words($search['keywords'], $search['use_ignore_word_list']);
			}
			else
			{
				$q['keywords'] = $this->_prep_ignore_words($search['keywords']);
			}

			// -------------------------------------
			//	Keywords dead? Fail out completely
			// -------------------------------------

			if ($q['keywords'] === FALSE) return FALSE;

			// -------------------------------------
			//	Now prep keywords
			// -------------------------------------

			$q['keywords']	= $this->prep_keywords($q['keywords'] , $inclusive_keywords, 'keywords');
		}

		// -------------------------------------
		//	Return
		// -------------------------------------

		return $q;
	}

	//	End prep query

    // -------------------------------------------------------------

	/**
	 * Prep site ids
	 *
	 * We want to be able to dynamically assign site ids, but we need to be careful here on what we're letting through
	 *
	 * @access	private
	 * @return	array
	 */

	function prep_site_ids($site = '')
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
	 * Prep sql
	 *
	 * Given a string belonging to a search argument, format an SQL statement that can be appended to our master SQL search query.
	 *
	 * @access	public
	 * @return	string
	 */

	public function prep_sql($switch = 'or', $q = array())
	{
		//	$q['keywords']		= What are the search arguments?
		//	$q['exactness']		= Is the search loose or precise?
		//	$q['word_boundary']	= Can the words be found inside other words? Set to TRUE to require that words be distinct and separate from other words.
		//	$q['field_name']	= User facing field name
		//	$q['db_field_name']	= DB field name like m.screen_name or md.pants_size

		// -------------------------------------
		//	Validate
		// -------------------------------------

		if (empty($q['keywords']) OR is_array($q['keywords']) === FALSE OR empty($q['field_name']) OR empty($q['db_field_name'])) return FALSE;

		// -------------------------------------
		//	Are we ignoring any fields via template param?
		// -------------------------------------

		if (! empty($q['field_name']) AND ee()->TMPL->fetch_param('ignore_field') !== FALSE AND in_array($q['field_name'], explode("|", ee()->TMPL->fetch_param('ignore_field'))) === TRUE)
		{
			return FALSE;
		}

		// -------------------------------------
		//	Defaults
		// -------------------------------------

		$q['exactness']		= (empty($q['exactness'])) ? 'non-exact': $q['exactness'];
		$q['word_boundary']	= (empty($q['word_boundary'])) ? FALSE: $q['word_boundary'];

		// -------------------------------------
		//	Evaluate whether we allow wildcards
		// -------------------------------------

		$allow_wildcards	= FALSE;

        // -------------------------------------
		//	Prep inclusion
		// -------------------------------------

		if ($switch == 'or')
		{
			if (! empty($q['keywords']['or_fuzzy']))
			{
				$temp_keywords = $q['keywords']['or'];

				if (isset($q['keywords']['or_fuzzy']) AND is_array($q['keywords']['or_fuzzy']))
				{
					foreach($q['keywords']['or_fuzzy'] as $fuzzy_set)
					{
						$temp_keywords = array_merge($temp_keywords, $fuzzy_set);
					}
				}
			}
			else
			{
				$temp_keywords	= $q['keywords'];
			}

			$temp	= array();

			foreach ($temp_keywords as $val)
			{
				if ($val == '') continue;

				if (strpos($val, $this->spaces) !== FALSE)
				{
					$val	= str_replace($this->spaces, ' ', $val);
				}

				if ($q['exactness'] == 'exact')
				{
					$temp[]	= $q['db_field_name'] . " = '" . $this->escape_str($val) . "'";
				}
				elseif ($q['word_boundary'] === TRUE)
				{
					$temp[]	= $q['db_field_name'] . " REGEXP '[[:<:]]" . $this->escape_str($val) . "[[:>:]]'";
				}
				else
				{
					if ($allow_wildcards AND stripos($val, $this->wildcard) !== FALSE)
					{
						$temp[]	= $q['db_field_name'] . " REGEXP '" . str_replace($this->wildcard, '[a-zA-Z0-9]+', $this->escape_str($val)) . "'";
					}
					else
					{
						$temp[]	= $q['db_field_name'] . " LIKE '%" . $this->escape_str($val) . "%'";
					}
				}
			}

			if (count($temp) > 0)
			{
				$out	= '(' . implode(' OR ', $temp) . ')';
			}
		}

        // -------------------------------------
		//	Prep exclusion
		// -------------------------------------

		if ($switch == 'not')
		{
			$temp	= array();

			foreach ($q['keywords'] as $val)
			{
				if ($val == '') continue;

				if (strpos($val, $this->spaces) !== FALSE)
				{
					$val	= str_replace($this->spaces, ' ', $val);
				}

				if ($q['exactness'] == 'exact')
				{
					$temp[]	= $q['db_field_name'] . " != '" . $this->escape_str($val) . "'";
				}
				elseif ($q['word_boundary'] === TRUE)
				{
					$temp[]	= $q['db_field_name'] . " NOT REGEXP '[[:<:]]" . $this->escape_str($val)."[[:>:]]'";
				}
				else
				{
					if ($allow_wildcards AND stripos($val, $this->wildcard) !== FALSE)
					{
						$temp[]	= $q['db_field_name'] . " NOT REGEXP '" . str_replace($this->wildcard, '[a-zA-Z0-9]+', $this->escape_str($val)) . "'";
					}
					else
					{
						$temp[]	= $q['db_field_name'] . " NOT LIKE '%" . $this->escape_str($val) . "%'";
					}
				}
			}

			if (count($temp) > 0)
			{
				$out	= '('.implode(' AND ', $temp).')';
			}
		}

        // -------------------------------------
		//	Empty
		// -------------------------------------

		if (empty($out)) return FALSE;

        // -------------------------------------
		//	Convert markers
		// -------------------------------------

		$out	= $this->convert_markers($out);

        // -------------------------------------
		//	Return
		// -------------------------------------

		return $out;
	}

	//	End prep sql

	// --------------------------------------------------------------------
}
// End class Super Search
