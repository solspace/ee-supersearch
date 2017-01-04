<h2><?=lang('assigned_entries');?></h2>
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
				<?=lang('remove');?>
			</th>
		</tr>
	</thead>
	<tbody>
		<?php if (empty($assigned)) { ?>
			<tr>
				<td colspan="5"><?=lang('no_assigned_entries')?></td>
			</td>
		<?php } else { ?>
		<?php foreach ($assigned as $entry_id => $entry) { ?>
			<tr>
				<td><?=$entry_id?></td>
				<td><a href="<?=$entry['link']?>"><?=$entry['title']?></a><br><span class="meta-info">&mdash; by: <?=$entry['author']?>, in: <?=$entry['channel']?></span></td>
				<td><?=$entry['date']?></td>
				<td><span class="status-tag st-<?=$entry['status']?>"><?=$entry['status']?></span></td>
				<td style="text-align:right;"><input type="checkbox" name="remove[]" value="<?=$entry_id?>" /></td>
			</tr>
		<?php } ?>
		<?php } ?>
	</tbody>
</table>