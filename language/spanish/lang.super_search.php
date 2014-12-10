<?php

/**
 * Super Search - Spanish Language
 *
 * @package		Solspace:Super Search
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2009-2014, Solspace, Inc.
 * @link		http://solspace.com/docs/super_search
 * @license		http://www.solspace.com/license_agreement
 * @version		2.1.0
 * @filesource	super_search/language/spanish/lang.super_search.php
 *
 * Translated to Spanish by N/A
 */

$lang = $L = array(

//----------------------------------------
// Required for MODULES page
//----------------------------------------

'super_search_module_name'				=>
'Súper Búsqueda',

'super_search_label'					=>
'Súper Búsqueda',

'super_search_module_version'			=>
'Súper Búsqueda',

'super_search_module_description'		=>
'Potente solución de búsqueda para ExpressionEngine',

'update_super_search_module' =>
'Actualizar Súper Búsqueda',

'update_failure' =>
'La actualización no ha sido un éxito.',

'update_successful' =>
'La actualización ha sido un éxito',

'online_documentation' => 
"Documentación en Línea",

//----------------------------------------
//  Main Menu
//----------------------------------------

'homepage'								=>
'Inicio',

'fields'								=>
'Campos',

'documentation'							=>
'Documentación en Línea',

'cache_rules'							=>
'Reglas del Cache',

'search_log'							=>
'Historial de Búsqueda',

'search_options'						=>
'Opciones',

'search_utils'							=>
'Utilidades',

//----------------------------------------
//  Buttons
//----------------------------------------

'save'									=>
'Guardar',

//----------------------------------------
//  Homepage & Global
//----------------------------------------

'success'								=>
'¡Éxito!',

'lexicon_needs_building' =>
'El Léxico de Búsqueda debe ser Construido',

'lexicon_explain' => 
'Para la funcionalidad de búsqueda avanzada, debemos crear un léxico del sitio.',

'build_now' =>
'Construirlo ahora',

'lexicon_rebuild' => 
'Reconstruir',

'lexicon_build' => 
'Construir',

'no_searches_yet' => 
'Todavía no se han grabado ninguna búsqueda.',

'no_searches_yet_long' =>
'Todavía no se han grabado ninguna búsqueda. Quizá debe <a href="%enable_link%">habilitar registros de búsqueda</a>.',

'no_searches_recorded' => 
'Todavía no se han registrado ninguna búsqueda.',

'no_searches_recorded_logging_off' =>
'Registro de Búsqueda esta inhabilitado. Para utilizar el historial debe <a href="%enable_link%">habilitar registros de búsqueda</a>',

'enable_search_logging' =>
'habilitar registros de búsqueda',

'enable_ga_integration' =>
'<strong>¿Sabias Que?</strong> Puedes conectar Súper Búsqueda para que funciona con Google Analítica. Detalles completos en la <a href="#">Documentación</a>.',

'top_search_terms' =>
'Términos de Búsqueda Más Populares',

'search_term' =>
'Término de Búsqueda',

'search_count' =>
'Recuento',

'search_rank' =>
'Nº',

'view_all' => 
'Ver Todos',

'recent_searches' =>
'Búsquedas Recientes',

'datetime' => 
'Hora',

'clear_log'	=>
'Vaciar Historial',

'search_log_cleared'	=>
'El historial de búsqueda de éste sitio ha sido vaciado.',

//----------------------------------------
//  Clear cache
//----------------------------------------

'cache'									=>
'Cache',

'clear_search_cache'					=>
'Vaciar el cache del buscador',

'cache_cleared'							=>
'El cache del buscador ha sido vaciado con éxito.',

//----------------------------------------
//  Search Options
//----------------------------------------

'manage_search_utils'					=>
'Utilidades de Búsqueda',

'manage_search_options'					=>
'Gestiona las Preferencias',

'ignore_common_word_list'				=>
'¿Ignorar palabras comunes de las búsquedas?',

'ignore_common_word_list_subtext'		=>
'La lista de palabras es completamente editable, y puede ser anulado a nivel de plantilla con el parámetro <a href="http://www.solspace.com/docs/detail/super_search_results/#use_ignore_word_list">use_ignore_word_list</a>.',

'ignore_word_list'						=>
'Lista de Palabras Ignoradas',

'ignore_word_list_subtext'				=>
'Si está habilitado, las siguientes palabras serán ignoradas en las búsquedas:',

'ignore_word_list_input_placeholder'	=>
'añade palabras extras, luego pulse intro ...',

'ignore_word_list_input_empty' 			=>
'La lista de palabras ignoradas esta vacía.',

'search_logging'						=>
'Preferencias de Registro',

'log_site_searches'						=>
'¿Registrar Búsquedas en el Sitio?',

'log_site_searches_subtext' 			=>
'El registro de búsqueda mantiene un record permanente de todas las búsquedas del sitio. Para la protección de los datos, todos los registros son anónimos. Éste preferencia debe ser marcada como SÍ para que la pestaña de Historial de Búsqueda puede mostrar los datos.',

'enable_smart_excerpt'					=>
'¿Utilizar Extractos Inteligentes?',

'enable_smart_excerpt_subtext'			=>
'Extractos Inteligentes alteran el variable {excerpt} truncando los resultados al rededor de los términos de búsqueda. Esto puede ser anulado a nivel de plantilla con el parámetro <a href="http://www.solspace.com/docs/detail/super_search_results/#smart_excerpts">smart_excerpts</a>.',

'enable_fuzzy_searching'					=>
'¿Utilizar Búsqueda Borroso?',

'enable_fuzzy_searching_subtext'			=>
'La Búsqueda Borroso ayuda con las palabras mal escritas, pluralizados, y términos similares.</em>',

'enable_fuzzy_searching_plurals'			=>
'Plurales y Singulares',

'enable_fuzzy_searching_plurals_subtext'			=>
'Los plurales y singulares son términos borrosos <em>(Específico al idioma)</em>.<br/>Ej: <strong>“abrigo” = “abrigos”, “pantalones” = “pantalón”</strong>',

'enable_fuzzy_searching_phonetics'			=>
'Fonética ',

'enable_fuzzy_searching_phonetics_subtext'			=>
'Palabras similares fonéticamente también son buscados <em>(Específico al idioma)</em>.<br/>Ej:  <strong>“Parra” = “Para”</strong>',

'enable_fuzzy_searching_spelling'			=>
'Ortografía ',

'enable_fuzzy_searching_spelling_subtext'			=>
'Se identifica faltas de ortografía y se hace un intento de corregirlas. El algoritmo automáticamente aprende y da un grado a las sugerencias basados en el contenido de su sitio. Después de un tiempo, se ajustara de una forma más específica al contenido. <br/>Ej: <strong>“Siencia” = “Ciencia”</strong>',

//----------------------------------------
//	Lexicon
//----------------------------------------

'manage_lexicon'						=>
'Léxico',

'lexicon' =>
'Léxico',

'build_search_lexicon' =>
'Construye el Léxico de Búsqueda',

'search_lexicon_explain' =>
'El léxico de búsqueda crea un conjunto de datos combinado de todos los términos únicos en todos sus sitios.<br/>
Con estos datos podemos habilitar la búsqueda borrosa, correcciones, y una mejor gestión de términos de búsqueda.<br/>
La primera vez puede tardar un tiempo, pero solo debe ser iniciado una vez.',
	
'built_just_now' =>
'El léxico acaba de ser creada',

'build_in_progress' =>
'En Progreso',

'build_complete' =>
'Completado',

'lexicon_last_built' =>
'Creado por última vez en ',

'lexicon_never_built' =>
'El léxico nunca fue creada',

//----------------------------------------
//	Suggestions
//----------------------------------------

'suggestions' =>
'Sugerencias Ortográficas',

'build_suggestions_corpus' =>
'Crea Sugerencias Ortográficas',

'suggestions_explain' =>
'Creamos una lista de palabras que han sido utilizadas como términos de búsqueda, pero no existen en el léxico, y intentamos encontrar la variación más probable para estos términos. Las sugerencias entonces son guardados en el cache para ser utilizados como términos normales. <br/><br/> 
	Las sugerencias pueden ser creadas cuando sean necesarias automáticamente durante una búsqueda, pero esto conlleva un retardo en la gestión del término la primera vez que es utilizado. <a href="#">Utilización ###NEEDS PROPER LINK###</a><br/>
	 La forma recomendada para gestionar sugerencias de búsqueda es tener un script cron que conecta con un url que calculará cualquier sugerencia necesario. <a href="#">Utilización ###NEEDS PROPER LINK###</a>',

'spelling_unknown_line' =>
'<strong>%i% Término%s% Desconocido%s%</strong> para encontrar sugerencias',

'spelling_known_line' =>
'<strong>%i% Término%s% Conocido%s%</strong> con sugerencias',

//----------------------------------------
//	Fields
//----------------------------------------

'custom_field_group' =>
'Grupo de Campos Personalizados',

'no_fields'								=>
'No existe campos personalizados para éste sitio.',

'no_fields_for_group'					=>
'No existe campos personalizados para éste grupo.',

'id'									=>
'ID',

'name'									=>
'Nombre',

'label'									=>
'Etiqueta',

'type'									=>
'Tipo',

'length'								=>
'Tamaño',

'precision'								=>
'Precisión',

'edit_field'							=>
'Editar Campo',

'field_explanation'						=>
'Ésta herramienta le permite controlar los tipos de datos MySQL de los campos personalizados de su sitio. Puede mejorar el rendimiento de su sitio cambiando los tipos de datos MySQL para que utilicen sólo el espacio necesario para los datos. Así como, su uno de sus campos solo va a incluir números, elige el tipo de campo MySQL que soporta el ordenado numérica en vez de la alfabética.',

'character_explanation'					=>
'El character o campo char contiene pequeños textos alfanuméricas. Utilice un campo caracter para guardar valores simples como \'sí\', \'no\', \'s\', \'n\'',

'integer_explanation'					=>
'El campo entero contiene números enteros. Son más grandes que campos enteros pequeños o campos enteros diminutos y utilicen más memoria.',

'float_explanation'						=>
'El campo float es el mejor campo para guardar valores decimales. Puede especificar el tamaño total del campo y también la precisión. Campos de éste tipo estan destinados para precios que pueden ser ordenados numéricamente.',

'decimal_explanation'						=>
'El campo decimal es ideal si va a guardar valores decimales, como por ejemplo importes monetarios. Puede especificar el tamaño total del campo y también la precisión decimal.',

'precision_explanation'					=>
'El valor de precisión indica el número de espacios decimales reservado para el decimal del número.',

'small_integer_explanation'				=>
'El campo entero pequeño es más pequeño que un campo entero normal y más grande que un campo entero diminuto. La mayoría de los números pueden ser guardados en éste tipo de campo.',

'text_explanation'						=>
'El campo de texto es uno de los campos más grandes en MySQL. Contienen grandes cantidades de textos o datos numéricas. Solo si necesita guardar grandes cantidades de textos debe utilizar éste tipo de campo.',

'tiny_integer_explanation'				=>
'El entero diminuto es el tipo de campo más pequeño. Guarda solo números muy pequeños en éstos campos.',

'varchar_explanation'					=>
'El campo varchar es uno de los campos más comunes utilizados en MySQL. Puede contener textos bastante largos pero no tanto espacio que un campo de texto.',

'field_length_required'					=>
'Por favor, indique el tamaño de su campo.',

'char_length_incorrect'					=>
'El tamaño de un campo character debe ser entre 1 y 255.',

'float_length_incorrect'				=>
'El tamaño de un campo float no puede ser menos que 1.',

'precision_length_incorrect'			=>
'El tamaño de un campo float debe ser más grande que su precisión decimal.',

'integer_length_incorrect'				=>
'El tamaño de un campo entero debe ser entre 1 y 4294967295.',

'small_integer_length_incorrect'		=>
'El tamaño de un campo entero pequeño debe ser entre 1 y 65535.',

'tiny_integer_length_incorrect'			=>
'El tamaño de un entero diminuto debe ser entre 1 y 255.',

'varchar_length_incorrect'				=>
'El tamaño de un campo varchar debe ser entre 1 y 255.',

'edit_confirm'							=>
'Confirmar los cambios al campo.',

'edit_field_question'					=>
'Está a punto de editar un campo. ¿Está seguro que quiere proceder?',

'edit_field_question_truncate'			=>
'Debido al tipo de campo que va a convertir, puede truncar o eliminar parte de los datos. Los cambios no pueden ser deshechos. ¿Está seguro que quiere proceder?',

'field_edited_successfully'				=>
'Su campo ha sido editado con éxito.',

//----------------------------------------
//	Preferences
//----------------------------------------

'preferences'	=>
'Preferencias',

'preferences_exp'	=>
'Las preferencias de Súper Búsqueda pueden ser controlados en ésta página.',

'preferences_not_available'	=>
'No hay preferencias disponibles todavía para éste módulo.',

'preferences_updated'	=>
'Preferencias Actualizadas',

'allow_keyword_search_on_playa_fields'	=>
'¿Permitir la búsqueda en campos Playa?',

'allow_keyword_search_on_playa_fields_exp'	=>
'Buscando palabras clave dentro de campos Playa puede crear confusiones en los resultados de la búsqueda. Solo si quiere buscar las palabras clave en los títulos de entradas relacionados con un entrada específica, debe habilitar ésta opción.',

'allow_keyword_search_on_relationship_fields'	=>
'¿Permitir la búsqueda en campos Relacionales?',

'allow_keyword_search_on_relationship_fields_exp'	=>
'Buscando palabras clave dentro de campos relacionales nativos de EE puede crear confusiones en los resultados de la búsqueda. Solo si quiere buscar las palabras clave en los títulos de entradas relacionados con un entrada específica, debe habilitar ésta opción.',

'yes'	=>
'Sí',

'no'	=>
'No',

//----------------------------------------
//	Caching rules
//----------------------------------------

'manage_caching_rules' =>
'Gestionar Reglas del Cache',

'current_cache' =>
'Cache Actual',

'refresh' =>
'Renovar',

'refresh_rules' =>
'Renovar por Reglas',

'refresh_explanation' =>
'Dejando éste valor en “0” causará que el cache solo sea renovada por las reglas de actualización de canales, o de plantillas abajo.',

'template_refresh' =>
'Renovar por Plantilla',

'template_refresh_explanation' =>
'Cuando uno de estas plantillas es editado, el cache de búsqueda será renovada.',

'weblog_refresh' =>
'Renovar por Weblog',

'weblog_refresh_explanation' =>
'Cuando una entrada es publicada o editada en uno de estos weblogs, el cache de búsqueda será renovada.',

'channel_refresh' =>
'Renovar por Canal',

'channel_refresh_explanation' =>
'Cuando una entrada es publicada o editada en uno de estos canales, el cache de búsqueda será renovada.',

'category_refresh' =>
'Renovar por Categorías',

'category_refresh_explanation' =>
'Cuando se crea una categoría o editado en uno de estos grupos de categorías, el cache de búsqueda será renovada.',

'rows' =>
'filas',

'refresh_now' =>
'Renovar ahora',

'next_refresh' =>
'(Próxima renovación: %n%)',

'in_minutes' =>
'(en minutos)',

'name_required' =>
'El nombre es obigatorio.',

'name_invalid' =>
'El nombre que ha incluido es inválido.',

'numeric_refresh' =>
'El intervalo de renovación debe ser numérica.',

'refresh_rule_updated' =>
'Sus Reglas de Cache han sido actualizadas y su cache ha sido renovada.',

//----------------------------------------
//  Update Page
//----------------------------------------

'update_super_search'					=>
'Actualizar Súper Búsqueda',

'super_search_module_disabled'	=>
'Parece que Súper Búsqueda no está instalada en ésta web. Por favor contacte al administrador del sitio.',

'super_search_module_out_of_date'	=>
'El módulo de Súper Búsqueda en éste web parece estar sin actualizar. Por favor contacte al administrador del sitio.',

'super_search_update_message'	=>
'Recientemente ha subido una versión nueva de Súper Búsqueda, por favor haga clik aquí para poner en marcha el script de actualización.',

'update_successful'						=>
'¡Actualización Realizada!',

//----------------------------------------
//	Search Log Page
//----------------------------------------

'period_year'					=>
'año',

'period_month'					=>
'mes',

'period_day'					=>
'día',

'period_hour'					=>
'hora',

'period_min'					=>
'minuto',

'period_sec'					=>
'segundo',

'period_postfix'				=>
's',

'period_ago'					=>
'hace',

'period_now'					=>
'ahora mismo',

'filter_searches'				=>
'Filtrar Búsquedas',

'terms'	=>
'escribe un término de búsqueda aquí ...',

'filter' =>
'Filtrar',

'no_searches_contained' => 
'No contiene búsquedas',

'filtering_terms_like' => 
'Filtrando términos como',

'filtering_term' => 
'Filtrando Término',

'search_term' => 
'Término de Búsqueda',

'searches_over_90' =>
'Búsquedas incluyendo el término en los últimos 90 días',

'count' => 
'Cuenta',

'first_searched' =>
'Primera Buscada',

'most_recent_search' => 
'Búsqueda Más Reciente',

'term_searches_in_last_90' =>
'Búsquedas de término en las últimas 90 días',

'searches_containing' =>
'búsquedas que contienen',

'all_searches' => 
'Todas las búsquedas',

'searches' => 
'búsquedas',

'searched_term' =>
'Término Buscado',

'date' =>
'Fecha',

'site' =>
'Sitio',

'more' =>
'Detalles de Búsqueda',

'ditto' =>
'&#12291;',

//----------------------------------------
//	Front-end search
//----------------------------------------

'search_not_allowed'					=>
'No está permitido para hacer una búsqueda.',

//----------------------------------------
//	Front-end search saving
//----------------------------------------

'search'	=>
'Buscar',

'search_not_found'	=>
'No se ha podido encontrar su búsqueda.',

'missing_name'	=>
'Por favor incluye un nombre para su búsqueda.',

'duplicate_name'	=>
'Éste nombre ya ha sido utilizado para una búsqueda guardada.',

'invalid_name'	=>
'El nombre de búsqueda que ha incluido no es válido.',

'duplicate_search'	=>
'Ya ha guardado éste búsqueda.',

'search_already_saved'					=>
'Ya ha guardado la búsqueda indicada.',

'search_successfully_saved'				=>
'Su búsqueda ha sido guardada con éxito.',

'search_successfully_unsaved'			=>
'Su búsqueda guardada ha sido borrado con éxito.',

'search_already_unsaved'				=>
'Ya ha borrado la búsqueda indicada.',

'search_successfully_deleted'			=>
'Su búsqueda ha sido borrado con éxito.',

'no_search_history_was_found'	=>
'No se ha encontrado un historial de búsqueda para usted.',

'last_search_cleared'	=>
'Su última búsqueda ha sido borrada.',

'site_switcher' => 
'Conmutador de Sitio',

'field_group_switcher' => 
'Conmutador de Grupo de Campos',

/* END */
''=>''
);
?>
