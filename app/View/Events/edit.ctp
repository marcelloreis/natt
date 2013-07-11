 <section class="g_1">
    <!-- New widget -->
    <div class="e-block">
        <header>
            <ul class="etabs">
                <?php $id = (isset($this->data[$modelClass]['id']) && !empty($this->data[$modelClass]['id']))?$this->data[$modelClass]['id']:null;?>
                <li class="<?php echo (isset($this->params['named']['habtm']))?'':'etabs-active';?>"><?php echo $this->Html->link(sprintf(__('Edit %s'), __d('fields', $modelClass)), array($id))?></li>
            <?php if(isset($this->data[$modelClass]['id']) && !empty($this->data[$modelClass]['id'])):?>
                    <li class="<?php echo (isset($this->params['named']['habtm']) && $this->params['named']['habtm'] == 'Responsible')?'etabs-active':'';?>"><?php echo $this->Html->link(sprintf(__('List %s'), __d('fields', 'Responsible')), array($this->data[$modelClass]['id'], 'habtm' => 'Responsible'))?></li>
                    <li class="<?php echo (isset($this->params['named']['habtm']) && $this->params['named']['habtm'] == 'Speaker')?'etabs-active':'';?>"><?php echo $this->Html->link(sprintf(__('List %s'), __d('fields', 'Speaker')), array($this->data[$modelClass]['id'], 'habtm' => 'Speaker'))?></li>
                    <li class="<?php echo (isset($this->params['named']['habtm']) && $this->params['named']['habtm'] == 'Sponsor')?'etabs-active':'';?>"><?php echo $this->Html->link(sprintf(__('List %s'), __d('fields', 'Sponsor')), array($this->data[$modelClass]['id'], 'habtm' => 'Sponsor'))?></li>
                <?php endif?>
                </ul>       
        </header>
        <div style="display:<?php echo (isset($this->params['named']['habtm']))?'none':'block'?>;" class="etabs-content" id="<?php echo $modelClass?>"> 
            <?php echo $this->AppForm->create($modelClass, array('defaultSize' => 'g_1_4'))?>
            <?php echo $this->element('toolbar-edit')?>
            <div class="inner-spacer set-cells">

            
                <?php 
                /**
                * Insere os IDs ocultos de relacionamento entre os models HasAndBelongsToMany
                */
                if(isset($this->params['named']['habtm']) && !empty($this->params['named']['habtm'])){
                    echo $this->element('habtm-hidden', array('habtmModel' => $this->params['named']['habtm']));
                }
                ?>
                
				<?php echo $this->Form->hidden('id')?>

				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('city_id')?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('people')?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('name')?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('about')?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<div class="g_1_4">
					<label for="mask_date"><?php echo __d('fields', 'Interval')?></label>
				</div> 
				<div class="g_3_4_last">
				    <?php echo $this->AppForm->input('date_ini', array('template' => 'input-clean'))?>
				    <?php echo $this->AppForm->input('date_ini_time', array('template' => 'input-clean', 'class' => 'msk-hour', 'placeholder' => __d('fields', 'Hour Ini')))?>
				    <?php echo $this->AppForm->input('date_end_time', array('template' => 'input-clean', 'class' => 'msk-hour', 'placeholder' => __d('fields', 'Hour End')))?>
				    <?php echo $this->AppForm->input('date_end', array('template' => 'input-clean'))?>
				</div>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('address')?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('neighborhood')?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('facebook_link')?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('twitter_link')?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('google_link')?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
            </div>   
            <?php echo $this->AppForm->end()?>
  
        </div>

    
        <?php 
        /**
        * Insere os formulários de relacionamento entre os models HasAndBelongsToMany
        */
        if(isset($this->params['named']['habtm']) && !empty($this->params['named']['habtm'])){
            echo $this->element('habtm', array('habtmModel' => $this->params['named']['habtm']));
        }
        ?>
        
    </div><!-- End .powerwidget -->
</section>
