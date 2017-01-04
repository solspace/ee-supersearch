<div class="box">
	<div class="tbl-ctrls">
<?php if (isset($form_url)):?>
	<?=form_open($form_url, (isset($form_attrs)) ? $form_attrs: array())?>
<?php endif;?>
		<?=ee('CP/Alert')->getAllInlines()?>
	<?php if ( ! empty($form_right_links)):?>
		<fieldset class="tbl-search right">
		<?php foreach ($form_right_links as $link_data):?>
		<a class="btn tn action" href="<?=$link_data['link']?>"><?=$link_data['title']?></a>
		<?php endforeach;?>
		</fieldset>
	<?php endif;?>
	<?php if (isset($cp_page_title)):?>
		<h1><?=$cp_page_title?></h1>
	<?php elseif (isset($wrapper_header)):?>
		<h1><?=$wrapper_header?></h1>
	<?php endif;?>
		<?php if (isset($filters)) echo $filters; ?>
		<?=$child_view?>
	<?php if (isset($pagination)):?>
		<div class="ss_clearfix"><?=$pagination?></div>
	<?php endif;?>
<?php if (isset($footer)):?>
	<?php if ($footer['type'] == 'form'):?>
		<fieldset class="form-ctrls">
		<?php if (isset($footer['submit_lang'])):?>
			<input class="btn submit" type="submit" value="<?=$footer['submit_lang']?>" />
		<?php endif;?>
		</fieldset>
	<?php else: ?>

	<?php endif;?>
<?php endif;?>
<?php if (isset($form_url)):?>
		</form>
<?php endif;?>
	</div>
</div>
