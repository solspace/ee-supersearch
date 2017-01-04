<?php

use Solspace\Addons\SuperSearch\Library\AddonBuilder;

class Super_search_mcp extends AddonBuilder
{
	public $row_limit	= 20;
	public $sess		= array();

	// -----------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	bool		Enable calling of methods based on URI string
	 * @return	string
	 */

	public function __construct( $switch = TRUE )
	{
		parent::__construct('module');

		//	----------------------------------------
		//	 UTF-8
		//	----------------------------------------

		if (function_exists( 'mb_internal_encoding'))
		{
			mb_internal_encoding('UTF-8');
		}

		// --------------------------------------------
		//  Module Menu Items
		// --------------------------------------------

		$this->set_nav(array(
			'homepage'		=> array(
				'link'  => $this->base,
				'title' => lang('homepage')
			),
			'log'	=> array(
				'link'  => $this->mcp_link(array(
					'method' => 'log'
				)),
				'title' => lang('log'),
			),
			'curation'	=> array(
				'link'  => $this->mcp_link(array(
					'method' => 'curation'
				)),
				'title' => lang('search_curation'),
				'sub_list'	=> array(
					'curation_list'	=> array(
						'link'  => $this->mcp_link(array(
							'method' => 'curation_list'
						)),
						'title' => lang('keyword_list')
					)
				),
			),
			'build_search_lexicon'	=> array(
				'link' => $this->mcp_link(array('method' => 'build_search_lexicon')),
				'title' => lang('build_search_lexicon')
			),
			'preferences'		=> array(
				'link'  => $this->mcp_link(array(
					'method' => 'preferences'
				)),
				'title' => lang('preferences'),
			),
			'demo_templates'		=> array(
				'link'  => $this->mcp_link(array(
					'method' => 'code_pack'
				)),
				'title' => lang('demo_templates'),
			),
			'resources'      => array(
				'title'    => lang('super_search_resources'),
				'sub_list' => array(
					'product_info'  => array(
						'link'     => 'https://solspace.com/expressionengine/super-search',
						'title'    => lang('super_search_product_info'),
						'external' => true,
					),
					'documentation' => array(
						'link'     => $this->docs_url,
						'title'    => lang('super_search_documentation'),
						'external' => true,
					),
					'support'       => array(
						'link'     => 'https://solspace.com/expressionengine/support',
						'title'    => lang('super_search_official_support'),
						'external' => true,
					),
				),
			),
		));

		$this->cached_vars['lang_module_version'] 	= lang('super_search_module_version');
		$this->cached_vars['module_version'] 		= ee('App')->get('super_search')->getVersion();
		$this->cached_vars['module_menu_highlight']	= 'module_home';

		// --------------------------------------------
		//  Sites
		// --------------------------------------------

		$this->cached_vars['sites']	= array();

		foreach($this->get_sites() as $site_id => $site_label)
		{
			$this->cached_vars['sites'][$site_id] = $site_label;
		}

		//--------------------------------------------
		//	just a helper
		//--------------------------------------------

		$this->clean_site_id = ee()->db->escape_str(ee()->config->item('site_id'));

		ee()->cp->add_to_head('<link rel="stylesheet" type="text/css" href="' . URL_THIRD_THEMES . 'super_search/css/solspace-fa.css">');
	}

	//	END constructor

	// -----------------------------------------------------------------

	/**
	 * clear_search_cache()
	 *
	 * @access	private
	 * @return	boolean
	 */

	public function clear_search_cache()
	{
		do
		{
			ee()->db->query(
				"DELETE FROM exp_super_search_cache
				WHERE site_id = " . ee()->db->escape_str(ee()->config->item('site_id')) . "
				LIMIT 1000"
			);
		}
		while (ee()->db->affected_rows() > 0);

		do
		{
			ee()->db->query(
				"DELETE FROM exp_super_search_history
				WHERE saved = 'n'
				AND cache_id NOT IN (
					SELECT cache_id
					FROM exp_super_search_cache
					WHERE site_id = " . ee()->db->escape_str(ee()->config->item('site_id')) . "
				)
				LIMIT 1000"
			);
		}
		while (ee()->db->affected_rows() > 0);

		$return	= $this->mcp_link(array(
			'method'	=> 'preferences',
			'msg'		=> 'cache_cleared'
		));

		return ee()->functions->redirect($return);
	}

	//	End clear_search_cache

	// -----------------------------------------------------------------


	/**
	 * Module's Home Page

	 * @access	public
	 * @param	string
	 * @return	null
	 */

	public function index($message = '')
	{
		// --------------------------------------------
		//  Top Terms
		// --------------------------------------------

		$terms = $this->fetch('Term')
			->filter('site_id', ee()->config->item('site_id'))
			->filter('term', '!=', '')
			->filter('count', '>', 0)
			->order('count', 'desc')
			->limit(20)
			->all();

		//--------------------------------------------
		//	Table
		//--------------------------------------------

		$table = ee('CP/Table', array(
			'sortable'	=> false,
			'search'	=> false
		));

		$tableData = array();

		foreach ($terms as $term)
		{
			$item = array();

			//	Count
			$item[]	= $term->count;

			//	Term
			$item[]	= array(
				'content'	=> $term->term,
				'href'		=> $this->mcp_link(array(
					'method'	=> 'log',
					'term_id'	=> $term->term_id
				))
			);

			//	First searched
			$item[]	= $this->_time_elapsed_string(time() - $term->first_seen);

			//	Recent search
			$item[]	= $this->_time_elapsed_string(time() - $term->last_seen);

			//	Curated entries
			$item[]	= $term->curated_count;

			$tableData[] = $item;
		}

		$table->setColumns(array(
			'search_count',
			'term',
			'first_seen',
			'last_seen',
			'curated_results'
		));

		$table->setData($tableData);

		$table->setNoResultsText('no_searches_yet');

		$this->cached_vars['terms_table'] = $table->viewData();

		//---------------------------------------------
		//  Load Page and set view vars
		//---------------------------------------------

		return $this->mcp_view(array(
			'file'		=> 'index',
			'highlight'	=> 'homepage',
			'pkg_css'	=> array('mcp_defaults'),
			'crumbs'	=> array(
				array(lang('homepage'))
			)
		));
	}

