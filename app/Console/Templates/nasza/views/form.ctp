 <?php if($action == 'edit'):?>
<section class="g_1">
    <!-- New widget -->
    <div class="e-block">
        <header>
            <ul class="etabs">
                <?php echo "<?php \$id = (isset(\$this->data[\$modelClass]['id']) && !empty(\$this->data[\$modelClass]['id']))?\$this->data[\$modelClass]['id']:null;?>\n"?>
                <li class="<?php echo "<?php echo (isset(\$this->params['named']['habtm']))?'':'etabs-active';?>"?>"><?php echo "<?php echo \$this->Html->link(sprintf(__('Edit %s'), __d('fields', \$modelClass)), array(\$id))?>"?></li>
    <?php if (!empty($associations['hasAndBelongsToMany'])):?>
        <?php echo "<?php if(isset(\$this->data['{$modelClass}']['id']) && !empty(\$this->data['{$modelClass}']['id'])):?>\n"?>
        <?php foreach ($associations['hasAndBelongsToMany'] as $k => $v):?>
            <li class="<?php echo "<?php echo (isset(\$this->params['named']['habtm']) && \$this->params['named']['habtm'] == '{$k}')?'etabs-active':'';?>"?>"><?php echo "<?php echo \$this->Html->link(sprintf(__('List %s'), __d('fields', '{$k}')), array(\$this->data[\$modelClass]['id'], 'habtm' => '{$k}'))?>"?></li>
        <?php endforeach?>
        <?php echo "<?php endif?>\n"?>
    <?php endif?>
            </ul>       
        </header>
        <div style="display:<?php echo "<?php echo (isset(\$this->params['named']['habtm']))?'none':'block'?>"?>;" class="etabs-content" id="<?php echo "<?php echo \$modelClass?>"?>"> 
            <?php echo "<?php echo \$this->AppForm->create(\$modelClass, array('defaultSize' => 'g_1_4'))?>\n"?>
            <?php echo "<?php echo \$this->element('toolbar-edit')?>\n";?>
            <div class="inner-spacer set-cells">

            <?php 
        if (!empty($associations['hasAndBelongsToMany'])){
            echo "
                <?php 
                /**
                * Insere os IDs ocultos de relacionamento entre os models HasAndBelongsToMany
                */
                if(isset(\$this->params['named']['habtm']) && !empty(\$this->params['named']['habtm'])){
                    echo \$this->element('habtm-hidden', array('habtmModel' => \$this->params['named']['habtm']));
                }
                ?>
                ";
        }


                foreach ($fields as $field) {
                    if ($field == $primaryKey) {
                    	echo "\n\t\t\t\t<?php echo \$this->Form->hidden('{$field}')?>\n\n";
                    } elseif (!in_array($field, array('created', 'modified', 'updated', 'trashed', 'deleted'))) {
                    	echo "\t\t\t\t<?php echo \$this->AppForm->separator()?>\n";
                        echo "\t\t\t\t<?php echo \$this->AppForm->input('{$field}')?>\n";
                    	echo "\t\t\t\t<?php echo \$this->AppForm->separator()?>\n";
                        echo "\t\t\t\t<hr/>\n";
                    }
                }
            ?>
            </div>   
            <?php echo "<?php echo \$this->AppForm->end()?>\n"?>  
        </div>

    <?php 
if (!empty($associations['hasAndBelongsToMany'])){
    echo "
        <?php 
        /**
        * Insere os formulários de relacionamento entre os models HasAndBelongsToMany
        */
        if(isset(\$this->params['named']['habtm']) && !empty(\$this->params['named']['habtm'])){
            echo \$this->element('habtm', array('habtmModel' => \$this->params['named']['habtm']));
        }
        ?>
        ";
}
    ?>

    </div><!-- End .powerwidget -->
</section>
<?php endif?>