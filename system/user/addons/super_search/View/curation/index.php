<?php $this->extend('_layouts/table_form_wrapper'); ?>

<?php if (empty($edit)) { ?>
<?=$caller->view('curation/keyword_search', NULL, true)?>
<?php } ?>

<?php if (! empty($exact_keyword)) { ?>
<?=$caller->view('curation/keyword_exact', NULL, true)?>
<?php } ?>

<?php if (! empty($results)) { ?>
<?=$this->embed('ee:_shared/table', $results)?>
<?php } ?>