	//	End index

	// -----------------------------------------------------------------

	/**
	 * preferences()
	 *
	 * @access	public
	 * @param	string
	 * @return	null
	 */

	public function preferences($message = '')
	{
		$this->prep_message($message, TRUE, TRUE);

		// --------------------------------------------
		//  Current Values
		// --------------------------------------------

		$prefModel		= $this->make('Preference');

		$defaultPrefs	= $prefModel->default_prefs;

		$prefs = $this->fetch('Preference')
			->filter('site_id', ee()->config->item('site_id'))
			->all()->getDictionary(
				'preference_name',
				'preference_value'
		);

		if (! empty($prefs['ignore_word_list']))
		{
			$prefs['ignore_word_list']	= str_replace('||', ', ', $prefs['ignore_word_list']);
		}

		// --------------------------------------------
		//  Start sections
		// --------------------------------------------

		$sections = array();

		$main_section = array();

		foreach ($defaultPrefs as $short_name => $data)
		{
			//	While in our loop, once we reach the fuzzy searching section, break it out as a separate section.
			if ($short_name == 'enable_fuzzy_searching')
			{
				$sections[]		= $main_section;
				$main_section	= array();
			}

			if ($short_name == 'third_party_search_indexes') continue;

			$desc_name	= $short_name . '_subtext';
			$desc		= lang($desc_name);

			//if we don't have a description don't set it
			$desc		= ($desc !== $desc_name) ? $desc : '';

			$required	= ($short_name != 'ignore_word_list') ? TRUE: FALSE;

			$main_section[$short_name] = array(
				'title'		=> lang($short_name),
				'desc'		=> $desc,
				'fields'	=> array(
					$short_name => array_merge($data, array(
						'value'		=> isset($prefs[$short_name]) ?
										$prefs[$short_name] :
										$data['default'],
						//we just require everything
						//its a settings form
						'required'	=> $required
					))
				)
			);
		}

		$sections['fuzzy_searching'] = $main_section;

		$main_section	= array();

		// --------------------------------------------
		//  Refresh link
		// --------------------------------------------

		$main_section['current_cache'] = array(
			'title'		=> lang('current_cache'),
			'desc'		=> lang('No searches have been cached.'),
			'fields'	=> array(
				'current_cache' => array(
					'type' 		=> 'html',
					'content'	=> ''
				)
			)
		);

		$nextRefresh	= $this->fetch('RefreshRule')
			->fields('date')
			->filter('site_id', ee()->config->item('site_id'))
			->first();

		if ($nextRefresh)
		{
			$main_section['current_cache']['desc']	= str_replace('%n%', $this->human_time($nextRefresh->date), lang('next_refresh'));
			$main_section['current_cache']['fields']['current_cache']['content']	= '<a class="btn tn action" href="' . $this->mcp_link(array('method' => 'clear_search_cache', 'msg'=> 'cache_cleared')) . '">' . lang('clear_cached_searches') . '</a>';
		}

		// --------------------------------------------
		//  Refresh rule
		// --------------------------------------------

		$refreshRule = $this->fetch('RefreshRule')
			->filter('site_id', ee()->config->item('site_id'))
			->first();

		$main_section['refresh'] = array(
			'title'		=> lang('refresh'),
			'desc'		=> lang('refresh_explanation'),
			'fields'	=> array(
				'refresh' => array(
					'type'		=> 'text',
					'attrs'		=> ' style="width:15%"',
					'default'	=> '0',
					'value'		=> ($refreshRule) ? $refreshRule->refresh: 0,
				)
			)
		);

		// --------------------------------------------
		//  Templates
		// --------------------------------------------

		$templates = ee('Model')
			->get('Template')
			->with('TemplateGroup')
			->filter('site_id', ee()->config->item('site_id'))
			->all();

		$selected	= $this->fetch('RefreshRule')
			->filter('site_id', ee()->config->item('site_id'))
			->filter('template_id', '>', 0)
			->all()
			->getDictionary('template_id', 'template_id');

		$data	= array(
			'name'		=> 'template_refresh',
			'optgroups'	=> array(),
			'selected'	=> $selected
		);

		foreach ($templates as $template)
		{
			$data['optgroups'][$template->TemplateGroup->group_name][$template->template_id]	= $template->template_name;
		}

		$main_section['template_refresh'] = array(
			'title'		=> lang('template_refresh'),
			'desc'		=> lang('template_refresh_explanation'),
			'fields'	=> array(
				'template_refresh' => array(
					'type' 		=> 'html',
					'content'	=> $this->view('form/multiselect', $data)
				)
			)
		);

		// --------------------------------------------
		//  Channels
		// --------------------------------------------

		$channels = ee('Model')
			->get('Channel')
			->filter('site_id', ee()->config->item('site_id'))
			->order('channel_title', 'asc')
			->all();

		$selected	= $this->fetch('RefreshRule')
			->filter('site_id', ee()->config->item('site_id'))
			->filter('channel_id', '>', 0)
			->all()
			->getDictionary('channel_id', 'channel_id');

		$data	= array(
			'name'		=> 'channel_refresh',
			'options'	=> array(),
			'selected'	=> $selected
		);

		foreach ($channels as $channel)
		{
			$data['options'][$channel->channel_id]	= $channel->channel_title;
		}

		$main_section['channel_refresh'] = array(
			'title'		=> lang('channel_refresh'),
			'desc'		=> lang('channel_refresh_explanation'),
			'fields'	=> array(
				'channel_refresh' => array(
					'type' 		=> 'html',
					'content'	=> $this->view('form/multiselect', $data)
				)
			)
		);

		// --------------------------------------------
		//  Category groups
		// --------------------------------------------

		$categories = ee('Model')
			->get('CategoryGroup')
			->filter('site_id', ee()->config->item('site_id'))
			->order('group_name', 'asc')
			->all();

		$selected	= $this->fetch('RefreshRule')
			->filter('site_id', ee()->config->item('site_id'))
			->filter('category_group_id', '>', 0)
			->all()
			->getDictionary('category_group_id', 'category_group_id');

		$data	= array(
			'name'		=> 'category_refresh',
			'options'	=> array(),
			'selected'	=> $selected
		);

		foreach ($categories as $category)
		{
			$data['options'][$category->group_id]	= $category->group_name;
		}

		$main_section['category_refresh'] = array(
			'title'		=> lang('category_refresh'),
			'desc'		=> lang('category_refresh_explanation'),
			'fields'	=> array(
				'category_refresh' => array(
					'type' 		=> 'html',
					'content'	=> $this->view('form/multiselect', $data)
				)
			)
		);

		$sections['caching_preferences'] = $main_section;

		$this->cached_vars['sections'] = $sections;

		$this->cached_vars['form_url'] = $this->mcp_link(array(
			'method' => 'update_preferences'
		));

		//---------------------------------------------
		//  Load Page and set view vars
		//---------------------------------------------

		// Final view variables we need to render the form
		$this->cached_vars += array(
			'base_url'				=> $this->mcp_link(array(
				'method' => 'update_preferences'
			)),
			'cp_page_title'			=> lang('Preferences'),
			'save_btn_text'			=> 'btn_save_settings',
			'save_btn_text_working'	=> 'btn_saving'
		);

		return $this->mcp_view(array(
			'file'		=> 'form',
			'highlight'	=> 'preferences',
			'pkg_css'	=> array('mcp_defaults'),
			'crumbs'	=> array(
				array(lang('preferences'))
			)
		));
	}

