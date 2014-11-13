<?php

 /**
 * Solspace - Super Search
 *
 * @package		Solspace:Super Search
 * @author		Solspace DevTeam
 * @copyright	Copyright (c) 2009-2012, Solspace, Inc.
 * @link		http://www.solspace.com/docs/addon/c/Super_Search/
 * @version		2.1.0
 * @filesource	./system/expressionengine/third_party/super_search/language/english/
 */

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
'Krachtig zoeken met ExpressionEngine',

'update_super_search_module' =>
'De Super Search add-on updaten',

'update_failure' =>
'De update is mislukt.',

'update_successful' =>
'De update is geslaagd.',

'online_documentation' => 
"Online documentatie",

//----------------------------------------
//  Main Menu
//----------------------------------------

'homepage'								=>
'Homepage',

'fields'								=>
'Velden',

'documentation'							=>
'Online documentatie',

'cache_rules'							=>
'Cacheregels',

'search_log'							=>
'Zoeklog',

'search_options'						=>
'Opties',

'search_utils'							=>
'Zoekhulp',


//----------------------------------------
//  Buttons
//----------------------------------------

'save'									=>
'Opslaan',

//----------------------------------------
//  Homepage & Global
//----------------------------------------

'success'								=>
'Gelukt!',

'lexicon_needs_building' =>
'Zoeklexicon moet samengesteld worden',

'lexicon_explain' => 
'Voor een geavanceerde zoekfunctionaliteit moeten we een sitelexicon samenstellen.',

'build_now' =>
'Nu samenstellen',

'lexicon_rebuild' => 
'Opnieuw samenstellen',

'lexicon_build' => 
'Samenstellen',

'no_searches_yet' => 
'Er zijn nog geen zoekopdrachten opgeslagen.',

'no_searches_yet_long' =>
'Er zijn nog geen zoekopdrachten opgeslagen. Misschien moet je het logboek nog <a href="%enable_link%">activeren</a>.',

'no_searches_recorded' => 
'Er zijn nog geen zoekopdrachten opgeslagen.',

'no_searches_recorded_logging_off' =>
'Logboek is uitgeschakeld. Om het logboek te gebruiken moet je het eerst nog <a href="%enable_link%">activeren</a>',

'enable_search_logging' =>
'logboek activeren',

'enable_ga_integration' =>
'<strong>Wist je dat...?</strong> Je kan SuperSearch koppelen aan Google Analytics. Details in de <a href="#">documentatie</a>.',

'top_search_terms' =>
'Meest gezochte woorden',

'search_term' =>
'Zoekwoord',

'search_count' =>
'Aantal',

'search_rank' =>
'#',

'view_all' => 
'Bekijk alles',

'recent_searches' =>
'Recente zoekopdrachten',

'datetime' => 
'Tijd',

'clear_log'	=>
'Logboek wissen',

'search_log_cleared'	=>
'Het logboek voor deze site is gewist.',



//----------------------------------------
//  Clear cache
//----------------------------------------

'cache'									=>
'Cache',

'clear_search_cache'					=>
'Gecachete zoekopdrachten wissen',

'cache_cleared'							=>
'De cache met zoekopdrachten is gewist.',

//----------------------------------------
//  Search Options
//----------------------------------------

'manage_search_utils'					=>
'Zoekhulp',

'manage_search_options'					=>
'Voorkeuren bewerken',

'ignore_common_word_list'				=>
'Bepaalde woorden negeren in zoekopdrachten?',

'ignore_common_word_list_subtext'		=>
'De woordenlijst kan volledig aangepast worden en kan per template overschreven worden met de <a href="http://www.solspace.com/docs/detail/super_search_results/#use_ignore_word_list">use_ignore_word_list</a> parameter.',

'ignore_word_list'						=>
'Te negeren woorden',


'ignore_word_list_subtext'				=>
'Indien aangevinkt zullen de volgende woorden genegeerd worden bij alle zoekopdrachten:',


