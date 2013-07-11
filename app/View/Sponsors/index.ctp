<div id="content-grid">
    <?php echo $this->AppGrid->create($modelClass, array('tableClass' => 'basic-table', 'id' => 'basic-table'))?>
    <thead>
        <?php $columns['id'] = $this->AppForm->input(null, array('class' => 'e-checkbox-trigger', 'type' => 'checkbox', 'template' => 'input-clean', 'name' => null, 'value' => null))?>
        <?php $columns['action'] = __('Actions')?>
        <?php unset($columns['about'])?>
        <?php unset($columns['type'])?>
        <?php echo $this->AppGrid->tr($columns)?>
    </thead>

    <tbody>
	<?php
        $map = strtolower($modelClass);
        if(count($$map)){
            foreach($$map as $k => $v){
                //Seta as larguras das colunas
                $v[$modelClass]['avatar_width'] = '70px';

                $v[$modelClass]['action'] = $this->element('table-actions', array('id' => $v[$modelClass]['id']));
                $v[$modelClass]['id'] = $this->AppForm->input("{$modelClass}.id.{$k}", array('type' => 'checkbox', 'template' => 'input-clean', 'value' => $v[$modelClass]['id'], 'placeholder' => $v[$modelClass][$fieldText]));
                $avatar = isset($v[$modelClass]['avatar']) && !empty($v[$modelClass]['avatar'])?$v[$modelClass]['avatar']:'avatar.jpg';
                $v[$modelClass]['avatar'] = $this->Html->image($avatar, array('id' => 'main-avatar'));
                
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
