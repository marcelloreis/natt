<?php if(isset($this->params['named']['habtm']) && $this->params['named']['habtm'] == $habtmModel):?>
    <div style="display:<?php echo (isset($this->params['named']['habtm']) && $this->params['named']['habtm'] == $habtmModel)?'block':'none';?>;" class="etabs-content" id="<?php echo $habtmModel?>">
        <?php echo $this->AppForm->create($modelClass)?>
            <?php echo $this->Form->hidden('id')?>
            <?php echo $this->Form->hidden('habtm', array('value' => $habtmModel))?>
            <div class="powerwidget powerwidget-habtm" role="widget">
                <div role="content">
                    <!-- .toolbar -->         
                    <?php echo $this->assign('toolbar-index-bulkAction', "<option value=\"{$this->Html->url(array('action' => 'unjoin'))}\">" . __d('app', 'Unjoin') . "</option>")?>
                    <?php echo $this->assign('toolbar-index-buttons', $this->Html->link(__d('app', 'add') . ' <span class="plus-10 plix-10"></span>', '#', array('title' => __d('app', 'Add Relationship'), 'rel' => $habtmModel, 'data-source' => "/" . Inflector::pluralize(strtolower($habtmModel)) . "/index/fkbox:habtm/habtmModel:{$modelClass}/habtmId:{$this->data[$modelClass]['id']}", 'class' => "fk-box reload button-text-icon tip-s", 'escape' => false)))?>
                    <?php echo $this->element('toolbar-index', array('config' => true, 'search' => false))?>

                        <div class="table-wrapper">
                            <table id="<?php echo $habtmModel?>" class="basic-table">
                                <thead>
                                    <tr>
                                        <th width="10"><?php echo $this->AppForm->input(null, array('class' => 'e-checkbox-trigger', 'type' => 'checkbox', 'template' => 'input-clean', 'name' => null, 'value' => null))?></th>
                                        <th width="20"><?php echo __d('app', 'ID')?></th>
                                        <th><?php echo __d('app', 'Description')?></th>
                                        <th width="70"><?php echo __d('app', 'Actions')?></th> 
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- new row -->
                                    <?php foreach ($$habtmModel as $k => $v):?>
                                        <tr>
                                            <td><?php echo $this->AppForm->input("{$habtmModel}.id.{$k}", array('type' => 'checkbox', 'template' => 'input-clean', 'value' => $v[$habtmModel]['id']));?></td>
                                            <td><?php echo $v[$habtmModel]['id']?></td>
                                            <td><?php echo $students[$v[$habtmModel]['student_id']]?></td>
                                            <td><?php echo $this->element('table-actions-habtm', array('id' => $this->data[$modelClass]['id'], 'habtm_id' => $v[$habtmModel]['id']));?></td>
                                        </tr>
                                    <?php endforeach?>
                                </tbody>
                            </table>
                        </div>
                </div>
            </div>
        <?php echo $this->AppForm->end()?>
    </div>

    <div class="modal-<?php echo $habtmModel?>" title="<?php echo __d('app', Inflector::pluralize($habtmModel) . " not associated with the {$modelClass}")?>" style="display:none"></div>
<?php endif?>