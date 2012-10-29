<?php

 /**
 * Solspace - Super Recherche
 *
 * @package		Solspace:Super Recherche
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
'Super Search / Super Recherche',

'super_search_label'					=>
'Super Recherche',

'super_search_module_version'			=>
'Super Recherche',

'super_search_module_description'		=>
'Un puissant module de recherche pour ExpressionEngine',

'update_super_search_module' =>
'Mettre à jour Super Recherche',

'update_failure' =>
'La mise à jour a échoué.',

'update_successful' =>
'La mise à jour a été réalisée avec succès.',

'online_documentation' =>
"Documentation en ligne",

//----------------------------------------
//  Main Menu
//----------------------------------------

'homepage'								=>
'Page d\'accueil',

'fields'								=>
'Champs',

'documentation'							=>
'Documentation en ligne',

'cache_rules'							=>
'Règles de cache',

'search_log'							=>
'Log de recherche',

'search_options'						=>
'Options',

'search_utils'							=>
'Utilitaires',


//----------------------------------------
//  Buttons
//----------------------------------------

'save'									=>
'Enregistrer',

//----------------------------------------
//  Homepage & Global
//----------------------------------------

'success'								=>
'Succès !',

'lexicon_needs_building' =>
'Le lexique de recherche doit être construit',

'lexicon_explain' =>
'Afin de bénéficier de la fonction de recherche avancée, nous devons d\'abord construire un lexique du site.',

'build_now' =>
'Construisez-le maintenant',

'lexicon_rebuild' =>
'Reconstruire',

'lexicon_build' =>
'Construire',

'no_searches_yet' =>
'Aucune recherche n\'a encore été enregistrée.',

'no_searches_yet_long' =>
'Aucune recherche n\'a encore été enregistrée. Vous devrez peut-être <a href="%enable_link%">activer la journalisation de la recherche</a>.',

'no_searches_recorded' =>
'Aucune recherche n\'a encore été journalisée.',

'no_searches_recorded_logging_off' =>
'La journalisation de la recherche est désactivée. Pour utiliser la journalisation de la recherche <a href="%enable_link%">activer la journalisation de la recherche</a>',

'enable_search_logging' =>
'activer la journalisation de la recherche',

'enable_ga_integration' =>
'<strong>Le saviez-vous ?</strong> Vous pouvez utiliser un point d\'entrée de Super Recherche afin qu\'il travaille de concert avec Google Analytics. Tous les détails sont dans la <a href="#">Documentation</a>.', /** needs proper link */

'top_search_terms' =>
'Principaux termes recherchés',

'search_term' =>
'Terme recherché',

'search_count' =>
'Compte',

'search_rank' =>
'#',

'view_all' =>
'Tout Voir',

'recent_searches' =>
'Recherches récentes',

'datetime' =>
'Horodatage',

'clear_log'	=>
'Effacer le log',

'search_log_cleared'	=>
'Le log de recherche a été effacé pour ce site.',



//----------------------------------------
//  Clear cache
//----------------------------------------

'cache'									=>
'Cache',

'clear_search_cache'					=>
'Effacer les recherches en cache',

'cache_cleared'							=>
'Le cache de recherche a été effacé avec succès.',

//----------------------------------------
//  Recherche Options
//----------------------------------------

'manage_search_utils'					=>
'Utilitaires de recherche',

'manage_search_options'					=>
'Gérer les préférences',

'ignore_common_word_list'				=>
'Ne pas prendre en compte les mots communs dans les recherches ?',

'ignore_common_word_list_subtext'		=>
'La liste des mots communs est complètement modifiable et peut être ignorée au niveau modèle grâce au paramètre <a href="http://www.solspace.com/docs/detail/super_search_results/#use_ignore_word_list">use_ignore_word_list</a>.',

'ignore_word_list'						=>
'Liste des mots ignorés',


'ignore_word_list_subtext'				=>
'Si activé, les mots suivants ne seront jamais pris en compte dans les recherches :',


'ignore_word_list_input_placeholder'	=>
'ajouter d\'autres mots à ignorer, puis appuyer sur Entrée...',

