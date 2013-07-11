<a href="javascript:void(0);">
	<span class="arrow-down-10 plix-10"></span>
</a> 
<?php $avatar = isset($userLogged['picture']) && !empty($userLogged['picture'])?$userLogged['picture']:'avatar.jpg';?>
<?php echo $this->Html->image($avatar)?>

<div>
	<ul>
		<li><?php echo $this->Html->link('<span class="settings-10 plix-10"></span>' . __('Settings'), array('controller' => 'users', 'action' => 'edit', $userLogged['id'], 'plugin' => null), array('escape' => false))?></li>
		<li><?php echo $this->Html->link('<span class="info-10 plix-10"></span>' . __('Logout'), array('controller' => 'users', 'action' => 'logout', 'plugin' => null), array('escape' => false))?></li>
	</ul>                                      
</div> 