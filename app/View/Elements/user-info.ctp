<?php if(isset($userLogged)):?>
	<div id="sidebar-profile">
        <?php 
        //Inicializa a varivel $padding considerando que o usuario logado nao terá um avatar cadastrado
        $padding = 'padding:0px;';
        ?>
        <?php if(isset($userLogged['picture']) && !empty($userLogged['picture'])):?>
        <?php 
        //Retorna o style ao estado padrao de um usuario com o avatar cadastrado
        $padding = '';
        ?>
        <div id="main-avatar">
        	<!-- <span class="indicator">38</span> -->
            <?php echo $this->Html->image($userLogged['picture'])?>
        </div>
        <?php endif?>
        <div style="<?php echo $padding?>" id="profile-info">
            <div>
                <?php 
                $url = isset($userLogged['Social']['link'])?$userLogged['Social']['link']:'#';
                echo $this->Html->link("<b>{$userLogged['given_name']}</b>", $url, array('escape' => false, 'target' => '_blank'));
                ?>
                <?php echo $this->Html->link(__('Personal Information'), array('controller' => 'users', 'action' => 'edit', $userLogged['id'], 'plugin' => null))?>
                <?php echo $this->Html->link(__('Logout'), array('controller' => 'users', 'action' => 'logout', 'plugin' => null))?>
            </div>
        </div>
    </div>
<?php endif;?>