'ignore_word_list_input_placeholder'	=>
'Voeg meer te negeren woorden toe en druk vervolgens op enter...',

'ignore_word_list_input_empty' 			=>
'De lijst met te negeren woorden is leeg.',


'search_logging'						=>
'Logvoorkeuren',

'log_site_searches'						=>
'Sitezoekopdrachten opslaan?',

'log_site_searches_subtext' 			=>
'Als je sitezoekopdrachten opslaat, worden die allemaal in een log bijgehouden. Om gegevens te beschermen, worden alle zoekopdrachten anoniem gemaakt. Deze optie moet op JA staan voordat de Zoeklogtab resultaten kan tonen.',

'enable_smart_excerpt'					=>
'Slimme fragmenten gebruiken?',

'enable_smart_excerpt_subtext'			=>
'Slimme fragmenten veranderen de {excerpt} variabele zodat die beperkt wordt tot de woorden rond het gezochte woord. De functie kan per template overschreven worden met de <a href="http://www.solspace.com/docs/detail/super_search_results/#smart_excerpts">smart_excerpts</a>parameter.',


'enable_fuzzy_searching'					=>
'Onduidelijke zoekopdrachten gebruiken?',

'enable_fuzzy_searching_subtext'			=>
'Onduidelijke zoekopdrachten helpt bij het zoeken naar fout gespelde woorden, meervouden en gelijkende zoekwoorden.</em>',


'enable_fuzzy_searching_plurals'			=>
'Meervouden en enkelvouden',

'enable_fuzzy_searching_plurals_subtext'			=>
'Meervouden en enkelvouden behoren tot de onduidelijke zoekopdrachten.<br/>Ex: <strong>“coat” = “coats”, “trousers” = “trouser”</strong>',


'enable_fuzzy_searching_phonetics'			=>
'Fonetisch ',

'enable_fuzzy_searching_phonetics_subtext'			=>
'Er wordt ook gezocht op fonetisch gelijkende woorden <em>(enkel voor Engels)</em>.<br/>Ex:  <strong>“Nolton” = “Noulton”</strong>',


'enable_fuzzy_searching_spelling'			=>
'Spelling ',

'enable_fuzzy_searching_spelling_subtext'			=>
'Foute spelling wordt herkend en er wordt geprobeerd ze te verbeteren. Het algoritme leert automatisch en baseert de volgorde van zijn suggesties op de inhoud van je site. Na verloop van tijd zal het beter op je inhoud afgestemd geraken.  <br/>Ex: <strong>“Sceince” = “Science”</strong>',


//----------------------------------------
//	Lexicon
//----------------------------------------

'manage_lexicon'						=>
'Lexicon',

'lexicon' =>
'Lexicon',

'build_search_lexicon' =>
'Stel zoeklexicon samen',

'search_lexicon_explain' =>
'Het zoeklexicon bouwt een gecombineerde dataset op van al de unieke termen in je sites. <br/>
Met die data kunnen we onduidelijke zoekopdrachten, verbeteringen en een betere behandeling van zoekopdrachten mogelijk maken.
<br/>
De eerste keer kan eventjes duren, maar dat is maar een keer nodig.',
	
'built_just_now' =>
'Lexicon werd net gebouwd',

'build_in_progress' =>
'Bezig',

'build_complete' =>
'Voltooid',

'lexicon_last_built' =>
'Laatste keer opgebouwd op ',

'lexicon_never_built' =>
'Het lexicon werd nog nooit opgebouwd',


//----------------------------------------
//	Suggestions
//----------------------------------------


'suggestions' =>
'Spellingsuggesties',


'build_suggestions_corpus' =>
'Bouw spellingsuggesties op',

