<h2><?=lang('entry_search');?></h2>
<div class="filters">
	<input type="hidden" name="term" value="<?php echo (isset($term_keyword)) ? $term_keyword: $term; ?>" />
	<input
		type="text"
		size="20"
		style="width:40%;"
		id="super_search_search_entries"
		name="entry_keyword"
		value="<?=$entry_keyword ?>"
		class="super_search_search_entries"
		placeholder="<?=lang('search_for_entries')?>"
	/>
	<input name="super_search_search_entry_button" type="submit" value="<?=lang('search');?>" class='btn submit' />
</div>
<br />
<?php if (! empty($entry_keyword)) { ?>
<table>
	<thead>
		<tr>
			<th>
				<?=lang('ID#');?>
			</th>
			<th>
				<?=lang('title');?>
			</th>
			<th>
				<?=lang('date');?>
			</th>
			<th>
				<?=lang('status');?>
			</th>
			<th style="text-align:right;">
				<?=lang('add');?>
			</th>
		</tr>
	</thead>

	<tbody>
		<?php if (empty($entries)) { ?>
			<tr>
				<td colspan="4"><?=lang('no_entries_found');?></td>
				<td></td>
			</tr>
		<?php } else { ?>
		<?php foreach ($entries as $entry_id => $entry) { ?>
			<tr>
				<td><?=$entry_id?></td>
				<td><a href="<?=$entry['link']?>"><?=$entry['title']?></a><br><span class="meta-info">&mdash; by: <?=$entry['author']?>, in: <?=$entry['channel']?></span></td>
				<td><?=$entry['date']?></td>
				<td><span class="status-tag st-<?=$entry['status']?>"><?=$entry['status']?></span></td>
				<td style="text-align:right;"><input type="checkbox" name="add[]" value="<?=$entry_id?>" /></td>
			</tr>
		<?php } ?>
		<?php } ?>
	</tbody>
</table>
<?php } ?>