	//	End preferences()

	// -----------------------------------------------------------------

	/**
	 * curation()
	 *
	 * @access	public
	 * @param	string
	 * @return	view
	 */

	public function curation($message = '')
	{
		$this->prep_message($message, TRUE, TRUE);

		// --------------------------------------------
		//  Keyword
		// --------------------------------------------

		$this->cached_vars['term_keyword']	= '';

		if (ee()->input->get_post('term_keyword'))
		{
			$this->cached_vars['term_keyword']	= ee()->input->get_post('term_keyword');

			//	Exact
			$exact_term = $this->fetch('Term')
				->filter('site_id', ee()->config->item('site_id'))
				->filter('term', ee()->input->get_post('term_keyword'))
				->first();

			if ($exact_term)
			{
				$this->cached_vars['exact_keyword']	= $exact_term->term;
				$this->cached_vars['term_id']		= $exact_term->term_id;
				$this->cached_vars['curated_count']	= $exact_term->curated_count;
				$this->cached_vars['edit_link']		= $this->mcp_link(array(
					'method'	=> 'curation_edit',
					'term_id'	=> $exact_term->term_id
				));
			}
		}

		// --------------------------------------------
		//  Terms search
		// --------------------------------------------

		if (ee()->input->get_post('term_keyword'))
		{
			$terms = ee()->db->select('term')
				->distinct()
				->where('site_id', ee()->config->item('site_id'))
				->like('term', ee()->input->get_post('term_keyword'))
				->order_by('term', 'asc');

			if (isset($exact_term))
			{
				$terms->where('term', '!=', $exact_term->term);
			}

			$terms	= $terms->get('exp_super_search_log');

			//	----------------------------------------
			//	Total
			//	----------------------------------------

			$total	= $terms->num_rows();
			$page	= 0;

			//	----------------------------------------
			//	Pagination
			//	----------------------------------------

			if ($total > $this->row_limit)
			{
				$terms = ee()->db->select('term')
					->distinct()
					->where('site_id', ee()->config->item('site_id'))
					->like('term', ee()->input->get_post('term_keyword'))
					->order_by('term', 'asc');

				if (isset($exact_term))
				{
					$terms->where('term', '!=', $exact_term->term);
				}

				$page	= $this->get_post_or_zero('page') ?: 1;

				$mcp_link_array = array(
					'method'	=> __FUNCTION__
				);

				$this->cached_vars['pagination'] = ee('CP/Pagination', $total)
									->perPage($this->row_limit)
									->currentPage($page)
									->render($this->mcp_link($mcp_link_array, false));

				$terms->limit($this->row_limit)->offset(($page - 1) * $this->row_limit);

				//	----------------------------------------
				//	Fetch
				//	----------------------------------------

				$terms	= $terms->get('exp_super_search_log');
			}

			//	----------------------------------------
			//	Start table
			//	----------------------------------------

			$tableData = array();

			foreach ($terms->result_array() as $term)
			{
				$tableData[] = array(
					array(
						'content'	=> $term['term'],
						'href'		=> $this->mcp_link(array(
							'method'	=> 'curation_edit',
							'term'		=> $term['term']
						))
					)
				);
			}

			// -------------------------------------
			//	build table
			// -------------------------------------

			$table = ee('CP/Table', array(
				'sortable'	=> false,
				'search'	=> false,
			));

			$table->setColumns(array(
				'other_terms',
			));

			$table->setData($tableData);

			$table->setNoResultsText(lang('no_other_terms') . '<br /><a class="btn tn action" href="' . $this->mcp_link(array('method' => 'curation_edit', 'term' => ee()->input->get_post('term_keyword'))) . '">' . lang('curate_new_term') . '</a><br /><br />');

			$this->cached_vars['results'] = $table->viewData(
				$this->mcp_link(array('method' => __FUNCTION__), false)
			);

			$this->cached_vars['form_right_links']		= array(
				array(
					'link'	=> $this->mcp_link(array('method' => 'curation')),
					'title'	=> lang('start_over'),
				)
			);
		}

		//---------------------------------------------
		//  Load Page and set view vars
		//---------------------------------------------

		$this->cached_vars['form_url']		= $this->mcp_link(array(
			'method' => 'curation'
		));

		// Final view variables we need to render the form
		$this->cached_vars += array(
			'base_url'				=> $this->mcp_link(array(
				'method' => 'curation'
			)),
			'cp_page_title'			=> lang('search_curation') . '<br /><i>' . lang('search_curation_explanation') . '</i>',
			'save_btn_text'			=> 'btn_search',
			'save_btn_text_working'	=> 'btn_searching'
		);

		return $this->mcp_view(array(
			'file'		=> 'curation/index',
			'highlight'	=> 'curation',
			'pkg_css'	=> array('mcp_defaults'),
			'crumbs'	=> array(
				array(lang('search_curation'))
			)
		));
	}