'suggestions_explain' =>
'We bouwen een lijst op met suggesties voor woorden waarop al gezocht werd, maar die niet in je lexicon staan. We proberen ook de meest voordehandliggende varianten op die termen te vinden. De suggesties worden vervolgens gecachet voor gebruik in normale zoekopdrachten. <br/><br/> 
	Suggesties kunnen indien nodig automatisch opgebouwd worden tijdens zoekopdrachten, maar dat zal de opzoeking enigszins vertragen telkens wanneer een nieuwe unieke term vereist is. <a href="#">Usage ###NEEDS PROPER LINK###</a><br/>
	 De beste manier om zoeksuggesties te onderhouden, is door een cron job "the a url"? te laten uitvoeren die automatisch nieuwe suggesties zal aanmaken indien nodig.<a href="#">Usage ###NEEDS PROPER LINK###</a>',

'spelling_unknown_line' =>
'<strong>%i% Unknown Term%s%</strong> om suggesties voor te vinden',


'spelling_known_line' =>
'<strong>%i% Known Term%s%</strong> met suggesties',



//----------------------------------------
//	Fields
//----------------------------------------

'custom_field_group' =>
'Groep met velden die je zelf kan kiezen',

'no_fields'								=>
'Er zijn voor deze site geen velden die je zelf kan kiezen.',

'no_fields_for_group'					=>
'Er zijn voor deze groep geen velden die je zelf kan kiezen.',

'id'									=>
'ID',

'name'									=>
'Naam',

'label'									=>
'Label',

'type'									=>
'Type',

'length'								=>
'Lengte',

'precision'								=>
'Aantal decimalen',

'edit_field'							=>
'Veld wijzigen',

'field_explanation'						=>
'Hiermee kan je op je site de MySQL-datatypes van de zelf te kiezen velden controleren. Je kan de kracht van de site verbeteren door het type MySQL-veld te veranderen zodat je enkel de ruimte gebruikt die nodig is voor je data. Als een van je velden enkel uit cijfers zal bestaan, kies dan een MySQL-veld dat numeriek in plaats van alfabetisch sorteert.',

'character_explanation'					=>
'In een character of char field zitten kleine alfanumerieke tekenreeksen. Gebruik een char field om velden met eenvoudige waarden op te slaan, zoals \'ja\', \'nee\', \'j\', \'n\'',

'integer_explanation'					=>
'Een integer field kan gehele getallen bevatten. Ze zijn groter dan small en tiny integer fields en vereisen meer geheugen.',

'float_explanation'						=>
'Een float field is het beste veld om te gebruiken als je decimale waarden wilt opslaan. Je kan zowel de totale lengte van het veld preciseren als het aantal decimalen. Velden van dit type zijn bedoeld om prijzen op te slaan die numeriek geordend kunnen worden.',

'decimal_explanation'						=>
'Een decimal field is een goed veld om te gebruiken als je decimale waarden wilt opslaan, bijvoorbeeld geldbedragen. Je kan zowel de totale lengte van het veld preciseren als het aantal decimalen.',

'precision_explanation'					=>
'Aantal decimalen geeft het aantal decimalen aan om voor te behouden voor een zwevendekommagetal.',

'small_integer_explanation'				=>
'Een small integer field is kleiner dan een integer field en groter dan een tiny integer field. De meeste getallen kunnen in dit type veld opgeslagen worden.',

'text_explanation'						=>
'Een text field is een van de grootste MySQL-velden. Ze kunnen grote hoeveelheden tekst of numerieke data bevatten. Gebruik dit type enkel als je grote tekstblokken gaat opslaan.',

'tiny_integer_explanation'				=>
'Een tiny integer field is het kleinste type veld. Sla enkel heel kleine getallen op in tiny integer fields.',

'varchar_explanation'					=>
'Een varchar field is een van de meestgebruikte MySQL-velden. Het kan vrij lange tekenreeksen aan, maar neemt niet zoveel ruimte in als een tekstveld.',

'field_length_required'					=>
'Gelieve een lengte aan te geven voor je veld.',

'char_length_incorrect'					=>
'De lengte van een character field moet tussen 1 en 255 liggen.',

'float_length_incorrect'				=>
'De lengte van een float field mag niet kleiner zijn dan 1.',

