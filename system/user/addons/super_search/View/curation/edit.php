<?php $this->extend('_layouts/table_form_wrapper'); ?>

<?php if (! empty($term_keyword)) { ?>
<?=$caller->view('curation/keyword_table', NULL, true)?>
<?php } ?>

<?=$caller->view('curation/assigned', NULL, true)?>

<?=$caller->view('curation/entry_search', NULL, true)?>