	//	End curation()

	// -----------------------------------------------------------------

	/**
	 * curation_edit()
	 *
	 * @access	public
	 * @param	string
	 * @return	view
	 */

	public function curation_edit($message = '')
	{
		$this->prep_message($message, TRUE, TRUE);

		// --------------------------------------------
		//  Keyword
		// --------------------------------------------

		$this->cached_vars['term_keyword']	= '';
		$this->cached_vars['term_id']		= '';

		if (ee()->input->get_post('term_id'))
		{
			$term = $this->fetch('Term')
				->filter('site_id', ee()->config->item('site_id'))
				->filter('term_id', ee()->input->get_post('term_id'))
				->first();

			$this->cached_vars['term_keyword']	= $term->term;
			$this->cached_vars['term_id']		= $term->term_id;
			$this->cached_vars['edit']			= TRUE;
		}
		elseif (ee()->input->get_post('term'))
		{
			$this->cached_vars['term_keyword']	= ee()->input->get_post('term');
			$term = $this->fetch('Term')
				->filter('site_id', ee()->config->item('site_id'))
				->filter('term', ee()->input->get_post('term'))
				->first();

			if ($term)
			{
				$this->cached_vars['term_keyword']	= $term->term;
				$this->cached_vars['term_id']		= $term->term_id;
				$this->cached_vars['edit']			= TRUE;
			}
		}

		// --------------------------------------------
		//  Remove entries?
		// --------------------------------------------

		if (! empty($_POST['remove']) AND ee()->input->get_post('term_id'))
		{
			$recount	= TRUE;

			ee()->db->query(
				"DELETE FROM exp_super_search_curated_entries
					WHERE term_id = " . ee()->db->escape_str(ee()->input->get_post('term_id')) . "
					AND entry_id IN (" . implode($_POST['remove']) . ")"
			);
		}

		// --------------------------------------------
		//  Assign entries?
		// --------------------------------------------

		if (! empty($_POST['add']))
		{
			$recount	= TRUE;

			if (empty($term) AND ee()->input->get_post('term'))
			{
				$term = $this->make('Term');
				$term->term		= ee()->input->get_post('term');
				$term->site_id	= ee()->config->item('site_id');
				$term->save();

				$this->cached_vars['term_keyword']	= $term->term;
				$this->cached_vars['term_id']		= $term->term_id;
				$this->cached_vars['edit']			= TRUE;
			}

			foreach ($_POST['add'] as $entry_id)
			{
				$new = $this->make('CuratedEntry');
				$new->term_id	= $term->term_id;
				$new->entry_id	= $entry_id;
				$new->save();
			}
		}

		// --------------------------------------------
		// Recount
		// --------------------------------------------

		if (isset($recount) AND isset($term->term_id))
		{
			$term = $this->fetch('Term')
				->filter('site_id', ee()->config->item('site_id'))
				->filter('term_id', $term->term_id)
				->first();

			$term->curated_count	= $this->fetch('CuratedEntry')
				->filter('term_id', $term->term_id)
				->count();

			$term->save();
		}

		// --------------------------------------------
		// Entries already assigned?
		// --------------------------------------------

		if (! empty($this->cached_vars['term_id']))
		{
			$assigned = $this->fetch('CuratedEntry')
				->filter('term_id', $this->cached_vars['term_id'])
				->all();

			if ($assigned->count() > 0)
			{
				foreach ($assigned as $entry)
				{
					$this->cached_vars['assigned'][$entry->entry_id]	= array(
						'entry_id'	=> $entry->entry_id,
						'link'		=> ee('CP/URL', 'publish/edit/entry/' . $entry->entry_id),
						'title'		=> $entry->ChannelEntry->title,
						'status'	=> $entry->ChannelEntry->status,
						'date'		=> $this->human_time($entry->ChannelEntry->date),
						'author'	=> $entry->ChannelEntry->Author->screen_name,
						'channel'	=> $entry->ChannelEntry->Channel->channel_title,
					);
				}
			}
		}

		// --------------------------------------------
		//  Entries
		// --------------------------------------------

		$this->cached_vars['entry_keyword']	= '';

		if (ee()->input->get_post('entry_keyword'))
		{
			$this->cached_vars['entry_keyword']	= ee()->input->get_post('entry_keyword');

			$entries	= ee('Model')->get('ChannelEntry')
				->filter('site_id', ee()->config->item('site_id'))
				->filter('title', 'LIKE', '%' . ee()->input->get_post('entry_keyword') . '%');

			if (! empty($this->cached_vars['assigned']))
			{
				foreach ($this->cached_vars['assigned'] as $entry_id => $entry)
				{
					$entries->filter('entry_id', '!=', $entry_id);
				}
			}

			//	----------------------------------------
			//	Pagination
			//	----------------------------------------

			$total	= $entries->count();
			$page	= 0;

			if ($total > $this->row_limit)
			{
				$page	= $this->get_post_or_zero('page') ?: 1;

				$mcp_link_array = array(
					'method'		=> __FUNCTION__,
					'term'			=> ee()->input->get_post('term_keyword'),
					'term_id'		=> ee()->input->get_post('term_id'),
					'entry_keyword'	=> ee()->input->get_post('entry_keyword')
				);

				$this->cached_vars['pagination'] = ee('CP/Pagination', $total)
									->perPage($this->row_limit)
									->currentPage($page)
									->render($this->mcp_link($mcp_link_array, false));

				$entries->limit($this->row_limit)->offset(($page - 1) * $this->row_limit);
			}

			if ($total > 0)
			{
				foreach ($entries->all() as $entry)
				{
					$this->cached_vars['entries'][$entry->entry_id]	= array(
						'entry_id'	=> $entry->entry_id,
						'link'		=> ee('CP/URL', 'publish/edit/entry/' . $entry->entry_id),
						'title'		=> $entry->title,
						'status'	=> $entry->status,
						'date'		=> $this->human_time($entry->date),
						'author'	=> $entry->Author->screen_name,
						'channel'	=> $entry->Channel->channel_title,
					);
				}
			}
		}

		//---------------------------------------------
		//  Load Page and set view vars
		//---------------------------------------------

		$this->cached_vars['form_url']		= $this->mcp_link(array(
			'method'	=> 'curation_edit',
			'term_id'	=> $this->cached_vars['term_id']
		));

		if (! empty($this->cached_vars['assigned']) OR ! empty($this->cached_vars['entries']))
		{
			$this->cached_vars['footer']		= array(
				'type'			=> 'form',
				'submit_lang'	=> lang('btn_add_remove_entries')
			);

			$this->cached_vars['form_right_links']		= array(
				array(
					'link'	=> $this->mcp_link(array('method' => 'curation')),
					'title'	=> lang('curate_another'),
				)
			);
		}

		// Final view variables we need to render the form
		$this->cached_vars += array(
			'base_url'				=> $this->mcp_link(array(
				'method' => 'curation_edit'
			)),
			'cp_page_title'			=> lang('search_curation') . '<br /><i>' . lang('search_curation_explanation') . '</i>',
			'save_btn_text'			=> 'btn_save',
			'save_btn_text_working'	=> 'btn_saving'
		);

		return $this->mcp_view(array(
			'file'		=> 'curation/edit',
			'highlight'	=> 'curation',
			'pkg_css'	=> array('mcp_defaults'),
			'crumbs'	=> array(
				array(
					lang('search_curation'),
					$this->mcp_link(array('method' => 'curation'), false)
				),
				array(lang('edit'))
			)
		));
	}