'precision_length_incorrect'			=>
'De lengte van een float field moet langer zijn dan zijn aantal decimalen.',

'integer_length_incorrect'				=>
'De lengte van een integer field moet tussen 1 en 4294967295 liggen.',

'small_integer_length_incorrect'		=>
'De lengte van een small integer field moet tussen 1 en 65535 liggen.',

'tiny_integer_length_incorrect'			=>
'De lengte van een tiny integer field length moet tussen 1 en 255 liggen.',

'varchar_length_incorrect'				=>
'De lengte van een varchar field moet tussen 1 en 255 liggen.',

'edit_confirm'							=>
'Bevestig veranderingen aan het veld.',

'edit_field_question'					=>
'Je gaat een veld wijzigen. Ben je zeker dat je wilt verdergaan?',

'edit_field_question_truncate'			=>
'Door naar het andere type veld te converteren, kan het zijn dat data worden afgerond of verwijderd. De wijzigingen kunnen niet ongedaan gemaakt worden. Ben je zeker dat je wilt verdergaan?',

'field_edited_successfully'				=>
'De wijziging aan je veld is geslaagd.',

//----------------------------------------
//	Preferences
//----------------------------------------

'preferences'	=>
'Voorkeuren',

'preferences_exp'	=>
'Op deze pagina kan je voorkeuren voor Super Search controleren.',

'preferences_not_available'	=>
'Voor deze module zijn nog geen voorkeuren beschikbaar.',

'preferences_updated'	=>
'Voorkeuren geüpdatet',

'allow_keyword_search_on_playa_fields'	=>
'Zoeken op trefwoord toestaan in Playa fields?',

'allow_keyword_search_on_playa_fields_exp'	=>
'Zoeken op trefwoord in Playa fields kan tot verwarrende zoekresultaten leiden. Schakel deze optie enkel in als je wilt zoeken op trefwoord in titels van lemma\'s die te maken hebben met een bepaald lemma.',

'allow_keyword_search_on_relationship_fields'	=>
'Zoeken op trefwoord toestaan in Relationship fields?',

'allow_keyword_search_on_relationship_fields_exp'	=>
'Zoeken op trefwoord in standaard EE Relationship fields kan tot verwarrende zoekresultaten leiden. Schakel deze optie enkel in als je wilt zoeken op trefwoord in titels van lemma\'s die te maken hebben met een bepaald lemma.',

'yes'	=>
'Ja',

'no'	=>
'Nee',

//----------------------------------------
//	Caching rules
//----------------------------------------

'manage_caching_rules' =>
'Cacheregels beheren',

'current_cache' =>
'Huidige cache',

'refresh' =>
'Refresh',

'refresh_rules' =>
'Refreshregels',

'refresh_explanation' =>
'Als deze waarde op "0" staat, zal de zoekcache enkele gerefresht worden door de regels die hieronder per channel of template ingesteld zijn.',

'template_refresh' =>
'Templaterefresh',

'template_refresh_explanation' =>
'Als een van deze gekozen templates gewijzigd wordt, zal de zoekcache gerefresht worden.',

'weblog_refresh' =>
'Weblogrefresh',

'weblog_refresh_explanation' =>
'Als op een van deze weblogs een lemma gepubliceerd of gewijzigd wordt, zal de zoekcache gerefresht worden.',

'channel_refresh' =>
'Channelrefresh',

'channel_refresh_explanation' =>
'Als op een van deze channels een lemma gepubliceerd of gewijzigd wordt, zal de zoekcache gerefresht worden.',

'category_refresh' =>
'Categoryrefresh',

'category_refresh_explanation' =>
'Als er een categorie wordt aangemaakt of gewijzigd in een van deze categorieëngroepen, zal de zoekcache gerefresht worden.',

'rows' =>
'rijen',

'refresh_now' =>
'Refresh nu',

'next_refresh' =>
'(Volgende refresh: %n%)',

'in_minutes' =>
'(in minuten)',

