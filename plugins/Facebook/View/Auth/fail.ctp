<div id="error-container">
    <div class="error-box">
    	<hgroup>
    		<?php echo $this->AppForm->separator()?>
        	<?php echo $this->Html->image('google_logo_41.png')?>
            <h2>Autorização Negada</h2>
        </hgroup>
        <p>
        Não foi possível autênticar o seu usuário do google, por favor tente mais tarde.
        </p>
        <div class="line"></div>
        <div class="ctrls">
        	<?php echo $this->Html->link('<span class="longarrow-left-10 plix-10"></span> Voltar', array('controller' => 'users', 'action' => 'login', 'plugin' => false), array('class' => 'button-icon-text', 'escape' => false))?>
        </div>
    </div>
</div>