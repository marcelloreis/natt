<div class="right">
	<?php 
 		if(isset($this->params['named']['trashed']) && $this->AppPermissions->check("{$this->name}.trash")){
 			echo $this->Html->link('<span class="inbox-10 plix-10"></span>', array("controller" => $this->params['controller'], "action" => "restore", $id), array('title' => __('Restore'), 'onclick' => "return confirm('" . __('Are you sure you want to restore this record from the trash?') . "');", 'class' => 'button-icon tip-s', 'escape' => false));
 		}else{
 			echo $this->Html->link('<span class="trashcan-10 plix-10"></span>', array("controller" => $this->params['controller'], "action" => "trash", $id), array('title' => __('Trash'), 'onclick' => "return confirm('" . __('Are you sure you want to move this record to the trash?') . "');", 'class' => 'button-icon tip-s', 'escape' => false));
 		}

	?>
</div>