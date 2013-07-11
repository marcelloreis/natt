<?php 
if(count($cities)){
	echo $this->Form->input("{$model}.city_id", array('label' => false, 'div' => false, 'options' => $cities, 'size' => '5'));
}
?>