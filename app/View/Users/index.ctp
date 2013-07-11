<?php $this->assign('title', __(ucfirst($this->params['controller'])));?>
<?php $this->assign('toolbar-index', $this->element('toolbar-index'));?>

<div id="content-grid">
    <!-- New widget -->
    <?php echo $this->AppGrid->create($modelClass, array('tableClass' => 'basic-table', 'id' => 'basic-table'))?>
    <thead>
        <?php $columns['id'] = '<input type="checkbox" name="" class="e-checkbox-trigger"/>'?>
        <?php $columns['action'] = __('Actions')?>
        <?php unset($columns['password'])?>
		<?php unset($columns['google_token'])?>
        <?php unset($columns['google_calendar_key'])?>
		<?php unset($columns['given_name'])?>
        <?php echo $this->AppGrid->tr($columns)?>
    </thead>

    <tbody>
        <?php 
        $map = strtolower($modelClass);
        if(count($$map)){
            foreach($$map as $k => $v){
                //Seta as larguras das colunas
                $v[$modelClass]['picture_width'] = '70px';
                
                $v[$modelClass]['action'] = $this->element('table-actions', array('id' => $v[$modelClass]['id']));
                $v[$modelClass]['id'] = $this->AppForm->input("{$modelClass}.id.{$k}", array('type' => 'checkbox', 'template' => 'input-clean', 'value' => $v[$modelClass]['id'], 'placeholder' => $v[$modelClass][$fieldText]));
                $v[$modelClass]['group_id'] = $v['Group']['name'];
                $v[$modelClass]['status'] = $this->AppUtils->boolTxt($v[$modelClass]['status'], 'Ativo', 'Inativo');
                $avatar = isset($v[$modelClass]['picture']) && !empty($v[$modelClass]['picture'])?$v[$modelClass]['picture']:'avatar.jpg';
                $v[$modelClass]['picture'] = $this->Html->image($avatar, array('id' => 'main-avatar'));
                echo $this->AppGrid->tr($v[$modelClass]);
            };
        }
        ?>
    </tbody>
    <?php echo $this->AppGrid->end(array('labelRight' => $this->Paginator->counter('Página {:page} de {:pages}, exibindo {:current} registros do total de {:count}, começando pelo registro {:start} até o {:end}')))?>
</div>

<?php 

//Responsável pela impressão do javascript
echo $this->Js->writeBuffer();

?>