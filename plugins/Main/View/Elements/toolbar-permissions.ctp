<div class="toolbar">
    <div class="left">
       <?php $aroModels = Configure::read("AclManager.aros")?>
       <?php if ($aroModels > 1): ?>
       <?php foreach ($aroModels as $aroModel): 
                switch ($aroModel) {
                  case 'Group':
                    $icon = '<span class="entypo-rain"></span>';
                    break;
                  case 'User':
                    $icon = '<span class="entypo-users"></span>';
                    break;
                }

              echo $this->Html->link($icon, array('aro' => $aroModel), array('escape' => false, 'class' => 'entypo-button entypo-16 tip-s', 'title' => sprintf(__('Manage by %s'), __d('fields', $aroModel))));
       ?>
<?php endforeach; ?>
<?php endif; ?>

<span class="separator"><!-- seperator --></span>     
<?php 
echo $this->Html->link('<span class="entypo-cross-round"></span>', array('action' => 'drop'), array('escape' => false, 'class' => 'entypo-button entypo-16 tip-s', 'title' => sprintf(__('Delete %s and %s'), __d('fields', 'Users'), __d('fields', 'Groups')), 'onclick' => "return confirm('" . sprintf(__('Are you sure you want to delete all %s and %s?'), __d('fields', 'Users'), __('Controllers')) . "');"));
echo $this->Html->link('<span class="entypo-forbidden"></span>', array('action' => 'drop_perms'), array('escape' => false, 'class' => 'entypo-button entypo-16 tip-s', 'title' => sprintf(__('Delete %s'), __('Permissions')), 'onclick' => "return confirm('" . sprintf(__('Are you sure you want to delete all %s?'), __('Permissions')) . "');"));
?>
<span class="separator"><!-- seperator --></span>     
<?php 
echo $this->Html->link('<span class="entypo-add-user"></span>', array('action' => 'update_aros'), array('escape' => false, 'class' => 'entypo-button entypo-16 tip-s', 'title' => sprintf(__('Refresh %s and %s'), __d('fields', 'Users'), __d('fields', 'Groups'))));
echo $this->Html->link('<span class="entypo-list-add"></span>', array('action' => 'update_acos'), array('escape' => false, 'class' => 'entypo-button entypo-16 tip-s', 'title' => sprintf(__('Refresh %s'), __('Controllers'))));
?>


<?php echo $this->element('toolbar-index-paginator')?>
</div><!-- End .left -->
<div class="right">
   <select name="bulkActionPermission" class="bulk-actions">
      <option value=""><?php echo __('Ações em massa')?></option>
      <option value="allow"><?php echo __('Permitir a todos')?></option>
      <option value="deny"><?php echo __('Negar a todos')?></option>
      <option value="inherit"><?php echo __('Limpar permissões')?></option>
  </select>
  <input type="button" value="<?php echo __('Aplicar')?>" class="button-text bulkActionPermission"/>

  <span class="separator"><!-- seperator --></span>     

  <input type="submit" value="<?php echo __('Salvar')?>" class="button-text tip-s" title="<?php echo __('Salvar todas as alterações')?>"/>
</div><!-- End .right -->
            </div><!-- End .toolbar -->