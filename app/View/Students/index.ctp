<div id="content-grid">
    <?php echo $this->AppGrid->create($modelClass, array('tableClass' => 'basic-table', 'id' => 'basic-table'))?>
    <thead>
        <?php $columns['id'] = $this->AppForm->input(null, array('class' => 'e-checkbox-trigger', 'type' => 'checkbox', 'template' => 'input-clean', 'name' => null, 'value' => null))?>
        <?php $columns['action'] = __('Actions')?>
        <?php unset($columns['password'])?>
        <?php unset($columns['matriculation'])?>
        <?php unset($columns['birthday'])?>
        <?php unset($columns['neighborhood'])?>
        <?php unset($columns['study_level'])?>
        <?php unset($columns['address'])?>
        <?php unset($columns['complement'])?>
        <?php unset($columns['shirt_size'])?>
        <?php unset($columns['doc'])?>
        <?php unset($columns['city_id'])?>
        <?php unset($columns['city_ds'])?>
        <?php unset($columns['state_ds'])?>
        <?php unset($columns['institution'])?>
        <?php unset($columns['course'])?>
        <?php unset($columns['course_ini'])?>
        <?php unset($columns['course_end'])?>
        <?php unset($columns['course_period'])?>
        <?php unset($columns['number'])?>
        <?php unset($columns['zipcode'])?>
        <?php unset($columns['newsletter'])?>
        <?php echo $this->AppGrid->tr($columns)?>
    </thead>

    <tbody>
	<?php
        $map = strtolower($modelClass);
        if(count($$map)){
            foreach($$map as $k => $v){
                $v[$modelClass]['action'] = $this->element('table-actions', array('id' => $v[$modelClass]['id']));
                $v[$modelClass]['id'] = $this->AppForm->input("{$modelClass}.id.{$k}", array('type' => 'checkbox', 'template' => 'input-clean', 'value' => $v[$modelClass]['id'], 'placeholder' => $v[$modelClass][$fieldText]));
                $v[$modelClass]['sex'] = ($v[$modelClass]['sex'] == FEMALE)?__d('app', 'Female'):__d('app', 'Male');

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
