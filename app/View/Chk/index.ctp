<?php $this->assign('title', __($title_view))?>

<div id="content-grid">
    <?php echo $this->AppGrid->create($modelClass, array('tableClass' => 'basic-table', 'id' => 'basic-table'))?>
    <thead>
        <?php //$columns['id'] = $this->AppForm->input(null, array('class' => 'e-checkbox-trigger', 'type' => 'checkbox', 'template' => 'input-clean', 'name' => null, 'value' => null))?>
        <?php $columns['action'] = __('Actions')?>
        <?php $columns['qt_processed'] = 'Registros'?>
        <?php $columns['client_id'] = 'Cliente'?>
        <?php unset($columns['id'])?>
        <?php unset($columns['filename'])?>
        <?php unset($columns['qt_landline'])?>
        <?php unset($columns['qt_addresses'])?>
        <?php unset($columns['qt_inconsistent'])?>
        <?php unset($columns['qt_not_found'])?>
        <?php unset($columns['qt_mobile'])?>
        <?php unset($columns['qt_obito'])?>
        <?php unset($columns['status'])?>
        <?php 
        if(isset($this->params['named']['processed']) && $this->params['named']['processed'] == 1){
            $columns['ini_process'] = 'Inicio';
            $columns['end_process'] = 'Fim';
        }else{
            unset($columns['ini_process']);
            unset($columns['end_process']);
        }
        ?>
        <?php echo $this->AppGrid->tr($columns)?>
    </thead>

    <tbody>
	<?php
        $map = strtolower($modelClass);
        if(count($$map)){
            foreach($$map as $k => $v){
                $v[$modelClass]['action'] = $this->element('project/table-actions-chk', array('id' => $v[$modelClass]['id']));
                // $v[$modelClass]['id'] = $this->AppForm->input("{$modelClass}.id.{$k}", array('type' => 'checkbox', 'template' => 'input-clean', 'value' => $v[$modelClass]['id'], 'placeholder' => $v[$modelClass][$fieldText]));
                $v[$modelClass]['client_id'] = $clientes[$v['Chk']['client_id']];
                $v[$modelClass]['qt_processed'] = number_format($v['Chk']['qt_processed'], 0, '', '.');

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
