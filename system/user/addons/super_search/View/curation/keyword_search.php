<h2><?=lang('keyword_search');?></h2>
<div class="filters">
	<input
		type="text"
		size="20"
		style="width:40%;"
		id="super_search_search_keywords"
		name="term_keyword"
		class="super_search_search_keywords"
		placeholder="<?=lang('search_for_keyword')?>"
		value="<?=$term_keyword?>"
		/>
	<input name="super_search_search_button" type="submit" value="<?=lang('search');?>" class='btn submit' />
</div>
<br />