'ignore_word_list_input_empty' 			=>
'La liste de mots à ignorer est vide.',


'search_logging'						=>
'Préférences de journalisation',

'log_site_searches'						=>
'Journaliser les recherches du site ?',

'log_site_searches_subtext' 			=>
'La journalisation des recherches permet de conserver un enregistrement permanent de toutes les recherches sur le site. Tous les enregistrements sont effectués de façon anonyme par souci de confidentialité et de protection de la vie privée. Afin de visualiser les données dans l\'onglet Log de recherche, cette préférence doit être mise à OUI.',

'enable_smart_excerpt'					=>
'Utiliser des extraits intelligents ?',

'enable_smart_excerpt_subtext'			=>
'Les extraits intelligents modifient la variable {excerpt} pour tronquer autour des termes de recherche. Ceci peut être ignoré au niveau modèle avec le paramètre <a href="http://www.solspace.com/docs/detail/super_search_results/#smart_excerpts">smart_excerpts</a>.',


'enable_fuzzy_searching'					=>
'Utiliser la recherche en logique floue ?',

'enable_fuzzy_searching_subtext'			=>
'La recherche en logique floue est un plus pour traiter les termes de recherche similaires, mal orthographiés ou les formes plurielles.', /** deleted </em> tag at end of sentence */


'enable_fuzzy_searching_plurals'			=>
'Pluriels et singuliers',

'enable_fuzzy_searching_plurals_subtext'			=>
'Les pluriels et singuliers sont des formes \'floues\' <em>(spécifique à certaines langues)</em>.<br/>Ex: <strong>“manteau” = “manteaux”, “pantalons” = “pantalon”</strong>',


'enable_fuzzy_searching_phonetics'			=>
'Phonétique ',

'enable_fuzzy_searching_phonetics_subtext'			=>
'Les mots phonétiquement proches sont également recherchés <em>(spécifique à certaines langues)</em>.<br/>Ex:  <strong>“Nolton” = “Noulton”</strong>',


'enable_fuzzy_searching_spelling'			=>
'Orthographe ',

'enable_fuzzy_searching_spelling_subtext'			=>
'Les fautes d\'orthographe sont identifiées et une tentative de correction est réalisée. L\'algorithme s\'éduque automatiquement et classe ses suggestions selon le contenu de votre site. Avec le temps, il s\'adaptera mieux à votre contenu spécifique. <br/>Ex: <strong>“Sceince” = “Science”</strong>',


//----------------------------------------
//	Lexique
//----------------------------------------

'manage_lexicon'						=>
'Lexique',

'lexicon' =>
'Lexique',

'build_search_lexicon' =>
'Construire le lexique de recherche',

'search_lexicon_explain' =>
'Le lexique de recherche construit une base de données combinée de tous les termes uniques présents sur vos sites. <br/>
Grâce à cette base on peut mettre en œuvre les recherches floues, les corrections de recherche et mieux gérer les termes de recherche.<br/>
La première exécution peut prendre un certain temps, mais elle ne devra tourner qu\'une seule fois.',

'built_just_now' =>
'Lexique construit à l\'instant',

'build_in_progress' =>
'En Cours',

'build_complete' =>
'Terminé',

'lexicon_last_built' =>
'Dernière construction le ',

'lexicon_never_built' =>
'Le lexique n\'a jamais été construit',


//----------------------------------------
//	Suggestions
//----------------------------------------


'suggestions' =>
'Suggestions d\'orthographe',


'build_suggestions_corpus' =>
'Construire les suggestions d\'orthographe',

'suggestions_explain' =>
'Nous construisons une base de suggestions pour les mots qui ont été recherchés mais qui n\'existe pas encore dans votre lexique, puis nous essayons de définir les variations les plus probables de ces termes. Les suggestions sont alors mises en cache afin d\'être utilisées dans les recherches standards. <br/><br/>
	La base de suggestions peut être automatiquement construite au fur et à mesure durant les recherches, mais cela engendrera un délai dans le traitement de la recherche la première fois qu\'un nouveau terme unique sera requis. <a href="#">Usage ###NEEDS PROPER LINK###</a><br/>
	 Pour traiter les suggestions de recherche, le mode recommandé est de faire tourner un travail Cron qui appellera l\'URL, ce qui permettra de calculer toutes les nouvelles suggestions requises. <a href="#">Usage ###NEEDS PROPER LINK###</a>',

