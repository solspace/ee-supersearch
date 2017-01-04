<?php $this->extend('_layouts/table_form_wrapper')?>

<h1><?=lang('top_search_terms')?></h1>

<?=$this->embed('ee:_shared/table', $terms_table)?>