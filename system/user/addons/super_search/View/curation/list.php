<?php $this->extend('_layouts/table_form_wrapper'); ?>
<h1><?=lang('keyword_list')?></h1>
<?=$this->embed('ee:_shared/table', $table)?>