'spelling_unknown_line' =>
'<strong>%i% Terme%s% Inconnu(s)</strong> pour pouvoir proposer la moindre suggestion',


'spelling_known_line' =>
'<strong>%i% Terme%s% Connu(s)</strong> avec des suggestions',



//----------------------------------------
//	Fields
//----------------------------------------

'custom_field_group' =>
'Groupe de champ personnalisé',

'no_fields'								=>
'Il n\'y a pas de champ personnalisé pour ce site.',

'no_fields_for_group'					=>
'Il n\'y a pas de champ personnalisé pour ce groupe.',

'id'									=>
'ID',

'name'									=>
'Nom',

'label'									=>
'Label',

'type'									=>
'Type',

'length'								=>
'Longueur',

'precision'								=>
'Précision',

'edit_field'							=>
'Éditer le Champ',

'field_explanation'						=>
'Cet outil vous permet de contrôler les types de données MySQL des champs personnalisés de votre site. Vous pouvez améliorer les performances de vos sites en modifiant les types de champ MySQL de façon à ce qu\'ils utilisent uniquement l\'espace mémoire nécessaire à vos données. De même, si l\'un de vos champs ne contiendra au final que des chiffres ou nombres, choisissez un type de champ MySQL qui supporte le tri numérique des données plutôt que le tri alphabétique.',

'character_explanation'					=>
'Un champ de type Caractère (char) contient de courtes chaînes alphanumériques. Utilisez un champ Caractère pour stocker des champs avec des valeurs simples comme \'oui\', \'non\', \'o\', \'n\'',

'integer_explanation'					=>
'Un champ de type Entier peut contenir de grands nombres. Ils sont plus longs que les types de champ petits ou minuscules entiers et occupent plus de mémoire système.',

'float_explanation'						=>
'Un champ de type Flottant est le meilleur type de champ à utiliser si vous devez stocker des valeurs décimales. Vous pouvez préciser à la fois la longueur totale du champ et la précision décimale. Les champs de ce type sont prévus pour stocker des prix qui peuvent être triés numériquement.',

'decimal_explanation'						=>
'Un champ de type Décimal est un champ adéquat si vous devez stocker des valeurs décimales, par exemple des montants monétaires. Vous pouvez préciser à la fois la longueur totale du champ et la précision décimale.',

'precision_explanation'					=>
'La valeur de précision indique le nombre de décimales à réserver pour un nombre à virgule.',

'small_integer_explanation'				=>
'Un champ de type Petit Entier est plus petit qu\'un champ de type Entier et plus grand qu\'un champ de type Minuscule Entier. La plupart des nombres peuvent être stockés dans ce type de champ.',

'text_explanation'						=>
'Un champ de type Texte est l\'un des plus grands types de champ MySQL. Ces derniers peuvent contenir de grandes quantités de données textuelles ou numériques. Vous ne devriez utiliser ce type de champ que si vous prévoyez de stocker de grands blocs de texte.',

'tiny_integer_explanation'				=>
'Un champ de type Minuscule Entier est le plus petit des types de champ. N\'y stockez que de tout petits nombres.',

'varchar_explanation'					=>
'Un champ de type Variable de Caractères (varchar) est l\'un des types de champ MySQL le plus couramment utilisé. Il peut contenir des chaînes assez longues, mais ne peut toutefois atteindre l\'espace de stockage d\'un champ de type Texte.',

'field_length_required'					=>
'Merci d\'indiquer la longueur de votre champ.',

'char_length_incorrect'					=>
'La longueur d\'un champ de type Caractère doit être comprise entre 1 et 255.',

'float_length_incorrect'				=>
'La longueur d\'un champ de type Flottant doit être supérieure ou égale à 1.',

'precision_length_incorrect'			=>
'La longueur d\'un champ de type Flottant doit être supérieure à sa précision décimale.',