'name_required' =>
'Geef een naam.',

'name_invalid' =>
'De opgegeven naam is ongeldig.',

'numeric_refresh' =>
'Het refreshinterval moet numeriek zijn.',

'refresh_rule_updated' =>
'Je cacheregels zijn geüpdated en je cache is gerefresht.',

//----------------------------------------
//  Update Page
//----------------------------------------

'update_super_search'					=>
'Update Super Search',

'super_search_module_disabled'	=>
'Super Search lijkt niet geïnstalleerd te zijn op deze website. Neem contact op met de beheerder van de website.',

'super_search_module_out_of_date'	=>
'De Super Search module op deze website lijkt niet up-to-date. Neem contact op met de beheerder van de website.',

'super_search_update_message'	=>
'Je hebt onlangs een nieuwe versie van Super Search geüploaded, gelieve hier te klikken om het updatescript te laten lopen.',

'update_successful'						=>
'Update geslaagd!',



//----------------------------------------
//	Search Log Page
//----------------------------------------

'period_year'					=>
'jaar',

'period_month'					=>
'maand',

'period_day'					=>
'dag',

'period_hour'					=>
'uur',

'period_min'					=>
'minuut',

'period_sec'					=>
'seconde',

'period_postfix'				=>
's',

'period_ago'					=>
'geleden',

'period_now'					=>
'nu',


'filter_searches'				=>
'Filter zoekopdrachten',

'terms'	=>
'typ hier een zoekterm ...',

'filter' =>
'Filter',

'no_searches_contained' => 
'Geen enkele zoekopdracht bevatte',

'filtering_terms_like' => 
'Filter termen als',

'filtering_term' => 
'Term wordt gefilterd',

'search_term' => 
'Zoekterm',

'searches_over_90' =>
'Zoekopdrachten van de laatste 90 dagen die de term bevatten',

'count' => 
'Tel',

'first_searched' =>
'Eerst gezocht',

'most_recent_search' => 
'Meest recente zoekopdracht',

'term_searches_in_last_90' =>
'Zoekopdrachten op de term in the laatste 90 dagen',

'searches_containing' =>
'zoekopdrachten die bevatten',

'all_searches' => 
'Alle zoekopdrachten',

'searches' => 
'zoekopdrachten',

'searched_term' =>
'Gezochte term',

'date' =>
'Datum',

'site' =>
'Site',

'more' =>
'Zoekdetails',

'ditto' =>
'&#12291;',

//----------------------------------------
//	Front-end search
//----------------------------------------

'search_not_allowed'					=>
'Zoeken is niet toegestaan.',

//----------------------------------------
//	Front-end search saving
//----------------------------------------

'search'	=>
'Zoek',

'search_not_found'	=>
'Je zoekopdracht kan niet gevonden worden.',

'missing_name'	=>
'Gelieve een naam te geven aan je zoekopdracht.',

'duplicate_name'	=>
'Die naam is al in gebruik voor een opgeslagen zoekopdracht.',

'invalid_name'	=>
'De zoeknaam die je opgaf, is ongeldig.',

'duplicate_search'	=>
'Je hebt deze zoekopdracht al opgeslagen.',

'search_already_saved'					=>
'Je hebt de aangegeven zoekopdracht al opgeslagen.',

'search_successfully_saved'				=>
'Je zoekopdracht is goed opgeslagen.',

'search_successfully_unsaved'			=>
'Je zoekopdracht werd correct geünsaved',

'search_already_unsaved'				=>
'Je hebt de aangegeven zoekopdracht al geünsaved',

'search_successfully_deleted'			=>
'Je zoekopdracht werd correct verwijderd',

'no_search_history_was_found'	=>
'Er werd voor jou geen zoekgeschiedenis gevonden',

'last_search_cleared'	=>
'Je laatste zoekopdracht is gewist.',

'site_switcher' => 
'Kies site',

'field_group_switcher' => 
'Kies veldengroep',

/* END */
''=>''
);
?>
