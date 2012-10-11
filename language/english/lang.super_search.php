<?php

$lang = $L = array(

//----------------------------------------
// Required for MODULES page
//----------------------------------------

'super_search_module_name'				=>
'Super Search',

'super_search_label'					=>
'Super Search',

'super_search_module_version'			=>
'Super Search',

'super_search_module_description'		=>
'Powerful search for ExpressionEngine',

'update_super_search_module' =>
'Update the Super Search add-on',

'update_failure' =>
'The update was not successful.',

'update_successful' =>
'The update was successful',

'online_documentation' => 
"Online Documentation",

//----------------------------------------
//  Main Menu
//----------------------------------------

'homepage'								=>
'Homepage',

'fields'								=>
'Fields',

'documentation'							=>
'Online Documentation',

'cache_rules'							=>
'Cache Rules',

'search_log'							=>
'Search Log',

'search_options'						=>
'Options',

'search_utils'							=>
'Utilities',


//----------------------------------------
//  Buttons
//----------------------------------------

'save'									=>
'Save',

//----------------------------------------
//  Homepage & Global
//----------------------------------------

'success'								=>
'Success!',

'lexicon_needs_building' =>
'Search Lexicon Needs Building',

'lexicon_explain' => 
'For advanced search functionality we need to build a site lexicon.',

'build_now' =>
'Build it now',

'lexicon_rebuild' => 
'Rebuild',

'lexicon_build' => 
'Build',

'no_searches_yet' => 
'No searches have been recorded yet.',

'no_searches_yet_long' =>
'No searches have been recorded yet. You may need to <a href="%enable_link%">enable search logging</a>.',

'no_searches_recorded' => 
'There have been no searches logged yet.',

'no_searches_recorded_logging_off' =>
'Search Logging is disabled. To use the search log <a href="%enable_link%">enable search logging</a>',

'enable_search_logging' =>
'enable search logging',

'enable_ga_integration' =>
'<strong>Did you know?</strong> You can hook up SuperSearch to work with Google Analytics. Full details in the <a href="#">Docs</a>.',

'top_search_terms' =>
'Top Search Terms',

'search_term' =>
'Search Term',

'search_count' =>
'Count',

'search_rank' =>
'#',

'view_all' => 
'View All',

'recent_searches' =>
'Recent Searches',

'datetime' => 
'Time',

'clear_log'	=>
'Clear Log',

'search_log_cleared'	=>
'The search log has been cleared for this site.',



//----------------------------------------
//  Clear cache
//----------------------------------------

'cache'									=>
'Cache',

'clear_search_cache'					=>
'Clear cached searches',

'cache_cleared'							=>
'The search cache was successfully cleared.',

//----------------------------------------
//  Search Options
//----------------------------------------

'manage_search_utils'					=>
'Search Utilites',

'manage_search_options'					=>
'Manage Preferences',

'ignore_common_word_list'				=>
'Ignore common words from searches?',

'ignore_common_word_list_subtext'		=>
'The wordlist is completely editable, and can be overridden at template level with the <a href="http://www.solspace.com/docs/detail/super_search_results/#use_ignore_word_list">use_ignore_word_list</a> parameter.',

'ignore_word_list'						=>
'Ignore Wordlist',


'ignore_word_list_subtext'				=>
'If enabled, the following words will be ignored in any searches:',


'ignore_word_list_input_placeholder'	=>
'add extra words to ignore, then hit enter ...',

'ignore_word_list_input_empty' 			=>
'The ignore word list is empty.',


'search_logging'						=>
'Logging Preferences',

'log_site_searches'						=>
'Log Site Searches?',

'log_site_searches_subtext' 			=>
'Search logging keeps permanent record of all site searches. For data-protection, all records are anonymized. This preference must be set to YES for the Search Log tab to show data.',

'enable_smart_excerpt'					=>
'Use Smart Excerpts?',

'enable_smart_excerpt_subtext'			=>
'Smart Excerpts alter the {excerpt} variable to truncate around the searched terms. This can be overridden at template level with the <a href="http://www.solspace.com/docs/detail/super_search_results/#smart_excerpts">smart_excerpts</a> parameter.',


'enable_fuzzy_searching'					=>
'Use Fuzzy Searching?',

'enable_fuzzy_searching_subtext'			=>
'Fuzzy searching helps with misspelled, pluralized, and similar search terms.</em>',


'enable_fuzzy_searching_plurals'			=>
'Plurals and Singulars',

'enable_fuzzy_searching_plurals_subtext'			=>
'Plurals and singlulars are fuzzies <em>(English language specific)</em>.<br/>Ex: <strong>“coat” = “coats”, “trowsers” = “trowser”</strong>',


'enable_fuzzy_searching_phonetics'			=>
'Phonetics ',

'enable_fuzzy_searching_phonetics_subtext'			=>
'Phonetically similar words are also searched <em>(English language specific)</em>.<br/>Ex:  <strong>“Nolton” = “Noulton”</strong>',


'enable_fuzzy_searching_spelling'			=>
'Spelling ',

'enable_fuzzy_searching_spelling_subtext'			=>
'Misspellings are identified and an attempt to correct them is made. The algorithm automatically learns and ranks its suggestions based on the content within your site. Over time it will become better tuned to your specific content. <br/>Ex: <strong>“Sceince” = “Science”</strong>',


//----------------------------------------
//	Lexicon
//----------------------------------------

'manage_lexicon'						=>
'Lexicon',

'lexicon' =>
'Lexicon',

'build_search_lexicon' =>
'Build Search Lexicon',

'search_lexicon_explain' =>
'The search lexicon builds a combined dataset of all the unique terms across your sites. <br/>
With this data we can enable fuzzy searches, search corrections and better search term handling.<br/>
The first run may take some time, but only needs to run once.',
	
'built_just_now' =>
'Lexicon built just now',

'build_in_progress' =>
'In Progress',

'build_complete' =>
'Complete',

'lexicon_last_built' =>
'Last built on ',

'lexicon_never_built' =>
'Lexicon has never been built',


//----------------------------------------
//	Suggestions
//----------------------------------------


'suggestions' =>
'Spelling Suggestions',


'build_suggestions_corpus' =>
'Build Spelling Suggestions',

'suggestions_explain' =>
'We build up a suggestion set for words that have been searched on, but don\'t exist in you leixcon and try and find the most likely variation for these terms. The suggestions are then cached for use in normal searches. <br/><br/> 
	Suggestions can automatically be built as needed during searches, but this will incur a delay in the search handling the first time a new unique term is required. <a href="#">Usage ###NEEDS PROPER LINK###</a><br/>
	 The recommended way to handle search suggestions is to have a cron job hit the a url which will calculate any new suggestions requried. <a href="#">Usage ###NEEDS PROPER LINK###</a>',

'spelling_unknown_line' =>
'<strong>%i% Unknown Term%s%</strong> to find suggestions for',


'spelling_known_line' =>
'<strong>%i% Known Term%s%</strong> with suggestions',



//----------------------------------------
//	Fields
//----------------------------------------

'custom_field_group' =>
'Custom Field Group',

'no_fields'								=>
'There are no custom fields for this site.',

'no_fields_for_group'					=>
'There are no custom fields for this group.',

'id'									=>
'ID',

'name'									=>
'Name',

'label'									=>
'Label',

'type'									=>
'Type',

'length'								=>
'Length',

'precision'								=>
'Precision',

'edit_field'							=>
'Edit Field',

'field_explanation'						=>
'This tool allows you to control the MySQL data types of the custom fields in your site. You can improve site performance by changing MySQL field types to use only the amount of space necessary for your data. As well, if one of your fields will contain only numbers, choose a MySQL field type that supports sorting data numerically instead of alphabetically.',

'character_explanation'					=>
'A character or char field contains small alphanumeric strings. Use a character field to store fields with simple values like \'yes\', \'no\', \'y\', \'n\'',

'integer_explanation'					=>
'An integer field can contain whole numbers. They are larger than small integer or tiny integer field types and takes up more memory.',

'float_explanation'						=>
'A float field is the best field to use if you will be storing decimal values. You can specify the total length of the field as well as the decimal precision. Fields of this type are intended for storing prices that can be sorted numerically.',

'decimal_explanation'						=>
'A decimal field is a good field to use if you will be storing decimal values, for example monetary amounts. You can specify the total length of the field as well as the decimal precision.',

'precision_explanation'					=>
'The precision value indicates the number of decimal places to reserve for a floating point number.',

'small_integer_explanation'				=>
'A small integer field is smaller than an integer field and larger than a tiny integer field. Most numbers can be stored in this type of field.',

'text_explanation'						=>
'A text field is one of the largest MySQL field types. They can contain large amounts of text or numeric data. Only if you will be storing large blocks of text should you use this field type.',

'tiny_integer_explanation'				=>
'A tiny integer field is the smallest field type. Store only very small numbers in tiny integer fields.',

'varchar_explanation'					=>
'A varchar field is one of the most commonly used types of MySQL fields. It can contain fairly long strings but not take up the large amount of space that a text field will.',

'field_length_required'					=>
'Please indicate a length for your field.',

'char_length_incorrect'					=>
'A character field length must be between 1 and 255.',

'float_length_incorrect'				=>
'A float field length must not be less than 1.',

'precision_length_incorrect'			=>
'A float field length must be larger than its decimal precision.',

'integer_length_incorrect'				=>
'An integer field length must be between 1 and 4294967295.',

'small_integer_length_incorrect'		=>
'A small integer field length must be between 1 and 65535.',

'tiny_integer_length_incorrect'			=>
'A tiny integer field length must be between 1 and 255.',

'varchar_length_incorrect'				=>
'A varchar field length must be between 1 and 255.',

'edit_confirm'							=>
'Confirm changes to field.',

'edit_field_question'					=>
'You are about to edit a field. Are you sure you want to proceed?',

'edit_field_question_truncate'			=>
'Because of the field type that you are converting to, there may be truncation or removal of data. The changes cannot be undone. Are you sure you want to proceed?',

'field_edited_successfully'				=>
'Your field was successfully edited.',

//----------------------------------------
//	Preferences
//----------------------------------------

'preferences'	=>
'Preferences',

'preferences_exp'	=>
'Preferences for Super Search can be controlled on this page.',

'preferences_not_available'	=>
'Preferences are not yet available for this module.',

'preferences_updated'	=>
'Preferences Updated',

'allow_keyword_search_on_playa_fields'	=>
'Allow keyword searching on Playa fields?',

'allow_keyword_search_on_playa_fields_exp'	=>
'Keyword searching on Playa fields can lead to confusing search results. Only if you want to keyword search the titles of entries related to a given entry should you enable this setting.',

'allow_keyword_search_on_relationship_fields'	=>
'Allow keyword searching on Relationship fields?',

'allow_keyword_search_on_relationship_fields_exp'	=>
'Keyword searching on native EE relationship fields can lead to confusing search results. Only if you want to keyword search the titles of entries related to a given entry should you enable this setting.',

'yes'	=>
'Yes',

'no'	=>
'No',

//----------------------------------------
//	Caching rules
//----------------------------------------

'manage_caching_rules' =>
'Manage Caching Rules',

'current_cache' =>
'Current Cache',

'refresh' =>
'Refresh',

'refresh_rules' =>
'Refresh Rules',

'refresh_explanation' =>
'Leaving this value at “0” will cause the search cache to only be refreshed by the channel or template update rules below.',

'template_refresh' =>
'Template Refresh',

'template_refresh_explanation' =>
'When one of these chosen templates is edited, the search cache will be refreshed.',

'weblog_refresh' =>
'Weblog Refresh',

'weblog_refresh_explanation' =>
'When an entry is published or edited in one of these weblogs, the search cache will be refreshed.',

'channel_refresh' =>
'Channel Refresh',

'channel_refresh_explanation' =>
'When an entry is published or edited in one of these channels, the search cache will be refreshed.',

'category_refresh' =>
'Category Refresh',

'category_refresh_explanation' =>
'When a category is created or edited in one of these category groups, the search cache will be refreshed.',

'rows' =>
'rows',

'refresh_now' =>
'Refresh now',

'next_refresh' =>
'(Next refresh: %n%)',

'in_minutes' =>
'(in minutes)',

'name_required' =>
'A name is required.',

'name_invalid' =>
'The name you provided is invalid.',

'numeric_refresh' =>
'The refresh interval must be numeric.',

'refresh_rule_updated' =>
'Your Caching rules have been updated and your cache has been refreshed.',

//----------------------------------------
//  Update Page
//----------------------------------------

'update_super_search'					=>
'Update Super Search',

'super_search_module_disabled'	=>
'Super Search does not appear to be installed on this website. Please contact the site administrator.',

'super_search_module_out_of_date'	=>
'The Super Search module on this website does not to appear to be up to date. Please contact the site administrator.',

'super_search_update_message'	=>
'You have recently uploaded a new version of Super Search, please click here to run the update script.',

'update_successful'						=>
'Update Successful!',



//----------------------------------------
//	Search Log Page
//----------------------------------------

'period_year'					=>
'year',

'period_month'					=>
'month',

'period_day'					=>
'day',

'period_hour'					=>
'hour',

'period_min'					=>
'minute',

'period_sec'					=>
'second',

'period_postfix'				=>
's',

'period_ago'					=>
'ago',

'period_now'					=>
'just now',


'filter_searches'				=>
'Filter Searches',

'terms'	=>
'type a search term here ...',

'filter' =>
'Filter',

'no_searches_contained' => 
'No searches contained',

'filtering_terms_like' => 
'Filtering terms like',

'filtering_term' => 
'Filtering Term',

'search_term' => 
'Search Term',

'searches_over_90' =>
'Searches including term over last 90 days',

'count' => 
'Count',

'first_searched' =>
'First Searched',

'most_recent_search' => 
'Most Recent Search',

'term_searches_in_last_90' =>
'Term searches in the last 90 days',

'searches_containing' =>
'searches containing',

'all_searches' => 
'All searches',

'searches' => 
'searches',

'searched_term' =>
'Searched Term',

'date' =>
'Date',

'site' =>
'Site',

'more' =>
'Search Details',

'ditto' =>
'&#12291;',

//----------------------------------------
//	Front-end search
//----------------------------------------

'search_not_allowed'					=>
'You are not allowed to search.',

//----------------------------------------
//	Front-end search saving
//----------------------------------------

'search'	=>
'Search',

'search_not_found'	=>
'Your search could not be found.',

'missing_name'	=>
'Please provide a name for your search.',

'duplicate_name'	=>
'That name has already been used for a saved search.',

'invalid_name'	=>
'The search name you have provided is not valid.',

'duplicate_search'	=>
'You have already saved this search.',

'search_already_saved'					=>
'You have already saved the indicated search.',

'search_successfully_saved'				=>
'Your search has been successfully saved.',

'search_successfully_unsaved'			=>
'Your search has been successfully un-saved.',

'search_already_unsaved'				=>
'You have already un-saved the indicated search.',

'search_successfully_deleted'			=>
'Your search has been successfully deleted.',

'no_search_history_was_found'	=>
'No search history was found for you.',

'last_search_cleared'	=>
'Your last search has been cleared.',

'site_switcher' => 
'Site Switcher',

'field_group_switcher' => 
'Field Group Switcher',

/* END */
''=>''
);
?>
