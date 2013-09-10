<div id="error-container">
    <div class="error-box">
    	<hgroup>
    		<?php echo $this->AppForm->separator()?>
        	<?php echo $this->Html->image('google_logo_41.png')?>
            <h2>Sem Autorização</h2>
        </hgroup>
        <p>
            Este sistema contem integrações com alguns aplicativos do google.
            Por favor, informe seu login e senha para que a integração seja bem sucedida.
        </p>
        <div class="line"></div>
        <div class="ctrls">
        	<?php echo $this->Html->link('Acessar conta google <span class="longarrow-right-10 plix-10"></span>', $authUrl, array('class' => 'button-text-icon last', 'escape' => false))?>
        </div>
    </div>
</div>