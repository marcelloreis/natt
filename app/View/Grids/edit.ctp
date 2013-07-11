<section class="g_1">
    <!-- New widget -->
    <div class="e-block">
        <header>
            <ul class="etabs">
                <?php $id = (isset($this->data['Grid']['id']) && !empty($this->data['Grid']['id']))?$this->data['Grid']['id']:null;?>
                <li class="<?php echo (isset($this->params['named']['habtm']))?'':'etabs-active';?>"><?php echo $this->Html->link(sprintf(__('Edit %s'), __d('fields', $modelClass)), array($id))?></li>
            	<?php if(isset($this->data['Grid']['id']) && !empty($this->data['Grid']['id'])):?>
                    <li class="<?php echo (isset($this->params['named']['habtm']) && $this->params['named']['habtm'] == 'Inscription')?'etabs-active':'';?>"><?php echo $this->Html->link(sprintf(__d('app', 'List %s'), __d('fields', 'Inscription')), array($this->data['Grid']['id'], 'habtm' => 'Inscription'))?></li>
                <?php endif?>
                </ul>       
        </header>
        <div style="display:<?php echo (isset($this->params['named']['habtm']))?'none':'block'?>;" class="etabs-content" id="Grid"> 
            <?php echo $this->AppForm->create($modelClass, array('defaultSize' => 'g_1_4'))?>
            <?php echo $this->element('toolbar-edit')?>
            <div class="inner-spacer set-cells">
				<!-- Inscription associados ao Grid -->
				<?php echo $this->element('habtm-hidden', array('habtmModel' => 'Inscription'))?>

				<?php echo $this->Form->hidden('id')?>

				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('event_id')?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('workshop_id')?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('speaker_id')?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('vacancies')?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('available')?>
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
				<?php echo $this->AppForm->input('description')?>
				<?php echo $this->AppForm->separator()?>
            </div>   
            <?php echo $this->AppForm->end()?>
  
        </div>
		<!-- Inscription -->
		<?php echo $this->element('app/habtm-grid', array('habtmModel' => 'Inscription'))?>

    
    </div><!-- End .powerwidget -->
</section>

