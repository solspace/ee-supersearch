<?php $this->extend('_layouts/table_form_wrapper')?>

<h1><?=lang('demo_templates')?><br><i><?=lang('demo_description');?></i></h1>

<?=$this->embed('ee:_shared/table', $table)?>