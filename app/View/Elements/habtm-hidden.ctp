<?php if(isset($this->params['named']['habtm']) && $this->params['named']['habtm'] == $habtmModel):?>
	 <div id="addHabtm-<?php echo $habtmModel?>">
	<?php
	    if(isset($this->data[$habtmModel])){
	        foreach ($this->data[$habtmModel] as $k => $v) {
	            if(is_array($v)){
	                echo $this->Form->hidden("{$habtmModel}.{$habtmModel}.{$k}", array('value' => $v[$hasAndBelongsToMany[$habtmModel]['with']][$hasAndBelongsToMany[$habtmModel]['associationForeignKey']]));
	            }
	        }
	    }
	?>
	</div>
<?php endif?>