<?php $this->assign('title', __('Add file'))?>
 <section class="g_1">
    <!-- New widget -->
    <div class="e-block">
        <header>
            <ul class="etabs">
                <?php $id = (isset($this->data[$modelClass]['id']) && !empty($this->data[$modelClass]['id']))?$this->data[$modelClass]['id']:null;?>
                    <li class="<?php echo (isset($this->params['named']['habtm']))?'':'etabs-active';?>"><?php echo $this->Html->link(sprintf(__('Edit %s'), __d('fields', $modelClass)), array($id))?></li>
                </ul>       
        </header>
        <div style="display:<?php echo (isset($this->params['named']['habtm']))?'none':'block'?>;" class="etabs-content" id="<?php echo $modelClass?>"> 
            <?php echo $this->AppForm->create($modelClass, array('type' => 'file', 'defaultSize' => 'g_1_4'))?>
            <?php echo $this->element('toolbar-edit')?>
            <div class="inner-spacer set-cells">
                <?php echo $this->Form->hidden('id')?>
                <?php echo $this->Form->hidden('status', array('value' => CHK_STATUS_QUEUE))?>
                <?php echo $this->AppForm->separator()?>
                <?php echo $this->AppForm->input('client_id', array('type' => __('select'), 'empty' => __('select'), 'options' => $clientes))?>
                <?php echo $this->AppForm->separator()?>
                <hr/>
                <?php echo $this->AppForm->separator()?>
                <?php echo $this->AppForm->input('Files.filename', array('label' => 'Arquivo', 'type' => 'file'))?>
                <?php echo $this->AppForm->separator()?>
            </div>   
            <?php echo $this->AppForm->end()?>
        </div>
    </div><!-- End .powerwidget -->
</section>
