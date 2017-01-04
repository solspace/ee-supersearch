<table>
	<thead>
		<tr>
			<th>
				<?=lang('keyword');?>
			</th>
		</tr>
	</thead>
	<tbody>
		<?php if (empty($term_keyword)) : ?>
			<tr>
				<td><?=lang('no_keyword_found');?></td>
			</tr>
		<?php else :?>
			<tr>
				<td><?=$term_keyword?></td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>
<br />