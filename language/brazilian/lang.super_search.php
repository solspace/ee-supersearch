<?php

 /**
 * Solspace - Super Search
 *
 * @package		Solspace:Super Search
 * @author		Solspace DevTeam
 * @copyright	Copyright (c) 2009-2012, Solspace, Inc.
 * @link		http://www.solspace.com/docs/addon/c/Super_Search/
 * @version		2.1.0
 * @translated to Brazilian Portuguese by MarchiMedia
 * @filesource	./system/expressionengine/third_party/super_search/language/english/
 */

$lang = $L = array(

//----------------------------------------
// Necessário para a página MÓDULOS
//----------------------------------------

'super_search_module_name'				=>
'Super Search',

'super_search_label'					=>
'Super Search',

'super_search_module_version'			=>
'Super Search',

'super_search_module_description'		=>
'Um poderoso mecanismo debusca para o ExpressionEngine',

'update_super_search_module' =>
'Atualizar o aplicativo Super Search',

'update_failure' =>
'A atualização não foi efetuada com sucesso.',

'update_successful' =>
'A atualização foi um sucesso',

'online_documentation' => 
"Documentação Online",

//----------------------------------------
//  Menu Principal
//----------------------------------------

'homepage'								=>
'Página Inicial',

'fields'								=>
'Campos',

'documentation'							=>
'Documentação Online',

'cache_rules'							=>
'Regras de Cache',

'search_log'							=>
'Log da Busca',

'search_options'						=>
'Opções',

'search_utils'							=>
'Utilitários',


//----------------------------------------
//  Botões
//----------------------------------------

'save'									=>
'Salvar',

//----------------------------------------
//  Homepage & Geral
//----------------------------------------

'success'								=>
'Sucesso!',

'lexicon_needs_building' =>
'É necessário construir o catálogo de palavras da Busca',

'lexicon_explain' => 
'Para que a  busca avançada seja funcional, nós precisamos construir um banco de dados com as palavras do site qyue será utilizado nestas buscas.',

'build_now' =>
'Construir Agora',

'lexicon_rebuild' => 
'Reconstruir',

'lexicon_build' => 
'Construir',

'no_searches_yet' => 
'Nenhuma busca foi armazenado ainda.',

'no_searches_yet_long' =>
'Nenhuma busca foi armazenada ainda. Você pode precisar ativar o <a href="%enable_link%">log da busca</a>.',

'no_searches_recorded' => 
'Ainda não existem logs de busca.',

'no_searches_recorded_logging_off' =>
'O log da Busca está desativado. Para utilizar o log da busca <a href="%enable_link%">ative o log da busca</a>',

'enable_search_logging' =>
'ativar log da busca',

'enable_ga_integration' =>
'<strong>Você sabia?</strong> Você pode ajustar o SuperSearch para trabalhar com o. Detalhes em nossa <a href="#">Documentação</a>.',

'top_search_terms' =>
'Top Termos de Busca',

'search_term' =>
'Buscar Termo',

'search_count' =>
'Countagem',

'search_rank' =>
'#',

'view_all' => 
'Ver Tudo',

'recent_searches' =>
'Buscas Recentes',

'datetime' => 
'Horário',

'clear_log'	=>
'Zerar Log',

'search_log_cleared'	=>
'O log da foi zerado para este site.',



//----------------------------------------
//  Limpar cache
//----------------------------------------

'cache'									=>
'Cache',

'clear_search_cache'					=>
'Zerar buscas em cache',

'cache_cleared'							=>
'O cache da busca foi zerada com sucesso.',

//----------------------------------------
//  Opções de busca
//----------------------------------------

'manage_search_utils'					=>
'Utilitários da Busca',

'manage_search_options'					=>
'Gerenciar Prefrências',

'ignore_common_word_list'				=>
'Ignorar palavras comuns das buscas?',

'ignore_common_word_list_subtext'		=>
'A lista de palavras é completamente editável, e pode ser sobregravada a nível de template com o parâmetro <a href="http://www.solspace.com/docs/detail/super_search_results/#use_ignore_word_list">use_ignore_word_list</a>.',

'ignore_word_list'						=>
'Lista de Palavras Ignoradas',


'ignore_word_list_subtext'				=>
'Se ativada, as seguintes palavras serão ignoradas em qualquer busca:',


'ignore_word_list_input_placeholder'	=>
'addicione palavras extras para ignorar, pressione enter ...',

'ignore_word_list_input_empty' 			=>
'A listagem de palavras ignoradas está vazia.',


'search_logging'						=>
'Preferências de Log',

'log_site_searches'						=>
'Logar Buscas efetuadas no site?',

'log_site_searches_subtext' 			=>
'O log de buscas mantem registros permanentes de todas as buscas do site. Para proteção dos dados, todos os registros são mantidos anonimamente. Esta preferência deve ser definida para SIM para o Log da busca para mostrar os dados.',

'enable_smart_excerpt'					=>
'Usar Fragamentos Inteligentes?',

'enable_smart_excerpt_subtext'			=>
'Fragmentos Inteligentes alteram a variável {excerpt} para efetuar um truncamento em torno dos termos de busca. isto pode ser sobregravado ao nível de template com o parâmetro <a href="http://www.solspace.com/docs/detail/super_search_results/#smart_excerpts">smart_excerpts</a>.',


'enable_fuzzy_searching'					=>
'Usar Busca Fuzzy?',

'enable_fuzzy_searching_subtext'			=>
'O método de busca Fuzzy é útil quando palavras com erros de ortografia, pluralizadas e termos similares são processadas.</em>',


'enable_fuzzy_searching_plurals'			=>
'Plurais e Singulares',

'enable_fuzzy_searching_plurals_subtext'			=>
'Plurais e Singulares são  fuzzies <em>(Palavra específica em inglês)</em>.<br/>Ex: <strong>“pele” = “peles”, “casa” = “casas”</strong>',


'enable_fuzzy_searching_phonetics'			=>
'Fonéticas ',

'enable_fuzzy_searching_phonetics_subtext'			=>
'Palavras foneticamente similares também são buscadas <em>(Apenas em inglês)</em>.<br/>Ex:  <strong>“Nolton” = “Noulton”</strong>',


'enable_fuzzy_searching_spelling'			=>
'Ortografia ',

'enable_fuzzy_searching_spelling_subtext'			=>
'Palavras com erros ortográficos são identificadas e será efetuada uma tentativa para corrigi-las. O algoritmo automaticamente aprende e rankeia sua sugestão baseados no conteúdo do seu site. Com o tempo ela se tornará melhor, sintonizada com seu conteúdo específico. <br/>Ex: <strong>“Ciênsia” = “Ciência”</strong>',


//----------------------------------------
//	Base de dados das palavras
//----------------------------------------

'manage_lexicon'						=>
'Banco de Palavras',

'lexicon' =>
'Banco de Palavras',

'build_search_lexicon' =>
'Construir Banco de Dados das Palavras',

'search_lexicon_explain' =>
'A busca pelo banco de palavras constrói um conjunto de daos de todos os termos únicos através dos seus sites. <br/>
Com estes dados, nós poderemos efetuar buscas fuzzy, buscar correções e melhor tratamento dos termos de busca.<br/>
Lega algum tempo quando para ser executado na primeira vez, mas isto precisa ser feito apenas uma vez.',
	
'built_just_now' =>
'Banco de Palavras está contruído agora',

'build_in_progress' =>
'Em Progresso',

'build_complete' =>
'Completo',

'lexicon_last_built' =>
'Construído pela última vez em ',

'lexicon_never_built' =>
'O banco de dados nunca foi construído',


//----------------------------------------
//	Sugestões
//----------------------------------------


'suggestions' =>
'Sugestões Ortográficas',


'build_suggestions_corpus' =>
'Construir Sugestões Ortográficas',

'suggestions_explain' =>
'Nós construímos um conjunto de sugestões para as palavras que forem buscadas no site, mas não existem no seu banco de palavras, e tenta localizar as variações mais utilizadas destes termos. As sugestões são então cacheadas para uso nas buscas normais. <br/><br/> 
	As sugestões podem automaticamente serem construídas de acordo com a necessidade durante as buscas, mas isto fará com que ocorra um certo delay apenas na primeira vez, quando um termo único for solicitado. <a href="#">Utilize ###NEEDS PROPER LINK###</a><br/>
	 Nós recomendamos que a melhor forma de trabalhar com estas sugestões de busca seria efetuar um cron com a url que irá calcular qualquer nova sugestão requisitada. <a href="#">Utilize ###NEEDS PROPER LINK###</a>',

'spelling_unknown_line' =>
'<strong>%i% Unknown Term%s%</strong> para localizar sugestões para',


'spelling_known_line' =>
'<strong>%i% Known Term%s%</strong> com sugestões',



//----------------------------------------
//	Campos
//----------------------------------------

'custom_field_group' =>
'Grupo de Campos Customizados',

'no_fields'								=>
'Não existem campos customizados para este site.',

'no_fields_for_group'					=>
'Não existem campos customizados para este grupo.',

'id'									=>
'ID',

'name'									=>
'Nome',

'label'									=>
'Rótulo',

'type'									=>
'Tipo',

'length'								=>
'Tamanho',

'precision'								=>
'Precisão',

'edit_field'							=>
'Editar Campo',

'field_explanation'						=>
'Esta ferramenta permite que voc~e controle os tipos de dados MySQL no seu site. Você pode melhorar a performance do seu site alterando os tipos de campos MySQL para usar apenas a quantidade necessária de espaço para seus dados. Assim, se um dos seus campos conter apenas números, escolha um tipo de campo MySql que suporte o ordenamento dos dadosnumericamente ao invés de ser alfabeticamente.',

'character_explanation'					=>
'Um caractere ou um campo de caracteres que contém pequenas strings alfanuméricas. Use um campo de caractere para armazenar os campos com simples valores, como \'sim\', \'não\', \'s\', \'n\'',

'integer_explanation'					=>
'Um campo inteiro pode conter números grandes. Eles são maiores que Inteiros Menores ou Pequenos Inteiros e consomem mais memório.',

'float_explanation'						=>
'Um campo flutuante é o melhor campo para usar se você armazena valores decimais. Você pode especificar o tamanho total do campo assim como sua precisão decimal. Campos deste tipo são interessantes para armazenar preços que podem ser ordenados numericamente.',

'decimal_explanation'						=>
'Um campo decimal é um bom campo para se usar para armazenar valores decimais, por exempo, quantias financeiras. Você pode especificar o tamanho total do campo, assim como sua precisão decimal.',

'precision_explanation'					=>
'O valor de precisão o número de casas decimais que serão reservados para um numero de ponto flutuante.',

'small_integer_explanation'				=>
'Inteiros Menores são campos menores que um campo de um inteiro e maior que um Pequeno Inteiro. A maioria os números podem ser armazenados neste tipo de campo.',

'text_explanation'						=>
'Um campo de texto é um dos maiores campos de dados do MySQL. Eles podem conter apenas grandes quantidades de texto ou dados numéricos. Apenas se você estiver armazenando grandes blocos de texto, você deverá usar este tipo de campo.',

'tiny_integer_explanation'				=>
'Um Pequeno Inteiro é o menor tipo de campo de dados disponível. Armazene apenas números pequenos neste tipo de campo.',

'varchar_explanation'					=>
'Um campo de caracteres variáveis é um dos mais comuns tipos de campos utilizados no MySQL. Ele pode conter longas strings, mas não utiliza a quantidade enorme de espaço que um campo de texto utiliza.',

'field_length_required'					=>
'Por favor indique um tamanho para seu campo.',

'char_length_incorrect'					=>
'O tamanho de um camppo de caractere deve possuir entre 1 e 255.',

'float_length_incorrect'				=>
'Um tamanho de campo flutuante não pode ser menor que 1.',

'precision_length_incorrect'			=>
'O tamanho de um campo flutuante não pode ser menor que sua precisão decimal.',

'integer_length_incorrect'				=>
'O tamanho de um campo inteiro deve ser algo entre 1 e 4294967295.',

'small_integer_length_incorrect'		=>
'O tamanho de um campo inteiro menor deve ser algo entre 1 e 65535.',

'tiny_integer_length_incorrect'			=>
'O tamanho de um campo inteiro pequeno deve ser algo entre 1 e 255.',

'varchar_length_incorrect'				=>
'Um campo de caracteres variáveis deve ser algo entre 1 e 255.',

'edit_confirm'							=>
'Confirmae as alterações do campo.',

'edit_field_question'					=>
'Você está prestes a editar um campo. Tem certeza que deseja continuar?',

'edit_field_question_truncate'			=>
'Por causa do tipo de campo que você está convertendo, podem ocorrer o truncamento ou remoção de dados. Estas mudanças não podem ser desfeitas. TEM CERTEZA QUE DESEJA CONTINUAR?',

'field_edited_successfully'				=>
'Seu campo foi editado com sucesso.',

//----------------------------------------
//	Preferências
//----------------------------------------

'preferences'	=>
'Preferências',

'preferences_exp'	=>
'As Preferências do Super Search podem ser controlados nesta página.',

'preferences_not_available'	=>
'As Preferências não estão disponíveis para este módulo.',

'preferences_updated'	=>
'Preferências Atualizadas',

'allow_keyword_search_on_playa_fields'	=>
'Permitir as buscas nos campos Playa?',

'allow_keyword_search_on_playa_fields_exp'	=>
'A busca nos campos Playa podem ocasionar resultados confusos. Apenas se você desejar efetuar uma busca nas palavras-chave dos títulos relacionados a um registro específico, você deve ativar esta configuração.',

'allow_keyword_search_on_relationship_fields'	=>
'Permitir a busca de palavras nos campos relacionados?',

'allow_keyword_search_on_relationship_fields_exp'	=>
'A busca de palavras-chave em campos relacionados nativos do EE podem ocasionar resultados confusos. Apenas se você desejar efetuar uma busca nas palavras-chave dos títulos relacionados a um registro específico, você deve ativar esta configuração.',

'yes'	=>
'Sim',

'no'	=>
'Não',

//----------------------------------------
//	regras de Cacheamento
//----------------------------------------

'manage_caching_rules' =>
'Gerenciar Regras de Cacheamento',

'current_cache' =>
'Cache Atual',

'refresh' =>
'Atualizar',

'refresh_rules' =>
'Atualizar Regras',

'refresh_explanation' =>
'Deixando este valor como “0” você deixará que o cache do canal seja atualizado pelas regras de atualização do canal ou do template, abaixo.',

'template_refresh' =>
'Atualização do Template',

'template_refresh_explanation' =>
'Quando uma destes templates é editado, o cache da busca será atualizado.',

'weblog_refresh' =>
'Atualização de Canal',

'weblog_refresh_explanation' =>
'Quando um registro é publicado ou editado em um destes canais, o cache da busca será atualizado.',

'channel_refresh' =>
'Atualizar Canal',

'channel_refresh_explanation' =>
'Quando umc anal é publicado ou editado em um destes canais, o cache da busca será atualizado.',

'category_refresh' =>
'Atualizar Categoria',

'category_refresh_explanation' =>
'Quando uma categoria é criada ou editada em um destes grupos de categoria, o cache da busca será atualizado.',

'rows' =>
'linhas',

'refresh_now' =>
'Renovar agora',

'next_refresh' =>
'(Próxima Atualização do Cache: %n%)',

'in_minutes' =>
'(em minutos)',

'name_required' =>
'Um nome é obrigatório.',

'name_invalid' =>
'O nome que você forneceu é inválido.',

'numeric_refresh' =>
'O intervalo de atualização deve ser numérico.',

'refresh_rule_updated' =>
'Suas regras de cacheamento foram atualizadas e seu cache foi atualizado.',

//----------------------------------------
//  Página de Atualizaçõa
//----------------------------------------

'update_super_search'					=>
'Atualizar o Super Search',

'super_search_module_disabled'	=>
'O módulo Super Search parece não estar instlado neste site. Por favor entre em contato com o Administrador do site.',

'super_search_module_out_of_date'	=>
'O módulo Super Search deste site parace que não está atualizado. Por favor entre em contato com o Administrador do site.',

'super_search_update_message'	=>
'Você recentemente subiu uma nova versão do Super Search, por favor clique aqui para executar o script de atualização.',

'update_successful'						=>
'Atualização Efetuada com Sucesso!',



//----------------------------------------
//	Log da Página da Busca
//----------------------------------------

'period_year'					=>
'ano',

'period_month'					=>
'mês',

'period_day'					=>
'dia',

'period_hour'					=>
'hora',

'period_min'					=>
'minuto',

'period_sec'					=>
'second',

'period_postfix'				=>
's',

'period_ago'					=>
'atrás',

'period_now'					=>
'agora',


'filter_searches'				=>
'Filtrar Buscas',

'terms'	=>
'digite um termo da busca aqui ...',

'filter' =>
'Filtro',

'no_searches_contained' => 
'Não contém buscas',

'filtering_terms_like' => 
'Filtrando termos como',

'filtering_term' => 
'Filtrando Termo',

'search_term' => 
'Buscar Termo',

'searches_over_90' =>
'A busca inclui termos listados nos últimos 90 dias',

'count' => 
'Contagem',

'first_searched' =>
'Primeira Busca',

'most_recent_search' => 
'Busca mais recente',

'term_searches_in_last_90' =>
'Termos de busca mais recentes nos últimos 90 dias',

'searches_containing' =>
'buscas contendo',

'all_searches' => 
'Todas as buscas',

'searches' => 
'buscas',

'searched_term' =>
'Termo Buscado',

'date' =>
'Data',

'site' =>
'Site',

'more' =>
'Detalhes da Busca',

'ditto' =>
'&#12291;',

//----------------------------------------
//	Front-end da busca
//----------------------------------------

'search_not_allowed'					=>
'Você não possui permissão para efetuar uma busca.',

//----------------------------------------
//	Front-end buscas salvas
//----------------------------------------

'search'	=>
'Buscar',

'search_not_found'	=>
'Sua busca não pode ser localizada.',

'missing_name'	=>
'Por favor forneça um nome para a sua busca.',

'duplicate_name'	=>
'O nome já foi utilizado para uma busca salva.',

'invalid_name'	=>
'O nome da busca que voc~e forneceu é inválido.',

'duplicate_search'	=>
'Você já salvou esta busca.',

'search_already_saved'					=>
'Você já salvou a busca indicada.',

'search_successfully_saved'				=>
'Sua busca foi salva com sucesso.',

'search_successfully_unsaved'			=>
'Sua busca foi não foi salva com sucesso.',

'search_already_unsaved'				=>
'Você cancelou o salvamento da busca indicada.',

'search_successfully_deleted'			=>
'Sua busca foi excluída com sucesso.',

'no_search_history_was_found'	=>
'Nenhum histórico da busca foi localizada para você.',

'last_search_cleared'	=>
'Sua última busca foi zerada.',

'site_switcher' => 
'Trocar de Site',

'field_group_switcher' => 
'Trocar Grupo de Campos',

/* FIM */
''=>''
);
?>
