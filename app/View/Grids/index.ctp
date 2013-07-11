<div id="content-grid">
    <?php echo $this->AppGrid->create($modelClass, array('tableClass' => 'basic-table', 'id' => 'basic-table'))?>
    <thead>
        <?php $columns['id'] = $this->AppForm->input(null, array('class' => 'e-checkbox-trigger', 'type' => 'checkbox', 'template' => 'input-clean', 'name' => null, 'value' => null))?>
        <?php $columns['action'] = __('Actions')?>
        <?php $columns['date_ini_time'] = __d('app', 'Hour Ini')?>
        <?php $columns['date_end_time'] = __d('app', 'Hour End')?>
        <?php unset($columns['description'])?>
        <?php unset($columns['date_ini'])?>
        <?php unset($columns['date_end'])?>
        <?php echo $this->AppGrid->tr($columns)?>
    </thead>

    <tbody>
	<?php
        $map = strtolower($modelClass);
        if(count($$map)){
            foreach($$map as $k => $v){
                $v[$modelClass]['action'] = $this->element('table-actions', array('id' => $v[$modelClass]['id']));
                $v[$modelClass]['id'] = $this->AppForm->input("{$modelClass}.id.{$k}", array('type' => 'checkbox', 'template' => 'input-clean', 'value' => $v[$modelClass]['id'], 'placeholder' => $v[$modelClass][$fieldText]));
                $v[$modelClass]['workshop_id'] = $v['Workshop']['name'];
                $v[$modelClass]['event_id'] = $v['Event']['name'];
                $v[$modelClass]['speaker_id'] = $v['Speaker']['name'];

                echo $this->AppGrid->tr($v[$modelClass]);
            }
        }
        ?> 
    </tbody>

    <tfoot>
        <?php echo $this->AppGrid->tr($columns)?>
    </tfoot>

    <?php echo $this->AppGrid->end()?>
</div>

<?php
/**
* Carrega todos os scripts de javascripts criados ate o momento
*/
echo $this->Js->writeBuffer();
?>
