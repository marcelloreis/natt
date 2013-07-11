<div class="toolbar">
    <div class="left">
	<!-- Escreve o texto informativo de acordo com a ação do usuario -->
	<?php if($this->params['action'] == 'add' || ($this->params['action'] == 'edit' && !count($this->params['pass']))):?>
        <span class="label"><?php echo __('Enter the data for the new record.')?></span>    
    <?php elseif(isset($this->request->data[$modelClass])):?>
        <span class="label"><?php echo $this->request->data[$modelClass]['modified']?></span>
        <span class="separator"><!-- seperator --></span>
        <span class="label"><?php echo __('Last change of this record.')?></span>    
	<?php endif;?>    
    </div><!-- End .left -->
    <div class="right">
	<!-- Insere o botao NOVO caso a acao seja de edicao -->
	<?php if($this->params['action'] == 'edit' && count($this->params['pass'])):?>
        <?php if($this->AppPermissions->check("{$this->name}.add")):?>
		  <a class="button-text" href="<?php echo $this->Html->url(array("controller" => $this->params['controller'], "action" => "add"))?>"><?php echo __('New') . " " . __d('fields', $modelClass)?></a>
        <?php endif;?>    
	<?php endif;?>    
		<a class="button-text" href="<?php echo $this->Html->url(array("controller" => $this->params['controller'], "action" => "index"))?>"><?php echo __('List') . " " . __d('fields', $this->name)?></a>
        <?php echo $this->AppForm->btn();?>
    </div><!-- End .right -->
</div><!-- End .toolbar -->