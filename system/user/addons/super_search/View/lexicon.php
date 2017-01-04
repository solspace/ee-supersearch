<?php $this->extend('_layouts/table_form_wrapper'); ?>

<div class="tbl-wrap">
	<div class="live_block">

		<span id="build_lexicon_block" class="block_action">

			<span class="last_build"><?=$lexicon_last_build?></span>
			<span class="just_now" style="display:none">
				<?=lang('built_just_now')?>
			</span>

			<button id="build_lexicon" class='submit live_block_build btn submit'  rel="<?=$lexicon_build_url?>"><?=lang('lexicon_build')?></button>

		</span>

		<div class="lexicon_build_state block_state" style="display:none;">

			<div class="ss_clearfix progress-container">
				<?=$this->embed('ee:_shared/progress_bar')?>
				<div id="update_percent">0%</div>
			</div>

			<div style="display:none;" id="lexicon_in_progress" class="block_in_progress">
				<?=lang('build_in_progress')?>
				<span id="lexicon_progress_count_current" class="block_progress_count_current">0</span> / <span id="lexicon_progress_count_total" class="block_progress_count_total"><?=$lexicon_progress_count_total?></span>
			</div>

		</div>

		<span style="display:none;" id="lexicon_success" class="block_success">
			<?=lang('build_complete')?>
		</span>

	</div>
</div>