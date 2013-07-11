<?php 
if(count($states)){
	echo $this->Form->input("{$model}.state_id", array('label' => false, 'div' => false, 'options' => $states, 'size' => '5'));	
}
?>