'integer_length_incorrect'				=>
'La longueur d\'un champ de type Entier doit être comprise entre 1 et 4294967295.',

'small_integer_length_incorrect'		=>
'La longueur d\'un champ de type Petit Entier doit être comprise entre 1 et 65535.',

'tiny_integer_length_incorrect'			=>
'La longueur d\'un champ de type Minuscule Entier doit être comprise entre 1 et 255.',

'varchar_length_incorrect'				=>
'La longueur d\'un champ de type Variable de Caractères (varchar) doit être comprise entre 1 et 255.',

'edit_confirm'							=>
'Confirmez les modifications apportées au champ.',

'edit_field_question'					=>
'Vous êtes sur le point d\'éditer un champ. Êtes-vous sûr de vouloir continuer ?',

'edit_field_question_truncate'			=>
'Des données seront peut-être tronquées ou supprimées si vous les convertissez vers ce nouveau type de champ. Les modifications ne pouvant être annulées, êtes-vous certain de vouloir continuer ?',

'field_edited_successfully'				=>
'Votre champ a été édité avec succès.',

//----------------------------------------
//	Preferences
//----------------------------------------

'preferences'	=>
'Préférences',

'preferences_exp'	=>
'Les préférences pour Super Recherche peuvent être gérées sur cette page.',

'preferences_not_available'	=>
'Les préférences ne sont pas encore disponibles pour ce module.',

'preferences_updated'	=>
'Préférences mises à jour',

'allow_keyword_search_on_playa_fields'	=>
'Autoriser la recherche de mots clefs dans les champs Playa ?',

'allow_keyword_search_on_playa_fields_exp'	=>
'La recherche de mots clefs dans les champs Playa peut conduire à des résultats de recherche disparates. Vous ne devriez activer ce paramétrage que si vous souhaitez rechercher les mots clefs dans les titres des entrées reliées à une entrée donnée.',

'allow_keyword_search_on_relationship_fields'	=>
'Autoriser la recherche de mots clefs dans les champs relationnels EE ?',

'allow_keyword_search_on_relationship_fields_exp'	=>
'La recherche de mots clefs dans les champs relationnels natifs de EE peut conduire à des résultats de recherche disparates. Vous ne devriez activer ce paramétrage que si vous souhaitez rechercher les mots clefs dans les titres des entrées reliées à une entrée donnée.',

'yes'	=>
'Oui',

'no'	=>
'Non',

//----------------------------------------
//	Caching rules
//----------------------------------------

'manage_caching_rules' =>
'Gérer les règles de cache',

'current_cache' =>
'Cache actuel',

'refresh' =>
'Actualiser',

'refresh_rules' =>
'Actualiser les règles',

'refresh_explanation' =>
'Si vous laissez cette valeur à “0”, le cache de recherche ne sera actualisé qu\'en fonction des règles de mise à jour du modèle ou du canal, ci-dessous.',

'template_refresh' =>
'Actualisation via le Modèle',

'template_refresh_explanation' =>
'Lorsque l\'un des modèles sélectionnés est édité, le cache de recherche est actualisé.',

'weblog_refresh' =>
'Actualisation via le Blog',

'weblog_refresh_explanation' =>
'Lorsqu\'une entrée est publiée ou éditée dans l\'un de ces blogs, le cache de recherche est actualisé.',

'channel_refresh' =>
'Actualisation via le Canal',

'channel_refresh_explanation' =>
'Lorsqu\'une entrée est publiée ou éditée dans l\'un de ces canaux, le cache de recherche est actualisé.',

'category_refresh' =>
'Actualisation via la Catégorie',

'category_refresh_explanation' =>
'Lorsqu\'une catégorie est créée ou éditée dans l\'un de ces groupes de catégorie, le cache de recherche est actualisé.',

'rows' =>
'rangées',

'refresh_now' =>
'Actualiser maintenant',

'next_refresh' =>
'(Prochaine actualisation : %n%)',

'in_minutes' =>
'(en minutes)',

'name_required' =>
'Un nom est requis.',

'name_invalid' =>
'Le nom communiqué est invalide.',

'numeric_refresh' =>
'L\'intervalle d\'actualisation doit être une donnée numérique.',