	//	End curation_edit()

	// -----------------------------------------------------------------

	/**
	 * curation_list()
	 *
	 * @access	public
	 * @param	string
	 * @return	view
	 */

	public function curation_list($message = '')
	{
		// --------------------------------------------
		//  Entries
		// --------------------------------------------

		$entries = ee()->db->select('exp_super_search_terms.term_id, exp_super_search_terms.term, exp_super_search_terms.curated_count')
			->distinct()
			->from('exp_super_search_terms')
			->join('exp_super_search_curated_entries', 'exp_super_search_terms.term_id = exp_super_search_curated_entries.term_id')
			->where('exp_super_search_terms.site_id', ee()->config->item('site_id'))
			->order_by('exp_super_search_terms.term', 'asc');

		//	----------------------------------------
		//	Total
		//	----------------------------------------

		$total	= $entries->get()->num_rows();
		$page	= 0;

		//	----------------------------------------
		//	Pagination
		//	----------------------------------------

		$entries = ee()->db->select('exp_super_search_terms.term_id, exp_super_search_terms.term, exp_super_search_terms.curated_count')
			->distinct()
			->from('exp_super_search_terms')
			->join('exp_super_search_curated_entries', 'exp_super_search_terms.term_id = exp_super_search_curated_entries.term_id')
			->where('exp_super_search_terms.site_id', ee()->config->item('site_id'))
			->order_by('exp_super_search_terms.term', 'asc');

		if ($total > $this->row_limit)
		{
			$page	= $this->get_post_or_zero('page') ?: 1;

			$mcp_link_array = array(
				'method'	=> __FUNCTION__
			);

			$this->cached_vars['pagination'] = ee('CP/Pagination', $total)
								->perPage($this->row_limit)
								->currentPage($page)
								->render($this->mcp_link($mcp_link_array, false));

			$entries->limit($this->row_limit)->offset(($page - 1) * $this->row_limit);
		}

		//	----------------------------------------
		//	Fetch
		//	----------------------------------------

		$entries	= $entries->get();

		//	----------------------------------------
		//	Start table
		//	----------------------------------------

		$tableData = array();

		foreach ($entries->result_array() as $entry)
		{
			$tableData[] = array(
				array(
					'content'	=> $entry['term'],
					'href'		=>$this->mcp_link(array(
						'method'	=> 'curation_edit',
						'term_id'	=> $entry['term_id']
					))
				),
				$entry['curated_count'],
			);
		}

		// -------------------------------------
		//	build table
		// -------------------------------------

		$table = ee('CP/Table', array(
			'sortable'	=> false,
			'search'	=> false,
		));

		$table->setColumns(array(
			'term',
			'count',
		));

		$table->setData($tableData);

		$table->setNoResultsText('no_curated_terms');

		$this->cached_vars['form_url']	= '';

		$this->cached_vars['table'] = $table->viewData(
			$this->mcp_link(array('method' => __FUNCTION__), false)
		);

		//---------------------------------------------
		//  Load Page and set view vars
		//---------------------------------------------

		return $this->mcp_view(array(
			'file'		=> 'curation/list',
			'highlight'	=> 'curation/curation_list',
			'pkg_css'	=> array('mcp_defaults'),
			'crumbs'	=> array(
				array(
					lang('search_curation'),
					$this->mcp_link(array('method' => 'curation'), false)
				),
				array(lang('keyword_list'))
			)
		));
	}

