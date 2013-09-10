<?php $this->assign('title', 'Gerenciamento de Permissões');?>
<?php $this->assign('description', 'Esta página permite que você gerencie facilmente as permissões de grupos e usuários do sistema.');?>

<section class="g_1">
    <!-- New widget -->
    <?php echo $this->AppForm->create('Perms');?>
    <div class="powerwidget">
        <header>
            <h2>Gerenciamento de permissões por <strong><?php echo (isset($this->params['named']['aro']))?__($this->params['named']['aro']):__('Group');?></strong></h2>
        </header>
        <div> 

			<?php echo $this->element('toolbar-permissions')?>

			<div class="inner-spacer"> 
				<div class="appointment-planner">


					<?php 
					/**
					* Completa o quadro de permissoes caso existam menos que 5 grupos
					*/
					$colsEmpty = 5 - count($aros);
					if($colsEmpty > 0){
						for($i=0; $i<$colsEmpty; $i++){
							$aros[count($aros)][$aroAlias]['name'] = 'empty';
						}
					}
					?>
					<!-- INICIO DO CABEÇALHO/GRUPOS -->
					<div class="ap-header-row">
						<div class="day-block-header-empty"><div></div></div>
						<?php foreach ($aros as $k => $v): ?>
						<?php if(isset($v[$aroAlias]['id'])):?>
							<?php $ico_allow = $this->Html->link(
																$this->Html->image('/Main/img/allow.png', array('alt' => 'permitir')), 
																'#', 
																array('title' => __('Permitir'), 'class' => 'bulkActionColumnPermission', 'id' => $v[$aroAlias]['id'], 'rel' => 'allow', 'escape' => false)
																) 
																. '&nbsp;'?>
							<?php $ico_deny = $this->Html->link(
																$this->Html->image('/Main/img/deny.png', array('alt' => 'negar')), 
																'#', 
																array('title' => __('Negar'), 'class' => 'bulkActionColumnPermission', 'id' => $v[$aroAlias]['id'], 'rel' => 'deny', 'escape' => false)
																) 
																. '&nbsp;'?>
							<?php $ico_inherit = $this->Html->link(
																$this->Html->image('/Main/img/inherit.png', array('alt' => 'Limpar permissões')), 
																'#', 
																array('title' => __('Limpar permissões'), 'class' => 'bulkActionColumnPermission', 'id' => $v[$aroAlias]['id'], 'rel' => 'inherit', 'escape' => false)
																) 
																. '&nbsp;'?>

							<div class="day-block-header">
								<div>
									<h3><?php echo __(h($v[$aroAlias][$aroDisplayField]))?></h3>
									<p><?php echo $ico_allow . $ico_deny . $ico_inherit?></p>
								</div>
							</div>
						<?php else:?>
							<div class="day-block-header disabled-block">
								<div>&nbsp;</div>
							</div>
						<?php endif?>

						<?php endforeach; ?>
					</div>
					<!-- FIM DO CABEÇALHO -->



					<!-- INICIO DA GRID/ACOES -->
					<?php $uglyIdent = Configure::read('AclManager.uglyIdent'); ?>
					<?php array_shift($acos)?>
					<?php foreach ($acos as $id => $aco):?>
						<?php 
						/**
						* Oculta as acoes/actions dos plugins da framework caso o usuario logado nao seja MASTER
						*/
						$hidden = (preg_match('/google|facebook|debugkit/si', $aco['Action']) && $this->Session->read('Auth.User.id') != ADMIN_USER);
						?>					
						<div style="display:<?php echo $hidden?'none':'block'?>;" class="ap-time-row">    
							<?php $action = $aco['Action']?>
							<?php $alias = __($aco['Aco']['alias'])?>
							<?php $ident = substr_count($action, '/')?>

							<?php $titleAction = ($ident == 1 ? "&raquo;<strong>" : "" ) . ($uglyIdent ? str_repeat("&nbsp;&nbsp;", $ident) : "") . h($alias) . ($ident == 1 ? "</strong>" : "" )?>
							<div class="time-block"><div><?php echo $titleAction?></div></div>

							<?php foreach ($aros as $k => $v): ?>
								<?php if(isset($v[$aroAlias]['id'])):?>
									<?php $inherit = $this->Form->value("Perms." . str_replace("/", ":", $action) . ".{$aroAlias}:{$v[$aroAlias]['id']}-inherit")?>
									<?php $allowed = $this->Form->value("Perms." . str_replace("/", ":", $action) . ".{$aroAlias}:{$v[$aroAlias]['id']}")?>
									<?php

									/**
									* Libera todos os acessos aos plugins caso os mesmos estejam ocultos
									*/
									if(preg_match('/google|facebook|debugkit/si', $aco['Action'])){
										$inherit = false;
										$allowed = true;
									}		

									$inherit_selected = '';
									$allowed_selected = '';
									$deny_selected = '';

									if($inherit){
										$inherit_selected = 'custom-grey';
										$value = "inherit";
										$txt_info = 'Herdar';
										$color = 'grey';
									}else if($allowed){
										$allowed_selected = 'custom-green';
										$value = "allow";
										$txt_info = 'Permitir';
										$color = 'green';
									}else{
										$deny_selected = 'custom-red';
										$value = "deny";
										$txt_info = 'Negar';
										$color = 'red';
									}
									?>


									<div style="display:<?php echo $hidden?'none':'block'?>;" class="day-block">
										<div>
											<?php echo $this->Form->input("Perms." . str_replace("/", ":", $action) . ".{$aroAlias}:{$v[$aroAlias]['id']}", array('type' => 'hidden'))?>

											<div class="multibar">    
	                                            <div>
	                                            <a rel="allow" style="border:none; border-right:1px solid #999999;" class="<?php echo $allowed_selected?> permsBtn-<?php echo $v[$aroAlias]['id']?>" href="javascript:void(0);"><span class="plus-10 plix-10"></span></a>
	                                            <a rel="deny" style="border:none; border-right:1px solid #999999;" class="<?php echo $deny_selected?> permsBtn-<?php echo $v[$aroAlias]['id']?>" href="javascript:void(0);"><span class="delete-10 plix-10"></span></a>
	                                            <a rel="inherit" style="border:none; border-right:1px solid #999999;" class="<?php echo $inherit_selected?> permsBtn-<?php echo $v[$aroAlias]['id']?>" href="javascript:void(0);"><span class="rows2-10 plix-10"></span></a>
	                                            <span class="txt-info" style="margin-left:3px; color:<?php echo $color?>;"><?php echo $txt_info?></span>
	                                            </div>
	                                        </div>
										</div>
									</div>										
								<?php else:?>

									<div class="day-block disabled-block">
										<div>&nbsp;</div>
									</div>	

								<?php endif?>
							<?php endforeach?>
						</div>
					<?php endforeach?>







					
				</div>
			</div>
		</div>
	</div>
	<?php echo $this->AppForm->end();?>
</section>
