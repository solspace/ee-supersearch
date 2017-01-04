<?php if (isset($optgroups)) { ?>
	<div class="scroll-wrap">
		<select name="<?=$name?>[]" multiple="multiple" style="width:70%;height:100px;">
			<?php foreach ($optgroups as $label => $data) { ?>
			<optgroup label="<?=$label?>">
				<?php foreach ($data as $key => $val) { $sel = (isset($selected) AND in_array($key, $selected)) ? ' selected="selected"': ''; ?>
				<option value="<?=$key?>"<?=$sel?>><?=$val?></option>
				<?php } ?>>
			</optgroup>
			<?php } ?>>
		</select>
	</div>
<?php } ?>
<?php if (isset($options)) { ?>
	<div class="scroll-wrap">
		<select name="<?=$name?>[]" multiple="multiple" style="width:70%;height:100px;">
			<?php foreach ($options as $key => $val) { $sel = (isset($selected) AND in_array($key, $selected)) ? ' selected="selected"': ''; ?>
			<option value="<?=$key?>"<?=$sel?>><?=$val?></option>
			<?php } ?>
		</select>
	</div>
<?php } ?>