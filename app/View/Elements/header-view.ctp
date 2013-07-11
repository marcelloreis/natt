<?php echo $this->fetch('control-panel')?>
<div class="spacer-10"><!-- spacer 10px --></div> 
<div class="page-header">
	<h2><?php echo $this->fetch('title', __d('fields', $this->name))?></h2>
	<div class="spacer-5"><!-- spacer 10px --></div> 	
	<p><?php echo $this->fetch('description')?></p>
	<?php echo $this->Session->flash(FLASH_SESSION_FORM)?>
</div>