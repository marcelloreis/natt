<div id="error-container">
    <div class="error-box">
    	<hgroup>
    		<?php echo $this->AppForm->separator()?>
        	<?php echo $this->Html->image('google_logo_41.png')?>
            <h2>Usuário Autorizado</h2>
        </hgroup>
        <p>
        Obrigado, a sua conta foi associada ao sistema com sucesso
        </p>
        <div class="line"></div>
        <div class="ctrls">
        	<?php echo $this->Html->link('<span class="longarrow-left-10 plix-10"></span> Acessar o sistema', array('controller' => 'users', 'action' => 'home', 'plugin' => false), array('class' => 'button-icon-text', 'escape' => false))?>
        </div>
    </div>
</div>