	//	End curation_list()

	// -----------------------------------------------------------------

	/**
	 * update_preferences()
	 *
	 * @access	public
	 * @param	string
	 * @return	null
	 */

	public function update_preferences()
	{
		$prefs = $this->make('Preference');

		$default_prefs = $prefs->default_prefs;

		$input_keys = $required = array_keys($default_prefs);

		unset($required['ignore_word_list']);

		$inputs = array();

		// -------------------------------------
		//	fetch only default prefs
		// -------------------------------------

		foreach ($input_keys as $input)
		{
			if (isset($_POST[$input]))
			{
				$inputs[$input] = ee()->input->post($input);
			}
		}

		// -------------------------------------
		//	validate (custom method)
		// -------------------------------------

		$result = $prefs->validateDefaultPrefs($inputs, $required);

		if ( ! $result->isValid())
		{
			$errors = array();

			foreach ($result->getAllErrors() as $name => $error_list)
			{
				foreach($error_list as $error_name => $error_msg)
				{
					$errors[] = lang($name) . ': ' . $error_msg;
				}
			}

			return $this->show_error($errors);
		}

		//	----------------------------------------
		//	Update Preferences
		//	----------------------------------------

		$site_id = ee()->config->item('site_id');
		$currentPrefs = $this->fetch('Preference')
			->filter('site_id', $site_id)
			->all()
			->indexBy('preference_name');

		foreach ($inputs as $name => $value)
		{
			//ignore word list
			if ($name == 'ignore_word_list')
			{
				$value	= str_replace(', ', '||', $value);
			}

			//update
			if (isset($currentPrefs[$name]))
			{
				$currentPrefs[$name]->preference_value = $value;
				$currentPrefs[$name]->save();
			}
			//insert
			else
			{
				$new = $this->make('Preference');
				$new->preference_value = $value;
				$new->preference_name = $name;
				$new->site_id = $site_id;
				$new->save();
			}
		}

		//	----------------------------------------
		//	Prep for refresh rules
		//	----------------------------------------

		$refresh		= (ee()->input->get_post('refresh') != '') ? ee()->input->get_post('refresh'): 0;
		$refreshDate	= ($refresh == 0) ? 0: (ee()->localize->now + ($refresh * 60));

		//	----------------------------------------
		//	Delete current rules
		//	----------------------------------------

		$sql	= "DELETE FROM exp_super_search_refresh_rules
					WHERE site_id = " . ee()->db->escape_str(ee()->config->item('site_id'));

		ee()->db->query($sql);

		//	----------------------------------------
		//	Set baseline refresh
		//	----------------------------------------

		$new			= $this->make('RefreshRule');
		$new->site_id	= ee()->config->item('site_id');
		$new->date		= $refreshDate;
		$new->refresh	= $refresh;
		$new->save();

		//	----------------------------------------
		//	Loop for event specific refresh rules
		//	----------------------------------------

		$rules	= array(
			'template_refresh'	=> 'template_id',
			'channel_refresh'	=> 'channel_id',
			'category_refresh'	=> 'category_group_id'
		);

		foreach ($rules as $rule => $id)
		{
			if (! empty($_POST[$rule]))
			{
				foreach ($_POST[$rule] as $val)
				{
					$new			= $this->make('RefreshRule');
					$new->site_id	= ee()->config->item('site_id');
					$new->date		= $refreshDate;
					$new->refresh	= $refresh;
					$new->$id		= $val;
					$new->save();
				}
			}
		}

		//	----------------------------------------
		//	Return view
		//	----------------------------------------

		return ee()->functions->redirect($this->mcp_link(array(
			'method'	=> 'preferences',
			'msg'		=> 'preferences_updated'
		)));
	}

	//	End update_preferences()


	// --------------------------------------------------------------------

	/**
	 * build_search_lexicon()
	 *
	 * @access	public
	 * @param	string $message message line for incoming
	 * @return	array	array for cp view processing
	 */

	public function build_search_lexicon($message = '')
	{
		$this->prep_message($message, TRUE, TRUE);

		// -------------------------------------
		// Lexicon stats
		// -------------------------------------

		$this->cached_vars['lexicon_progress_count_total'] = ee('Model')->get('ChannelEntry')->count();

		$log = $this->fetch('LexiconLog')
			->fields('action_date')
			->filter('origin', 'manual')
			->order('action_date', 'desc')
			->first();

		if ($log)
		{
			$last_build = $this->human_time($log->action_date);

			$this->cached_vars['lexicon_last_build'] = lang('lexicon_last_built') . $last_build;// has never been built.';
		}
		else
		{
			$this->cached_vars['lexicon_last_build'] =  lang('lexicon_never_built');
		}

		$this->cached_vars['lexicon_build_url'] = $this->mcp_link(array(
			'method'	=> 'lexicon',
			'build'		=> 'yes',
			'total'		=> ee('Model')->get('ChannelEntry')->count(),
			'batch'		=> 0
		));

		$this->cached_vars['percent']			= 0;
		$this->cached_vars['success_png_url']	= $this->theme_url . "images/success.png";

		//---------------------------------------------
		//  Load page
		//---------------------------------------------

		$this->cached_vars['cp_page_title']	= lang('build_search_lexicon') . '<br /><i>' . lang('build_search_lexicon_explanation') . '</i>' ;

		return $this->mcp_view(array(
			'file'		=> 'lexicon',
			'highlight'	=> 'build_search_lexicon',
			'pkg_js'	=> array('lexicon'),
			'pkg_css'	=> array('mcp_defaults', 'utilities_progress_meter'),
			'crumbs'	=> array(
				array(lang('build_search_lexicon'))
			)
		));
	}

