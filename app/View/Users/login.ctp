<?php 
echo $this->assign('css-login', $this->Html->css('login'));
/**
* Remove o css das abas de formulario pois estao conflitando com o css do menu
*/
echo $this->assign('etabs', '');
echo $this->assign('js-login', $this->Html->script('login'));
?>


    <div id="login-sn">
    
        <!-- Put your logo here -->
        <div id="logo">
            <h1><?php echo TITLE_APP?></h1>
        </div>
        <!-- Show a dialog -->
        <?php echo $this->Session->flash(FLASH_SESSION_LOGIN)?>
        
        <!-- The main part -->                   
        <div id="login-outher">        
            <div id="login-inner">
                <header>
                    <h2>Algum texto sobre o sistema.</h2> 
                </header>
                
                <div id="login-content">
                    <div id="login-content-inner">
                        <ul id="tab-menu" class="etabs">
                            <li><a href="#etab1"><?php echo $this->Html->image('icons/social/16/howto.png')?> Como funciona</a></li>
                            <li><a href="#etab2"><?php echo $this->Html->image('icons/social/16/login.png')?> Login</a></li>
                        </ul>        
                        <div id="etab1" class="etabs-content">
                            <div style="text-align:justify" class="g_1">
                                <h3>Como funciona o <?php echo TITLE_APP?>?</h3>
                                <p>
                                    Texto explicativo sobre o sistema <b><?php echo TITLE_APP?></b>
                                </p>
                            </div>
                        </div>

                        <div id="etab2" class="etabs-content">
                            <?php echo $this->AppForm->create('User') ?>
                                <?php 
                                $loginHomolog = TITLE_APP == 'Homolagação'?'marcelo@nasza.com.br':'';
                                $passHomolog = TITLE_APP == 'Homolagação'?'123456':'';
                                ?>
                                <?php echo $this->AppForm->input('email', array('value' => $loginHomolog, 'template' => 'input-login', 'class' => 'email', 'placeholder' => 'E-mail', 'tabindex' => "1", 'data-validation-type' => "email")) ?>
                                <div class="spacer-10"><!-- spacer 20px --></div> 
                                <?php echo $this->AppForm->input('password', array('value' => $passHomolog, 'template' => 'input-login', 'class' => 'password', 'placeholder' => 'Senha', 'tabindex' => "2", 'data-validation-type' => "present")) ?>
                                <div class="spacer-10"><!-- spacer 20px --></div> 

                                <div class="g_1">
                                    <?php echo $this->AppForm->btn('Entrar')?>
                                </div>      
                            <?php echo $this->AppForm->end(); ?>
                        </div><!-- End tab -->
                    </div><!-- End #login-content-inner --> 
                </div><!-- End #login-content --> 
            </div><!-- End #login-inner -->                                  
        </div><!-- End #login-outher --> 
        

        <!-- place your copyright text here -->
        <footer id="footer">
            Copyright © <?php echo date('Y')?> <a target="_blank" href="<?php echo COPYRIGHT_LINK?>"><?php echo COPYRIGHT?></a>
        </footer> 
    </div><!-- End "#login" -->        
