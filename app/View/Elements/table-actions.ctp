<div class="right">
	<?php 
		$fkbox = isset($this->params['named']['fkbox'])?$this->params['named']['fkbox']:null;
		switch ($fkbox) {
			case 'belongsto':
				echo $this->Html->link('<span class="normalscreen-10 plix-10"></span>', '#', array('title' => __('Add record'), 'class' => 'button-icon tip-s addBelongsTo', 'escape' => false));
				break;
			
			case 'habtm':
				// echo $this->Html->link('<span class="normalscreen-10 plix-10"></span>', '#', array('title' => __('Add/Delete record'), 'class' => 'button-icon tip-s addHabtm', 'escape' => false));
				break;
			
			default:
		 		echo $this->Html->link('<span class="magnifyglass-10 plix-10"></span>', array("controller" => $this->params['controller'], "action" => "view", $id), array('title' => __('View Record'), 'class' => 'button-icon tip-s', 'escape' => false));
		 		echo $this->Html->link('<span class="pencil-10 plix-10"></span>', array("controller" => $this->params['controller'], "action" => "edit", $id), array('title' => __('Edit Record'), 'class' => 'button-icon tip-s', 'escape' => false));
		 		if(isset($this->params['named']['trashed']) && $this->AppPermissions->check("{$this->name}.trash")){
		 			echo $this->Html->link('<span class="inbox-10 plix-10"></span>', array("controller" => $this->params['controller'], "action" => "restore", $id), array('title' => __('Restore'), 'onclick' => "return confirm('" . __('Are you sure you want to restore this record from the trash?') . "');", 'class' => 'button-icon tip-s', 'escape' => false));
		 		}else{
		 			echo $this->Html->link('<span class="trashcan-10 plix-10"></span>', array("controller" => $this->params['controller'], "action" => "trash", $id), array('title' => __('Trash'), 'onclick' => "return confirm('" . __('Are you sure you want to move this record to the trash?') . "');", 'class' => 'button-icon tip-s', 'escape' => false));
		 		}
				break;
		}

	?>
</div>