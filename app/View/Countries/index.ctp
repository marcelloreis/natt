<?php $this->assign('title', __(ucfirst($this->params['controller'])));?>
<?php $this->assign('toolbar-index-top', $this->element('toolbar-index', array('config' => true, 'search' => true)));?>
<?php $this->assign('toolbar-index-bottom', $this->element('toolbar-index'));?>

<div id="content-grid">
    <?php echo $this->AppGrid->create($modelClass, array('tableClass' => 'basic-table', 'id' => 'basic-table'))?>
    <thead>
        <?php $columns['id'] = $this->AppForm->input("{$modelClass}.id.trigger", array('class' => 'e-checkbox-trigger', 'type' => 'checkbox', 'template' => 'input-clean', 'value' => null))?>
        <?php $columns['action'] = __('Actions')?>
        <?php $columns['printable_name'] = __('Name')?>
        <?php unset($columns['name'])?>
        <?php echo $this->AppGrid->tr($columns)?>
    </thead>

    <tbody>
        <?php 
        $map = strtolower($modelClass);
        if(count($$map)){
            foreach($$map as $k => $v){
                $v[$modelClass]['action'] = $this->element('table-actions', array('id' => $v[$modelClass]['id']));
                $v[$modelClass]['id'] = $this->AppForm->input("{$modelClass}.id.{$k}", array('type' => 'checkbox', 'template' => 'input-clean', 'value' => $v[$modelClass]['id'], 'placeholder' => $v[$modelClass][$fieldText]));

                echo $this->AppGrid->tr($v[$modelClass]);
            };
        }
        ?>
    </tbody>

    <tfoot>
        <?php echo $this->AppGrid->tr($columns)?>
    </tfoot>

    <?php echo $this->AppGrid->end()?>
</div>

<?php 

//Responsável pela impressão do javascript
echo $this->Js->writeBuffer();

?>