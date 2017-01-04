<div class="tbl-wrap">
	<table class="code_pack_templates">
		<tbody>
	<?php foreach ($cpt as $group => $templates):?>
			<tr class="sub-heading" data-group='<?=$group?>'>
				<td>
					<span class="icon"></span>
					<span class='heading-name'><?=$caller->lower_name?>_<?=$group?></span>
				</td>
			<tr>

		<?php foreach ($templates as $template):?>
			<tr>
				<td><span class="icon"></span><?=$template?></td></tr>
			</tr>
		<?php endforeach;?>
	<?php endforeach;?>
		</tbody>
	</table>
</div>