<!-- message: error, alert, success -->
<div class="spacer-10"></div>
<div class="dialog <?php echo $class?>">
	<?php if(isset($multiple)):?>
		<?php foreach ($multiple as $k => $v):?>
			<p><?php echo $this->Html->image("icons/dialogs/{$class}-16.png", array('alt' => $class))?><?php echo $v?></p><br />
		<?php endforeach?>
	<?php elseif(isset($message)):?>
		<p><?php echo $this->Html->image("icons/dialogs/{$class}-16.png", array('alt' => $class))?><?php echo $message?></p>
	<?php endif?>
	<span>x</span>
</div>
<!-- end: message -->