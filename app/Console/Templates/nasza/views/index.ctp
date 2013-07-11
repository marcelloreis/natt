<div id="content-grid">
    <?php echo "<?php echo \$this->AppGrid->create(\$modelClass, array('tableClass' => 'basic-table', 'id' => 'basic-table'))?>\n" ?>
    <thead>
        <?php echo "<?php \$columns['id'] = \$this->AppForm->input(null, array('class' => 'e-checkbox-trigger', 'type' => 'checkbox', 'template' => 'input-clean', 'name' => null, 'value' => null))?>\n"?>
        <?php echo "<?php \$columns['action'] = __('Actions')?>\n" ?>
        <?php echo "<?php echo \$this->AppGrid->tr(\$columns)?>\n" ?>
    </thead>

    <tbody>
<?php echo "\t<?php
        \$map = strtolower(\$modelClass);
        if(count(\$\$map)){
            foreach(\$\$map as \$k => \$v){
                \$v[\$modelClass]['action'] = \$this->element('table-actions', array('id' => \$v[\$modelClass]['id']));
                \$v[\$modelClass]['id'] = \$this->AppForm->input(\"{\$modelClass}.id.{\$k}\", array('type' => 'checkbox', 'template' => 'input-clean', 'value' => \$v[\$modelClass]['id'], 'placeholder' => \$v[\$modelClass][\$fieldText]));
                ";

    if(isset($associations['belongsTo'])){
        foreach ($associations['belongsTo'] as $k => $v) {
            echo "\$v[\$modelClass]['{$v['foreignKey']}'] = \$v['{$k}']['name'];\n";
        }
    }
        echo "
                echo \$this->AppGrid->tr(\$v[\$modelClass]);
            }
        }
        ?>";
?> 
    </tbody>

    <tfoot>
        <?php echo "<?php echo \$this->AppGrid->tr(\$columns)?>\n"?>
    </tfoot>

    <?php echo "<?php echo \$this->AppGrid->end()?>\n" ?>
</div>

<?php 
echo "<?php
/**
* Carrega todos os scripts de javascripts criados ate o momento
*/
echo \$this->Js->writeBuffer();
?>\n";
?>