'refresh_rule_updated' =>
'Vos règles de cache ont été mises à jour et votre cache a été actualisé.',

//----------------------------------------
//  Update Page
//----------------------------------------

'update_super_search'					=>
'Mettre à Jour Super Recherche',

'super_search_module_disabled'	=>
'Le module Super Recherche ne semble pas installé sur ce site web. Merci de contacter l\'administrateur du site.',

'super_search_module_out_of_date'	=>
'Le module Super Recherche de ce site web ne semble pas à jour. Merci de contacter l\'administrateur du site.',

'super_search_update_message'	=>
'Vous avez récemment téléchargé une nouvelle version du module Super Recherche, merci de cliquer ici pour exécuter le script de mise à jour.',

'update_successful'						=>
'Mise à Jour réussie !',



//----------------------------------------
//	Recherche Log Page
//----------------------------------------

'period_year'					=>
'année',

'period_month'					=>
'mois',

'period_day'					=>
'jour',

'period_hour'					=>
'heure',

'period_min'					=>
'minute',

'period_sec'					=>
'seconde',

'period_postfix'				=>
's', /** to be reviewed in context */

'period_ago'					=>
'auparavant', /** this cannot really be translated, the sentence should be written the other way in French: 5 minutes ago => il y a 5 minutes. So translated to 5 minutes auparavant. To be reviewed */

'period_now'					=>
'à l\'instant',


'filter_searches'				=>
'Filtrer les recherches',

'terms'	=>
'Saisissez un terme de recherche ici ...',

'filter' =>
'Filtrer',

'no_searches_contained' =>
'Aucune recherche trouvée',

'filtering_terms_like' =>
'Filtrage de termes comme',

'filtering_term' =>
'Filtrage de terme',

'search_term' =>
'Terme recherché',

'searches_over_90' =>
'Recherches ayant intégrée ce(s) terme(s) lors des 90 derniers jours', /** to be reviewed in context */

'count' =>
'Compte',

'first_searched' =>
'Première recherche',

'most_recent_search' =>
'Recherche la plus récente',

'term_searches_in_last_90' =>
'Recherches ayant intégrée ce(s) terme(s) lors des 90 derniers jours', /** to be reviewed in context */

'searches_containing' =>
'recherches contenant',

'all_searches' =>
'Toutes les recherches',

'searches' =>
'recherches',

'searched_term' =>
'Terme recherché',

'date' =>
'Date',

'site' =>
'Site',

'more' =>
'Détails de la recherche effectuée',

'ditto' =>
'&#12291;',

//----------------------------------------
//	Front-end recherche
//----------------------------------------

'search_not_allowed'					=>
'Vous n\'êtes pas autorisé à effectuer une recherche.',

//----------------------------------------
//	Front-end recherche saving
//----------------------------------------

'recherche'	=>
'Recherche',

'search_not_found'	=>
'Votre recherche n\'a pas pû être trouvée.',

'missing_name'	=>
'Merci de donner un nom à votre recherche.',

'duplicate_name'	=>
'Ce nom a déjà été utilisé pour une recherche sauvegardée.',

'invalid_name'	=>
'Le nom donné à votre recherche n\'est pas valide.',

'duplicate_search'	=>
'Vous avez déjà enregistré cette recherche.',

'search_already_saved'					=>
'Vous avez déjà enregistré la recherche indiquée.',

'search_successfully_saved'				=>
'Votre recherche a été sauvegardée avec succès.',

'search_successfully_unsaved'			=>
'votre recherche est repassée en statut non sauvegardé avec succès.',

'search_already_unsaved'				=>
'Vous avez déjà repassé la recherche indiquée en statut non sauvegardé.',

'search_successfully_deleted'			=>
'Votre recherche a été supprimée avec succès.',

'no_search_history_was_found'	=>
'Aucun historique de recherche n\'a pu être trouvé pour vous.',

'last_search_cleared'	=>
'Votre dernière recherche a été mise à blanc.',

'site_switcher' =>
'Sélecteur de site',

'field_group_switcher' =>
'Sélecteur de groupe de champ',

/* END */
''=>''
);
?>
