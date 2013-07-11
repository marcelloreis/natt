 <section class="g_1">
    <!-- New widget -->
    <div class="e-block">
        <header>
            <ul class="etabs">
                <?php $id = (isset($this->data[$modelClass]['id']) && !empty($this->data[$modelClass]['id']))?$this->data[$modelClass]['id']:null;?>
                    <li class="<?php echo (isset($this->params['named']['habtm']))?'':'etabs-active';?>"><?php echo $this->Html->link(sprintf(__('Edit %s'), __d('fields', $modelClass)), array($id))?></li>
            	<?php if(isset($this->data[$modelClass]['id']) && !empty($this->data[$modelClass]['id'])):?>
                    <li class="<?php echo (isset($this->params['named']['habtm']) && $this->params['named']['habtm'] == 'Student')?'etabs-active':'';?>"><?php echo $this->Html->link(sprintf(__('List %s'), __d('fields', 'Student')), array($this->data[$modelClass]['id'], 'habtm' => 'Student'))?></li>
                <?php endif?>                </ul>       
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
				<?php echo $this->AppForm->input('event_id')?>
				<?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('subject')?>
				<?php echo $this->AppForm->separator()?>
                <hr/>
                <?php echo $this->AppForm->separator()?>
                <?php echo $this->AppForm->input('content', array('template' => 'input-editor', 'id' => 'wysihtml5-textarea'))?>
                <?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->separator()?>
                <div class="g_1_4">
                    <label>Tags Disponíveis</label>
                </div>                
                <div class="g_3_4_last">
                     <hr/>
                     <a href="#" class="tag darkblue"><?php echo __d('app', 'Student')?></a>
                     <hr/>
                     <a href="#" title="<?php echo __d('app', "Student's Name")?>" class="tag tip-s">%st_name%</a>
                     <a href="#" title="<?php echo __d('app', "Student Email")?>" class="tag tip-s">%st_email%</a>
                     <a href="#" title="<?php echo __d('app', "Student Enrollment")?>" class="tag tip-s">%st_matriculation%</a> 
                     <a href="#" title="<?php echo __d('app', "Document Student")?>" class="tag tip-s">%st_doc%</a>
                     <a href="#" title="<?php echo __d('app', "Birthday Student")?>" class="tag tip-s">%st_birthday%</a>
                     <a href="#" title="<?php echo __d('app', "Student's Telephone")?>" class="tag tip-s">%st_telephone%</a>
                     <a href="#" title="<?php echo __d('app', "Student Sex")?>" class="tag tip-s">%st_sex%</a>
                     <a href="#" title="<?php echo __d('app', "Clothing Size Student")?>" class="tag tip-s">%st_shirt_size%</a>
                     <a href="#" title="<?php echo __d('app', "Address Student")?>" class="tag tip-s">%st_address%</a>
                     <a href="#" title="<?php echo __d('app', "Student's Complement")?>" class="tag tip-s">%st_complement%</a> 
                     <a href="#" title="<?php echo __d('app', "Neighborhood Student")?>" class="tag tip-s">%st_neighborhood%</a>
                     <a href="#" title="<?php echo __d('app', "Educational level of the student")?>" class="tag tip-s">%st_study_level%</a>
                     <hr/>
                     <a href="#" class="tag darkblue"><?php echo __d('app', 'Event')?></a>
                     <hr/>
                     <a href="#" title="<?php echo __d('app', "Event Name")?>" class="tag tip-s">%ev_name%</a>
                     <a href="#" title="<?php echo __d('app', "About Event")?>" class="tag tip-s">%ev_about%</a>
                     <a href="#" title="<?php echo __d('app', "Date of start of the event")?>" class="tag tip-s">%ev_date_ini</a> 
                     <a href="#" title="<?php echo __d('app', "Date of the end of the event")?>" class="tag tip-s">%ev_date_end%</a>
                     <a href="#" title="<?php echo __d('app', "Event Address")?>" class="tag tip-s">%ev_address%</a>
                     <a href="#" title="<?php echo __d('app', "Neighbothood Event")?>" class="tag tip-s">%ev_neighborhood%</a>
                     <a href="#" title="<?php echo __d('app', "Facebook Link")?>" class="tag tip-s">%ev_facebook_link%</a>
                     <a href="#" title="<?php echo __d('app', "Twitter Link")?>" class="tag tip-s">%ev_twitter_link%</a>
                     <a href="#" title="<?php echo __d('app', "Google Link")?>" class="tag tip-s">%ev_google_link%</a>
                </div>                 
				<?php echo $this->AppForm->separator()?>
                <hr/>
                <?php echo $this->AppForm->separator()?>
                <?php echo $this->AppForm->input('attached', array('type' => 'file'))?>
                <?php echo $this->AppForm->separator()?>
				<hr/>
				<?php echo $this->AppForm->separator()?>
				<?php echo $this->AppForm->input('Send')?>
				<?php echo $this->AppForm->separator()?>
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
