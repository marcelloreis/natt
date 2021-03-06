<!-- Carrega o campo de busca  -->
<?php if(isset($search) && $search === true):?>
    <?php echo $this->AppForm->create($modelClass, array('class' => $requestHandler));?>
    <div class="map-search">
        <?php echo $this->form->hidden('q', array('value' => $requestHandler));?>
        <?php $value = isset($this->params['named']['search'])?$this->params['named']['search']:'';?>
        <?php echo $this->AppForm->input('search', array('label' => __('What are you looking for') . ", {$userLogged['given_name']}?", 'value' => $value, 'template' => 'input-clean'));?>

        <button class="button-text" id="gmap-search-1-submit" type="submit"><?php echo __('Search')?></button>
    </div>
    <?php echo $this->AppForm->end(); ?>
<?php endif?>


<div class="toolbar <?php echo (!isset($search))?'toolbar-bottom':'';?>">
    <div class="left">
        <?php $config = isset($config)?$config:false;?>
        <?php echo $this->element('toolbar-index-paginator', array('config' => $config))?>
    </div><!-- End .left -->
    <div class="right">
        <?php if($this->AppPermissions->check("{$this->name}.trash") && isset($trashed) && !isset($this->params['named']['habtmModel'])):?>
        <?php $trashedIndicator = $trashed > 0?"<p>{$trashed}</p>":''?>
                <?php echo $this->fetch('toolbar-index-buttons-trash', 
                $this->Html->link(sprintf(__("Show all %s"), __d('fields', $this->name)), array('action' => 'index'), array('title' => sprintf(__("Show all %s"), __d('fields', $this->name)), 'class' => 'button-text button-small tip-s', 'escape' => false))
                . "<span></span>"
                . $this->Html->link(sprintf(__("Trash"), __d('fields', $this->name)) . $trashedIndicator, array('action' => 'index', 'trashed' => true), array('title' => sprintf(__("Show all %s trashed"), __d('fields', $this->name)), 'class' => 'button-text button-small tip-s', 'escape' => false))
                )?>
        <?php endif;?>

        <?php if($requestHandler == 'ajax' && isset($this->params['named']['habtmModel'])):?>
            <?php echo $this->fetch('toolbar-index-buttons', $this->Html->link(__('Save associated records') . ' <span class="normalscreen-10 plix-10"></span>', '#', array('title' => __('Save associated records'), 'class' => "button-text-icon saveAddHabtm tip-s", 'rel' => $this->params['named']['habtmModel'], 'escape' => false)))?>
        <?php elseif($this->AppPermissions->check("{$this->name}.add")):?>
            <span class="separator"><!-- seperator --></span>
            <?php echo $this->fetch('toolbar-index-buttons', $this->Html->link(__('add') . ' <span class="plus-10 plix-10"></span>', array('action' => 'add'), array('title' => sprintf(__("Add a %s"), __d('fields', $modelClass)), 'class' => "button-text-icon tip-s", 'escape' => false)))?>
        <?php endif;?>
    </div><!-- End .right -->
</div><!-- End .toolbar -->