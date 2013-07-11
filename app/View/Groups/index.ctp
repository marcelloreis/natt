<?php $this->assign('title', __(ucfirst($this->params['controller'])));?>
<?php $this->assign('toolbar-index', $this->element('toolbar-index'));?>

<div id="content-grid">
    <?php echo $this->AppGrid->create($modelClass, array('tableClass' => 'basic-table', 'id' => 'basic-table'))?>
    <thead>
        <?php $columns['id'] = '<input type="checkbox" name="" class="e-checkbox-trigger"/>'?>
        <?php $columns['action'] = __('Actions')?>
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
            }
        }
        ?>
    </tbody>
    <?php echo $this->AppGrid->end(array('labelRight' => $this->Paginator->counter('Página {:page} de {:pages}, exibindo {:current} registros do total de {:count}, começando pelo registro {:start} até o {:end}')))?>
</div>

<?php 

/**
* Carrega todos os scripts de javascripts criados ate o momento
*/
echo $this->Js->writeBuffer();
?>