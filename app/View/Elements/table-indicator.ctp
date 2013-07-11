<?php foreach ($indicators as $k => $v):?>
	<span original-title="<?php echo $k?>" class="indicator tip-s is_paid" id="is_paid-<?php echo $id?>" rel="<?php echo strtolower($k)?>">
		<?php if($v):?>
			<span></span>
		<?php endif?>
	</span>
<?php endforeach?>