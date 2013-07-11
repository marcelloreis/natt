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
            <?php echo $this->AppForm->create($modelClass, array('defaultSize' => 'g_1_4'))?>
            <?php echo $this->element('toolbar-edit')?>
            <div class="inner-spacer set-cells">
				<?php echo $this->Form->hidden('id')?>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('city_id')?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('name')?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('email')?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('password')?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('matriculation')?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('doc')?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('birthday')?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('telephone')?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('sex', array('type' => 'select', 'empty' => __d('app', 'Select'), 'options' => $sex))?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('shirt_size', array('type' => 'select', 'empty' => __d('app', 'Select'), 'options' => $shirt_size))?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('study_level', array('type' => 'select', 'empty' => __d('app', 'Select'), 'options' => $study_level))?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('zipcode', array('class' => 'msk-zipcode'))?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('instituition')?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('course')?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('course_ini')?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('course_end')?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('course_period')?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('address')?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('number')?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('complement')?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('neighborhood')?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('newsletter')?>
				<?php echo $this->AppForm->separator()?>
            </div>   
            <?php echo $this->AppForm->end()?>
        </div>
    </div><!-- End .powerwidget -->
</section>