	//	End build_search_lexicon()

	// -----------------------------------------------------------------

	/**
	 * Module's Lexicon Page
	 *
	 * @access	public
	 * @param	string
	 * @return	null
	 */

	public function lexicon( $message = '' )
	{
		if (ee()->input->get_post('build') == 'yes')
		{
			if (ee()->input->get_post('ajax') == 'yes')
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

				$this->cached_vars['response'] = $this->model('Data')->build_lexicon( $type, 0 , $batch, $total);

				exit(json_encode($this->cached_vars['response']));
			}
		}
	}

	//	End lexicon

	// -----------------------------------------------------------------

	/**
	 * log()
	 *
	 * @access	public
	 * @param	string
	 * @return	view
	 */

	public function log()
	{
		$this->prep_message('', TRUE, TRUE);

		//	----------------------------------------
		//	Clearing the log?
		//	----------------------------------------

		if (ee()->input->get('clear_log') !== FALSE AND $this->check_yes(ee()->input->get('clear_log')))
		{
			ee()->db->where('site_id', ee()->config->item('site_id'))->delete('super_search_log');

			return ee()->functions->redirect($this->mcp_link(array(
				'method'	=> 'log',
				'msg'		=> 'search_log_cleared'
			)));
		}

		//	----------------------------------------
		//	Query
		//	----------------------------------------

		$logs = $this->fetch('Log')
			->filter('site_id', ee()->config->item('site_id'))
			->order('search_date', 'desc');

		//	----------------------------------------
		//	Term id
		//	----------------------------------------

		if ($this->get_post_or_zero('term_id'))
		{
			$term = $this->fetch('Term')
				->fields('term')
				->filter('site_id', ee()->config->item('site_id'))
				->filter('term_id', $this->get_post_or_zero('term_id'))
				->first();

			if ($term)
			{
				$logs->filter('term', $term->term);
			}
		}

		//	----------------------------------------
		//	Term
		//	----------------------------------------

		if (ee()->input->get('term'))
		{
			$logs->filter('term', ee()->input->get('term'));
		}

		//	----------------------------------------
		//	Start table
		//	----------------------------------------

		$tableData = array();

		//	----------------------------------------
		//	Anything?
		//	----------------------------------------

		if ($logs->count() > 0)
		{
			//	----------------------------------------
			//	Pagination
			//	----------------------------------------

			$page	= 0;

			if ($logs->count() > $this->row_limit)
			{
				$page	= $this->get_post_or_zero('page') ?: 1;

				$mcp_link_array = array(
					'method' => __FUNCTION__
				);

				$this->cached_vars['pagination'] = ee('CP/Pagination', $logs->count())
									->perPage($this->row_limit)
									->currentPage($page)
									->render($this->mcp_link($mcp_link_array, false));

				$logs->limit($this->row_limit)->offset(($page - 1) * $this->row_limit);
			}

			foreach ($logs->all() as $log)
			{
				$details	= array();

				if ($log->query)
				{
					$query	= (strpos($log->query, '{') === FALSE) ? base64_decode($log->query): $log->query;

					$query	= unserialize($query);

					$first	= TRUE;

					foreach ($query as $extra => $val)
					{
						if (! $first)
						{
							$details[]	= ', ';
						}
						else
						{
							$first = FALSE;
						}

						$details[]	= $extra . ': ';

						if (is_array($val))
						{
							foreach ($val as $subkey => $subval)
							{
								if (is_array($subval))
								{
									if (! empty($subval))
									{
										$details[]	= '['.$subkey.']=' . implode('|', $subval) . ' ';
									}
								}
								else
								{
									$details[]	= ' ' . $subval . ' ';
								}
							}
						}
						else
						{
							$details[]	= $val;
						}
					}
				}

				$tableData[] = array(
					$this->_time_elapsed_string(time() - $log->search_date),
					array(
						'content'	=> $log->term,
						'href'		=>$this->mcp_link(array(
							'method'	=> 'log',
							'term'		=> $log->term
						))
					),
					$log->results,
					implode('', $details)
				);
			}
		}

		// -------------------------------------
		//	build table
		// -------------------------------------

		$table = ee('CP/Table', array(
			'sortable'	=> false,
			'search'	=> false,
		));

		$table->setColumns(array(
			'search_date',
			'term',
			'results',
			'search_details',
		));

		$table->setData($tableData);

		$table->setNoResultsText('no_searches_recorded');

		$this->cached_vars['form_url']	= '';

		$this->cached_vars['table'] = $table->viewData(
			$this->mcp_link(array('method' => __FUNCTION__), false)
		);

		$this->cached_vars['form_right_links']		= array(
			array(
				'link' => $this->mcp_link(array('method' => 'log', 'clear_log' => 'y')),
				'title' => lang('clear_log'),
			)
		);

		//---------------------------------------------
		//  Load Page and set view vars
		//---------------------------------------------

		return $this->mcp_view(array(
			'file'		=> 'log',
			'highlight'	=> 'log',
			'pkg_css'	=> array('mcp_defaults'),
			'crumbs'	=> array(
				array(lang('log'))
			)
		));
	}

	//	End log()

	// -----------------------------------------------------------------

	/**
	 * Code pack page
	 *
	 * @access public
	 * @param	string	$message	lang line for update message
	 * @return	string				html output
	 */

	public function code_pack($message = '')
	{
		$this->prep_message($message, TRUE, TRUE);

		// --------------------------------------------
		//	Load vars from code pack lib
		// --------------------------------------------

		$codePack = $this->lib('CodePack');
		$cpl      =& $codePack;

		$cpl->autoSetLang = true;

		$cpt = $cpl->getTemplateDirectoryArray(
			$this->addon_path . 'code_pack/'
		);

		// --------------------------------------------
		//  Start sections
		// --------------------------------------------

		$sections = array();

		$main_section = array();

		// --------------------------------------------
		//  Prefix
		// --------------------------------------------

		$main_section['template_group_prefix'] = array(
			'title'		=> lang('template_group_prefix'),
			'desc'		=> lang('template_group_prefix_desc'),
			'fields'	=> array(
				'prefix' => array(
					'type'		=> 'text',
					'value'		=> $this->lower_name . '_',
				)
			)
		);

		// --------------------------------------------
		//  Templates
		// --------------------------------------------

		$main_section['templates'] = array(
			'title'		=> lang('groups_and_templates'),
			'desc'		=> lang('groups_and_templates_desc'),
			'fields'	=> array(
				'templates' => array(
					'type'		=> 'html',
					'content'	=> $this->view('code_pack_list', compact('cpt')),
				)
			)
		);

		// --------------------------------------------
		//  Compile
		// --------------------------------------------

		$this->cached_vars['sections'][] = $main_section;

		$this->cached_vars['form_url'] = $this->mcp_link(array(
			'method' => 'code_pack_install'
		));

		$this->cached_vars['box_class'] = 'code_pack_box';

		//---------------------------------------------
		//  Load Page and set view vars
		//---------------------------------------------

		// Final view variables we need to render the form
		$this->cached_vars += array(
			'base_url'				=> $this->mcp_link(array(
				'method' => 'code_pack_install'
			)),
			'cp_page_title'			=> lang('demo_templates') .
										'<br /><i>' . lang('demo_description') . '</i>' ,
			'save_btn_text'			=> 'install_demo_templates',
			'save_btn_text_working'	=> 'btn_saving'
		);

		ee('CP/Alert')->makeInline('shared-form')
		->asIssue()
		->addToBody(lang('prefix_error'))
		->cannotClose()
		->now();

		return $this->mcp_view(array(
			'file'		=> 'form',
			'highlight'	=> 'demo_templates',
			'pkg_css'	=> array('mcp_defaults'),
			'pkg_js'	=> array('code_pack'),
			'crumbs'	=> array(
				array(lang('demo_templates'))
			)
		));
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
			return ee()->functions->redirect($this->mcp_link(array(
				'method' => 'code_pack'
			)));
		}

		// -------------------------------------
		//	load lib
		// -------------------------------------

		$codePack = $this->lib('CodePack');
		$cpl      =& $codePack;

		$cpl->autoSetLang = true;

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

		$return = $cpl->installCodePack($variables);

		//--------------------------------------------
		//	Table
		//--------------------------------------------

		$table = ee('CP/Table', array(
			'sortable'	=> false,
			'search'	=> false
		));

		$tableData = array();

		//--------------------------------------------
		//	Errors or regular
		//--------------------------------------------

		if (! empty($return['errors']))
		{
			foreach ($return['errors'] as $error)
			{
				$item = array();

				//	Error
				$item[]	= lang('error');

				//	Label
				$item[]	= $error['label'];

				//	Field type
				$item[]	= str_replace(
					array(
						'%conflicting_groups%',
						'%conflicting_data%',
						'%conflicting_global_vars%'
					),
					array(
						implode(", ", $return['conflicting_groups']),
						implode("<br />", $return['conflicting_global_vars'])
					),
					$error['description']
				);

				$tableData[] = $item;
			}
		}
		else
		{
			foreach ($return['success'] as $success)
			{
				$item = array();

				//	Error
				$item[]	= lang('success');

				//	Label
				$item[]	= $success['label'];

				//	Field type
				if (isset($success['link']))
				{
					$item[]	= array(
						'content'	=> $success['description'],
						'href'		=>$success['link']
					);
				}
				else
				{
					$item[]	= str_replace(
						array(
							'%template_count%',
							'%global_vars%',
							'%success_link%'
						),
						array(
							$return['template_count'],
							implode("<br />", $return['global_vars']),
							''
						),
						$success['description']
					);
				}

				$tableData[] = $item;
			}
		}

		$table->setColumns(array(
			'status',
			'description',
			'details',
		));

		$table->setData($tableData);

		$table->setNoResultsText('no_results');

		$this->cached_vars['table'] 	= $table->viewData();

		$this->cached_vars['form_url']	= '';

		//---------------------------------------------
		//  Load Page and set view vars
		//---------------------------------------------

		return $this->mcp_view(array(
			'file'		=> 'code_pack_install',
			'highlight'	=> 'demo_templates',
			'pkg_css'	=> array('mcp_defaults'),
			'crumbs'	=> array(
				array(lang('demo_templates'))
			)
		));
	}
	//END code_pack_install


	public function install_levenshtein()
	{
		//be safe
		$sql = "DROP FUNCTION IF EXISTS LEVENSHTEIN;";
		ee()->db->query($sql);

		$sql = $this->model('Data')->define_levenshtein();
		ee()->db->query($sql);

		return $this->mcp_view(array(
			'file'		=> 'install_levenshtein',
			'pkg_css'	=> array('mcp_defaults'),
			'crumbs'	=> array(
				array(lang(''))
			)
		));
	}


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
}

// END CLASS Super Search
